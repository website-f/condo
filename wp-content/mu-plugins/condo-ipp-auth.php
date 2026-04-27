<?php
/*
Plugin Name: Condo IPP Centralized Auth
Description: Authenticates WordPress logins (including /icp-login) against the IPP `Users` table so admin and Laravel agent share one credential store. Falls back to native WP auth for users that do not exist in IPP.
Version: 0.1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Condo_Ipp_Auth' ) ) {

    final class Condo_Ipp_Auth {

        const IPP_DB_HOST     = 'localhost';
        const IPP_DB_USER     = 'root';
        const IPP_DB_PASSWORD = 'root';
        const IPP_DB_NAME     = 'ipp_user';
        const IPP_TABLE       = 'Users';
        const IPP_DETAIL_TABLE = 'UserDetails';

        public static function boot() {
            // Priority 30: run after wp_authenticate_username_password (priority 20) but before
            // the empty-result fall-through. We supersede the default flow when IPP knows the user.
            add_filter( 'authenticate', array( __CLASS__, 'authenticate' ), 30, 3 );
        }

        public static function authenticate( $user, $username, $password ) {
            // If something earlier already produced a valid user, keep it.
            if ( $user instanceof WP_User ) {
                return $user;
            }

            $username = trim( (string) $username );
            $password = (string) $password;

            if ( $username === '' || $password === '' ) {
                return $user; // Let WP show its own empty-field error.
            }

            $ipp = self::ipp_connection();
            if ( ! $ipp ) {
                return $user; // DB unreachable -> let WP try its native flow.
            }

            $ipp_user = self::find_ipp_user( $ipp, $username );

            if ( ! $ipp_user ) {
                $ipp->close();
                return $user; // Not an IPP user -> WP native auth handles (or fails) it.
            }

            if ( ! self::verify_password( $password, (string) $ipp_user['password'] ) ) {
                $ipp->close();
                return new WP_Error(
                    'incorrect_password',
                    __( '<strong>Error:</strong> The credentials you entered are incorrect.' )
                );
            }

            // Re-store as plaintext to match the legacy app's behavior (Laravel does the same on login).
            self::sync_ipp_password_plaintext( $ipp, (int) $ipp_user['id'], $password );

            $detail = self::find_ipp_detail( $ipp, (string) $ipp_user['username'] );
            $ipp->close();

            $wp_user = self::resolve_or_create_wp_user( $ipp_user, $detail, $password );

            if ( is_wp_error( $wp_user ) ) {
                return $wp_user;
            }

            return $wp_user;
        }

        protected static function ipp_connection() {
            $mysqli = @new mysqli(
                self::IPP_DB_HOST,
                self::IPP_DB_USER,
                self::IPP_DB_PASSWORD,
                self::IPP_DB_NAME
            );

            if ( $mysqli->connect_errno ) {
                return null;
            }

            $mysqli->set_charset( 'utf8mb4' );
            return $mysqli;
        }

        protected static function find_ipp_user( mysqli $db, string $username ): ?array {
            $stmt = $db->prepare(
                'SELECT id, username, password, activated, adminaccess FROM ' . self::IPP_TABLE . ' WHERE username = ? LIMIT 1'
            );
            if ( ! $stmt ) {
                return null;
            }
            $stmt->bind_param( 's', $username );
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            return $row ?: null;
        }

        protected static function find_ipp_detail( mysqli $db, string $username ): ?array {
            $stmt = $db->prepare(
                'SELECT firstname, lastname, email FROM ' . self::IPP_DETAIL_TABLE . ' WHERE username = ? LIMIT 1'
            );
            if ( ! $stmt ) {
                return null;
            }
            $stmt->bind_param( 's', $username );
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            return $row ?: null;
        }

        /**
         * Mirror of App\Support\LegacyPassword::check (Laravel side) so the same hashes verify.
         */
        protected static function verify_password( string $plain, string $stored ): bool {
            if ( $stored === '' ) {
                return false;
            }

            $info = password_get_info( $stored );
            if ( ( $info['algoName'] ?? 'unknown' ) !== 'unknown' ) {
                return password_verify( $plain, $stored );
            }

            if ( preg_match( '/^[a-f0-9]{32}$/i', $stored ) === 1 ) {
                return hash_equals( strtolower( $stored ), md5( $plain ) );
            }

            if ( preg_match( '/^[a-f0-9]{40}$/i', $stored ) === 1 ) {
                return hash_equals( strtolower( $stored ), sha1( $plain ) );
            }

            return hash_equals( $stored, $plain );
        }

        protected static function sync_ipp_password_plaintext( mysqli $db, int $id, string $plain ): void {
            $stmt = $db->prepare( 'UPDATE ' . self::IPP_TABLE . ' SET password = ? WHERE id = ?' );
            if ( ! $stmt ) {
                return;
            }
            $stmt->bind_param( 'si', $plain, $id );
            $stmt->execute();
            $stmt->close();
        }

        /** Mirror of App\Support\LegacyText::decode — firstname/lastname are stored as u#### codepoints. */
        protected static function legacy_text_decode( $value ): string {
            $value = (string) $value;
            if ( $value === '' || preg_match( '/^(?:u[0-9a-fA-F]{4})+$/', $value ) !== 1 ) {
                return $value;
            }
            preg_match_all( '/u([0-9a-fA-F]{4})/', $value, $matches );
            $out = '';
            foreach ( $matches[1] as $hex ) {
                $cp = hexdec( $hex );
                $out .= function_exists( 'mb_chr' ) ? mb_chr( $cp, 'UTF-8' ) : html_entity_decode( '&#' . $cp . ';', ENT_QUOTES, 'UTF-8' );
            }
            return $out;
        }

        protected static function resolve_or_create_wp_user( array $ipp_user, ?array $detail, string $plain_password ) {
            $username  = (string) $ipp_user['username'];
            $email     = isset( $detail['email'] ) ? trim( (string) $detail['email'] ) : '';
            $firstname = isset( $detail['firstname'] ) ? self::legacy_text_decode( $detail['firstname'] ) : '';
            $lastname  = isset( $detail['lastname'] ) ? self::legacy_text_decode( $detail['lastname'] ) : '';
            $is_admin  = (int) ( $ipp_user['adminaccess'] ?? 0 ) === 1;

            $wp_user = get_user_by( 'login', $username );

            if ( ! $wp_user && $email !== '' ) {
                $wp_user = get_user_by( 'email', $email );
            }

            if ( ! $wp_user ) {
                $insert_email = $email !== '' ? $email : $username . '@condo.com.my';

                $user_id = wp_insert_user( array(
                    'user_login'   => $username,
                    'user_pass'    => $plain_password,
                    'user_email'   => $insert_email,
                    'first_name'   => $firstname,
                    'last_name'    => $lastname,
                    'display_name' => trim( $firstname . ' ' . $lastname ) ?: $username,
                    'role'         => $is_admin ? 'administrator' : 'editor',
                ) );

                if ( is_wp_error( $user_id ) ) {
                    return $user_id;
                }

                if ( $is_admin && is_multisite() ) {
                    grant_super_admin( $user_id );
                }

                return get_user_by( 'id', $user_id );
            }

            // Keep WP password in sync with the IPP credential the user just typed.
            wp_set_password( $plain_password, $wp_user->ID );
            $wp_user = get_user_by( 'id', $wp_user->ID ); // wp_set_password destroys cookies; reload.

            // Sync admin flag.
            if ( $is_admin && ! user_can( $wp_user, 'manage_options' ) ) {
                $wp_user->set_role( 'administrator' );
                if ( is_multisite() ) {
                    grant_super_admin( $wp_user->ID );
                }
            }

            return $wp_user;
        }
    }

    Condo_Ipp_Auth::boot();
}

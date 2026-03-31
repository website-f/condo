<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'property_community' );

/** Database username */
define( 'DB_USER', 'property_community' );

/** Database password */
define( 'DB_PASSWORD', 'bKex3NrrPKnHTF44268Y' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'xzR/.Wmi#Ic_5F5K&KY;g. BTl)$7MLbzcAC@?LRhyo7*4k!G4.TKtK9.RMNv}0X' );
define( 'SECURE_AUTH_KEY',  '<qZR!O`SvUr_7dxS%2dlVB%E2uxaA7D9S+:{E/m4hZE+:7V8qoCoOe(keg6RiTKo' );
define( 'LOGGED_IN_KEY',    ')YC`iK;F_1wM&95<>sPZV@642dQ@a=a%LLD#<W0AF}qY >eML(p#z:T&$sa$.i#T' );
define( 'NONCE_KEY',        'gbpNqMw8:FRcF?tjyuqW.(RRa#u.={z)V{km#FEyV7%[X$S)hCs/#/uPL>x_[YF<' );
define( 'AUTH_SALT',        '{]M?B}C6Z}VU^3=m~s![]Jp<6E))4M].Ou=T uqt{sjqTzRXG&!/ICAk(kdYW;wz' );
define( 'SECURE_AUTH_SALT', '2(qr|Fz4|X~Fgc~%Q1oSOLROaGRKSTmG-W+{cR>RepTr]]Lwg(9?q@9`E+33JV c' );
define( 'LOGGED_IN_SALT',   'k8%#N9+_itEp9^6$(zl%.G?mNitgraW`r~_DaWRF^$N-r3$I7hXm5r*i^mQ^-gdq' );
define( 'NONCE_SALT',       'cL5)U7~g,6Dr{3jP|%J/a4?/N#g,~B456&] }.4x+iF}tN6QHIR#Mrs)tPVKH6&o' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'com_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

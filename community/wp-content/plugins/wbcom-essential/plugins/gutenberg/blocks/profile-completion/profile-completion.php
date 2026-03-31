<?php
/**
 * Profile Completion Block Registration.
 *
 * @package WBCOM_Essential
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * Only register if BuddyPress is active.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function wbcom_essential_profile_completion_block_init() {
	// Only register if BuddyPress is active.
	if ( ! function_exists( 'buddypress' ) ) {
		return;
	}

	$build_path = WBCOM_ESSENTIAL_PATH . 'build/blocks/profile-completion/';
	if ( file_exists( $build_path . 'block.json' ) ) {
		register_block_type( $build_path );
	}
}
add_action( 'init', 'wbcom_essential_profile_completion_block_init' );

if ( ! function_exists( 'wbcom_essential_calculate_profile_completion' ) ) {
	/**
	 * Calculate profile completion percentage.
	 *
	 * @param int   $user_id         User ID.
	 * @param array $selected_groups Selected profile groups.
	 * @param array $photo_types     Photo types to check.
	 * @return array Profile completion data.
	 */
	function wbcom_essential_calculate_profile_completion( $user_id, $selected_groups, $photo_types ) {
		$progress_details       = array();
		$grand_total_fields     = 0;
		$grand_completed_fields = 0;

		// Profile Photo.
		if ( in_array( 'profile_photo', $photo_types, true ) ) {
			++$grand_total_fields;

			remove_filter( 'bp_core_avatar_default', 'reign_alter_bp_core_avatar_default', 10 );
			remove_filter( 'bp_core_default_avatar_user', 'reign_alter_bp_core_default_avatar_user', 10 );

			$is_profile_photo_uploaded = bp_get_user_has_avatar( $user_id ) ? 1 : 0;

			if ( $is_profile_photo_uploaded ) {
				++$grand_completed_fields;
			}

			$progress_details['photo_type']['profile_photo'] = array(
				'is_uploaded' => $is_profile_photo_uploaded,
				'name'        => __( 'Profile Photo', 'wbcom-essential' ),
			);
		}

		// Cover Photo.
		if ( in_array( 'cover_photo', $photo_types, true ) ) {
			++$grand_total_fields;

			$is_cover_photo_uploaded = bp_attachments_get_user_has_cover_image( $user_id ) ? 1 : 0;

			if ( $is_cover_photo_uploaded ) {
				++$grand_completed_fields;
			}

			$progress_details['photo_type']['cover_photo'] = array(
				'is_uploaded' => $is_cover_photo_uploaded,
				'name'        => __( 'Cover Photo', 'wbcom-essential' ),
			);
		}

		// Groups Fields.
		$total_fields     = 0;
		$completed_fields = 0;

		if ( function_exists( 'bp_xprofile_get_groups' ) ) {
			$profile_groups = bp_xprofile_get_groups(
				array(
					'fetch_fields'     => true,
					'fetch_field_data' => true,
					'user_id'          => $user_id,
				)
			);

			if ( ! empty( $profile_groups ) ) {
				foreach ( $profile_groups as $single_group_details ) {
					if ( empty( $single_group_details->fields ) ) {
						continue;
					}

					$group_id = $single_group_details->id;

					// Skip if not in selected groups (check both string and int types for compatibility).
					if ( ! in_array( (string) $group_id, $selected_groups, true ) && ! in_array( $group_id, $selected_groups, true ) ) {
						continue;
					}

					// Check if repeater group.
					$is_group_repeater_str = bp_xprofile_get_meta( $group_id, 'group', 'is_repeater_enabled', true );
					$is_group_repeater     = ( 'on' === $is_group_repeater_str ) ? true : false;

					$group_total_fields     = 0;
					$group_completed_fields = 0;

					foreach ( $single_group_details->fields as $group_single_field ) {
						// If repeater, only count first set.
						if ( $is_group_repeater ) {
							$field_id     = $group_single_field->id;
							$clone_number = bp_xprofile_get_meta( $field_id, 'field', '_clone_number', true );
							if ( $clone_number > 1 ) {
								continue;
							}
						}

						$field_data_value = maybe_unserialize( $group_single_field->data->value ?? '' );

						if ( ! empty( $field_data_value ) ) {
							++$group_completed_fields;
						}

						++$group_total_fields;
					}

					$grand_total_fields     += $group_total_fields;
					$grand_completed_fields += $group_completed_fields;

					// Store each group separately with its actual BP group ID.
					$progress_details['groups'][ $group_id ] = array(
						'group_name'             => $single_group_details->name,
						'group_total_fields'     => $group_total_fields,
						'group_completed_fields' => $group_completed_fields,
					);
				}
			}
		}

		$progress_details['total_fields']     = $grand_total_fields;
		$progress_details['completed_fields'] = $grand_completed_fields;

		return wbcom_essential_format_profile_progress( $progress_details );
	}
}

if ( ! function_exists( 'wbcom_essential_format_profile_progress' ) ) {
	/**
	 * Format profile progress data.
	 *
	 * @param array $user_progress_arr Raw progress data.
	 * @return array Formatted progress data.
	 */
	function wbcom_essential_format_profile_progress( $user_progress_arr ) {
		$loggedin_user_domain = bp_loggedin_user_domain();
		$profile_slug         = bp_get_profile_slug();

		// Initialize the return array to prevent undefined variable bug.
		$user_prgress_formatted = array(
			'completion_percentage' => 0,
			'groups'                => array(),
		);

		if ( $user_progress_arr['total_fields'] > 0 ) {
			// Cast to int because round() returns float, and render.php uses strict comparison (===).
			$profile_completion_percentage                   = (int) round( ( $user_progress_arr['completed_fields'] * 100 ) / $user_progress_arr['total_fields'] );
			$user_prgress_formatted['completion_percentage'] = $profile_completion_percentage;
		}

		$listing_number = 1;

		// Ensure groups key exists to prevent foreach errors.
		if ( ! isset( $user_progress_arr['groups'] ) || ! is_array( $user_progress_arr['groups'] ) ) {
			$user_progress_arr['groups'] = array();
		}

		foreach ( $user_progress_arr['groups'] as $group_id => $group_details ) {
			$group_link = trailingslashit( $loggedin_user_domain . $profile_slug . '/edit/group/' . $group_id );

			$user_prgress_formatted['groups'][] = array(
				'number'             => $listing_number,
				'label'              => $group_details['group_name'],
				'link'               => $group_link,
				'is_group_completed' => ( $group_details['group_total_fields'] === $group_details['group_completed_fields'] ) ? true : false,
				'total'              => $group_details['group_total_fields'],
				'completed'          => $group_details['group_completed_fields'],
			);

			++$listing_number;
		}

		// Profile Photo.
		if ( isset( $user_progress_arr['photo_type']['profile_photo'] ) ) {
			$change_avatar_link  = trailingslashit( $loggedin_user_domain . $profile_slug . '/change-avatar' );
			$is_profile_uploaded = ( 1 === $user_progress_arr['photo_type']['profile_photo']['is_uploaded'] );

			$user_prgress_formatted['groups'][] = array(
				'number'             => $listing_number,
				'label'              => $user_progress_arr['photo_type']['profile_photo']['name'],
				'link'               => $change_avatar_link,
				'is_group_completed' => ( $is_profile_uploaded ) ? true : false,
				'total'              => 1,
				'completed'          => ( $is_profile_uploaded ) ? 1 : 0,
			);

			++$listing_number;
		}

		// Cover Photo.
		if ( isset( $user_progress_arr['photo_type']['cover_photo'] ) ) {
			$change_cover_link = trailingslashit( $loggedin_user_domain . $profile_slug . '/change-cover-image' );
			$is_cover_uploaded = ( 1 === $user_progress_arr['photo_type']['cover_photo']['is_uploaded'] );

			$user_prgress_formatted['groups'][] = array(
				'number'             => $listing_number,
				'label'              => $user_progress_arr['photo_type']['cover_photo']['name'],
				'link'               => $change_cover_link,
				'is_group_completed' => ( $is_cover_uploaded ) ? true : false,
				'total'              => 1,
				'completed'          => ( $is_cover_uploaded ) ? 1 : 0,
			);

			++$listing_number;
		}

		return $user_prgress_formatted;
	}
}

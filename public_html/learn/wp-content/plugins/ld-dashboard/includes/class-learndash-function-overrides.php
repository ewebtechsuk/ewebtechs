<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class SharedFunctions
 *
 * @package uncanny_learndash_groups
 */
class LDD_Learndash_Function_Overrides {

	/**
	 * @var null
	 */
	private static $instance = null;


	/**
	 * @return LDD_Learndash_Function_Overrides|null
	 */
	public static function get_instance() {

		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * LDD_Learndash_Function_Overrides constructor.
	 */
	public function __construct() {}

	/**
	 * @param $transient
	 *
	 * @return mixed
	 */
	public static function get_ld_transient( $transient ) {
		return get_transient( $transient );
	}

	/**
	 * @param $transient
	 * @param $data
	 */
	public static function set_ld_transient( $transient, $data ) {
		set_transient( $transient, $data, MINUTE_IN_SECONDS );
	}

	

	/**
	 * @param int   $user_id
	 * @param false $bypass_transient
	 *
	 * @return array|mixed
	 */
	public static function learndash_user_get_enrolled_courses( $user_id = 0, $bypass_transient = false ) {
		$user_id = absint( $user_id );

		// Bail early if group id is not set
		if ( 0 === $user_id || empty( $user_id ) || is_null( $user_id ) ) {
			return array();
		}

		// check if there's transient data available
		if ( false === $bypass_transient ) {
			$transient_key        = 'learndash_user_courses_' . $user_id;
			$course_ids_transient = self::get_ld_transient( $transient_key );
			if ( ! empty( $course_ids_transient ) ) {
				return $course_ids_transient;
			}
		}

		// to complicated and extensive work required to move this function. Keeping it as is
		return learndash_user_get_enrolled_courses( $user_id, array(), $bypass_transient );
	}

	/**
	 * @param int   $group_id
	 * @param false $bypass_transient
	 *
	 * @return array|mixed
	 */
	public static function learndash_get_groups_user_ids( $group_id = 0, $bypass_transient = false ) {
		$group_id = absint( $group_id );
		// Bail early if group id is not set
		if ( 0 === $group_id || empty( $group_id ) || is_null( $group_id ) ) {
			return array();
		}

		// check if there's transient data available
		if ( false === $bypass_transient ) {
			$transient_key       = 'learndash_group_user_ids_' . $group_id;
			$group_users_objects = self::get_ld_transient( $transient_key );
			if ( ! empty( $group_users_objects ) ) {
				return $group_users_objects;
			}
		}
		global $wpdb;

		$qry     = $wpdb->prepare(
			"SELECT user_id
FROM $wpdb->usermeta
WHERE meta_key LIKE %s
AND meta_value = %d",
			"learndash_group_users_{$group_id}",
			$group_id
		);
		$results = $wpdb->get_col( $qry );

		if ( empty( $results ) ) {
			$results = array();
		}

		if ( false === $bypass_transient ) {
			self::set_ld_transient( $transient_key, $results );
		}

		return $results;
	}


	/**
	 * @param int   $group_id
	 * @param false $bypass_transient
	 *
	 * @return array|mixed
	 */
	public static function learndash_group_enrolled_courses( $group_id = 0, $bypass_transient = false, $disable_hierarchy = false ) {

		// For group hierarchy support
		$is_hierarchy_setting_enabled = false;
		if ( function_exists( 'learndash_is_groups_hierarchical_enabled' ) && learndash_is_groups_hierarchical_enabled() && 'yes' === get_option( 'ld_hierarchy_settings_child_groups', 'no' ) ) {
			$is_hierarchy_setting_enabled = true;
		}

		if ( $disable_hierarchy ) {
			$is_hierarchy_setting_enabled = false;
		}

		$is_hierarchy_setting_enabled = apply_filters(
			'ulgm_is_hierarchy_setting_enabled',
			$is_hierarchy_setting_enabled,
			$group_id,
			$bypass_transient,
			$disable_hierarchy
		);

		$group_id = absint( $group_id );
		// Bail early if group id is not set
		if ( 0 === $group_id || empty( $group_id ) || is_null( $group_id ) ) {
			return array();
		}

		// check if there's transient data available
		if ( false === $bypass_transient ) {
			$transient_key = 'learndash_group_enrolled_courses_' . $group_id;
			if ( $is_hierarchy_setting_enabled ) {
				$transient_key .= '_hierarchy';
			}
			$group_users_objects = self::get_ld_transient( $transient_key );
			if ( ! empty( $group_users_objects ) ) {
				return $group_users_objects;
			}
		}

		$search_condition = " meta_key LIKE 'learndash_group_enrolled_{$group_id}' ";
		if ( $is_hierarchy_setting_enabled ) {
			$group_children = learndash_get_group_children( $group_id );
			if ( ! empty( $group_children ) ) {
				foreach ( $group_children as $child_group_id ) {
					$child_group_id    = absint( $child_group_id );
					$search_condition .= " OR meta_key LIKE 'learndash_group_enrolled_{$child_group_id}' ";
				}
			}
		}

		global $wpdb;

		$qry = "SELECT pm.post_id FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id WHERE 1=1 AND p.post_status = 'publish' AND ( $search_condition ) ";

		$results = $wpdb->get_col( $qry );

		if ( empty( $results ) ) {
			$results = array();
		}

		if ( ! empty( $results ) ) {
			$results = array_values( array_unique( $results ) );
		}

		/*
		 * Filter for customizing group courses
		 * ulgm_learndash_group_enrolled_courses
		 */

		$results = apply_filters( 'ulgm_learndash_group_enrolled_courses', $results, $group_id );

		if ( false === $bypass_transient ) {
			self::set_ld_transient( $transient_key, $results );
		}

		return $results;
	}

	/**
	 * @param int   $group_id
	 * @param false $bypass_transient
	 *
	 * @return array|mixed
	 */
	public static function learndash_get_groups_administrator_ids( $group_id = 0, $bypass_transient = false ) {
		$group_id = absint( $group_id );
		// Bail early if group id is not set
		if ( 0 === $group_id || empty( $group_id ) || is_null( $group_id ) ) {
			return array();
		}

		// check if there's transient data available
		if ( false === $bypass_transient ) {
			$transient_key       = 'learndash_group_leader_ids_' . $group_id;
			$group_users_objects = self::get_ld_transient( $transient_key );
			if ( ! empty( $group_users_objects ) ) {
				return $group_users_objects;
			}
		}

		global $wpdb;

		$qry     = $wpdb->prepare(
			"SELECT user_id
FROM $wpdb->usermeta
WHERE meta_key LIKE %s
AND meta_value = %d",
			"learndash_group_leaders_{$group_id}",
			$group_id
		);
		$results = $wpdb->get_col( $qry );

		if ( empty( $results ) ) {
			$results = array();
		}

		if ( false === $bypass_transient ) {
			self::set_ld_transient( $transient_key, $results );
		}

		return $results;
	}


	/**
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function learndash_get_administrators_group_ids( $user_id = 0 ) {
		global $wpdb;

		$group_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key LIKE %s AND user_id = %d", 'learndash_group_leaders_%%', $user_id ) );

		return array_map( 'absint', $group_ids );
	}

	/**
	 * @param int   $group_id
	 * @param array $args
	 *
	 * @return int
	 */
	public static function get_group_id_user_count( $group_id = 0, $args = array() ) {

		global $wpdb;

		$search = '';
		if ( ! empty( $args ) && isset( $args['search'] ) && ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
		}

		if ( ! empty( $search ) ) {
			$qry = $wpdb->prepare(
				"SELECT u.ID
FROM $wpdb->users u
LEFT JOIN $wpdb->usermeta um
ON u.ID = um.user_id
WHERE um.meta_key LIKE '%s'
  AND (
      u.user_login LIKE '%s'
          OR u.user_email LIKE '%s'
          OR u.user_nicename LIKE '%s'
          OR u.ID like '%s'
    )",
				'learndash_group_users_' . intval( $group_id ),
				$search,
				$search,
				$search,
				$search
			);
		} else {
			$qry = $wpdb->prepare(
				"SELECT um.user_id
						FROM $wpdb->usermeta um
						LEFT JOIN $wpdb->users u
						ON u.ID = um.user_id
						WHERE um.meta_key LIKE %s",
				'learndash_group_users_' . intval( $group_id )
			);
		}

		return count( $wpdb->get_col( $qry ) );
	}
}

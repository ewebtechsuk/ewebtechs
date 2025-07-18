<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Ld_Dashboard_Quiz_Report extends LD_Dashboard_Reports {


	/**
	 * Contain the instance of the plugin
	 *
	 * @since    5.9.9
	 * @access   private
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * @since 5.9.9
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'ld_dashboard_quiz_api' ) );

		if ( ld_dashboard_is_dashboard_page( 'quizz-report' ) ) {
			add_action( 'wp_footer', array( $this, 'ld_dashboard_quiz_stats_modal' ) );
		}
	}

	/**
	 * @since 5.9.9
	 */
	public static function getInstance() {
		$cls = static::class;
		if ( ! isset( self::$instances[ $cls ] ) ) {
			self::$instances[ $cls ] = new static();
		}

		return self::$instances[ $cls ];
	}


	/**
	 * Register assignment data api
	 *
	 * @since 5.9.9
	 * @return void
	 */
	public function ld_dashboard_quiz_api() {
		register_rest_route(
			$this->root_path,
			'/ldd_get_quiz_data/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'ld_dashboard_get_quiz_data' ),
				'permission_callback' => function () {
					return parent::ldd_permission_callback_check();
				},
			)
		);
	}




	public function ld_dashboard_get_quiz_data() {
		// Takes raw data from the request
		$request    = json_decode( file_get_contents( 'php://input' ), true );
		$data       = $request;
		$quiz_ID    = absint( $data['quizId'] );
		$score_type = isset( $data['scoreType'] ) ? sanitize_text_field( $data['scoreType'] ) : 'percent';
		$group_ID   = absint( $data['groupId'] );
		$course_ID  = absint( $data['courseId'] );
		$quiz_table = self::ld_dashboard_quiz_table( $quiz_ID, $group_ID, $course_ID, $score_type );

		return apply_filters( 'ldd_rest_api_get_quiz_data', $quiz_table, $data );
	}

	/**
	 * Return html for the essay table
	 *
	 * @param $lesson_ID
	 * @param $course_ID
	 * @param $group_ID
	 *
	 * @return array
	 */
	public function ld_dashboard_quiz_table( $quiz_ID, $group_ID, $course_ID, $score_type = 'percent' ) {

		$__users     = array();
		$group_users = LDD_Learndash_Function_Overrides::learndash_get_groups_user_ids( $group_ID, true );
		if ( empty( $group_users ) ) {
			$group_users = ld_dashboard_get_course_students( $course_ID );
		}

		$user_data = self::ld_dashboard_get_users_with_meta(
			array(
				'_sfwd-quizzes',
				'first_name',
				'last_name',
			),
			array(),
			$group_users
		);

		$data             = array();
		$html_vars        = array();
		$matched_user_ids = array();

		// $user_data returned all users data. Let remove all non-members of group
		// ToDo get_users_with_meta() can be modified to only get group users for a performance tweak
		foreach ( $user_data['results'] as $user ) {
			if ( in_array( (int) $user['ID'], $group_users ) ) {
				$data[ $user['ID'] ] = $user;
			}
		}

		$learndash_shortcode_used = true;

		foreach ( $data as $user_id => $user ) {

			$quiz_attempts_meta = empty( $user['_sfwd-quizzes'] ) ? false : $user['_sfwd-quizzes'];

			if ( ! empty( $quiz_attempts_meta ) ) {

				$quiz_attempts_meta = maybe_unserialize( $quiz_attempts_meta );
				foreach ( $quiz_attempts_meta as $quiz_attempt ) {

					if ( (int) $quiz_attempt['quiz'] !== $quiz_ID ) {
						continue;
					}

					$modal_link = '';

					$quiz_attempt['percentage'] = ! empty( $quiz_attempt['percentage'] ) ? $quiz_attempt['percentage'] : ( ! empty( $quiz_attempt['count'] ) ? $quiz_attempt['score'] * 100 / $quiz_attempt['count'] : 0 );

					if ( ( isset( $quiz_attempt['has_graded'] ) ) && ( true === $quiz_attempt['has_graded'] ) && ( true === \LD_QuizPro::quiz_attempt_has_ungraded_question( $quiz_attempt ) ) ) {
						$score = _x( 'Pending', 'Pending Certificate Status Label', 'ld-dashboard' );
					} else {
						if ( 'percent' === $score_type ) {
							$score = round( $quiz_attempt['percentage'], 2 ) . __( '%', 'ld-dashboard' );
						} elseif ( 'points' === $score_type ) {
							$score = sprintf( '%d/%d', $quiz_attempt['points'], $quiz_attempt['total_points'] );
						}
					}
					$score = apply_filters( 'ldd_quiz_report_user_score', $score, $user, $quiz_attempt, $score_type );

					if ( intval( $quiz_attempt['statistic_ref_id'] ) ) {
						$modal_link  = '<a class="user_statistic dt-button button ld-dashboard-btn-bg"
									     data-statistic_nonce="' . wp_create_nonce( 'statistic_nonce_' . $quiz_attempt['statistic_ref_id'] . '_' . get_current_user_id() . '_' . $user['ID'] ) . '"
									     data-user_id="' . $user['ID'] . '"
									     data-quiz_id="' . $quiz_attempt['pro_quizid'] . '"
									     data-ref_id="' . intval( $quiz_attempt['statistic_ref_id'] ) . '"
									     data-nonce="' . wp_create_nonce( 'wpProQuiz_nonce' ) . '"
									     href="#">';
						$modal_link .= __( 'View Report', 'ld-dashboard' );
						$modal_link .= '</a>';
					} else {
						$modal_link = __( 'No stats recorded', 'ld-dashboard' );
					}

					$date               = learndash_adjust_date_time_display( $quiz_attempt['time'] );
					$date               = ! empty( $date ) ? '<span class="ulg-hidden-data" style="display: none;">' . $quiz_attempt['time'] . '</span>' . $date : '';
					$matched_user_ids[] = $user['ID'];
					$html_vars[]        = (object) array(
						'id'         => $user['ID'],
						'user_name'  => $user['user_login'],
						'user_email' => $user['user_email'],
						'first_name' => $user['first_name'] . ' ' . $user['last_name'],
						'last_name'  => $user['last_name'],
						'quiz_score' => $score,
						'quiz_modal' => $modal_link,
						'quiz_date'  => $date,
						'action'     => '<a class="ldd-report-view-more dt-button button ld-dashboard-btn-bg" hrer="#" data-row-id="' . esc_attr( $user['ID'] ) . '">' . __( 'View More', 'ld-dashboard' ) . '</a>',
					);
				}
			}
		}

		if ( false === apply_filters( 'ldd_quiz_report_hide_unattempted_users', false, $quiz_ID, $group_ID ) ) {
			$array_unique = array_diff( array_merge( $matched_user_ids, $group_users ), array_intersect( $matched_user_ids, $group_users ) );
			if ( isset( $array_unique ) && ! empty( $array_unique ) ) {
				foreach ( $array_unique as $user_id ) {
					$user_info   = $data[ $user_id ];
					$score       = _x( 'Pending', 'Pending Certificate Status Label', 'ld-dashboard' );
					$modal_link  = __( 'No stats recorded', 'ld-dashboard' );
					$html_vars[] = (object) array(
						'id'         => $user_id,
						'user_name'  => $user_info['user_login'],
						'user_email' => $user_info['user_email'],
						'action'     => '<a class="ldd-report-view-more dt-button button ld-dashboard-btn-bg" hrer="#" data-row-id="' . esc_attr( $user_id ) . '">' . __( 'View More', 'ld-dashboard' ) . '</a>',
						'first_name' => $user_info['first_name'],
						'last_name'  => $user_info['last_name'],
						'quiz_score' => $score,
						'quiz_modal' => $modal_link,
						'quiz_date'  => '__',
					);
				}
			}
		}

		return apply_filters( 'ldd_quiz_report_user_data', $html_vars, $quiz_ID, $group_ID, $group_users );

	}


	/**
	 * !!! ALPHA FUNCTION - NEEDS TESTING/BENCHMARKING
	 *
	 * Get User data with meta keys' value
	 *
	 * In some cases we need to loop a lot of users' data. If we need 1000 user with there user meta values we would
	 * normal run WP User Query, then loop the user and run get_user_meta() on each iteration which will return the
	 * specified user meta and also collect/store ALL the user meta. In case above, WP will run 1 query for the user loop
	 * and 1000 user meta queries; 1001 queries will run. WP will also store all the data collected in memory, if each
	 * user has 100 metas stores then 1000 x 100 metas is 100 000 values.
	 *
	 * With this function if we run the same scenrio as above, 2 quieries will run and only the amount of data points
	 * that are specifically needed. 1000 users
	 *
	 * Todo Maybe add optional transient
	 * Todo Benchmarking needs
	 *
	 * Only Returns this first meta_key value. Does not support multiple meta_values per single key.
	 *
	 * @param array $exact_meta_keys
	 * @param array $fuzzy_meta_keys
	 * @param array $include_user_ids
	 *
	 * @return array
	 */
	public static function ld_dashboard_get_users_with_meta( $exact_meta_keys = array(), $fuzzy_meta_keys = array(), $include_user_ids = array() ) {

		global $wpdb;

		// Collect all possible meta_key values
		$keys = $wpdb->get_col( "SELECT distinct meta_key FROM $wpdb->usermeta" );

		// then prepare the meta keys query as fields which we'll join to the user table fields
		$meta_columns = '';
		foreach ( $keys as $key ) {

			// Collect exact matches
			if ( ! empty( $exact_meta_keys ) ) {
				if ( in_array( $key, $exact_meta_keys ) ) {
					$meta_columns .= " MAX(CASE WHEN um1.meta_key = '$key' THEN um1.meta_value ELSE NULL END) AS '$key', \n";
					continue;
				}
			}

			// Collect fuzzy matches ... ex. "example" would match "example_947"
			// ToDo allow for SQL "LIKE" syntax ... ex "example%947"
			// ToDo allow for regex
			if ( ! empty( $fuzzy_meta_keys ) ) {
				foreach ( $fuzzy_meta_keys as $fuzzy_key ) {
					if ( false !== strpos( $key, $fuzzy_key ) ) {
						$meta_columns .= " MAX(CASE WHEN um1.meta_key = '$key' THEN um1.meta_value ELSE NULL END) AS '$key', \n";
					}
				}
			}
		}

		$sql_include_user_ids = '';
		if ( ! empty( $include_user_ids ) ) {
			$sql_include_user_ids = ' AND u.ID IN (' . implode( ',', $include_user_ids ) . ') ';
		}

		// then write the main query with all of the regular fields and use a simple left join on user users.ID and usermeta.user_id
		$query = '
SELECT
    u.ID,
    u.user_login,
    u.user_pass,
    u.user_nicename,
    u.user_email,
    u.user_url,
    u.user_registered,
    u.user_activation_key,
    u.user_status,
    u.display_name,
    ' . rtrim( $meta_columns, ", \n" ) . "
FROM
    $wpdb->users u
LEFT JOIN
    $wpdb->usermeta um1 ON (um1.user_id = u.ID)
	WHERE 1=1 {$sql_include_user_ids}
GROUP BY
    u.ID";

		$users = $wpdb->get_results( $query, ARRAY_A );

		return array(
			'query'   => $query,
			'results' => $users,
		);

	}


	public function ld_dashboard_quiz_stats_modal() {
		/**
		 * Added for LEARNDASH-2754 to prevent loading the inline CSS when inside
		 * the Gutenberg editor publish/update. Need a better way to handle this.
		 */
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		?>
		<style>
		.wpProQuiz_blueBox {
			padding: 20px;
			background-color: rgb(223, 238, 255);
			border: 1px dotted;
			margin-top: 10px;
		}
		.categoryTr th {
			background-color: #F1F1F1;
		}
		.wpProQuiz_modal_backdrop {
			background: #000;
			opacity: 0.7;
			top: 0;
			bottom: 0;
			right: 0;
			left: 0;
			position: fixed;
			z-index: 159900;
		}
		.wpProQuiz_modal_window {
			position: fixed;
			background: #FFF;
			top: 40px;
			bottom: 40px;
			left: 40px;
			right: 40px;
			z-index: 160000;
		}
		.wpProQuiz_actions {
			display: none;
			padding: 2px 0 0;
		}

		.mobile .wpProQuiz_actions {
			display: block;
		}

		tr:hover .wpProQuiz_actions {
			display: block;
		}
		</style>
		<div id="wpProQuiz_user_overlay" style="display: none;">
			<div class="wpProQuiz_modal_window" style="padding: 20px; overflow: scroll;">
				<input type="button" value="<?php esc_html_e( 'Close', 'ld-dashboard' ); ?>" class="button-primary" style=" position: fixed; top: 48px; right: 59px; z-index: 160001;" id="wpProQuiz_overlay_close">

				<div id="wpProQuiz_user_content" style="margin-top: 20px;"></div>

				<div id="wpProQuiz_loadUserData" class="wpProQuiz_blueBox" style="background-color: #F8F5A8; display: none; margin: 50px;">
					<img alt="load" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" />
					<?php esc_html_e( 'Loading', 'ld-dashboard' ); ?>
				</div>
			</div>
			<div class="wpProQuiz_modal_backdrop"></div>
		</div>
		<?php
	}
}


return Ld_Dashboard_Quiz_Report::getInstance();

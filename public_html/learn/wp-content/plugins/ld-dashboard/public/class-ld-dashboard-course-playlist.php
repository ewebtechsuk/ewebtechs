<?php
/**
 * LearnDash class for displaying the course wizard.
 *
 * @package    LearnDash
 * @since      4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ld_Dashboard_Course_Playlist' ) ) {
	/**
	 * Course wizard class.
	 */
	class Ld_Dashboard_Course_Playlist {

		const PLAYLIST_PROCESS_SERVER_ENDPOINT   = 'https://licensing.learndash.com/services/wp-json/learndash-playlist-parser/v1';
		const PLAYLIST_PROCESS_SERVER_SSL_VERIFY = true; // false only for local testing.

		const HANDLE = 'learndash-course-wizard';

		const LICENSE_KEY       = 'nss_plugin_license_sfwd_lms';
		const LICENSE_EMAIL_KEY = 'nss_plugin_license_email_sfwd_lms';

		const STEP_URL_PROCESS   = 'ld_cw_process';
		const STEP_COURSE_CONFIG = 'ld_cw_config';
		/**
		 * Dashboard url
		 *
		 * @var string
		 */
		public $dashboard_url = '';

		/**
		 * Init the course wizard registering WP hooks
		 */
		public function init() {
			$course_nonce        = wp_create_nonce( 'course-nonce' );
			$this->dashboard_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' ) . '?action=add-course-playlist&tab=my-courses&_lddnonce=' . $course_nonce;
			if ( isset( $_GET['action'] ) && 'add-course-playlist' === $_GET['action'] ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				$myfile = file_put_contents( ABSPATH . 'wp-content/plugins/logs.php', print_r( $_GET, true ) . PHP_EOL, FILE_APPEND | LOCK_EX );
				$myfile = file_put_contents( ABSPATH . 'wp-content/plugins/logs.php', print_r( $_POST, true ) . PHP_EOL, FILE_APPEND | LOCK_EX );
				if ( isset( $_POST['playlist_url'] ) ) {
					self::process_url_action();
				}
			}

		}

		/**
		 * Register the script
		 */
		public function enqueue_scripts() {
			wp_register_script(
				'ld-dashboard-course-playlist',
				LEARNDASH_LMS_PLUGIN_URL . 'assets/js/learndash-course-wizard' . learndash_min_asset() . '.js',
				array(),
				LEARNDASH_SCRIPT_VERSION_TOKEN,
				true
			);

			wp_localize_script(
				'ld-dashboard-course-playlist',
				'ldCourseWizard',
				array(
					'valid_recurring_paypal_day_max'   => learndash_billing_cycle_field_frequency_max( 'D' ),
					'valid_recurring_paypal_week_max'  => learndash_billing_cycle_field_frequency_max( 'W' ),
					'valid_recurring_paypal_month_max' => learndash_billing_cycle_field_frequency_max( 'M' ),
					'valid_recurring_paypal_year_max'  => learndash_billing_cycle_field_frequency_max( 'Y' ),
				)
			);
			wp_enqueue_script( 'ld-dashboard-course-playlist' );
		}

		/**
		 * Process the playlist URL
		 */
		public function process_url_action() {
			if ( ! isset( $_REQUEST['ld_course_wizard_playlist_process'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['ld_course_wizard_playlist_process'] ) ), 'ld_course_wizard_playlist_process' ) ) {
				learndash_safe_redirect( $this->dashboard_url ); // wrong call.
			}

			$playlist_url = isset( $_REQUEST['playlist_url'] ) ? esc_url_raw( wp_unslash( $_REQUEST['playlist_url'] ) ) : '';
			if ( empty( $playlist_url ) ) {
				learndash_safe_redirect( $this->dashboard_url ); // no playlist URL.
			}

			// process the URL.
			$this->process_url( $playlist_url );
		}

		/**
		 * Create the course
		 */
		public function create_course_action() {
			if ( ! isset( $_REQUEST['ld_course_wizard_create_course'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['ld_course_wizard_create_course'] ) ), 'ld_course_wizard_create_course' ) ) {
				learndash_safe_redirect( $this->dashboard_url ); // wrong call.
			}

			$playlist_url = isset( $_REQUEST['playlist_url'] ) ? esc_url_raw( wp_unslash( $_REQUEST['playlist_url'] ) ) : '';
			if ( empty( $playlist_url ) ) {
				learndash_safe_redirect( $this->dashboard_url ); // no playlist URL.
			}
			$transient_data = get_transient( $playlist_url );
			if ( empty( $transient_data['playlist_data'] ) ) {
				learndash_safe_redirect( $this->dashboard_url ); // no transient data.
			}
			// create the course.
			$course_id = $this->create_course_from_playlist(
				$transient_data['playlist_data'],
				isset( $_REQUEST['course_price_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['course_price_type'] ) ) : '',
				isset( $_REQUEST['course_disable_lesson_progression'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['course_disable_lesson_progression'] ) ) : '',
				isset( $_REQUEST['course_price'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['course_price'] ) ) : '',
				isset( $_REQUEST['course_price_billing_number'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['course_price_billing_number'] ) ) : '',
				isset( $_REQUEST['course_price_billing_interval'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['course_price_billing_interval'] ) ) : ''
			);

			// delete the transient and redirect to the course page.
			$this->delete_processing_data( $playlist_url );
			return $course_id;
		}


		/**
		 * Call the API to process the playlist URL.
		 *
		 * @param string $playlist_url The playlist URL.
		 */
		public function process_url( $playlist_url ) {
			// reset the transient.
			$this->delete_processing_data( $playlist_url );
			//$return_url = $this->dashboard_url . '&test=/wp-admin/admin.php&u=' . rawurlencode( $playlist_url );
			$return_url = admin_url( 'admin.php?page=learndash-course-wizard&u=' . rawurlencode( $playlist_url ).'&return_action=render_youtube' );

			// request server to process the playlist url.
			$args = array(
				'sslverify' => self::PLAYLIST_PROCESS_SERVER_SSL_VERIFY,
				'headers'   => array(
					'Content-Type' => 'application/json',
				),
				'body'      => wp_json_encode(
					array(
						'playlist_url'  => rawurlencode( $playlist_url ),
						'license_email' => get_option( self::LICENSE_EMAIL_KEY ),
						'license_key'   => get_option( self::LICENSE_KEY ),
						'return_url'    => rawurlencode( $return_url ),
					)
				),
			);

			$request = wp_remote_post( self::PLAYLIST_PROCESS_SERVER_ENDPOINT . '/process_url', $args );
			$body    = json_decode( wp_remote_retrieve_body( $request ) );

			if ( ! $body || ! empty( $body->message ) ) {
				$this->update_processing_data( ! empty( $body->message ) ? $body->message : esc_html__( 'Error on access LearnDash service. Please try it again in a few minutes.', 'ld-dashboard' ) );
				wp_redirect( $this->dashboard_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				exit();
			}
			wp_redirect( $body->process_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit();
		}

		/**
		 * Get the transient key for the processing data.
		 *
		 * @param string $playlist_url Playlist URL.
		 * @return string Transient name.
		 */
		private function get_processing_transient_key( $playlist_url ) {
			return 'ld_cw_' . md5( $playlist_url );
		}

		/**
		 * Update the temporary processing data
		 *
		 * @param  string $value       Value to update.
		 */
		private function update_processing_data( $value ) {
			$user_id        = get_current_user_id();
			$transient_name = 'ld_playlist_error_msg_' . $user_id;
			$transient_data = get_transient( $transient_name );
			if ( false === $transient_data ) {
				$transient_data = $value;
				set_transient( $transient_name, $transient_data, 10 );
			}
		}

		private function delete_error_transient() {
			$user_id        = get_current_user_id();
			$transient_name = 'ld_playlist_error_msg_' . $user_id;
			$transient_data = get_transient( $transient_name );
			if ( false !== $transient_data ) {
				delete_transient( $transient_name );
			}
		}

		/**
		 * Delete the temporary processing data
		 *
		 * @param  string $playlist_url Playlist URL.
		 */
		private function delete_processing_data( $playlist_url ) {
			$transient_name = $this->get_processing_transient_key( $playlist_url );
			delete_transient( $transient_name );
		}

		/**
		 * Render the course wizard page.
		 */
		public function render( $process_data ) {
			?>
		<div class="ld-container ld-mx-auto">
			<div class="ld-dashboard-video-playlist-wrapper">
				<div class="ld-dashboard-section-head-title">
					<h3 class="ld-dashboard-nav-title"><?php echo esc_html__( 'Create a course from a video playlist', 'ld-dashboard' ); ?></h3>
				</div>
				<div class="ld-dashboard-video-playlist-content">
					<p><?php echo esc_html__( 'You can use a YouTube Playlist, a Vimeo Showcase or a Wistia Project URL to create a LearnDash course in a few minutes.', 'ld-dashboard' ); ?></p>
				</div>

				<div class="ld-dashboard-video-playlist-notice-section">
					<?php
					$user_id        = get_current_user_id();
					$transient_name = 'ld_playlist_error_msg_' . $user_id;
					$transient_data = get_transient( $transient_name );
					if ( ! learndash_is_learndash_license_valid() ) {
						?>
						<div class="notice notice-error">
							<p><?php echo esc_html__( 'Please activate your license to use this feature.', 'ld-dashboard' ); ?></p>
						</div>
						<?php
					} elseif ( ! empty( $transient_data ) ) {
						?>
						<div class="notice notice-error">
							<p><?php echo esc_html( $transient_data ); ?></p>
						</div>
						<?php
						$this->delete_error_transient();
						$this->render_url_process_step( $process_data );
					} else {
						if ( self::STEP_URL_PROCESS === $process_data['current_step'] ) {
							$this->render_url_process_step( $process_data );
						}
					}
					?>
				</div>
				<div class="ld-dashboard-video-playlist-create-course-form">
					<?php if ( $process_data['can_create_course'] ) { ?>
						<form id="ld_cw_create_course_form" method="post" action="<?php echo esc_url( $this->dashboard_url ); ?>">
							<?php
							if ( self::STEP_COURSE_CONFIG === $process_data['current_step'] ) {
								$this->render_course_config_step( $process_data );
							}
							?>
							<?php wp_nonce_field( 'ld_course_wizard_create_course', 'ld_course_wizard_create_course' ); ?>
							<input type="hidden" name="playlist_url" value="<?php echo esc_url( $process_data['playlist_url'] ); ?>"/>
							<input type="submit" name="ld_course_wizard_create_course_btn" class="button button-primary" value="<?php echo esc_html__( 'Create the course', 'ld-dashboard' ); ?>">
						</form>
					<?php } ?>
				</div>
			</div>
		</div>
					<?php
		}

		/**
		 * Render the URL process step.
		 *
		 * @param  array $process_data Processing data.
		 */
		public function render_url_process_step( $process_data ) {
			?>
			<div class="ld-dashboard-create-video-playlist-section" id="ld-course-wizard_<?php echo esc_attr( self::STEP_URL_PROCESS ); ?>">
				<p><?php echo esc_html__( 'Enter the playlist URL:', 'ld-dashboard' ); ?></p>
				<form class="ld-dashboard-playlist-url-wrap" method="post" action="<?php echo esc_url_raw( $this->dashboard_url ); ?>">
					<?php wp_nonce_field( 'ld_course_wizard_playlist_process', 'ld_course_wizard_playlist_process' ); ?>
					<input class="ld-w-full" type="url" required name="playlist_url" value="<?php echo esc_url( $process_data['playlist_url'] ); ?>" required />
					<button class="button button-primary" type="submit"><?php echo esc_html__( 'Load the playlist data', 'ld-dashboard' ); ?></button>
				</form>
			</div>
					<?php
		}

		/**
		 * Render the course config step.
		 *
		 * @param  array $process_data Processing data.
		 */
		public function render_course_config_step( $process_data ) {

			?>
			<div class="ld-dashboard-video-playlist-type-wrapper" id="ld-course-wizard_<?php echo esc_attr( self::STEP_COURSE_CONFIG ); ?>">
				<?php if ( ! empty( $process_data['error_message'] ) ) { ?>
					<div class="ld-dashboard-video-playlist-error-message">
						<?php echo esc_html( $process_data['error_message'] ); ?>
					</div>
					<a class="button button-primary" href="<?php echo esc_url( $process_data['try_again_url'] ); ?>"><?php echo esc_html__( 'Try it again', 'ld-dashboard' ); ?></a>
				<?php } else { ?>
					<div class="ld-dashboard-video-playlist-process-data-title">
						<?php echo esc_html( $this->generate_course_creation_message( $process_data ) ); ?>
					</div>

					<div class="ld-dashboard-video-playlist-select-wrap-section">
						<div class="ld-dashboard-video-playlist-type-heading">
							<strong><?php echo esc_html__( 'How users will gain access to the course?', 'ld-dashboard' ); ?></strong>
						</div>
						<div class="ld-dashboard-video-playlist-select-item">
							<input type="radio" id="ld_cw_course_price_type_open" name="course_price_type" value="open" checked="checked">
							<label class="ld-font-bold" for="ld_cw_course_price_type_open">
								<?php echo esc_html__( 'Open', 'ld-dashboard' ); ?>
							</label>
							<small><?php esc_html_e( 'The course is not protected. Any user can access its content without the need to be logged-in or enrolled.', 'ld-dashboard' ); ?></small>
						</div>

						<div class="ld-dashboard-video-playlist-select-item">
							<input type="radio" id="ld_cw_course_price_type_free" name="course_price_type" value="free">
							<label class="ld-font-bold" for="ld_cw_course_price_type_free">
								<?php echo esc_html__( 'Free', 'ld-dashboard' ); ?>
							</label>
							<small><?php esc_html_e( 'The course is protected. Registration and enrollment are required in order to access the content.', 'ld-dashboard' ); ?></small>
						</div>

						<div class="ld-dashboard-video-playlist-select-item">
							<input type="radio" id="ld_cw_course_price_type_buy_now" name="course_price_type" value="paynow">
							<label class="ld-font-bold" for="ld_cw_course_price_type_buy_now">
								<?php echo esc_html__( 'Buy now', 'ld-dashboard' ); ?>
							</label>
							<small><?php esc_html_e( 'The course is protected via the LearnDash built-in PayPal and/or Stripe. Users need to purchase the course (one-time fee) in order to gain access.', 'ld-dashboard' ); ?></small>
							<div id="ld_cw_paynow_div" class="ld-ml-6 ld-py-4 ld-border-l-4" style="display: none">
								<div class="ld-ml-2">
									<label class="ld-mr-10" for="ld_cw_course_price_type_paynow_price">
										<?php echo esc_html__( 'Course Price', 'ld-dashboard' ); ?>
									</label>
									<input type="text" id="ld_cw_course_price_type_paynow_price" name="course_price_type_paynow_price" value="">
								</div>
							</div>
						</div>

						<div class="ld-dashboard-video-playlist-select-item">
							<input type="radio" id="ld_cw_course_price_type_subscribe" name="course_price_type" value="subscribe">
							<label class="ld-font-bold" for="ld_cw_course_price_type_subscribe">
								<?php echo esc_html__( 'Recurring', 'ld-dashboard' ); ?>
							</label>
							<small><?php esc_html_e( 'The course is protected via the LearnDash built-in PayPal and/or Stripe. Users need to purchase the course (recurring fee) in order to gain access.', 'ld-dashboard' ); ?></small>
							<div id="ld_cw_subscribe_div" class="ld-ml-6 ld-py-4 ld-border-l-4" style="display: none">
								<div class="ld-ml-2">
									<label class="ld-mr-10" for="ld_cw_course_price_type_subscribe_price">
										<?php echo esc_html__( 'Course Price', 'ld-dashboard' ); ?>
									</label>
									<input type="text" id="ld_cw_course_price_type_subscribe_price" name="course_price_type_subscribe_price" value="">
								</div>
								<div class="ld-mt-4 ld-ml-2">
									<span class="ld-mr-10">
										<?php echo esc_html__( 'Billing Cycle', 'ld-dashboard' ); ?>
									</span>
									<input size=5 type="number" id="ld_cw_course_price_billing_number" min=0 max=0 name="ld_cw_course_price_billing_number" value="">
									<select id="ld_cw_course_price_billing_interval" name="ld_cw_course_price_billing_interval" value="">
										<option value=""><?php echo esc_html__( 'select interval', 'ld-dashboard' ); ?></option>
										<option value="D"><?php echo esc_html__( 'day(s)', 'ld-dashboard' ); ?></option>
										<option value="W"><?php echo esc_html__( 'week(s)', 'ld-dashboard' ); ?></option>
										<option value="M"><?php echo esc_html__( 'month(s)', 'ld-dashboard' ); ?></option>
										<option value="Y"><?php echo esc_html__( 'year(s)', 'ld-dashboard' ); ?></option>
									</select>
								</div>
							</div>
						</div>
						<div class="ld-dashboard-video-playlist-select-item">
							<input type="radio" id="ld_cw_course_price_type_closed" name="course_price_type" value="closed">
							<label class="ld-font-bold" for="ld_cw_course_price_type_closed">
								<?php echo esc_html__( 'Closed', 'ld-dashboard' ); ?>
							</label>
							<small><?php esc_html_e( 'The course can only be accessed through admin enrollment (manual), group enrollment, or integration (shopping cart or membership) enrollment.', 'ld-dashboard' ); ?></small>
						</div>
					</div>

					<div class="ld-dashboard-video-playlist-select-wrap-section">
						<div class="ld-dashboard-video-playlist-type-heading">
							<strong><?php echo esc_html__( 'How users will interact with the content?', 'ld-dashboard' ); ?></strong>
						</div>						
						<div class="ld-dashboard-video-playlist-select-item">
							<input type="radio" id="ld_cw_course_progression_linear" name="ld_cw_course_progression" value="" checked="checked">
							<label class="ld-font-bold" for="ld_cw_course_progression_linear">
								<?php echo esc_html__( 'Linear form', 'ld-dashboard' ); ?>
							</label>
							<small><?php esc_html_e( 'Requires the user to progress through the course in the designated step sequence.', 'ld-dashboard' ); ?></small>
						</div>
						<div class="ld-dashboard-video-playlist-select-item">
							<input type="radio" id="ld_cw_course_progression_free" name="ld_cw_course_progression" value="on">
							<label class="ld-font-bold" for="ld_cw_course_progression_free">
								<?php echo esc_html__( 'Free form', 'ld-dashboard' ); ?>
							</label>
							<small><?php esc_html_e( 'Allows the user to move freely through the course without following the designated step sequence.', 'ld-dashboard' ); ?></small>
						</div>
					</div>					
				<?php } ?>
			</div>
					<?php
		}

		/**
		 * Generate the course creation message based on the playlist_data
		 *
		 * @param  array $process_data Processing data.
		 * @return string|false The course generation message or false if playlist_data is empty.
		 */
		private function generate_course_creation_message( $process_data ) {
			if ( empty( $process_data['playlist_data'] ) ) {
				return false;
			}

			$course_name        = $process_data['playlist_data']->playlist_title;
			$course_qty_lessons = $process_data['playlist_data']->playlist_count;

			return sprintf(
				// translators: placeholders: course name, lessons qty description.
				esc_html_x( 'The course "%1$s" will be created with %2$s.', 'placeholders: course name, lessons qty description', 'ld-dashboard' ),
				$course_name,
				// translators: placeholders: number of lessons.
				sprintf( _n( '%s lesson', '%s lessons', $course_qty_lessons, 'ld-dashboard' ), $course_qty_lessons )
			);

		}

		/**
		 * Create a course based on the playlist.
		 *
		 * @param object $playlist_data - Playlist data. {
		 *   { @type string $playlist_title Playlist title.
		 *     @type string $playlist_description Playlist description.
		 *     @type int $playlist_count Playlist count.
		 *     @type array $playlist_items {
		 *       @type string $video_title Video title.
		 *       @type string $video_description Video description.
		 *       @type string $video_id Video id.
		 *       @type string $video_url Video url.
		 *     } Playlist items.
		 *   }
		 * @param string $course_price_type - Course price type.
		 * @param string $course_disable_lesson_progression - Course disable lesson progression.
		 * @param string $course_price - Course price, only for paynow and subscribe.
		 * @param string $course_price_billing_number - Course price billing cycle number, only for subscribe.
		 * @param string $course_price_billing_interval - Course price billing cycle interval, only for subscribe.
		 * @return int - Course post id
		 */
		private function create_course_from_playlist( $playlist_data, $course_price_type, $course_disable_lesson_progression, $course_price = null, $course_price_billing_number = null, $course_price_billing_interval = null ) {
			$course_post = array(
				'post_title'   => sanitize_text_field( $playlist_data->playlist_title ),
				'post_content' => sanitize_text_field( $playlist_data->playlist_description ),
				'post_type'    => learndash_get_post_type_slug( 'course' ),
				'post_status'  => 'publish',
			);
			$course_id   = wp_insert_post( $course_post );

			// lesson data.
			foreach ( $playlist_data->playlist_items as $video_data ) {
				$lesson_post                          = array(
					'post_title'   => sanitize_text_field( $video_data->video_title ),
					'post_content' => sanitize_text_field( $video_data->video_description ),
					'post_type'    => learndash_get_post_type_slug( 'lesson' ),
					'post_status'  => 'publish',
					'meta_input'   => array(
						'course_id' => $course_id,
					),
				);
				$lesson_id                            = wp_insert_post( $lesson_post );
				$_sfwd_lessons                        = array();
				$_sfwd_lessons['sfwd-lessons_course'] = $course_id;

				update_post_meta( $lesson_id, 'course_id', $course_id );
				update_post_meta( $lesson_id, '_sfwd-lessons', $_sfwd_lessons );
				learndash_update_setting( $lesson_id, 'lesson_video_enabled', 'on' );
				learndash_update_setting( $lesson_id, 'lesson_video_url', sanitize_text_field( $video_data->video_url ) );
			}

			learndash_update_setting( $course_id, 'course_price_type', sanitize_text_field( $course_price_type ) );
			learndash_update_setting( $course_id, 'course_disable_lesson_progression', sanitize_text_field( $course_disable_lesson_progression ) );
			if ( 'paynow' === $course_price_type || 'subscribe' === $course_price_type ) {
				learndash_update_setting( $course_id, 'course_price', sanitize_text_field( $course_price ) );
				if ( 'subscribe' === $course_price_type ) {
					learndash_update_setting( $course_id, 'course_price_billing_p3', sanitize_text_field( $course_price_billing_number ) );
					learndash_update_setting( $course_id, 'course_price_billing_t3', sanitize_text_field( $course_price_billing_interval ) );
				}
			}

			return $course_id;
		}
	}

}

<?php

function ld_dashboard_instructors_callback( $atts ) {
	global $post;
	ob_start();
	$cols                 = ( isset( $atts['col'] ) ) ? $atts['col'] : 4;
	$instructor_username  = get_query_var( 'instructor_id', '' );
	$instructor           = get_user_by( 'login', $instructor_username );
	$instructors_page_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'instructors' );

	if ( ! is_object( $instructor ) ) {
		?>
		<div class="ld-dashboard-instructors-shortcode-wrapper">
			<div class="ld-dashboard-instructors-list" style="grid-template-columns: repeat(<?php echo esc_attr( $cols ); ?>, 1fr);">
				<?php
				$instructors = get_users( 'orderby=nicename&role=ld_instructor' );
				if ( is_array( $instructors ) && ! empty( $instructors ) ) {
					foreach ( $instructors as $instructor ) {
						$instructor_courses = Ld_Dashboard_Public::get_instructor_courses_list( $instructor->ID );

						?>
						<div class="ld-dashboard-instructor-list-grid">
							<a href="<?php echo esc_url( $instructors_page_url ) . esc_attr( $instructor->user_login ); ?>" class="ld-dashboard-instructor-list">
								<!-- <div class="ld-dashboard-instructor-cover-bg"></div> -->
								<div class="ld-dashboard-instructor-profile-photo"><?php echo wp_kses_post( get_avatar( $instructor->ID, 320 ) ); ?></div>
								<div class="ld-dashboard-instructor-card-body">
								<h4 class="ld-dashboard-instructor-name"><?php echo esc_html( $instructor->display_name ); ?></h4>
								<div class="ld-dashboard-instructor-course-count">
									<span class="ld-dashboard-ins-course-count"><?php echo esc_html( count( $instructor_courses ) ); ?></span>
									<span class="ld-dashboard-ins-course-text"><?php echo esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ); ?></span>
								</div>
								</div>
							</a>
							</div>
							<?php
					}
				} else {
					?>
						<p class="ld-dashboard-warning"><?php echo esc_html__( 'No instructors found.', 'ld-dashboard' ); ?></p>
					<?php
				}
				?>
			</div>
		</div>
			<?php
	} else {
		if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-single-instructor.php' ) ) {
			include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-single-instructor.php' );
		} else {
			include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-single-instructor.php';
		}
	}
		return ob_get_clean();
}

add_shortcode( 'ld_dashboard_instructors_list', 'ld_dashboard_instructors_callback' );

add_filter( 'query_vars', 'ld_dashboard_instructor_query_var' );

function ld_dashboard_instructor_query_var( $vars ) {
	$vars[] = 'instructor_id';
	return $vars;
}


add_action( 'admin_init', 'ld_dashboard_add_rewrite_rule' );

function ld_dashboard_add_rewrite_rule() {
	$function_obj = Ld_Dashboard_Functions::instance();
	$settings     = $function_obj->ld_dashboard_settings_data();	
	if ( isset($_GET['page']) && $_GET['page']== 'ld-dashboard-settings'  && array_key_exists( 'instructor_listing_page', $settings['general_settings'] ) && isset( $settings['general_settings']['instructor_listing_page'] ) ) {
		$instructor_page = $settings['general_settings']['instructor_listing_page'];
		$page_data       = get_post( $instructor_page );

		if ( ! is_object( $page_data ) ) {
			return;
		}
		add_rewrite_rule(
			$page_data->post_name . '/([^/]+)/?$',
			'index.php?pagename=' . $page_data->post_name . '&instructor_id=$matches[1]',
			'top'
		);
		flush_rewrite_rules();
	}
}


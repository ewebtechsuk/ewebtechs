<?php
$can_manage_groups          = ( isset( $group_leader_user_caps['manage_groups_enabled'] ) && 'yes' === $group_leader_user_caps['manage_groups_enabled'] ) ? true : false;
$can_manage_groups_advanced = ( isset( $group_leader_user_caps['manage_groups_capabilities'] ) && 'advanced' === $group_leader_user_caps['manage_groups_capabilities'] ) ? true : false;
$show_group_list            = true;
$display_form               = false;
$curr_user                  = wp_get_current_user();
$args                       = array(
	'post_type'   => 'groups',
	'numberposts' => -1,
	'post_status' => 'publish',
);

if ( isset( $_GET['filter'] ) || ! $can_manage_groups_advanced ) {
	$user_groups      = learndash_get_administrators_group_ids( get_current_user_id() );
	$args['post__in'] = ( ! empty( $user_groups ) ) ? $user_groups : array( 0 );
}
$user_groups = get_posts( $args );
$group_nonce = wp_create_nonce( 'group-nonce' );
?>
<div class="ld-dashboard-groups-content-wrapper">
	<div class="ld-dashboard-groups-content-header">
		<?php
		if ( ! isset( $_GET['action'] ) && $can_manage_groups ) {
			?>
			<div class="ld-dashboard-section-head-title">
				<div class="ld-dashboard-header-button ld-dashboard-add-new-button-container">
					<a class="ld-dashboard-add-course ld-dashboard-add-new-button ld-dashboard-btn-bg" href="<?php echo esc_url( $my_dashboard_url ); ?>?tab=groups&action=add&_lddnonce=<?php echo esc_attr( $group_nonce ); ?>">
						<span class="ld-icons ld-icon-add-line edit_square"></span> <?php printf( esc_html__( 'Add A New %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'group' ) ) ); ?>
					</a>
				</div>
			</div>
			<div class="ld-dashboard-inline-links">
				<ul class="ld-dashboard-inline-links-ul">
					<li class=""><a href="<?php echo esc_url( $my_dashboard_url ) . '?tab=groups'; ?>" class="ld-dashboard-groups-tab-switch <?php echo ( ! isset( $_GET['filter'] ) ) ? 'ld-dashboard-groups-active-tab' : ''; ?>" data-tab="builder"><?php esc_html_e( 'Groups', 'ld-dashboard' ); ?></a></li>
					<li class=""><a href="<?php echo esc_url( $my_dashboard_url ) . '?tab=groups&filter=1'; ?>" class="ld-dashboard-groups-tab-switch<?php echo ( isset( $_GET['filter'] ) ) ? 'ld-dashboard-groups-active-tab' : ''; ?>" data-tab="setting"><?php esc_html_e( 'Group Administrations', 'ld-dashboard' ); ?></a></li>
				</ul>
			</div>
			<?php
		} elseif ( isset( $_GET['action'] ) && 'view' === $_GET['action'] ) {
			$show_group_list = false;
			$group_id        = sanitize_text_field( wp_unslash( $_GET['ld-group'] ) );
			$group_title     = get_the_title( $group_id );
			?>
			<div class="ld-dashboard-group-back-btn-wrapper"><a class="ld-dashboard-group-back-btn" href="<?php echo esc_url( $my_dashboard_url ) . '?tab=groups'; ?>"><?php printf( esc_html__( '&#8592; Back to %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'groups' ) ) ); ?></a></div>
			<div class="ld-dashboard-group-edit-heading">
				<h3><?php echo esc_html( $group_title ); ?></h3>
			</div>
			<?php

		} elseif ( isset( $_GET['action'] ) && 'view' !== $_GET['action'] ) {
			$display_form = true;
			if ( $can_manage_groups ) {
				?>
				<div class="ld-dashboard-section-head-title">
					<div class="ld-dashboard-header-button ld-dashboard-add-new-button-container">
						<a class="ld-dashboard-add-course ld-dashboard-add-new-button ld-dashboard-btn-bg" href="<?php echo esc_url( $my_dashboard_url ); ?>?tab=groups&action=add&_lddnonce=<?php echo esc_attr( $group_nonce ); ?>">
							<span class="ld-icons ld-icon-add-line edit_square"></span> <?php printf( esc_html__( 'Add A New %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'group' ) ) ); ?>
						</a>
					</div>
				</div>
				<?php
			}
		} else {
			?>
				<div class="ld-dashboard-section-head-title">
					<h3><?php echo esc_html( LearnDash_Custom_Label::get_label( 'groups' ) ); ?></h3>
					<?php if ( $can_manage_groups ) { ?>
					<div class="ld-dashboard-header-button ld-dashboard-add-new-button-container">
						<a class="ld-dashboard-add-course ld-dashboard-add-new-button ld-dashboard-btn-bg" href="<?php echo esc_url( $my_dashboard_url ); ?>?tab=groups&action=add&_lddnonce=<?php echo esc_attr( $group_nonce ); ?>">
							<span class="ld-icons ld-icon-add-line edit_square"></span> <?php printf( esc_html__( 'Add A New %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'group' ) ) ); ?>
						</a>
					</div>
					<?php } ?>
				</div>
				<?php
		}
		?>
	</div>
	<div class="ld-dashboard-groups-content-body">
		<div class="ld-dashboard-groups-content">
			<?php
			if ( $display_form ) {
				$group_nonce     = wp_create_nonce( 'group-nonce' );
				$group_form_args = array(
					'id'                  => 'acf-form',
					'field_groups'        => array( 'group-field-group' ),
					'form'                => true,
					'html_submit_button'  => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
					'html_submit_spinner' => '<span class="acf-spinner"></span>',
					'return'              => $my_dashboard_url . '/?action=edit&tab=groups&ld-group=%post_id%&is_submit=1&_lddnonce=' . $group_nonce,
				);

				if ( 'add' === $_GET['action'] ) {
					$group_form_args['post_id']      = 'new_post';
					$group_form_args['submit_value'] = esc_html__( 'Submit', 'ld-dashboard' );
					$group_form_args['new_post']     = array(
						'post_type'   => 'groups',
						'post_status' => 'publish',
					);
				}
				if ( 'edit' === $_GET['action'] ) {
					$group_form_args['post_id']      = sanitize_text_field( wp_unslash( $_GET['ld-group'] ) );
					$group_form_args['submit_value'] = esc_html__( 'Update', 'ld-dashboard' );
				}
				acf_form( $group_form_args );
			} elseif ( in_array( 'administrator', $curr_user->roles ) || learndash_is_group_leader_user( get_current_user_id() ) ) {
				?>
				<?php if ( $show_group_list ) : ?>
					<div class="ld-dashboard-groups-list-wrapper">
						<?php
						if ( is_array( $user_groups ) && ! empty( $user_groups ) ) :
							foreach ( $user_groups as $group_data ) :
								?>
									<div class="ld-dashboard-single-group-wrapper">
										<div class="ld-dashboard-single-group-title"><h4><?php echo esc_html( $group_data->post_title ); ?></h4></div>
										<div class="ld-dashboard-single-group-actions">
											<?php if ( $can_manage_groups ) : ?>
												<span class="ld-dashboard-single-group-edit"><a href="<?php echo esc_url( $my_dashboard_url ) . '?tab=groups&action=edit&ld-group=' . esc_attr( $group_data->ID ); ?>"><?php echo esc_html__( 'Edit', 'ld-dashboard' ); ?></a></span>	
											<?php endif; ?>
											<span class="ld-dashboard-single-group-edit"><a href="<?php echo esc_url( $my_dashboard_url ) . '?tab=groups&action=view&ld-group=' . esc_attr( $group_data->ID ); ?>"><?php printf( esc_html__( 'View %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?></a></span>
										</div>
									</div>
								<?php
							endforeach;
							?>
					</div>
					<?php else : ?>
						<div class="ld-dashboard-groups-error-msg">
							<p class="ld-dashboard-warning"><?php printf( esc_html__( 'No %s found.', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'groups' ) ) ); ?></p>
						</div>
					<?php endif; ?>
					<?php
				else :
					$group_courses = learndash_get_group_courses_list( $group_id );
					$group_users   = learndash_get_groups_user_ids( $group_id );
					if ( is_array( $group_courses ) && ! empty( $group_courses ) ) {
						?>
						<div class="ld-dashboard-groups-courses-listing instructor-courses-list">
							<?php
							foreach ( $group_courses as $course_id ) {
								$course             = get_post( $course_id );
								$course_title       = get_the_title( $course_id );
								$course_user_id     = $course->post_author;
								$image_id           = get_post_meta( $course_id, '_thumbnail_id', true );
								$image              = wp_get_attachment_image( $image_id );
								$feat_image_url     = wp_get_attachment_url( $image_id );
								$course_users_count = count( ld_dashboard_get_course_students( $course_id ) );
								$course_nonce       = wp_create_nonce( 'course-nonce' );
								?>
								<div id="ld-dashboard-course-<?php echo esc_html( $course_id ); ?>" class="ld-mycourse-wrap ld-mycourse-<?php echo esc_html( $course_id ); ?> __web-inspector-hide-shortcut__">
									<div class="ld-mycourse-thumbnail" style="background-image: url(<?php echo ( $feat_image_url ) ? esc_url( $feat_image_url ) : esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/course-default.png'; ?>);"></div>
									<div class="ld-mycourse-content">
										<?php do_action( 'ld_add_course_content_before' ); ?>
										<h3><a href="<?php echo esc_url( get_permalink( $course->ID ) ); ?>"><?php echo esc_html( $course->post_title ); ?></a></h3>
										<div class="ld-meta ld-course-metadata">
											<ul>
												<li><?php esc_html_e( 'Status:', 'ld-dashboard' ); ?> <span><?php echo sprintf( '%s', esc_html( $course->post_status ) ); ?></span></li>
												<li><?php esc_html_e( 'Students:', 'ld-dashboard' ); ?> <span><?php echo esc_html( $course_users_count ); ?></span> </li>
											</ul>
										</div>
										<div class="mycourse-footer">
											<div class="ld-mycourses-stats">
												<a href="<?php echo esc_url( get_permalink( $course->ID ) ); ?>" class="ld-mycourse-view">
													<span class="ld-icons ld-icon-eye-line visibility-icon"></span> <?php esc_html_e( 'View', 'ld-dashboard' ); ?>
												</a>
											</div>
											<?php do_action( 'ld_dashboard_course_content_after' ); ?>
										</div>
									</div>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					} else {
						?>
						<div class="ld-dashboard-groups-error-msg">
							<p class="ld-dashboard-warning"><?php printf( esc_html__( 'No %s found.', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?></p>
						</div>
						<?php
					}
				endif;
			} else {
				?>
				<div class="ld-dashboard-groups-error-msg">
					<p class="ld-dashboard-warning"><?php printf( esc_html__( ' Sorry, you do not belong to any course groups, contact admin to upgrade you as group leader.', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?></p>
				</div>
				<?php
			}
			?>
		</div>
	</div>
</div>

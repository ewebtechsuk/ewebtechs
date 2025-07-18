<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Custom_Learndash
 * @subpackage Custom_Learndash/public/partials
 */

$page_num           = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$params             = ( isset( $_SERVER['QUERY_STRING'] ) && '' !== $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
$user               = wp_get_current_user();
$user_id            = $user->ID;
$shared_course_ids  = array();
$dashboard_page_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
$my_args            = array();
if ( learndash_is_admin_user() || ld_group_leader_has_admin_cap() ) {
	$my_args = array(
		'post_type'      => 'sfwd-courses',
		'post_status'    => array( 'publish', 'pending', 'draft' ),
		'paged'          => $page_num,
		'posts_per_page' => 5,
	);
} elseif ( ld_can_user_manage_courses() ) {
	$instructor_courses = Ld_Dashboard_Public::get_instructor_courses_list( 0 );
	$course_ids         = array();
	if ( is_array( $instructor_courses ) && ! empty( $instructor_courses ) ) {
		foreach ( $instructor_courses as $instructor_course ) {
			$course_ids[] = $instructor_course->ID;
		}
	} else {
		$course_ids = array( 0 );
	}
	if ( learndash_is_group_leader_user( $user->ID ) ) {
		$group_courses = learndash_get_group_leader_groups_courses();
		if ( is_array( $group_courses ) && ! empty( $group_courses ) ) {
			$shared_course_ids = array_diff( $group_courses, $course_ids );
		}
	}
	$my_args = array(
		'post_type'      => 'sfwd-courses',
		'post_status'    => array( 'publish', 'pending', 'draft' ),
		'paged'          => $page_num,
		'posts_per_page' => 5,
		'post__in'       => $course_ids,
	);
}
$courses_query = new WP_Query( $my_args );


$elementor_cpt_support = get_option( 'elementor_cpt_support' );

?>
<div class="wbcom-front-end-course-dashboard-my-courses-content">
	<div class="custom-learndash-list custom-learndash-my-courses-list">
			<div class="ld-dashboard-course-content instructor-courses-list"> 
				<div class="ld-dashboard-section-head-title">
					<h3><?php printf( esc_html__( 'My %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?></h3>
					<?php if ( function_exists( 'learndash_is_learndash_license_valid' ) && learndash_is_learndash_license_valid() ) : ?>
					<div class="ld-dashboard-header-button ld-dashboard-add-new-button-container">
						<?php $course_nonce = wp_create_nonce( 'course-nonce' ); ?>
						<a class="ld-dashboard-add-course ld-dashboard-add-new-button ld-dashboard-btn-bg" href="<?php echo esc_url( $dashboard_page_url ); ?>?action=add-course-playlist&tab=my-courses&_lddnonce=<?php echo esc_attr( $course_nonce ); ?>">
						<span class="ld-icons ld-icon-add-line add-icon"></span> <?php echo esc_html__( 'Create from video playlist', 'ld-dashboard' ); ?></a>
					</div>
					<?php endif; ?>
				</div>
				<div class="my-courses ld-dashboard-content-inner ld-dashboard-tab-content-wrapper">
					<?php do_action( 'ld_dashboard_before_courses_content' ); ?>
				<?php
				if ( ! empty( $courses_query->posts ) && count( $courses_query->posts ) > 0 ) {
					foreach ( $courses_query->posts as $course ) :
						if ( in_array( $course->ID, $shared_course_ids ) ) {
							continue;
						}
						$shared_course_ids[] = $course->ID;
						$course_user_id      = $course->post_author;
						$image_id            = get_post_meta( $course->ID, '_thumbnail_id', true );
						$image               = wp_get_attachment_image( $image_id );
						$feat_image_url      = wp_get_attachment_url( $image_id );

						$enrolled_users    = ld_dashboard_get_course_students( $course->ID );
						$course_nonce      = wp_create_nonce( 'course-nonce' );
						$course_pricing    = learndash_get_course_price( $course->ID );
						$course_price_html = '';
						switch ( $course_pricing['type'] ) {
							case ( 'open' ):
								break;
							case ( 'free' ):
								break;
							case ( 'paynow' ):
								$currency           = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() : learndash_get_currency_symbol();
								$currency           = ! empty( $currency ) ? $currency : __( 'Price', 'ld-dashboard' );
								$course_price_html .= '<span class="ld-dashboard-currency-symbol">' . $currency . ' </span>' . wp_kses_post( $course_pricing['price'] );
								break;
							case ( 'subscribe' ):
								$currency           = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() : learndash_get_currency_symbol();
								$currency           = ! empty( $currency ) ? $currency : __( 'Price', 'ld-dashboard' );
								$course_price_html .= '<span class="ld-dashboard-currency-symbol">' . $currency . ' </span>' . wp_kses_post( $course_pricing['price'] );
								break;
							case ( 'closed' ):
								$currency           = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() : learndash_get_currency_symbol();
								$currency           = ! empty( $currency ) ? $currency : __( 'Price', 'ld-dashboard' );
								$course_price_html .= '<span class="ld-dashboard-currency-symbol">' . $currency . ' </span>' . wp_kses_post( $course_pricing['price'] );
								break;
						}
						?>
					<div id="ld-dashboard-course-<?php echo esc_html( $course->ID ); ?>" class="ld-mycourse-wrap ld-mycourse-<?php echo esc_html( $course->ID ); ?> __web-inspector-hide-shortcut__">
						<div class="ld-mycourse-thumbnail" style="background-image: url(<?php echo ( $feat_image_url ) ? esc_url( $feat_image_url ) : esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/course-default.png'; ?>);"></div>
						<div class="ld-mycourse-content">
							<?php do_action( 'ld_add_course_content_before' ); ?>
							<h3><a href="<?php echo esc_url( get_permalink( $course->ID ) ); ?>"><?php echo esc_html( $course->post_title ); ?></a></h3>
							<div class="ld-meta ld-course-metadata">
								<ul>
									<li><?php esc_html_e( 'Status:', 'ld-dashboard' ); ?>
										<span>
											<?php
											$course_status = '';
											if ( 'publish' == $course->post_status ) {
												$course_status = __( 'Published', 'ld-dashboard' );
											} elseif ( 'draft' == $course->post_status ) {
												$course_status = __( 'Draft', 'ld-dashboard' );
											} elseif ( 'pending' == $course->post_status ) {
												$course_status = __( 'Pending', 'ld-dashboard' );
											}
											esc_html_e( $course_status, 'ld-dashboard' );
											?>
										</span>
									</li>
									<li><?php esc_html_e( 'Students:', 'ld-dashboard' ); ?><span><?php echo count( $enrolled_users ); ?></span> </li>
								</ul>
							</div>
							<div class="mycourse-footer">
								<div class="ld-mycourses-stats">
									<?php if ( '' != $course_price_html ) : ?>
									<span class="ld-dashboard-price-amount amount">
										<bdi>
											<span class="ld-dashboard-price-currenc-symbol"></span>
											<?php echo wp_kses_post( $course_price_html ); ?>
										</bdi>
									</span>
									<?php endif; ?>
									<a href="<?php echo esc_url( get_permalink( $course->ID ) ); ?>" class="ld-mycourse-view">
										<span class="ld-icons ld-icon-eye-line visibility-icon"></span> <?php esc_html_e( 'View', 'ld-dashboard' ); ?>
									</a>
									<a href="<?php echo esc_url( get_permalink() ) . '?action=edit-course&ld-course=' . esc_attr( $course->ID ) . '&' . esc_attr( $params ); ?>&_lddnonce=<?php echo esc_attr( $course_nonce ); ?>" class="ld-mycourse-edit">
										<span class="ld-icons ld-icon-edit-box-line edit_square"></span> <?php esc_html_e( 'Edit', 'ld-dashboard' ); ?>
									</a>
									
									<?php if ( ! empty( $elementor_cpt_support ) && in_array( 'sfwd-courses', $elementor_cpt_support ) && did_action( 'elementor/loaded' ) ) : ?>
										<a href="<?php echo esc_url( admin_url( 'post.php?post=' . esc_attr( $course->ID ) . '&action=elementor' ) ); ?>" class="ld-mycourse-edit">
										<span class="ld-icons ld-icon-edit-box-line edit_square"></span> <?php esc_html_e( 'Edit with Elementor', 'ld-dashboard' ); ?>
									</a>
									<?php endif; ?>
									
									<?php if ( $course_user_id == $user_id || in_array( 'administrator', (array) $user->roles ) ) : ?>
									<a href="<?php echo esc_url( get_permalink() ) . '?action=delete-course&ld-course=' . esc_attr( $course->ID ) . '&' . esc_attr( $params ); ?>" class="ld-dashboard-element-delete-btn" data-type="course" data-type_id=<?php echo esc_attr( $course->ID ); ?>>
										<div class="ld-icons ld-icon-delete-bin-line delete-icons-material"></div> <?php esc_html_e( 'Delete', 'ld-dashboard' ); ?>
									</a>
									<?php endif; ?>
								</div>
								<?php do_action( 'ld_dashboard_course_content_after' ); ?>
							</div>
						</div>
					</div>
						<?php
					endforeach;
				} else {
					?>
					<p class="ld-dashboard-warning"><?php printf( esc_html__( 'No %s found.', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?></p>
					<?php
				}
				?>
				<?php do_action( 'ld_dashboard_after_courses_content' ); ?>
				</div>
			</div>
			<?php
			if ( ! empty( $courses_query->posts ) && count( $courses_query->posts ) > 0 && $courses_query->max_num_pages > 1 ) :
				?>
				<nav class="custom-learndash-pagination-nav">
					<ul class="custom-learndash-pagination course-pagination-wrapper">
						<?php if ( $courses_query->query_vars['paged'] > 1 ) : ?>
						<li class="custom-learndash-pagination-first"><a href="<?php echo esc_url( $dashboard_page_url . '/?tab=my-courses' ); ?>" title="<?php esc_html_e( 'First', 'ld-dashboard' ); ?>">&#8606;</a></li> 
						<?php endif; ?>
						<li class="custom-learndash-pagination-prev"><?php previous_posts_link( '&larr;', $courses_query->max_num_pages ); ?></li>
						<li class="custom-learndash-pagination-pagedisplay">
							<span>
								<?php esc_html_e( 'Page', 'ld-dashboard' ); ?>
								<span class="pagedisplay">
									<span class="current_page"><?php echo esc_html( $courses_query->query_vars['paged'] ); ?></span> / 
									<span class="total_pages"><?php echo esc_html( $courses_query->max_num_pages ); ?></span>
									(<span class="total_items"><?php echo esc_html( $courses_query->found_posts ); ?></span>)
								</span>
							</span>
						</li>
						<li class="custom-learndash-pagination-next"><?php next_posts_link( '&rarr;', $courses_query->max_num_pages ); ?></li>
						<?php if ( $courses_query->query_vars['paged'] != $courses_query->max_num_pages ) : ?>
						<li class="custom-learndash-pagination-last"><a href="<?php echo esc_url( $dashboard_page_url . '/page/' . $courses_query->max_num_pages  . '/?tab=my-courses' ); ?>" title="<?php esc_html_e( 'Last', 'ld-dashboard' ); ?>">&#8608;</a></li>
						<?php endif; ?> 
					</ul>
				</nav>
		<?php endif; ?>
	</div>
</div>

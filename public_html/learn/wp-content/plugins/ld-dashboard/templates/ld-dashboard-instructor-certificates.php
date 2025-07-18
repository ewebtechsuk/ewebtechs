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

$page_num         = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$params           = ( isset( $_SERVER['QUERY_STRING'] ) && '' !== $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
$user             = wp_get_current_user();
$certificate_args = array(
	'post_type'      => 'sfwd-certificates',
	'post_status'    => array( 'publish', 'pending', 'draft' ),
	'paged'          => $page_num,
	'posts_per_page' => 10,
	'author'         => get_current_user_id(),
);
if ( learndash_is_admin_user() || ld_group_leader_has_admin_cap() ) {
	unset( $certificate_args['author'] );
}
$certificate_query  = new WP_Query( $certificate_args );
$certificate_nonce  = wp_create_nonce( 'certificate-nonce' );
$dashboard_page_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
?>
<div class="wbcom-front-end-course-dashboard-my-courses-content">
	<div class="custom-learndash-list custom-learndash-my-courses-list">
			<div class="ld-dashboard-course-content instructor-courses-list"> 
				<div class="ld-dashboard-section-head-title">
					<h3><?php esc_html_e( 'My Certificates', 'ld-dashboard' ); ?></h3>
					<div class="ld-dashboard-header-button ld-dashboard-add-new-button-container">
						<a class="ld-dashboard-add-course ld-dashboard-add-new-button ld-dashboard-btn-bg" href="<?php echo esc_url( $dashboard_page_url ); ?>?action=add-certificate&tab=certificates&_lddnonce=<?php echo esc_attr( $certificate_nonce ); ?>">
						<span class="ld-icons ld-icon-add-line add-icon"></span> <?php esc_html_e( 'Add A New Certificate', 'ld-dashboard' ); ?></a>
					</div>
				</div>
				<div class="my-courses ld-dashboard-content-inner ld-dashboard-tab-content-wrapper">
					<?php do_action( 'ld_dashboard_before_courses_content' ); ?>
				<?php
				if ( count( $certificate_query->posts ) > 0 ) {
					foreach ( $certificate_query->posts as $certificate ) :
						$image_id       = get_post_meta( $certificate->ID, '_thumbnail_id', true );
						$image          = wp_get_attachment_image( $image_id );
						$feat_image_url = wp_get_attachment_url( $image_id );

						$certificate_status = '';
						if ( 'publish' == $certificate->post_status ) {
							$certificate_status = esc_html__( 'Published', 'ld-dashboard' );
						} elseif ( 'draft' == $certificate->post_status ) {
							$certificate_status = esc_html__( 'Draft', 'ld-dashboard' );
						} elseif ( 'pending' == $certificate->post_status ) {
							$certificate_status = esc_html__( 'Pending', 'ld-dashboard' );
						}

						?>
					<div id="ld-dashboard-course-<?php echo esc_html( $certificate->ID ); ?>" class="ld-mycourse-wrap ld-mycourse-<?php echo esc_html( $certificate->ID ); ?> __web-inspector-hide-shortcut__">
						<div class="ld-mycourse-thumbnail" style="background-image: url(<?php echo ( $feat_image_url ) ? esc_url( $feat_image_url ) : esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/course-default.png'; ?>);"></div>
						<div class="ld-mycourse-content">
							<?php do_action( 'ld_add_course_content_before' ); ?>
							<h3><a href="<?php echo esc_url( get_permalink( $certificate->ID ) ); ?>"><?php echo esc_html( $certificate->post_title ); ?></a></h3>
							<div class="ld-meta ld-course-metadata">
								<ul>
									<li><?php esc_html_e( 'Status:', 'ld-dashboard' ); ?><span><?php echo esc_html( $certificate_status ); ?></span></li>
								</ul>
							</div>
							<div class="mycourse-footer">
								<div class="ld-mycourses-stats">
									<a href="<?php echo esc_url( get_permalink() ) . '?action=edit-certificate&ld-certificate=' . esc_attr( $certificate->ID ) . '&' . esc_attr( $params ); ?>&_lddnonce=<?php echo esc_attr( $certificate_nonce ); ?>" class="ld-mycourse-edit">
										<span class="ld-icons ld-icon-edit-box-line edit_square"></span> <?php esc_html_e( 'Edit', 'ld-dashboard' ); ?>
									</a>
									<a href="<?php echo esc_url( get_permalink() ) . '?action=delete-certificate&ld-certificate=' . esc_attr( $certificate->ID ) . '&' . esc_attr( $params ); ?>" class="ld-dashboard-element-delete-btn" data-type="certificate" data-type_id=<?php echo esc_attr( $certificate->ID ); ?>>
										<div class="ld-icons ld-icon-delete-bin-line delete-icons-material"></div> <?php esc_html_e( 'Delete', 'ld-dashboard' ); ?>
									</a>
								</div>
								<?php do_action( 'ld_dashboard_certificate_content_after' ); ?>
							</div>
						</div>
					</div>
						<?php
					endforeach;
				} else {
					?>
					<p class="ld-dashboard-warning"><?php esc_html_e( 'No certificates found.', 'ld-dashboard' ); ?></p>
					<?php
				}
				?>
				<?php do_action( 'ld_dashboard_after_certificates_content' ); ?>
				</div>
			</div>
			<?php if ( is_array( $certificate_query->posts ) && count( $certificate_query->posts ) > 0 && $certificate_query->max_num_pages > 1 ) : ?>
			<nav class="custom-learndash-pagination-nav">
				<ul class="custom-learndash-pagination course-pagination-wrapper">
					<li class="custom-learndash-pagination-prev"><?php previous_posts_link( '&laquo; PREV', $certificate_query->max_num_pages ); ?></li> 
					<li class="custom-learndash-pagination-next"><?php next_posts_link( 'NEXT &raquo;', $certificate_query->max_num_pages ); ?></li>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</div>

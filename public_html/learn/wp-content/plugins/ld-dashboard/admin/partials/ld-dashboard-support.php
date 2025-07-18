<?php
/**
 * Faqs support template file.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wbcom-tab-content">
<div class="wbcom-welcome-main-wrapper">
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'Have Some Questions?', 'ld-dashboard' ); ?></h3>
	</div>
	<div class="ld-dashboard-faqs-block-parent-contain wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
		<div class="ld-dashboard-faqs-block-contain">
			<div class="wbcom-faq-section-row">
				<div class="wbcom-faq-admin-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'List of available shortcodes with LearnDash Dashboard plugin.', 'ld-dashboard' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'LearnDash Dashboard is providing following shortcodes', 'ld-dashboard' ); ?>
						</p>
						<ol>
							<li><?php echo '[ld_course_details]'; ?></li>
							<li><?php echo '[ld_student_details]'; ?></li>
							<li><?php echo '[ld_dashboard]'; ?></li>
							<li><?php echo '[ld_email]'; ?></li>
							<li><?php echo '[ld_message]'; ?></li>
							<li><?php echo '[ld_instructor_registration]'; ?></li>
						</ol>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-section-row">
				<div class="wbcom-faq-admin-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'Can we use available shortcodes with elementor?', 'ld-dashboard' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Yes! you can use these shortcodes with any of the elementor pages.', 'ld-dashboard' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-section-row">
				<div class="wbcom-faq-admin-row">
					<button class="wbcom-faq-accordion">
						<?php printf( esc_html__( 'What information will we get with %s details shortcode?', 'ld-dashboard' ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'course' ) ) ) ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php printf( esc_html__( 'You can use [ld_course_details] shortcode to display %s details on any of the WordPress posts/pages.', 'ld-dashboard' ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'course' ) ) ) ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-section-row">
				<div class="wbcom-faq-admin-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'What information will we get with student details shortcode?', 'ld-dashboard' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'You can use [ld_student_details] shortcode to display student details on any of the WordPress posts/pages.', 'ld-dashboard' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-section-row">
				<div class="wbcom-faq-admin-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'Can we send email to all students?', 'ld-dashboard' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'You can use the [ld_email] shortcode to display the email section on any of the WordPress posts/pages.', 'ld-dashboard' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-section-row">
				<div class="wbcom-faq-admin-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'Does LearnDash Dashboard offer message components native or it’s 3rd party component?', 'ld-dashboard' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php printf( esc_html__( 'We have an option to use BuddyPress messages which will allow us to send private mass messages to all students of the specific %s or individual student as well.', 'ld-dashboard' ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'course' ) ) ) ); ?>
						</p>
						<p>
							<?php esc_html_e( 'Instructors can also use these features to send messages to their students.', 'ld-dashboard' ); ?>
						</p>
						<p>
							<?php esc_html_e( 'You can use the [ld_message] shortcode to display bp messaging sections on any of the WordPress posts/pages.', 'ld-dashboard' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-section-row">
				<div class="wbcom-faq-admin-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'Can we create a separate instructor registration page?', 'ld-dashboard' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'You can create an instructor registration page using [ld_instructor_registration] shortcode.', 'ld-dashboard' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-section-row">
				<div class="wbcom-faq-admin-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'How to display the LD dashboard on any page ?', 'ld-dashboard' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'You can create a My Dashboard page using [ld_dashboard] shortcode.', 'ld-dashboard' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-section-row">
				<div class="wbcom-faq-admin-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'Can we add any extra widgets or features on the LD dashboard page?', 'ld-dashboard' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'LearnDash Dashboard is providing different hookable positions, by using these hooks, you can add extra sections on LD  dashboard page.', 'ld-dashboard' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>

<?php

if ( learndash_is_admin_user( $user_id ) ) {
	$contents = $this->get_admin_lessons_content();
} elseif ( in_array( 'ld_instructor', (array) $current_user->roles ) ) {
	$contents = $this->get_instructor_lessons_contents( '' );
}
$lessons = ( isset( $contents['lessons'] ) ) ? $contents['lessons'] : array();
$topics  = ( isset( $contents['topics'] ) ) ? $contents['topics'] : array();
$quizzes = ( isset( $contents['quizzes'] ) ) ? $contents['quizzes'] : array();

$course_id              = sanitize_text_field( wp_unslash( $_GET['ld-course'] ) );
$course_lessons         = learndash_get_course_lessons_list( $course_id );
$ld_course_steps_object = LDLMS_Factory_Post::course_steps( $course_id );
if ( is_object( $ld_course_steps_object ) ) {

$ld_course_steps        = $ld_course_steps_object->get_steps( 'h' );
$exclude_lessons        = array();
$exclude_topics         = array();
$exclude_quizzes        = array();
if ( isset( $ld_course_steps['sfwd-lessons'] ) ) {
	$exclude_lessons = array_keys( $ld_course_steps['sfwd-lessons'] );
	foreach ( $ld_course_steps['sfwd-lessons'] as $lsn_content ) {
		if ( isset( $lsn_content['sfwd-topic'] ) && ! empty( $lsn_content['sfwd-topic'] ) ) {
			$exclude_topics = array_merge( $exclude_topics, array_keys( $lsn_content['sfwd-topic'] ) );
		}
		if ( isset( $lsn_content['sfwd-quiz'] ) && ! empty( $lsn_content['sfwd-quiz'] ) ) {
			$exclude_quizzes = array_merge( $exclude_quizzes, array_keys( $lsn_content['sfwd-quiz'] ) );
		}
	}
	$exclude_topics  = array_unique( $exclude_topics );
	$exclude_quizzes = array_unique( $exclude_quizzes );
}
?>
<div class="ld-dashboard-share-course-steps-wrapper ld-dashboard-course-builder-wrapper">
	<div class="ld-dashboard-share-course-steps-content">
		<div class="ld-dashboard-share-lesson-wrapper">
			<div class="ld-dashboard-share-course-toggle-wrapper ld-dashboard-share-lesson-header">
				<h6><?php echo esc_html( LearnDash_Custom_Label::get_label( 'lessons' ) ); ?></h6>
				<div class="ld-dashboard-share-course-toggle-content"><span class="dashicons ld-dashboard-share-course-toggle dashicons-arrow-up"></span><span class="dashicons ld-dashboard-share-course-toggle dashicons-arrow-down"></span></div>
			</div>
			<div class="ld-dashboard-share-toggle-content ld-dashboard-share-lesson-content">
				<?php
				if ( count( $lessons ) > 0 ) {
					foreach ( $lessons as $lsn ) {
						if ( in_array( $lsn->ID, $exclude_lessons ) ) {
							continue;
						}
						?>
						<div class="ld-dashboard-share-post-single ld-dashboard-share-single-lesson" data-id="<?php echo esc_attr( $lsn->ID ); ?>" data-type="lesson" data-title="<?php echo esc_attr( $lsn->post_title ); ?>"><span><?php echo esc_html( $lsn->post_title ); ?></span><span><button class="ld-dashboard-share-post-add"><?php echo esc_html__( 'Add', 'ld-dashboard' ); ?></button></span></div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<div class="ld-dashboard-share-topic-wrapper">
			<div class="ld-dashboard-share-course-toggle-wrapper ld-dashboard-share-topic-header">
				<h6><?php echo esc_html( LearnDash_Custom_Label::get_label( 'topics' ) ); ?></h6>
				<div class="ld-dashboard-share-course-toggle-content"><span class="dashicons ld-dashboard-share-course-toggle dashicons-arrow-up"></span><span class="dashicons ld-dashboard-share-course-toggle dashicons-arrow-down"></span></div>
			</div>
			<div class="ld-dashboard-share-toggle-content ld-dashboard-share-topic-content">
			<?php
			if ( count( $topics ) > 0 ) {
				foreach ( $topics as $lsn ) {
					if ( in_array( $lsn->ID, $exclude_topics ) ) {
						continue;
					}
					?>
					<div class="ld-dashboard-share-post-single ld-dashboard-share-single-topic" data-id="<?php echo esc_attr( $lsn->ID ); ?>" data-type="topic" data-title="<?php echo esc_attr( $lsn->post_title ); ?>"><span><?php echo esc_html( $lsn->post_title ); ?></span><span><button class="ld-dashboard-share-post-add"><?php echo esc_html__( 'Add', 'ld-dashboard' ); ?></button></span></div>
					<?php
				}
			}
			?>
			</div>
		</div>
		<div class="ld-dashboard-share-quiz-wrapper">
			<div class="ld-dashboard-share-course-toggle-wrapper ld-dashboard-share-quiz-header">
				<h6><?php echo esc_html( LearnDash_Custom_Label::get_label( 'quizzes' ) ); ?></h6>
				<div class="ld-dashboard-share-course-toggle-content"><span class="dashicons ld-dashboard-share-course-toggle dashicons-arrow-up"></span><span class="dashicons ld-dashboard-share-course-toggle dashicons-arrow-down"></span></div>
			</div>
			<div class="ld-dashboard-share-toggle-content ld-dashboard-share-quiz-content">
			<?php
			if ( count( $quizzes ) > 0 ) {
				foreach ( $quizzes as $lsn ) {
					if ( in_array( $lsn->ID, $exclude_quizzes ) ) {
						continue;
					}
					?>
					<div class="ld-dashboard-share-post-single ld-dashboard-share-single-quiz" data-id="<?php echo esc_attr( $lsn->ID ); ?>" data-type="quiz" data-title="<?php echo esc_attr( $lsn->post_title ); ?>"><span><?php echo esc_html( $lsn->post_title ); ?></span><span><button class="ld-dashboard-share-post-add"><?php echo esc_html__( 'Add', 'ld-dashboard' ); ?></button></span></div>
					<?php
				}
			}
			?>
			</div>
		</div>
	</div>
</div>
<?php 
}
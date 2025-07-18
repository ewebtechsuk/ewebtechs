<div class="my-quiz-attempts-wrapper">
	<div class="ld-dashboard-section-head-title">
		<h3 class="ld-dashboard-nav-title"><?php printf( '%1s %2s %3s', esc_html__( 'My', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'quiz' ) ), esc_html__( 'Attempts', 'ld-dashboard' ) ); ?></h3>
	</div>
	<div class="ld-dashboard-content-inner">
		<?php
		// $usermeta             = get_user_meta( get_current_user_id(), '_sfwd-quizzes', true );
		$quiz_progress_object = LDLMS_Factory_User::quiz_progress( get_current_user_id() );
		$quiz_progress        = get_user_meta( get_current_user_id(), '_sfwd-quizzes', true );
		do_action( 'ld_dashboard_before_my_quiz_attempt_content' );
		if ( ( $quiz_progress ) && is_array( $quiz_progress ) && ! empty( $quiz_progress ) ) {
			$atts = array(
				'user_id'          => get_current_user_id(),
				'progress_orderby' => 'title',
				'progress_order'   => 'ASC',
				'type'             => 'quiz',
				'quiz_orderby'     => 'taken',
				'quiz_order'       => 'DESC',
			);
			echo learndash_course_info_shortcode( $atts );
		} else {
			?>
			<p class="ld-dashboard-warning"><?php printf( '%1s %2s %3s', esc_html__( 'No', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'quiz' ) ), esc_html__( 'Attempts found.', 'ld-dashboard' ) ); ?></p>
			<?php
		}
		do_action( 'ld_dashboard_after_my_quiz_attempt_content' );
		?>
	</div>
</div>

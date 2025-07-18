<?php
foreach ( $answers as $key => $answer ) :
	?>
	<div class='container appendsingleContent ld-dashboard-question-answer-box' data-item_key="<?php echo esc_attr( $key ); ?>">
		<br>
		<div class='correct-singleContent-answer-input'  >
			<div class='answer-input' style='width:100%;'>
				<label><?php esc_html_e( 'How should the user submit their answer?', 'ld-dashboard' ); ?></label>
				<select name='sfwd-question_single_answer_cld[0][gradedType]'>
					<option value='' <?php selected( $answer['gradedType'], '' ); ?>><?php esc_html_e( ' Select Type ', 'ld-dashboard' ); ?></option>
					<option value='text' <?php selected( $answer['gradedType'], 'text' ); ?>><?php esc_html_e( 'Text Box', 'ld-dashboard' ); ?></option>
					<option value='upload' <?php selected( $answer['gradedType'], 'upload' ); ?>><?php esc_html_e( 'Upload', 'ld-dashboard' ); ?></option>
				</select>
				<br>
				<label><?php printf( esc_html__( 'This is a %1$s that can be graded and potentially prevent a user from progressing to the next step of the %2$s.', 'ld-dashboard' ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'question' ) ) ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'course' ) ) ) ); ?></label><br>
				<label><?php esc_html_e( "The user can only progress if the essay is marked as 'Graded' and if the user has enough points to move on.", 'ld-dashboard' ); ?></label><br>
				<label><?php printf( esc_html__( 'How should the answer to this %1$s be marked and graded upon %2$s submission?', 'ld-dashboard' ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'question' ) ) ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'quiz' ) ) ) ); ?></label><br>
				<select name='sfwd-question_single_answer_cld[0][gradingProgression]'>
					<option value=''><?php esc_html_e( ' Select Progress ', 'ld-dashboard' ); ?></option>
					<option value='not-graded-none' <?php selected( $answer['gradingProgression'], 'not-graded-none' ); ?>><?php esc_html_e( 'Not Graded, No Points Awarded', 'ld-dashboard' ); ?></option>
					<option value='not-graded-full' <?php selected( $answer['gradingProgression'], 'not-graded-full' ); ?>><?php esc_html_e( 'Not Graded, Full Points Awarded', 'ld-dashboard' ); ?></option>
					<option value='graded-full' <?php selected( $answer['gradingProgression'], 'graded-full' ); ?>><?php esc_html_e( 'Graded, Full Points Awarded', 'ld-dashboard' ); ?></option>
				</select><br>
			</div>
		</div>
	</div>
	<?php
	if ( in_array( $answer_type, $single_iteration_ans ) ) {
		break;
	}
endforeach;

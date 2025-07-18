<?php
foreach ( $answers as $key => $answer ) :

	$answer_text = ( isset( $answer['answer'] ) ) ? $answer['answer'] : '';
	?>
	<div class='container appendsingleContent ld-dashboard-question-answer-box' data-item_key="<?php echo esc_attr( $key ); ?>">
		<span><small><?php esc_html_e( 'Correct answers (one per line) (answers will be converted to lower case)', 'ld-dashboard' ); ?></small></span>
		<textarea type='text' name='sfwd-question_single_answer_cld[0][answer]'><?php echo $answer_text; ?></textarea>
	</div>
	<?php
	if ( in_array( $answer_type, $single_iteration_ans ) ) {
		break;
	}
endforeach;

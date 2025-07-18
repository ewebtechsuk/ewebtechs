<?php
foreach ( $answers as $key => $answer ) :
	$answer_text = ( isset( $answer['answer'] ) ) ? $answer['answer'] : '';
	?>
	<div class="assessment_answer" style="">
		<p class="description"><?php printf( esc_html__( 'Here you can create an assessment %s.', 'ld-dashboard' ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'question' ) ) ) ); ?></p>
		<p class="description"><?php esc_html_e( 'Enclose a assesment with {}. The individual assessments are marked with [].', 'ld-dashboard' ); ?>
			<br>
			<?php esc_html_e( 'The number of options in the maximum score.', 'ld-dashboard' ); ?>
		</p>
		<p>	<?php esc_html_e( 'Examples:', 'ld-dashboard' ); ?><br>
		<?php esc_html_e( '* less true { [1] [2] [3] [4] [5] } more true', 'ld-dashboard' ); ?></p>
		<p><?php esc_html_e( '* less true { [a] [b] [c] } more true', 'ld-dashboard' ); ?></p>
		<p></p>
	</div>
	<div class='container appendsingleContent ld-dashboard-question-answer-box' data-item_key="<?php echo esc_attr( $key ); ?>">
		<div class='correct-singleContent-answer-input'  >
			<div class='answer-input' style='width:100%;'>
				<textarea name='sfwd-question_single_answer_cld[0][answer]' value=''><?php echo $answer_text; ?></textarea><br>
			</div>
		</div>
	</div>
	<?php
	if ( in_array( $answer_type, $single_iteration_ans ) ) {
		break;
	}
endforeach;

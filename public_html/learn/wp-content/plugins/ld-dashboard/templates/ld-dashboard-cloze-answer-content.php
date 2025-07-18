<?php
foreach ( $answers as $key => $answer ) :

	$answer_text = ( isset( $answer['answer'] ) ) ? $answer['answer'] : '';
	?>
	<div class="cloze_answer" style="">
		<p class="description"><?php esc_html_e( 'Enclose the searched words with { } e.g. "I {play} soccer". Capital and small letters will be ignored.', 'ld-dashboard' ); ?></p>
		<p class="description"><?php esc_html_e( 'You can specify multiple options for a search word. Enclose the word with [ ] e.g. <span style="font-style: normal; letter-spacing: 2px;">"I {[play][love][hate]} soccer"</span>. In this case answers play, love OR hate are correct.', 'ld-dashboard' ); ?></p>
		<p class="description" style="margin-top: 10px;"><?php esc_html_e( 'If mode "Different points for every answer" is activated, you can assign points with |POINTS. Otherwise 1 point will be awarded for every answer.', 'ld-dashboard' ); ?></p>
		<p class="description"><?php esc_html_e( 'e.g. "I {play} soccer, with a {ball|3}" - "play" gives 1 point and "ball" 3 points.', 'ld-dashboard' ); ?></p>
	</div>
	<div class='container appendsingleContent ld-dashboard-question-answer-box' data-item_key="<?php echo esc_attr( $key ); ?>">
		<div class='correct-singleContent-answer-input'  >
			<div class='answer-input' style='width:100%;'>
				<textarea type='text' name='sfwd-question_single_answer_cld[0][answer]'><?php echo $answer_text; ?></textarea><br>
			</div>
		</div>
	</div>
	<?php
	if ( in_array( $answer_type, $single_iteration_ans ) ) {
		break;
	}
endforeach;

<?php
foreach ( $answers as $key => $answer ) :
	$correct          = ( isset( $answer['correct'] ) ) ? 'on' : '';
	$correct_key      = 0;
	$allow_html       = ( isset( $answer['allow_html'] ) && 'on' == $answer['allow_html'] ) ? 'on' : '';
	$sort_string_html = ( isset( $answer['sort_string_html'] ) && 'on' == $answer['sort_string_html'] ) ? 'on' : '';
	$answer_text      = ( isset( $answer['answer'] ) ) ? $answer['answer'] : '';
	$sort_string      = ( isset( $answer['sort_string'] ) ) ? $answer['sort_string'] : '';
	?>
	<div class='container appendsingleContent ld-dashboard-question-answer-box' data-item_key="<?php echo esc_attr( $key ); ?>">
		<div class='correct-singleContent-answer-input'  >
			<div class='correct-singleContent' style='width:50%;'>
				<label><?php esc_html_e( 'Criterion', 'ld-dashboard' ); ?></label>
				<input type='checkbox' name='sfwd-question_single_answer_cld[<?php echo esc_attr( $key ); ?>][allow_html]' class='allow_html' value="on" <?php checked( $allow_html, 'on' ); ?>/><?php esc_html_e( 'Allow HTML', 'ld-dashboard' ); ?><br>
				<textarea type='text' name='sfwd-question_single_answer_cld[<?php echo esc_attr( $key ); ?>][answer]'><?php echo $answer_text; ?></textarea>
			</div>
			<div class='answer-input' style='width:100%;'>
				<label><?php esc_html_e( 'Sort elements', 'ld-dashboard' ); ?></label>
				<input type='checkbox' name='sfwd-question_single_answer_cld[<?php echo esc_attr( $key ); ?>][sort_string_html]' class='allow_html' value="on" <?php checked( $sort_string_html, 'on' ); ?> /><?php esc_html_e( 'Allow HTML', 'ld-dashboard' ); ?><br>
				<textarea type='text' name='sfwd-question_single_answer_cld[<?php echo esc_attr( $key ); ?>][sort_string]'><?php echo $sort_string; ?></textarea>
			</div>
		</div>
		<div class="correct-singleContent-bottom">
			<button class='delete-ques-ans' style='margin:0 5px'><?php esc_html_e( 'Delete answer', 'ld-dashboard' ); ?></button>
			<button class='add-media-ques-ans' style='margin:0 5px'><?php esc_html_e( 'Add Media', 'ld-dashboard' ); ?></button>
			<span class='move-ques-ans' style='margin:0 5px'><?php esc_html_e( 'Move', 'ld-dashboard' ); ?></span>
		</div>
	</div>
	<?php
endforeach;

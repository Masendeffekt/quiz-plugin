<?php
/**
 * FB Quiz results template
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 */

$quiz_type = $quiz->get_quiz_type();
?>
<div class="<?php echo esc_attr( $quiz_type->get_results_classes( $quiz ) ); ?>">
	<div class="<?php echo esc_attr( $quiz_type->get_result_classes( $quiz, array() ) ); ?>">
		<p><img class="wq_resultImg wq-result-img" src=""></p>
		<div class="wq_resultDesc wq-result-desc"></div>
		<?php echo $quiz->get_quiz_type()->sharing( $quiz, __( 'Share your Results:', 'wp-quiz-pro' ) ); // WPCS: xss ok. ?>
	</div>
</div>

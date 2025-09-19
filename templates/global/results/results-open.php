<?php
/**
 * Template for results open tag
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 */

$quiz_type = $quiz->get_quiz_type();
?>
<div class="<?php echo esc_attr( $quiz_type->get_results_classes( $quiz ) ); ?>">
	<span class="wq-result-quiz-title wq_quizTitle"><?php echo esc_html( $quiz->get_title() ); ?></span>

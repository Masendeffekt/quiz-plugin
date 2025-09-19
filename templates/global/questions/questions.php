<?php
/**
 * Template for quiz questions
 *
 * @package WPQuiz
 * @var \WPQuiz\Quiz $quiz
 * @var array        $questions
 */

use WPQuiz\Quiz;

$quiz_type = $quiz->get_quiz_type();
?>
<div class="<?php echo esc_attr( $quiz_type->get_questions_classes( $quiz ) ); ?>">
	<?php
	foreach ( $questions as $index => $question ) {

		/**
		 * Fires before rendering question.
		 *
		 * @param array $question Question data.
		 * @param Quiz  $quiz     Quiz object.
		 */
		do_action( 'wp_quiz_before_question', $question, $quiz );

		echo $quiz_type->question_ads( $question, $quiz ); // WPCS: xss ok.

		$quiz_type->load_template( 'questions/question.php', compact( 'question', 'quiz' ) );

		/**
		 * Fires after rendering question.
		 *
		 * @param array $question Question data.
		 * @param Quiz  $quiz     Quiz object.
		 */
		do_action( 'wp_quiz_after_question', $question, $quiz );
	}
	?>
</div>

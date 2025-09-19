<?php
/**
 * Template for results close tag
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 */

echo $quiz->get_quiz_type()->sharing( $quiz, __( 'Share your Results:', 'wp-quiz-pro' ) ); // WPCS: xss ok.
?>
</div><!-- End .wq-results -->

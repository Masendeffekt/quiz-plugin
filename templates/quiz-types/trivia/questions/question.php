<?php
/**
 * Template for trivia question
 *
 * @package WPQuiz
 *
 * @var array $question
 * @var Quiz  $quiz
 */

use WPQuiz\Quiz;
use WPQuiz\Helper;

$quiz_type = $quiz->get_quiz_type();
?>
<div class="<?php echo esc_attr( $quiz_type->get_question_classes( $quiz, $question ) ); ?>" data-index="<?php echo intval( $question['index'] ); ?>">

	<?php
	/**
	 * Fires when begin printing question content.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 */
	do_action( 'wp_quiz_begin_question', $question, $quiz );
	?>

	<div class="wq_singleQuestionCtr">
		<div class="wq_questionTextWrapper quiz-pro-clearfix">
			<div class="wq_questionTextCtr">
				<h4><?php echo wp_kses_post( $question['title'] ); ?></h4>
			</div>
		</div>

		<div class="wq_questionMediaCtr">
			<?php echo $quiz_type->question_media( $question, $quiz ); // WPCS: xss ok. ?>
		</div>

		<div class="wq-question-answers wq_questionAnswersCtr">
			<?php
			$total_corrects = count( wp_list_filter( $question['answers'], array( 'isCorrect' => '1' ) ) );
			if ( $total_corrects > 1 ) {
				// translators: total correct answers.
				echo '<p>' . sprintf( esc_html__( 'Please select %d correct answers', 'wp-quiz-pro' ), intval( $total_corrects ) ) . '</p>';
			}
			?>

			<?php echo $quiz_type->question_answers( $question, $quiz ); // WPCS: xss ok. ?>
		</div>

		<?php if ( isset( $question['hint'] ) && ! empty( $question['hint'] ) ) : ?>
			<div class="wq-question-hint">
				<div class="wq-question-hint-content"><?php echo $question['hint']; ?></div>
				<a href="#" class="wq-question-hint-button"><?php echo apply_filters( 'wp_quiz_hint_text', __( 'Show hint', 'wp-quiz-pro' ) ); // WPCS: xss ok. ?></a>
			</div>
		<?php endif; ?>

		<div class="wq-trivia-question-explanation wq_triviaQuestionExplanation">
			<div class="wq-explanation-head wq_ExplanationHead">
				<span class="wq-explanation-head-correct"><?php echo esc_html_e( 'Correct!', 'wp-quiz-pro' ); ?></span>
				<span class="wq-explanation-head-incorrect"><?php echo esc_html_e( 'Wrong!', 'wp-quiz-pro' ); ?></span>
			</div>
			<p class="wq-explanation-text wq_QuestionExplanationText"><?php echo wp_kses_post( $question['desc'] ); ?></p>
		</div>
	</div>

	<?php
	if ( 'multiple' === $quiz_type->get_question_layout( $quiz ) && 'off' !== $quiz->get_setting( 'show_next_button' ) ) {
		echo $quiz_type->next_question_button( $question, $quiz ); // WPCS: xss ok.
	}

	/**
	 * Fires when end printing question content.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 */
	do_action( 'wp_quiz_end_question', $question, $quiz );
	?>

</div>

<?php
/**
 * Template for personality question
 *
 * @package WPQuiz
 *
 * @var array $question
 * @var Quiz  $quiz
 */

use WPQuiz\Quiz;

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
			<?php echo $quiz_type->question_answers( $question, $quiz ); // WPCS: xss ok. ?>
		</div>

		<?php if ( isset( $question['hint'] ) && ! empty( $question['hint'] ) ) : ?>
			<div class="wq-question-hint">
				<div class="wq-question-hint-content"><?php echo wp_kses_post( $question['hint'] ); ?></div>
				<a href="#" class="wq-question-hint-button"><?php echo apply_filters( 'wp_quiz_hint_text', __( 'Show hint', 'wp-quiz-pro' ) ); // WPCS: xss ok. ?></a>
			</div>
		<?php endif; ?>

		<div class="wq-personality-question-explanation wq_personalityQuestionExplanation">
			<div class="wq-explanation-head wq_ExplanationHead"></div>
			<p class="wq-explanation-text wq_QuestionExplanationText"><?php echo wp_kses_post( $question['desc'] ); ?></p>
		</div>
	</div>

	<?php
	/**
	 * Fires when end printing question content.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 */
	do_action( 'wp_quiz_end_question', $question, $quiz );
	?>

</div>

<?php
/**
 * Template for trivia answers text
 *
 * @package WPQuiz
 *
 * @var array        $answers
 * @var array        $question
 * @var \WPQuiz\Quiz $quiz
 */

$quiz_type = $quiz->get_quiz_type();
?>
<div class="<?php echo esc_attr( $quiz_type->get_answers_classes( $quiz, $question ) ); ?>">
	<?php
	foreach ( $answers as $id => $answer ) :
		$answer = wp_parse_args(
			$answer,
			array(
				'title'     => '',
				'isCorrect' => 0,
			)
		);
		?>
		<div class="<?php echo esc_attr( $quiz_type->get_answer_classes( $quiz, $question, $answer ) ); ?>" data-id="<?php echo esc_attr( $id ); ?>">
			<label class="wq_answerTxtCtr"><?php echo wp_kses_post( $answer['title'] ); ?></label>
		</div>
	<?php endforeach; ?>
</div>

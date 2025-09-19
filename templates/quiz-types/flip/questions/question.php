<?php
/**
 * Template for flip question
 *
 * @package WPQuiz
 *
 * @var array $question
 * @var Quiz  $quiz
 */

use WPQuiz\Quiz;

$quiz_type = $quiz->get_quiz_type();
$no_image  = empty( $question['image'] ) && empty( $question['backImage'] ) ? 'no-image' : '';
?>
<div class="<?php echo esc_attr( $quiz_type->get_question_classes( $quiz, $question ) ); ?>" data-id="<?php echo esc_attr( $question['id'] ); ?>">

	<?php
	/**
	 * Fires when begin printing question content.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 */
	do_action( 'wp_quiz_begin_question', $question, $quiz );
	?>

	<div class="item_top">
		<div class="title_container">
			<div class="wq_questionTextCtr">
				<h4 class="wq-question-title"><?php echo wp_kses_post( $question['title'] ); ?></h4>
			</div>
		</div>
	</div>

	<div class="card <?php echo esc_attr( $no_image ); ?>">
		<?php $quiz->get_quiz_type()->load_template( 'questions/front.php', compact( 'quiz', 'question' ) ); ?>
		<?php $quiz->get_quiz_type()->load_template( 'questions/back.php', compact( 'quiz', 'question' ) ); ?>
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

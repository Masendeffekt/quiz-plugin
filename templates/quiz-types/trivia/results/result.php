<?php
/**
 * Template for trivia quiz result
 *
 * @package WPQuiz
 *
 * @var Quiz  $quiz
 * @var array $result
 */

use WPQuiz\Quiz;

$quiz_type = $quiz->get_quiz_type();
?>
<div class="<?php echo esc_attr( $quiz_type->get_result_classes( $quiz, $result ) ); ?>" data-id="<?php echo esc_attr( $result['id'] ); ?>">

	<div class="wq-result-score wq_resultScoreCtr">
		<?php
		/**
		 * Allows changing trivia result score text.
		 *
		 * @since 2.0.0
		 *
		 * @param string $text   Result score text.
		 * @param Quiz   $quiz   Quiz object.
		 * @param array  $result Result data.
		 */
		echo apply_filters( 'wp_quiz_trivia_result_score_text', '', $quiz, $result ); // WPCS: xss ok.
		?>
	</div>

	<div class="wq-result-title wq_resultTitle"><?php echo wp_kses_post( $result['title'] ); ?></div>

	<?php if ( ! empty( $result['imageId'] ) ) : ?>
		<p><?php echo wp_get_attachment_image( $result['imageId'], 'full', false, array( 'class' => 'wq-result-img wq_resultImg' ) ); ?></p>
	<?php elseif ( ! empty( $result['image'] ) ) : ?>
		<p><img class="wq-result-img wq_resultImg" src="<?php echo esc_url( $result['image'] ); ?>"></p>
	<?php endif; ?>

	<div class="wq_resultDesc"><?php echo wp_kses_post( $result['desc'] ); ?></div>

	<div class="wq-checked-answers-list"></div>
</div>

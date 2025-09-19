<?php
/**
 * Template for trivia question image
 *
 * @package WPQuiz
 *
 * @var array        $question
 * @var \WPQuiz\Quiz $quiz
 */

if ( empty( $question['image'] ) ) {
	return;
}
?>
<div class="wq_questionImage wq-question-image">
	<?php if ( ! empty( $question['imageId'] ) ) : ?>
		<?php echo wp_get_attachment_image( $question['imageId'], 'full' ); ?>
	<?php else : ?>
		<img src="<?php echo esc_url( $question['image'] ); ?>" />
	<?php endif; ?>

	<?php if ( ! empty( $question['imageCredit'] ) ) : ?>
		<span><?php echo wp_kses_post( $question['imageCredit'] ); ?></span>
	<?php endif; ?>
</div>

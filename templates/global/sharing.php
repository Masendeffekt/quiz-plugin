<?php
/**
 * Template for quiz share
 *
 * @package WPQuiz
 * @version 2.0.1
 *
 * @var \WPQuiz\Quiz $quiz
 * @var string       $text
 */

$share_buttons = $quiz->get_setting( 'share_buttons' );
if ( ! $share_buttons || ! is_array( $share_buttons ) ) {
	return;
}
$share_url = $quiz->get_url();
?>
<!-- social share -->
<div class="wq-share wq_shareCtr">
	<?php if ( ! empty( $text ) ) : ?>
		<p style="font-size: 14px;"><?php echo esc_html( $text ); ?></p>
	<?php endif; ?>

	<?php if ( in_array( 'fb', $share_buttons, true ) ) : ?>
		<button class="wq-share-fb wq_shareFB" data-url="<?php echo esc_url( $share_url ); ?>"><i class="sprite sprite-facebook"></i><span><?php esc_html_e( 'Facebook', 'wp-quiz-pro' ); ?></span></button>
	<?php endif; ?>

	<?php if ( in_array( 'tw', $share_buttons, true ) ) : ?>
		<button class="wq-share-tw wq_shareTwitter" data-url="<?php echo esc_url( $share_url ); ?>"><i class="sprite sprite-twitter"></i><span><?php esc_html_e( 'Twitter', 'wp-quiz-pro' ); ?></span></button>
	<?php endif; ?>

	<?php if ( in_array( 'g+', $share_buttons, true ) ) : ?>
		<button class="wq-share-gp wq_shareGP" data-url="<?php echo esc_url( $share_url ); ?>"><i class="sprite sprite-google-plus"></i><span><?php esc_html_e( 'Google+', 'wp-quiz-pro' ); ?></span></button>
	<?php endif; ?>

	<?php if ( in_array( 'vk', $share_buttons, true ) ) : ?>
		<button class="wq-share-vk wq_shareVK" data-url="<?php echo esc_url( $share_url ); ?>"><i class="sprite sprite-vk"></i><span><?php esc_html_e( 'VK', 'wp-quiz-pro' ); ?></span></button>
	<?php endif; ?>
</div>
<!--// social share-->

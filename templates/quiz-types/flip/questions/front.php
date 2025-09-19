<?php
/**
 * Template for front card of flip quiz
 *
 * @package WPQuiz
 *
 * @var array $question
 * @var Quiz  $quiz
 */

use WPQuiz\Quiz;

if ( ! $quiz->get_quiz_type()->has_front_card( $question ) ) {
	return;
}

$inline_css = array();

if ( ! empty( $question['frontBgColor'] ) ) {
	$inline_css[] = "background-color: {$question['frontBgColor']}";
}
if ( ! empty( $question['frontFontColor'] ) ) {
	$inline_css[] = "color: {$question['frontFontColor']}";
}

$style = $inline_css ? sprintf( 'style="%s"', esc_attr( implode( '; ', $inline_css ) ) ) : '';
?>
<div class="front" <?php echo $style; // WPCS: xss ok. ?>>
	<?php if ( ! empty( $question['image'] ) ) : ?>

		<?php if ( ! empty( $question['imageId'] ) ) : ?>
			<?php echo wp_get_attachment_image( $question['imageId'], 'full' ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( $question['image'] ); ?>" />
		<?php endif; ?>

		<?php if ( ! empty( $question['imageCredit'] ) ) : ?>
			<span class="credits"><?php echo wp_kses_post( $question['imageCredit'] ); ?></span>
		<?php endif; ?>

	<?php endif; ?>

	<?php if ( ! empty( $question['frontDesc'] ) ) : ?>
		<div class="desc"><div><?php echo wp_kses_post( $question['frontDesc'] ); ?></div></div>
	<?php endif; ?>

	<?php if ( $quiz->get_quiz_type()->has_back_card( $question ) ) : ?>
		<span class="top-desc"><?php esc_html_e( 'Click to Flip', 'wp-quiz-pro' ); ?></span>
	<?php endif; ?>
</div>

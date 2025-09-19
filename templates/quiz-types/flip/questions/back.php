<?php
/**
 * Template for back card of flip quiz
 *
 * @package WPQuiz
 *
 * @var array $question
 * @var Quiz  $quiz
 */

use WPQuiz\Quiz;

if ( ! $quiz->get_quiz_type()->has_back_card( $question ) ) {
	return;
}

$inline_css = array();

if ( ! empty( $question['backBgColor'] ) ) {
	$inline_css[] = "background-color: {$question['backBgColor']}";
}
if ( ! empty( $question['backFontColor'] ) ) {
	$inline_css[] = "color: {$question['backFontColor']}";
}

$style = $inline_css ? sprintf( 'style="%s"', esc_attr( implode( '; ', $inline_css ) ) ) : '';
?>
<div class="back" <?php echo $style; // WPCS: xss ok. ?>>
	<?php if ( ! empty( $question['backImage'] ) ) : ?>

		<?php if ( ! empty( $question['backImageId'] ) ) : ?>
			<?php echo wp_get_attachment_image( $question['backImageId'], 'full' ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( $question['backImage'] ); ?>" />
		<?php endif; ?>

		<?php if ( ! empty( $question['backImageCredit'] ) ) : ?>
			<span class="credits"><?php echo wp_kses_post( $question['backImageCredit'] ); ?></span>
		<?php endif; ?>

	<?php endif; ?>

	<?php if ( ! empty( $question['desc'] ) ) : ?>
		<div class="desc"><div><?php echo wp_kses_post( $question['desc'] ); ?></div></div>
	<?php endif; ?>
</div>

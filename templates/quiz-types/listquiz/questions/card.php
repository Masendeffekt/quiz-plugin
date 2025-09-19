<?php
/**
 * Template for question card of list quiz
 *
 * @package WPQuiz
 *
 * @var array $question
 * @var Quiz  $quiz
 */

use WPQuiz\Quiz;

$inline_css = array();

if ( ! empty( $question['bgColor'] ) ) {
	$inline_css[] = "background-color: {$question['bgColor']}";
}
if ( ! empty( $question['fontColor'] ) ) {
	$inline_css[] = "color: {$question['fontColor']}";
}

$no_image = empty( $question['image'] ) ? 'no-image' : '';

$style = $inline_css ? sprintf( 'style="%s"', esc_attr( implode( '; ', $inline_css ) ) ) : '';
?>
<div class="card <?php echo esc_attr( $no_image ); ?>" <?php echo $style; // WPCS: xss ok. ?>>
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

	<?php if ( ! empty( $question['desc'] ) ) : ?>
		<div class="desc"><div><?php echo wp_kses_post( $question['desc'] ); ?></div></div>
	<?php endif; ?>

</div>

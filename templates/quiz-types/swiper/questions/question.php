<?php
/**
 * Template for swiper question
 *
 * @package WPQuiz
 *
 * @var array        $question
 * @var \WPQuiz\Quiz $quiz
 */

$index_count = $question['index'] + 1 . '/' . $question['count'];
?>
<div class="wq-swiper-item" data-id="<?php echo esc_attr( $question['id'] ); ?>">

	<i class="wq-swiper-dislike-icon sprite sprite-times"></i>
	<i class="wq-swiper-like-icon sprite sprite-check"></i>

	<div class="wq-swiper-item-image">
		<?php if ( ! empty( $question['imageId'] ) ) : ?>
			<?php echo wp_get_attachment_image( $question['imageId'], 'full' ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( $question['image'] ); ?>" />
		<?php endif; ?>

		<span><?php echo wp_kses_post( $question['imageCredit'] ); ?></span>
	</div>

	<div class="wq-swiper-item-info">
		<span class="wq-swiper-item-title"><?php echo esc_html( $question['title'] ); ?></span>
		<span class="wq-swiper-item-index"><?php echo esc_html( $index_count ); ?></span>
	</div>

</div>

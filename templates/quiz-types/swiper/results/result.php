<?php
/**
 * Swiper result template
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 * @var array        $question
 */

$quiz_type = $quiz->get_quiz_type();
?>
<div class="<?php echo esc_attr( $quiz_type->get_result_classes( $quiz, $question ) ); ?>" data-id="<?php echo esc_attr( $question['id'] ); ?>" data-index="<?php echo intval( $question['index'] ); ?>">
	<div class="resultImageWrapper">

		<?php if ( ! empty( $question['imageId'] ) ) : ?>
			<?php echo wp_get_attachment_image( $question['imageId'], 'thumbnail' ); ?>
		<?php elseif ( ! empty( $question['image'] ) ) : ?>
			<img src="<?php echo esc_url( $question['image'] ); ?>">
		<?php endif; ?>

		<span class="indexWrapper"><?php echo intval( $question['index'] + 1 ); ?></span>
	</div>

	<div class="wq-result-content resultContent">
		<span><?php echo wp_kses_post( $question['title'] ); ?></span>
		<div class="resultUpDownVote">
			<span class="resultUpVote">
				<i class="sprite sprite-check"></i>
				<span class="wq-votes-up-count upVote"><?php echo esc_html( $quiz_type->format_number( $question['votesUp'] ) ); ?></span>
			</span>
			<span class="resultDownVote">
				<i class="sprite sprite-times"></i>
				<span class="wq-votes-down-count downVote"><?php echo esc_html( $quiz_type->format_number( $question['votesDown'] ) ); ?></span>
			</span>
		</div>
	</div>
</div>

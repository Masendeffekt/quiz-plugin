<?php
/**
 * Template for personality quiz result
 *
 * @package WPQuiz
 *
 * @var array        $result
 * @var string       $el_class
 * @var \WPQuiz\Quiz $quiz
 */

$quiz_type = $quiz->get_quiz_type();
?>
<div class="<?php echo esc_attr( $quiz_type->get_result_classes( $quiz, $result ) ); ?>" data-id="<?php echo esc_attr( $result['id'] ); ?>">

	<div class="wq-result-score wq_resultScoreCtr"></div>

	<div class="wq-result-title wq_resultTitle"><?php echo wp_kses_post( $result['title'] ); ?></div>

	<?php if ( ! empty( $result['imageId'] ) ) : ?>
		<p><?php echo wp_get_attachment_image( $result['imageId'], 'full', false, array( 'class' => 'wq-result-img wq_resultImg' ) ); ?></p>
	<?php elseif ( ! empty( $result['image'] ) ) : ?>
		<p><img class="wq-result-img wq_resultImg" src="<?php echo esc_url( $result['image'] ); ?>"></p>
	<?php endif; ?>

	<div class="wq_resultDesc"><?php echo wp_kses_post( $result['desc'] ); ?></div>
</div>

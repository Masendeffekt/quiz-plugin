<?php
/**
 * Template for quiz intro
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 * @var string       $message
 */

?>
<!-- Quiz intro -->
<div class="wq-quiz-intro-container wq_triviaQuizTimerInfoCtr">
	<?php if ( ! empty( $message ) ) : ?>
		<?php echo wp_kses_post( wpautop( $message ) ); ?>
	<?php endif; ?>

	<button type="button" class="wq-begin-quiz-btn wq_beginQuizCtr"><?php esc_html_e( 'Begin!', 'wp-quiz-pro' ); ?></button>
</div>
<!-- // Quiz intro-->

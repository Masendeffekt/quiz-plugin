<?php
/**
 * Template for FB share force action
 *
 * @package WPQuiz
 * @var \WPQuiz\Quiz $quiz
 */

?>
<div class="wq-force-action-fb-share wq_quizForceShareCtr">
	<p><?php esc_html_e( 'Please share this quiz to view your results.', 'wp-quiz-pro' ); ?></p>
	<button class="wq_forceShareFB" data-url="<?php echo esc_url( $quiz->get_url() ); ?>">
		<i class="wq-icon wq-icon-facebook"></i>
		<span><?php esc_html_e( 'Facebook', 'wp-quiz-pro' ); ?></span>
	</button>
</div>

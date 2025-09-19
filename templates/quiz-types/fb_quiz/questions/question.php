<?php
/**
 * Template for Facebook quiz question
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 * @var array        $question
 */

use WPQuiz\Helper;

$quiz_type       = $quiz->get_quiz_type();
$continue_as_btn = $quiz->get_setting( 'continue_as_btn' );
if ( ! $continue_as_btn ) {
	$continue_as_btn = Helper::get_option( 'continue_as_btn' );
}
$continue_as = 'on' === $continue_as_btn ? 'wq-continue-as-btn' : '';
?>
<div class="<?php echo esc_attr( $quiz_type->get_question_classes( $quiz, $question ) ); ?>">
	<div class="wq_loader-container">
		<div class="wq_loader_text">
			<?php Helper::spinner(); ?>
			<h3 id="wq_text_loader"><?php esc_html_e( 'Analyzing profile ...', 'wp-quiz-pro' ); ?></h3>
		</div>
	</div>

	<div class="wq_questionTextDescCtr">
		<h4><?php echo wp_kses_post( $question['title'] ); ?></h4>
		<?php if ( ! empty( $question['desc'] ) ) : ?>
			<p class="desc"><?php echo wp_kses_post( $question['desc'] ); ?></p>
		<?php endif; ?>
	</div>

	<div class="wq_questionMediaCtr">
		<?php echo $quiz_type->question_media( $question, $quiz ); ?>
	</div>

	<div></div>

	<div class="wq_questionLogin">
		<p><?php esc_html_e( 'Please login with Facebook to see your result', 'wp-quiz-pro' ); ?></p>
		<button class="wq_loginFB <?php echo esc_attr( $continue_as ); ?>">
			<i class="sprite sprite-facebook"></i>
			<span><?php esc_html_e( 'Login with Facebook', 'wp-quiz-pro' ); ?></span>
		</button>
	</div>
</div>

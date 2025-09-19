<?php
/**
 * Subscribe locker template
 *
 * @package WPQuizPro
 * @since 2.1.7
 *
 * @var Quiz $quiz
 */

use WPQuiz\Quiz;
use WPQuiz\Helper;

$form_title    = Helper::get_option( 'subscribe_locker_box_title' );
$consent_label = Helper::get_option( 'subscribe_box_user_consent' );
$consent_desc  = Helper::get_option( 'subscribe_box_user_consent_desc' );
?>
<form method="post" class="wq-locker wq-subscribe-locker" data-quiz-id="<?php echo intval( $quiz->get_id() ); ?>">
	<?php
	if ( $form_title ) {
		echo '<div class="wq-subscribe-locker__heading">' . esc_html( $form_title ) . '</div>';
	}
	?>

	<div class="wq-error"></div>

	<div class="wq-subscribe-locker__field">
		<label>
			<?php esc_html_e( 'Your name', 'wp-quiz-pro' ); ?>
			<input type="text" name="username" required>
		</label>
	</div>

	<div class="wq-subscribe-locker__field">
		<label>
			<?php esc_html_e( 'Your email', 'wp-quiz-pro' ); ?>
			<input type="email" name="email" required>
		</label>
	</div>

	<?php if ( $consent_label ) : ?>
		<div class="wq-subscribe-locker__field wq-consent-wrapper">
			<label>
				<input type="checkbox" name="consent" value="1" required>
				<?php echo esc_html( $consent_label ); ?>
			</label>
		</div>
	<?php endif; ?>

	<?php if ( $consent_desc ) : ?>
		<div class="wq-subscribe-locker__field wq-consent-desc"><?php echo wp_kses_post( $consent_desc ); ?></div>
	<?php endif; ?>

	<button><?php esc_html_e( 'Begin quiz', 'wp-quiz-pro' ); ?></button>
</form>

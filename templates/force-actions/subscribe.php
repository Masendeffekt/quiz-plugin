<?php
/**
 * Template for subscribe force action
 *
 * @package WPQuiz
 *
 * @var Quiz $quiz
 */

use WPQuiz\Quiz;
use WPQuiz\Helper;

$form_title    = Helper::get_option( 'subscribe_box_title' );
$consent_label = Helper::get_option( 'subscribe_box_user_consent' );
$consent_desc  = Helper::get_option( 'subscribe_box_user_consent_desc' );
$uniq_id       = wp_rand( 0, 100 );
?>
<div class="wq-force-action-subscribe wq_quizEmailCtr">
	<div class="wq-error"></div>

	<form class="wq-subscribe-form" action="" method="post">
		<p><?php echo esc_html( $form_title ); ?></p>

		<div>
			<label for="wq-subscribe-username-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Your first name:', 'wp-quiz-pro' ); ?></label>
			<input type="text" id="wq-subscribe-username-<?php echo esc_attr( $uniq_id ); ?>" name="username" required>
		</div>

		<div>
			<label for="wq-subscribe-email-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Your email address:', 'wp-quiz-pro' ); ?></label>
			<input type="email" id="wq-subscribe-email-<?php echo esc_attr( $uniq_id ); ?>" name="email" required>
		</div>

		<?php if ( $consent_label ) : ?>
			<div class="wq-consent-wrapper">
				<label>
					<input type="checkbox" name="consent" required />
					<?php echo esc_html( $consent_label ); ?>
				</label>
			</div>
		<?php endif; ?>

		<?php if ( $consent_desc ) : ?>
			<p class="wq-consent-desc"><?php echo wp_kses_post( $consent_desc ); ?></p>
		<?php endif; ?>

		<p><button type="submit"><?php esc_html_e( 'Show my results >>', 'wp-quiz-pro' ); ?></button></p>
	</form>
</div>

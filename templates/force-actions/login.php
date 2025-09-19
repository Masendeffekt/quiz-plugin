<?php
/**
 * Template for login force action
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 */

if ( is_user_logged_in() ) {
	return;
}
$uniq_id = mt_rand( 0, 100 );
?>
<div class="wq-force-login">
	<h4><?php esc_html_e( 'You need to login to view the result', 'wp-quiz-pro' ); ?></h4>

	<div class="wq-error"></div>

	<form class="wq-js-login-form wq-force-login__form" action="" method="post">

		<p>
			<label for="wq-login-username-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Username or email:', 'wp-quiz-pro' ); ?></label>
			<input type="text" id="wq-login-username-<?php echo esc_attr( $uniq_id ); ?>" name="username" required>
		</p>

		<p>
			<label for="wq-login-password-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Password:', 'wp-quiz-pro' ); ?></label>
			<input type="password" id="wq-login-password-<?php echo esc_attr( $uniq_id ); ?>" name="password" required>
		</p>

		<p class="wq-login-remember">
			<label>
				<input type="checkbox" name="remember" value="1">
				<?php esc_html_e( 'Remember', 'wp-quiz-pro' ); ?>
			</label>
		</p>

		<p><button type="submit"><?php esc_html_e( 'Login', 'wp-quiz-pro' ); ?></button></p>

		<p>
			<a href="#" class="wq-js-show-register-form"><?php esc_html_e( 'Create an account', 'wp-quiz-pro' ); ?></a> |
			<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" target="_blank"><?php esc_html_e( 'Forgot password', 'wp-quiz-pro' ); ?></a>
		</p>
	</form>

	<form class="wq-js-register-form wq-force-login__form wq-force-login__form--register" action="" method="post">

		<p>
			<label for="wq-register-first-name-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'First name:', 'wp-quiz-pro' ); ?></label>
			<input type="text" id="wq-register-first-name-<?php echo esc_attr( $uniq_id ); ?>" name="first_name" required>
		</p>

		<p>
			<label for="wq-register-last-name-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Last name:', 'wp-quiz-pro' ); ?></label>
			<input type="text" id="wq-register-last-name-<?php echo esc_attr( $uniq_id ); ?>" name="last_name" required>
		</p>

		<p>
			<label for="wq-register-username-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Username:', 'wp-quiz-pro' ); ?> (*)</label>
			<input type="text" id="wq-register-username-<?php echo esc_attr( $uniq_id ); ?>" name="username" required>
		</p>

		<p>
			<label for="wq-register-email-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Email address:', 'wp-quiz-pro' ); ?> (*)</label>
			<input type="email" id="wq-register-email-<?php echo esc_attr( $uniq_id ); ?>" name="email" required>
		</p>

		<p>
			<label for="wq-register-password-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Password:', 'wp-quiz-pro' ); ?> (*)</label>
			<input type="password" id="wq-register-password-<?php echo esc_attr( $uniq_id ); ?>" name="password" required>
		</p>

		<p>
			<label for="wq-register-password2-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Confirm password:', 'wp-quiz-pro' ); ?> (*)</label>
			<input type="password" id="wq-register-password2-<?php echo esc_attr( $uniq_id ); ?>" name="password2" required>
		</p>

		<p><button type="submit"><?php esc_html_e( 'Register', 'wp-quiz-pro' ); ?></button></p>

		<p>
			<a href="#" class="wq-js-show-login-form"><?php esc_html_e( 'Login', 'wp-quiz-pro' ); ?></a> |
			<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" target="_blank"><?php esc_html_e( 'Forgot password', 'wp-quiz-pro' ); ?></a>
		</p>
	</form>

</div><!-- End .wq-force-login -->

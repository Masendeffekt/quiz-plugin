<?php
/**
 * Quiz result email
 *
 * @package WPQuiz
 */

namespace WPQuiz\Emails;

use WPQuiz\Template;

/**
 * Class AdminResultEmail
 */
class AdminResultEmail extends Email {

	/**
	 * Whether to use HTML in email.
	 *
	 * @var bool
	 */
	protected $html = true;

	/**
	 * Prefix for hooks.
	 *
	 * @var string
	 */
	protected $prefix = 'wp_quiz_admin_result_email_';

	/**
	 * Gets email subject.
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected function subject() {
		$subject = apply_filters( "{$this->prefix}_subject", __( '[%%site_name%%] Someone played a quiz', 'wp-quiz-pro' ) );
		return $this->replace_string( $subject );
	}

	/**
	 * Gets email content body.
	 *
	 * @return string
	 */
	protected function content_body() {
		ob_start();
		Template::load_template( 'emails/admin-result/body.php' );
		$output = ob_get_clean();
		return apply_filters( "{$this->prefix}_body", $output );
	}

	/**
	 * Gets css for email content.
	 *
	 * @return string
	 */
	protected function content_css() {
		ob_start();
		Template::load_template( 'emails/admin-result/css.php' );
		$output = ob_get_clean();
		return apply_filters( "{$this->prefix}_css", $output );
	}
}

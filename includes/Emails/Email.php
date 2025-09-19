<?php
/**
 * Abstract email class
 *
 * @package WPQuiz
 */

namespace WPQuiz\Emails;

use Exception;
use Pelago\Emogrifier\CssInliner;

/**
 * Class Email
 */
abstract class Email {

	/**
	 * Whether to use HTML in email.
	 *
	 * @var bool
	 */
	protected $html = false;

	/**
	 * Replacements
	 *
	 * @var array
	 */
	protected $replacements = array();

	/**
	 * Prefix for hooks.
	 *
	 * @var string
	 */
	protected $prefix = 'wp_quiz_';

	/**
	 * Email constructor.
	 */
	public function __construct() {
		$this->replacements = array(
			'%%site_name%%' => get_bloginfo( 'name' ),
		);
	}

	/**
	 * Gets email subject.
	 *
	 * @abstract
	 * @access protected
	 *
	 * @return string
	 */
	abstract protected function subject();

	/**
	 * Gets email content header.
	 *
	 * @return string
	 */
	protected function content_header() {
		return '';
	}

	/**
	 * Gets email content footer.
	 *
	 * @return string
	 */
	protected function content_footer() {
		return '';
	}

	/**
	 * Gets email content body.
	 *
	 * @abstract
	 *
	 * @return string
	 */
	abstract protected function content_body();

	/**
	 * Gets css for email content.
	 *
	 * @return string
	 */
	protected function content_css() {
		return '';
	}

	/**
	 * Gets attachments.
	 *
	 * @return array
	 */
	protected function attachments() {
		return array();
	}

	/**
	 * Gets headers.
	 *
	 * @return array
	 */
	protected function headers() {
		/**
		 * Allows changing the email headers.
		 *
		 * @since 2.1.3
		 *
		 * @param array $headers Array of email headers.
		 * @param Email $email   Email object.
		 */
		return apply_filters( 'wp_quiz_email_headers', array(), $this );
	}

	/**
	 * Replaces string in email content.
	 *
	 * @param string $find    String needs to be replaced.
	 * @param string $replace Replaced string.
	 */
	public function replace( $find, $replace ) {
		$this->replacements[ $find ] = $replace;
	}

	/**
	 * Adds replaces data.
	 *
	 * @param array $replaces Replaces data.
	 */
	public function replaces( array $replaces ) {
		$this->replacements += $replaces;
	}

	/**
	 * Sends mail.
	 *
	 * @param string $recipient Recipient email address.
	 */
	public function send( $recipient ) {
		$content = $this->get_content();

		if ( $this->html ) {
			add_filter( 'wp_mail_content_type', array( $this, 'content_type_html' ) );
		}

		/**
		 * Fires before sending mails.
		 *
		 * @since 0.1.0
		 *
		 * @param Email $email Email object.
		 */
		do_action( "{$this->prefix}email_before_sending", $this );

		wp_mail( $recipient, $this->subject(), $content, $this->headers(), $this->attachments() );

		/**
		 * Fires after sending mail.
		 *
		 * @since 0.1.0
		 *
		 * @param Email $email Email object.
		 */
		do_action( "{$this->prefix}email_after_sending", $this );

		if ( $this->html ) {
			remove_filter( 'wp_mail_content_type', array( $this, 'content_type_html' ) );
		}
	}

	/**
	 * Gets email content.
	 *
	 * @return string
	 */
	public function get_content() {
		$content = $this->content_header() . $this->content_body() . $this->content_footer();
		$content = $this->replace_string( $content );

		if ( $this->html && $this->content_css() ) {
			try {
				$content = CssInliner::fromHtml( $content )->inlineCss( $this->content_css() )->render();
			} catch ( Exception $e ) {
				error_log( $e->getMessage() );
			}
		}

		return $content;
	}

	/**
	 * Replaces string.
	 *
	 * @param string $str String.
	 * @return string
	 */
	protected function replace_string( $str ) {
		if ( $this->replacements ) {
			$str = str_replace( array_keys( $this->replacements ), array_values( $this->replacements ), $str );
		}
		return $str;
	}

	/**
	 * Filters mail content type.
	 *
	 * @return string
	 */
	public function content_type_html() {
		return 'text/html';
	}
}

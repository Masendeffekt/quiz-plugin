<?php
/**
 * Mail service abstract class.
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription\MailServices;

use WP_Error;

/**
 * Class MailService.
 */
abstract class MailService {

	/**
	 * Service name.
	 *
	 * @var string
	 */
	protected $name = 'service-name';

	/**
	 * Service title.
	 *
	 * @var string
	 */
	protected $title = 'Service Name';

	/**
	 * Class MailService constructor.
	 */
	public function __construct() {
		add_action( 'wp_quiz_mail_service_options', array( $this, 'register_options' ), 10, 2 );
	}

	/**
	 * Gets name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Gets title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Gets prefix for registering options.
	 *
	 * @return string
	 */
	protected function get_options_prefix() {
		return $this->name . '_';
	}

	/**
	 * Gets cmb2 dependency param.
	 *
	 * @return array
	 */
	protected function get_dependency() {
		return array(
			array( 'mail_service', $this->name ),
		);
	}

	/**
	 * Registers options.
	 *
	 * @param \CMB2 $cmb CMB2 object.
	 */
	abstract public function register_options( \CMB2 $cmb );

	/**
	 * Subscribes email.
	 *
	 * @param string $email Email address.
	 * @param string $name  Subscriber name.
	 * @return mixed|false|WP_Error Return data base on API response or `false` or WP_Error on failure.
	 */
	abstract public function subscribe( $email, $name );
}

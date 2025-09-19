<?php
/**
 * Mailchimp mail service
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription\MailServices;

use Exception;
use WP_Error;
use WPQuiz\Helper;

/**
 * Class Mailchimp
 */
class Mailchimp extends MailService {

	/**
	 * Mailchimp constructor.
	 */
	public function __construct() {
		$this->name  = 'mailchimp';
		$this->title = __( 'Mailchimp', 'wp-quiz-pro' );
		parent::__construct();
	}

	/**
	 * Gets API object.
	 *
	 * @throws Exception Exception.
	 *
	 * @param string $api_key API key.
	 * @return \DrewM\MailChimp\MailChimp
	 */
	protected function get_mailchimp( $api_key ) {
		try {
			return new \DrewM\MailChimp\MailChimp( $api_key );
		} catch ( Exception $e ) {
			throw $e;
		}
	}

	/**
	 * Subscribes email.
	 *
	 * @param string $email Subscriber email address.
	 * @param string $name  Subscriber name.
	 *
	 * @return array|false|WP_Error
	 */
	public function subscribe( $email, $name ) {
		try {
			$api_key      = Helper::get_option( 'mailchimp_api_key' );
			$list_id      = Helper::get_option( 'mailchimp_list_id' );
			$double_optin = 'on' === Helper::get_option( 'mailchimp_double_optin' );

			/**
			 * Allow enabling double notification of mailchimp.
			 *
			 * @param bool $enable Enable or not.
			 */
			$double_optin = apply_filters( 'wp_quiz_mailchimp_double_notification', $double_optin );

			if ( ! $email || ! $api_key || ! $list_id ) {
				throw new Exception( __( 'Empty email, api key or list ID', 'wp-quiz-pro' ) );
			}

			$args = array(
				'email_address' => $email,
				'status'        => ! $double_optin ? 'subscribed' : 'pending',
			);
			if ( $name ) {
				$name_fields          = $this->get_name_fields( $name );
				$args['merge_fields'] = array(
					'FNAME' => $name_fields['FNAME'],
					'LNAME' => $name_fields['LNAME'],
				);
			}

			$mailchimp = $this->get_mailchimp( $api_key );

			$response = $mailchimp->post( "lists/{$list_id}/members", $args );
			if ( isset( $response['status'] ) && 200 !== intval( $response['status'] ) ) {
				return new WP_Error(
					$response['status'] . '-' . sanitize_title( $response['title'] ),
					$response['detail'],
					$response
				);
			}

			return $response;
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Gets name fields to pass to mailchimp API.
	 *
	 * @param string $name Subscriber name.
	 * @return array
	 */
	protected function get_name_fields( $name ) {
		$fname     = $name;
		$lname     = '';
		$space_pos = strpos( $name, ' ' );
		if ( $space_pos ) {
			$fname = substr( $name, 0, $space_pos );
			$lname = substr( $name, $space_pos );
		}
		return array(
			'FNAME' => $fname,
			'LNAME' => $lname,
		);
	}

	/**
	 * Registers subscription options.
	 *
	 * @param \CMB2 $cmb CMB2 object.
	 */
	public function register_options( \CMB2 $cmb ) {
		$dep    = $this->get_dependency();
		$prefix = $this->get_options_prefix();

		$desc = sprintf(
			// translators: API key link.
			esc_html__( 'The %s of your MailChimp account.', 'wp-quiz-pro' ),
			'<a href="https://us1.admin.mailchimp.com/account/api/" target="_blank">' . esc_html__( 'API key', 'wp-quiz-pro' ) . '</a>'
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'API Key', 'wp-quiz-pro' ),
				'id'   => $prefix . 'api_key',
				'type' => 'text',
				'desc' => $desc,
				'dep'  => $dep,
				'attributes'  => array(
					'type' => 'password',
				),
			)
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'List ID', 'wp-quiz-pro' ),
				'id'   => $prefix . 'list_id',
				'type' => 'text',
				'dep'  => $dep,
			)
		);

		$cmb->add_field(
			array(
				'name'    => esc_html__( 'Double Opt-in', 'wp-quiz-pro' ),
				'desc'    => esc_html__( 'Send double opt-in notification', 'wp-quiz-pro' ),
				'id'      => $prefix . 'double_optin',
				'type'    => 'switch',
				'default' => 'off',
				'dep'     => $dep,
			)
		);
	}
}

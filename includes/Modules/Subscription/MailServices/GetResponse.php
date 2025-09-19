<?php
/**
 * GetResponse mail service
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription\MailServices;

use Exception;
use WP_Error;
use WPQuiz\Helper;
use \GetResponse as GetResponseAPI;

/**
 * Class GetResponse
 */
class GetResponse extends MailService {

	/**
	 * Class GetResponse constructor.
	 */
	public function __construct() {
		$this->name  = 'getresponse';
		$this->title = __( 'GetResponse', 'wp-quiz-pro' );
		parent::__construct();
	}

	/**
	 * Gets GetResponse API object.
	 *
	 * @param string $api_key API key.
	 * @return GetResponseAPI
	 */
	protected function get_getresponse( $api_key ) {
		return new GetResponseAPI( $api_key );
	}

	/**
	 * Subscribes email.
	 *
	 * @param string $email Email address.
	 * @param string $name  Subscriber name.
	 * @return mixed|false Return data base on API response or `false` on failure.
	 *
	 * @throws Exception Exception.
	 */
	public function subscribe( $email, $name ) {
		try {
			$api_key = Helper::get_option( 'getresponse_api_key' );
			$list_id = Helper::get_option( 'getresponse_campaign_name' );

			if ( ! $email || ! $api_key || ! $list_id ) {
				throw new Exception( __( 'Empty email, api key or list ID', 'wp-quiz-pro' ) );
			}

			$getresponse = $this->get_getresponse( $api_key );

			$data = array(
				'campaign'   => array( 'campaignId' => $list_id ),
				'email'      => $email,
				'dayOfCycle' => 0,
				'name'       => $name,
			);

			$result = $getresponse->addContact( $data );

			if ( isset( $result->httpStatus ) && 200 !== intval( $result->httpStatus ) ) {
				return new WP_Error( $result->httpStatus, $result->message, $result );
			}
			return true;
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage(), $e );
		}
	}

	/**
	 * Registers options.
	 *
	 * @param \CMB2 $cmb CMB2 object.
	 */
	public function register_options( \CMB2 $cmb ) {
		$dep    = $this->get_dependency();
		$prefix = $this->get_options_prefix();

		$desc = sprintf(
			// translators: API key link.
			esc_html__( 'The %s of your GetResponse account.', 'wp-quiz-pro' ),
			'<a href="https://app.getresponse.com/api" target="_blank">' . esc_html__( 'API key', 'wp-quiz-pro' ) . '</a>'
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
				'name' => esc_html__( 'List Token', 'wp-quiz-pro' ),
				'id'   => $prefix . 'campaign_name',
				'desc' => __( 'Please go to the campaign setting to get this value', 'wp-quiz-pro' ),
				'type' => 'text',
				'dep'  => $dep,
			)
		);
	}
}

<?php
/**
 * ConvertKit mail service
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription\MailServices;

use CMB2;
use Exception;
use WP_Error;
use WPQuiz\Helper;
use WPS_ConvertKitApi;

/**
 * Class ConvertKit
 */
class ConvertKit extends MailService {

	/**
	 * ConvertKit constructor.
	 */
	public function __construct() {
		$this->name  = 'convertkit';
		$this->title = __( 'ConvertKit', 'wp-quiz-pro' );
		parent::__construct();
	}

	/**
	 * Gets API object.
	 *
	 * @throws Exception Exception.
	 *
	 * @param string $api_key API key.
	 * @return WPS_ConvertKitApi
	 */
	protected function get_api( $api_key ) {
		try {
			require_once wp_quiz()->includes_dir() . 'Modules/Subscription/libs/class-wps-convertkitapi.php';
			return new WPS_ConvertKitApi( $api_key );
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
			$api_key = Helper::get_option( 'convertkit_api_key' );
			$list_id = Helper::get_option( 'convertkit_list_id' );

			if ( ! $email || ! $api_key || ! $list_id ) {
				throw new Exception( __( 'Empty email, api key or list ID', 'wp-quiz-pro' ) );
			}

			$api      = $this->get_api( $api_key );
			$response = $api->subscribeToAForm( $list_id, $email, $name );
			if ( ! $response ) {
				return false;
			}

			$response = json_decode( $response, true );
			if ( ! empty( $response['subscription'] ) ) {
				return true;
			}
			if ( isset( $response['error'] ) ) {
				return new WP_Error( sanitize_title( $response['error'] ), $response['message'] );
			}
			return new WP_Error( 'unknown-error', 'Unknows error', $response );
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Registers subscription options.
	 *
	 * @param CMB2 $cmb CMB2 object.
	 */
	public function register_options( CMB2 $cmb ) {
		$dep    = $this->get_dependency();
		$prefix = $this->get_options_prefix();

		$cmb->add_field(
			array(
				'name'       => esc_html__( 'API Key', 'wp-quiz-pro' ),
				'id'         => $prefix . 'api_key',
				'type'       => 'text',
				'dep'        => $dep,
				'attributes' => array(
					'type' => 'password',
				),
			)
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'Form ID', 'wp-quiz-pro' ),
				'id'   => $prefix . 'list_id',
				'type' => 'text',
				'dep'  => $dep,
			)
		);
	}
}

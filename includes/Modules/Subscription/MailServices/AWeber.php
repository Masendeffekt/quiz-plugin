<?php
/**
 * AWeber mail service
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription\MailServices;

use CMB2;
use Exception;
use GuzzleHttp\Client;
use WP_Error;
use WPQuiz\Helper;

/**
 * Class AWeber
 */
class AWeber extends MailService {

	/**
	 * AWeber Client ID.
	 *
	 * @var string
	 */
	const CLIENT_ID = 'j0G5I13c6qQRJspwqJQ9J0Ihr6dMWxuO';

	/**
	 * AWeber redirect URL.
	 *
	 * @var string
	 */
	const REDIRECT_URI = 'urn:ietf:wg:oauth:2.0:oob';

	/**
	 * AWeber API base URL.
	 *
	 * @var string
	 */
	const BASE_URL = 'https://api.aweber.com/1.0/';

	/**
	 * Subscriber ad tracking.
	 *
	 * @var string
	 */
	const AD_TRACKING = 'wp-quiz-pro';

	/**
	 * List ID.
	 *
	 * @var string
	 */
	protected $list_id = '';

	/**
	 * Class AWeber constructor.
	 */
	public function __construct() {
		$this->name  = 'aweber';
		$this->title = __( 'AWeber', 'wp-quiz-pro' );

		$options = Helper::get_option( 'aweber' );
		if ( ! empty( $options['listid'] ) ) {
			$this->list_id = str_replace( 'awlist', '', $options['listid'] );
		}

		add_action( 'wp_ajax_wp_quiz_connect_aweber', array( $this, 'handle_ajax_connect' ) );
		add_action( 'wp_ajax_wp_quiz_disconnect_aweber', array( $this, 'handle_ajax_disconnect' ) );
		add_action( 'wp_ajax_wp_quiz_refresh_aweber_lists', array( $this, 'handle_ajax_refresh_lists' ) );

		parent::__construct();
	}

	/**
	 * Gets aweber API code challenge data.
	 *
	 * @return array
	 */
	public function get_code_challenge() {
		$data = Helper::get_option( 'aweber_code_challenge' );

		if ( ! $data ) {
			$verifier_byte  = Helper::generate_random_string( 64 );
			$code_verifier  = rtrim( strtr( base64_encode( $verifier_byte ), '+/', '-_' ), '=' );
			$challenge_byte = hash( 'sha256', $code_verifier, true );
			$code_challenge = rtrim( strtr( base64_encode( $challenge_byte ), '+/', '-_' ), '=' );
			$data = compact( 'code_verifier', 'code_challenge' );
			Helper::update_option( 'aweber_code_challenge', $data );
		}

		return $data;
	}

	/**
	 * Gets token data.
	 *
	 * @return array|null
	 */
	protected function get_token_data() {
		return Helper::get_option( 'aweber_refresh_token_data' );
	}

	/**
	 * Checks if AWeber is connected.
	 *
	 * @return bool
	 */
	public function is_connected() {
		$options = $this->get_token_data();
		return ! empty( $options['access_token'] );
	}

	/**
	 * Refreshes token.
	 *
	 * @return bool|WP_Error
	 */
	public function refresh_token() {
		$token_data = $this->get_token_data();
		if ( empty( $token_data['refresh_token'] ) ) {
			return new WP_Error( 'not-connected', __( 'Not connected!', 'wp-quiz-pro' ) );
		}

		$response = wp_remote_post(
			'https://auth.aweber.com/oauth2/token',
			array(
				'body' => 'grant_type=refresh_token&refresh_token=' . $token_data['refresh_token'] . '&client_id=' . self::CLIENT_ID,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$response_body = json_decode( $response_body, true );

		if ( ! empty( $response_body['error'] ) ) {
			return new WP_Error( $response_body['error'], $response_body['error_description'] );
		}

		Helper::update_option( 'aweber_refresh_token_data', $response_body );
		return true;
	}

	/**
	 * Gets login URL.
	 *
	 * @return string
	 */
	public function get_login_url() {
		$code_challenge = $this->get_code_challenge();
		$params         = array(
			'response_type'         => 'code',
			'client_id'             => self::CLIENT_ID,
			'redirect_uri'          => self::REDIRECT_URI,
			'scope'                 => 'subscriber.write+account.read+list.read+subscriber.read-extended',
			'code_challenge'        => $code_challenge['code_challenge'],
			'code_challenge_method' => 'S256',
		);
		return add_query_arg( $params, 'https://auth.aweber.com/oauth2/authorize' );
	}

	/**
	 * Handles AJAX connect AWeber.
	 */
	public function handle_ajax_connect() {
		check_ajax_referer( 'wp_rest' );
		if ( empty( $_POST['token'] ) ) {
			wp_send_json_error( __( 'Empty token', 'wp-quiz-pro' ) );
		}

		$code_challenge = self::get_code_challenge();
		$response       = wp_remote_post(
			'https://auth.aweber.com/oauth2/token',
			array(
				'body'    => 'grant_type=authorization_code&code=' . $_POST['token'] . '&redirect_uri=' . self::REDIRECT_URI . '&client_id=' . self::CLIENT_ID . '&code_verifier=' . $code_challenge['code_verifier'],
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		$response_body = wp_remote_retrieve_body( $response );

		Helper::update_option( 'aweber_refresh_token_data', json_decode( $response_body, true ) );

		wp_send_json_success();
	}

	/**
	 * Handles AJAX disconnect AWeber.
	 */
	public function handle_ajax_disconnect() {
		check_ajax_referer( 'wp_rest' );
		Helper::delete_option( 'aweber_refresh_token_data' );
		wp_send_json_success();
	}

	/**
	 * Handles AJAX refresh lists.
	 */
	public function handle_ajax_refresh_lists() {
		check_ajax_referer( 'wp_rest' );
		if ( ! $this->is_connected() ) {
			$lists = array();
		} else {
			$lists = $this->get_lists( true );
		}
		wp_send_json_success( $lists );
	}

	/**
	 * Gets all of the entries for a collection.
	 *
	 * @param Client $client       HTTP Client used to make a GET request.
	 * @param string $access_token Access token to pass in as an authorization header.
	 * @param string $url          Full url to make the request.
	 * @return array Every entry in the collection
	 */
	protected function get_collection( Client $client, $access_token, $url ) {
		$collection = array();
		while ( isset( $url ) ) {
			$request = $client->get(
				$url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
					),
				)
			);

			$body       = $request->getBody();
			$page       = json_decode( $body, true );
			$collection = array_merge( $page['entries'], $collection );
			$url        = isset( $page['next_collection_link'] ) ? $page['next_collection_link'] : null;
		}
		return $collection;
	}

	/**
	 * Subscribes email.
	 *
	 * @param string $email Email address.
	 * @param string $name  Subscriber name.
	 * @return mixed|false|WP_Error
	 */
	public function subscribe( $email, $name ) {
		$result = $this->do_subscribe( $email, $name );

		if ( $result instanceof Exception && 401 === $result->getCode() ) {
			$this->refresh_token();
			$result = $this->do_subscribe( $email, $name );
		}

		if ( $result instanceof Exception ) {
			return new WP_Error( $result->getCode(), $result->getMessage(), $result );
		}

		return $result;
	}

	/**
	 * Gets the lists.
	 *
	 * @param bool $force Force to call the API and update the cache value.
	 * @return array|Exception
	 */
	public function get_lists( $force = false ) {
		$lists = get_transient( 'aweber_lists' );
		if ( $lists && ! $force ) {
			return $lists;
		}

		try {
			$client       = new Client();
			$token_data   = $this->get_token_data();
			$access_token = $token_data['access_token'];

			$accounts = $this->get_collection( $client, $access_token, self::BASE_URL . 'accounts' );

			// Get all the list entries for the first account.
			$list_url = $accounts[0]['lists_collection_link'];
			$lists    = $this->get_collection( $client, $access_token, $list_url );

			set_transient( 'aweber_lists', $lists );

			return $lists;
		} catch ( Exception $e ) {
			if ( 401 === $e->getCode() ) {
				$this->refresh_token();
				return $this->get_lists( $force );
			}

			return $e;
		}
	}

	/**
	 * Does subscribe.
	 *
	 * @param string $email Email address.
	 * @param string $name  Subscribe name.
	 * @return true|Exception
	 */
	protected function do_subscribe( $email, $name ) {
		try {
			if ( ! $this->is_connected() ) {
				throw new Exception( __( 'Not connected!', 'wp-quiz-pro' ) );
			}

			$client       = new Client();
			$token_data   = $this->get_token_data();
			$access_token = $token_data['access_token'];

			$accounts    = $this->get_collection( $client, $access_token, self::BASE_URL . 'accounts' );
			$account_url = $accounts[0]['self_link'];

			if ( ! empty( $this->list_id ) ) {
				$subs_url = "{$account_url}/lists/{$this->list_id}/subscribers";
			} else {
				// Get all the list entries for the first account.
				$list_url = $accounts[0]['lists_collection_link'];
				$lists    = $this->get_collection( $client, $access_token, $list_url );

				if ( ! isset( $lists[0]['subscribers_collection_link'] ) ) {
					// There is no list.
					throw new Exception( __( 'No AWeber list found!', 'wp-quiz-pro' ) );
				}

				$subs_url = $lists[0]['subscribers_collection_link'];
			}

			// Find out if a subscriber exists on the first list.
			$params     = array(
				'ws.op' => 'find',
				'email' => $email,
			);

			$find_url   = $subs_url . '?' . http_build_query( $params );
			$found_subs = $this->get_collection( $client, $access_token, $find_url );

			if ( ! isset( $found_subs[0]['self_link'] ) ) {
				// Add the subscriber if they are not already on the first list.
				$data = array(
					'email'       => $email,
					'name'        => $name,
					'ad_tracking' => self::AD_TRACKING,
				);

				$body = $client->post(
					$subs_url,
					array(
						'json'    => $data,
						'headers' => array( 'Authorization' => 'Bearer ' . $access_token ),
					)
				);

				// Get the subscriber entry using the Location header from the post request.
				$subscriber_url      = $body->getHeader( 'Location' )[0];
				$subscriber_response = $client->get(
					$subscriber_url,
					array( 'headers' => array( 'Authorization' => 'Bearer ' . $access_token ) )
				)->getBody();
				return json_decode( $subscriber_response, true );
			}

			// Update the subscriber if they are on the first list.
			$data = array(
				'name'        => $name,
				'ad_tracking' => self::AD_TRACKING,
			);

			$subscriber_url      = $found_subs[0]['self_link'];
			$subscriber_response = $client->patch(
				$subscriber_url,
				array(
					'json'    => $data,
					'headers' => array( 'Authorization' => 'Bearer ' . $access_token ),
				)
			)->getBody();

			return json_decode( $subscriber_response, true );
		} catch ( Exception $e ) {
			return $e;
		}
	}

	/**
	 * Registers options.
	 *
	 * @param CMB2 $cmb CMB2 object.
	 */
	public function register_options( CMB2 $cmb ) {
		$cmb->add_field(
			array(
				'id'   => 'aweber',
				'type' => 'aweber',
				'name' => __( 'AWeber options', 'wp-quiz-pro' ),
				'dep'  => $this->get_dependency(),
			)
		);
	}
}

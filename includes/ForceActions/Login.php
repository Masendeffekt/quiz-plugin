<?php
/**
 * Login force action
 *
 * @package WPQuiz
 */

namespace WPQuiz\ForceActions;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WPQuiz\Quiz;
use WPQuiz\REST\REST;
use WPQuiz\Template;

/**
 * Class Login
 */
class Login extends ForceAction {

	/**
	 * Login constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->id    = 'login';
		$this->title = __( 'Login/Register', 'wp-quiz-pro' );

		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Gets force action output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function output( Quiz $quiz ) {
		if ( is_user_logged_in() ) {
			return '';
		}
		ob_start();
		Template::load_template( 'force-actions/login.php', compact( 'quiz' ) );
		return ob_get_clean();
	}

	/**
	 * Enqueues css and js.
	 */
	public function enqueue() {
		wp_enqueue_script( 'wp-quiz-force-action-login', wp_quiz()->assets() . 'js/force-actions/login.js', array( 'jquery', 'wp-quiz' ), '2.0.0', true );
	}

	/**
	 * Registers REST routes.
	 */
	public function register_rest_routes() {
		register_rest_route(
			REST::REST_NAMESPACE,
			'login',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'login' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			REST::REST_NAMESPACE,
			'register',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'register' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * REST login.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function login( WP_REST_Request $request ) {
		$username = $request->get_param( 'username' );
		$password = $request->get_param( 'password' );
		$remember = $request->get_param( 'remember' );

		if ( ! $username || ! $password ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => __( 'Username or password must not be empty', 'wp-quiz-pro' ),
				)
			);
		}

		$credentials = array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		);

		$user = wp_signon( $credentials );
		if ( is_wp_error( $user ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => $user->get_error_message(),
				)
			);
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * REST login.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function register( WP_REST_Request $request ) {
		$first_name = $request->get_param( 'first_name' );
		$last_name  = $request->get_param( 'last_name' );
		$username   = $request->get_param( 'username' );
		$email      = $request->get_param( 'email' );
		$password   = $request->get_param( 'password' );
		$password2  = $request->get_param( 'password2' );

		if ( ! $username || ! $email || ! $password || ! $password2 ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => __( 'You must fill all required fields', 'wp-quiz-pro' ),
				)
			);
		}

		if ( $password !== $password2 ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => __( 'Password does not match', 'wp-quiz-pro' ),
				)
			);
		}

		$user_data = array(
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => $password,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'role'       => 'wp_quiz_player',
		);

		$user = wp_insert_user( $user_data );
		if ( is_wp_error( $user ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => $user->get_error_message(),
				)
			);
		}

		wp_signon(
			array(
				'user_login'    => $email,
				'user_password' => $password,
			)
		);

		return rest_ensure_response( array( 'success' => true ) );
	}
}

<?php
/**
 * Admin REST routes
 *
 * @package WPQuiz
 */

namespace WPQuiz\REST;

use Exception;
use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Request;
use WPQuiz\Admin\AdminPages\Support;
use WPQuiz\Importer;
use WPQuiz\Modules\Subscription\MailServices\Manager;

/**
 * Class Admin
 */
class Admin extends REST {

	/**
	 * REST base.
	 *
	 * @var string
	 */
	protected $rest_base = 'admin/';

	/**
	 * Checks if user can use REST request.
	 *
	 * @return bool
	 */
	public function permission_callback() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Registers REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'environment-info',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_environment_info' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			$this->rest_base . 'import-quizzes',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'import_quizzes' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			$this->rest_base . 'import-quizzes-progress',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'import_quizzes_progress' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			$this->rest_base . 'connect-aweber',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'connect_aweber' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			$this->rest_base . 'disconnect-aweber',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'disconnect_aweber' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
			)
		);
	}

	/**
	 * REST gets environment info.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function get_environment_info( WP_REST_Request $request ) {
		$support_page = new Support();
		return rest_ensure_response( $support_page->debug_data_output() );
	}

	/**
	 * REST imports quizzes.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function import_quizzes( WP_REST_Request $request ) {
		$importer        = new Importer();
		$download_images = $request->get_param( 'download_images' );
		$force_new       = $request->get_param( 'force_new' );
		$importer->set_download_images( $download_images && 'false' !== $download_images );
		$importer->set_author( get_current_user_id() );
		$importer->import_quizzes( $request->get_param( 'quizzes' ), $force_new && 'false' !== $force_new );
		return true;
	}

	/**
	 * REST gets import quizzes progress.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function import_quizzes_progress( WP_REST_Request $request ) {
		return rest_ensure_response(
			array(
				'remain' => wp_quiz()->import_process->get_remain(),
			)
		);
	}

	/**
	 * REST connects AWeber.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function connect_aweber( WP_REST_Request $request ) {
		$auth_code = $request->get_param( 'auth_code' );
		if ( ! $auth_code ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => __( 'The auth code must not be empty', 'wp-quiz-pro' ),
				)
			);
		}

		$aweber = Manager::get( 'aweber' );
		if ( ! $aweber ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => __( 'AWeber subscription not found', 'wp-quiz-pro' ),
				)
			);
		}

		try {
			$credentials = $aweber->connect( $auth_code );

			$option_id      = $request->get_param( 'option_id' );
			$options        = get_option( 'wp_quiz_pro_default_settings' );
			$aweber_options = ! empty( $options[ $option_id ] ) ? $options[ $option_id ] : array();
			$aweber_options = wp_parse_args( $credentials, $aweber_options );

			$options[ $option_id ] = $aweber_options;
			update_option( 'wp_quiz_pro_default_settings', $options );

			return rest_ensure_response(
				array(
					'success' => true,
					'data'    => $credentials,
				)
			);
		} catch ( Exception $e ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * REST disconnects AWeber.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function disconnect_aweber( WP_REST_Request $request ) {
		$option_id = $request->get_param( 'option_id' );
		$options   = get_option( 'wp_quiz_pro_default_settings' );
		if ( isset( $options[ $option_id ] ) ) {
			unset( $options[ $option_id ] );
		}
		return rest_ensure_response(
			array(
				'success' => update_option( 'wp_quiz_pro_default_settings', $options ),
			)
		);
	}
}

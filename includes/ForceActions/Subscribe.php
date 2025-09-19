<?php
/**
 * Subscribe force action
 *
 * @package WPQuiz
 */

namespace WPQuiz\ForceActions;

use CMB2;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WPQuiz\Helper;
use WPQuiz\Modules\Subscription\Database;
use WPQuiz\PostTypeQuiz;
use WPQuiz\Quiz;
use WPQuiz\REST\REST;
use WPQuiz\Template;

/**
 * Class Subscribe
 */
class Subscribe extends ForceAction {

	/**
	 * Subscribe constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->id    = '1';
		$this->title = __( 'Capture Email', 'wp-quiz-pro' );

		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Gets force action output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function output( Quiz $quiz ) {
		ob_start();
		Template::load_template( 'force-actions/subscribe.php', compact( 'quiz' ) );
		return ob_get_clean();
	}

	/**
	 * Registers custom options.
	 *
	 * @param CMB2   $cmb       CMB2 object.
	 * @param string $where     Where to register. Accepts `settings`, `meta_box`.
	 * @param string $quiz_type Quiz type.
	 */
	public function custom_options( CMB2 $cmb, $where = 'settings', $quiz_type = '*' ) {
		$prefix = '';
		$dep    = array();

		$defaults = Helper::get_default_options();

		if ( 'meta_box' === $where ) {
			$prefix = 'wp_quiz_';
			$dep    = array(
				array( 'wp_quiz_force_action', $this->get_id() ),
			);
		}

		if ( 'settings' === $where ) {
			$cmb->add_field(
				array(
					'id'      => $prefix . 'subscribe_locker_box_title',
					'type'    => 'text',
					'name'    => __( 'Subscribe To Play Form Title', 'wp-quiz-pro' ),
					'dep'     => $dep,
					'default' => $defaults['subscribe_locker_box_title'],
				)
			);

			$cmb->add_field(
				array(
					'id'      => $prefix . 'subscribe_box_title',
					'type'    => 'text',
					'name'    => __( 'Subscribe Box Title', 'wp-quiz-pro' ),
					'dep'     => $dep,
					'default' => $defaults['subscribe_box_title'],
				)
			);

			$cmb->add_field(
				array(
					'id'      => $prefix . 'subscribe_box_user_consent',
					'type'    => 'text',
					'name'    => __( 'Consent Label', 'wp-quiz-pro' ),
					'dep'     => $dep,
					'default' => $defaults['subscribe_box_user_consent'],
				)
			);

			$cmb->add_field(
				array(
					'id'      => $prefix . 'subscribe_box_user_consent_desc',
					'type'    => 'wysiwyg',
					'name'    => __( 'Consent Description', 'wp-quiz-pro' ),
					'dep'     => $dep,
					'default' => $defaults['subscribe_box_user_consent_desc'],
				)
			);
		}

		parent::custom_options( $cmb, $where, $quiz_type );
	}

	/**
	 * Enqueues css and js.
	 */
	public function enqueue() {
		wp_enqueue_script( 'wp-quiz-force-action-subscribe', wp_quiz()->assets() . 'js/force-actions/subscribe.js', array( 'jquery', 'wp-quiz' ), '2.0.0', true );
	}

	/**
	 * Registers REST routes.
	 */
	public function register_rest_routes() {
		return; // TODO.
		register_rest_route(
			REST::REST_NAMESPACE,
			'subscribe',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'rest_subscribe' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * REST subscribes.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function rest_subscribe( WP_REST_Request $request ) {
		$data = $request->get_params();

		if ( empty( $data['quiz_id'] ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => __( 'Invalid quiz', 'wp-quiz-pro' ),
				)
			);
		}

		$quiz = PostTypeQuiz::get_quiz( $data['quiz_id'] );
		if ( ! $quiz ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => __( 'Invalid quiz', 'wp-quiz-pro' ),
				)
			);
		}

		if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'data'    => __( 'Invalid email address', 'wp-quiz-pro' ),
				)
			);
		}

		/**
		 * Fires when subscribing an email.
		 *
		 * @since 2.0.0
		 *
		 * @param array $data Subscribe data.
		 * @param Quiz  $quiz Quiz object.
		 */
		do_action( 'wp_quiz_subscribe_email', $data, $quiz );

		$mail_service         = Helper::get_option( 'mail_service' );
		$data['mail_service'] = $mail_service;
		$email                = $data['email'];
		$name                 = ! empty( $data['username'] ) ? $data['username'] : '';
		if ( $mail_service ) {
			// Mail subscription.
			if ( ! Helper::subscribe_email( $email, $name ) ) {
				error_log( sprintf( __( "Can't subscribe email %s", 'wp-quiz-pro' ), $email ) );
			}
		}

		// Add database record.
		$emails = new Database();
		if ( ! $emails->has( $email ) ) {
			$email_id = $emails->add( $data );

			if ( ! $email_id ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'data'    => __( 'Failed to add email record', 'wp-quiz-pro' ),
					)
				);
			}
		}

		return rest_ensure_response( array( 'success' => true ) );
	}
}

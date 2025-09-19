<?php
/**
 * Backend assets management
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin;

use WPQuiz\QuizTypeManager;

/**
 * Class Assets
 */
class Assets {

	/**
	 * Import/Export page screen ID.
	 *
	 * @var string
	 */
	const IMPORT_EXPORT_PAGE = 'wp_quiz_page_wp_quiz_ie';

	/**
	 * Settings page screen ID.
	 *
	 * @var string
	 */
	const SETTINGS_PAGE = 'wp_quiz_page_wp_quiz_pro_default_settings';

	/**
	 * New quiz page screen ID.
	 *
	 * @var string
	 */
	const NEW_QUIZ_PAGE = 'wp_quiz_page_wp-quiz-new';

	/**
	 * Edit quiz page screen ID.
	 *
	 * @var string
	 */
	const EDIT_QUIZ_PAGE = 'wp_quiz';

	/**
	 * Initializes.
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueues assets.
	 */
	public function enqueue() {
		$screen     = get_current_screen();
		$quiz_types = QuizTypeManager::get_all( true );

		wp_register_style( 'jquery-tipsy', wp_quiz()->admin_assets() . 'libs/tipsy/tipsy.css', array(), '1.0.0a' );
		wp_register_script( 'jquery-tipsy', wp_quiz()->admin_assets() . 'libs/tipsy/jquery.tipsy.js', array( 'jquery' ), '1.0.0a', true );

//		wp_register_script( 'wp-quiz-babel-helpers', wp_quiz()->admin_assets() . 'js/babel-helpers.js', array(), '2.0.0', true );

		wp_register_script( 'jquery.serialize-object', wp_quiz()->admin_assets() . 'js/jquery.serialize-object.js', array( 'jquery' ), '2.5.0', true ); // WordPress has jquery-serialize-object script in core.

		wp_register_style( 'wp-quiz-admin', wp_quiz()->admin_assets() . 'css/admin.css', array(), wp_quiz()->version );
		wp_register_script( 'wp-quiz-admin', wp_quiz()->admin_assets() . 'js/admin.js', array( 'jquery-ui-sortable' ), wp_quiz()->version, true );

		wp_localize_script(
			'wp-quiz-admin',
			'wpQuizAdmin',
			array(
				'restUrl'   => rest_url(),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
				'i18n'      => array(
					'quizTypeSelect'        => __( 'Select quiz type', 'wp-quiz-pro' ),
					'editImage'             => __( 'Edit image', 'wp-quiz-pro' ),
					'uploadImage'           => __( 'Upload image', 'wp-quiz-pro' ),
					'appIdMustNotEmpty'     => __( 'The app ID must not be empty', 'wp-quiz-pro' ),
					'accessKeyMustNotEmpty' => __( 'The access key must not be empty', 'wp-quiz-pro' ),
				),
			)
		);

		wp_register_style( 'wp-quiz-meta-boxes', wp_quiz()->admin_assets() . 'css/meta-boxes.css', array(), wp_quiz()->version );
		wp_register_script( 'wp-quiz-meta-boxes', wp_quiz()->admin_assets() . 'js/meta-boxes.js', array( 'wp-quiz-admin', 'jquery.serialize-object', 'jquery-tipsy', 'jquery-ui-sortable' ), wp_quiz()->version, true );

		wp_register_style( 'wp-quiz-admin-rtl', wp_quiz()->admin_assets() . 'css/admin-rtl.css', array(), wp_quiz()->version );

		foreach ( $quiz_types as $quiz_type ) {
			wp_register_script( 'wp-quiz-admin-' . $quiz_type->get_name(), wp_quiz()->admin_assets() . "js/quiz-types/{$quiz_type->get_name()}.js", array( 'wp-quiz-meta-boxes' ), wp_quiz()->version, true );
		}

		// Enqueue.
		if ( in_array( $screen->id, array( self::IMPORT_EXPORT_PAGE, self::SETTINGS_PAGE, self::NEW_QUIZ_PAGE, self::EDIT_QUIZ_PAGE ), true ) ) {
			wp_enqueue_style( 'wp-quiz-admin' );

			if ( is_rtl() ) {
				wp_enqueue_style( 'wp-quiz-admin-rtl' );
			}

//			wp_enqueue_script( 'wp-quiz-babel-helpers' );
			wp_enqueue_script( 'wp-quiz-admin' );
		}

		if ( self::EDIT_QUIZ_PAGE === $screen->id ) {
			wp_enqueue_media();

			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_style( 'jquery-tipsy' );
			wp_enqueue_script( 'jquery-tipsy' );

			wp_enqueue_script( 'jquery.serialize-object' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_style( 'wp-quiz-meta-boxes' );
			wp_enqueue_script( 'wp-quiz-meta-boxes' );
		}
	}
}

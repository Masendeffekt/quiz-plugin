<?php
/**
 * Redirect force action
 *
 * @package WPQuiz
 */

namespace WPQuiz\ForceActions;

use CMB2;
use WPQuiz\Quiz;

/**
 * Class Redirect
 */
class Redirect extends ForceAction {

	/**
	 * Redirect constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->id    = 'redirect';
		$this->title = __( 'Redirect To', 'wp-quiz-pro' );
	}

	/**
	 * Gets force action output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function output( Quiz $quiz ) {
		$this->enqueue();
		return '';
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
		if ( 'meta_box' === $where ) {
			$prefix = 'wp_quiz_';
			$dep    = array(
				array( 'wp_quiz_force_action', $this->get_id() ),
			);
		}

		$cmb->add_field(
			array(
				'id'   => $prefix . 'redirect_url',
				'type' => 'text',
				'name' => __( 'Redirect URL', 'wp-quiz-pro' ),
				'desc' => __( 'Redirect to this URL after completing quiz', 'wp-quiz-pro' ),
				'dep'  => $dep,
			)
		);

		parent::custom_options( $cmb, $where, $quiz_type );
	}

	/**
	 * Enqueues css and js.
	 */
	public function enqueue() {
		wp_enqueue_script( 'wp-quiz-force-action-redirect', wp_quiz()->assets() . 'js/force-actions/redirect.js', array( 'jquery', 'wp-quiz' ), '2.0.0', true );
	}
}

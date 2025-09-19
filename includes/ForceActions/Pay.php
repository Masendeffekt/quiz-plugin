<?php

namespace WPQuiz\ForceActions;

use CMB2;
use WPQuiz\Quiz;
use WPQuiz\Template;

class Pay extends ForceAction {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->id    = 'pay';
		$this->title = __( 'Pay', 'wp-quiz-pro' );
	}

	/**
	 * Gets force action output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function output( Quiz $quiz ) {
		$amount = $quiz->get_setting( 'force_action_pay_amount' );
		ob_start();
		Template::load_template(
			'force-actions/pay.php',
			array(
				'quiz'   => $quiz,
				'amount' => floatval( $amount ),
			)
		);
		return ob_get_clean();
	}

	/**
	 * Enqueues css and js.
	 */
	public function enqueue() {
		wp_enqueue_script( 'wp-quiz-force-action-' . $this->id, wp_quiz()->assets() . 'js/force-actions/pay.js', array( 'jquery', 'wp-quiz' ), wp_quiz()->version, true );
	}

	/**
	 * Registers custom options.
	 *
	 * @param CMB2   $cmb       CMB2 object.
	 * @param string $where     Where to register. Accepts `settings`, `meta_box`.
	 * @param string $quiz_type Quiz type.
	 */
	public function custom_options( CMB2 $cmb, $where = 'settings', $quiz_type = '*' ) {
		if ( 'meta_box' === $where ) {
			$prefix = 'wp_quiz_';
			$dep    = array(
				array( 'wp_quiz_force_action', $this->get_id() ),
			);

			$cmb->add_field(
				array(
					'id'   => $prefix . 'force_action_pay_amount',
					'type' => 'text',
					'name' => __( 'Pay amount', 'wp-quiz-pro' ),
					'dep'  => $dep,
				)
			);
		}

		parent::custom_options( $cmb, $where, $quiz_type );
	}
}

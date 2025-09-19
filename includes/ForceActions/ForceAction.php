<?php
/**
 * Force action abstract class
 *
 * @package WPQuiz
 */

namespace WPQuiz\ForceActions;

use CMB2;
use WPQuiz\Quiz;

/**
 * Class ForceAction
 */
abstract class ForceAction {

	/**
	 * Force action ID.
	 *
	 * @var string
	 */
	protected $id = 'force-action';

	/**
	 * Force action title.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * ForceAction constructor.
	 */
	public function __construct() {}

	/**
	 * Gets action ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Gets action title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Gets force action output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	abstract public function output( Quiz $quiz );

	/**
	 * Enqueues css and js.
	 */
	public function enqueue() {}

	/**
	 * Registers custom options.
	 *
	 * @param CMB2   $cmb       CMB2 object.
	 * @param string $where     Where to register. Accepts `settings`, `meta_box`.
	 * @param string $quiz_type Quiz type.
	 */
	public function custom_options( CMB2 $cmb, $where = 'settings', $quiz_type = '*' ) {
		/**
		 * Allows registering force action custom options.
		 *
		 * @since 2.0.0
		 *
		 * @param CMB2   $cmb       CMB2 object.
		 * @param string $where     Where to register.
		 * @param string $quiz_type Quiz type.
		 */
		do_action( "wp_quiz_force_action_{$this->id}_custom_options", $cmb, $where, $quiz_type );
	}
}

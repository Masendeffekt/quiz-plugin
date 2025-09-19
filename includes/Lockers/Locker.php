<?php
/**
 * Locker abstract class
 *
 * @package WPQuiz
 */

namespace WPQuiz\Lockers;

use WPQuiz\Quiz;

/**
 * Class Locker
 */
abstract class Locker {

	/**
	 * Quiz object.
	 *
	 * @var Quiz
	 */
	protected $quiz;

	/**
	 * Locker constructor.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	public function __construct( Quiz $quiz ) {
		$this->quiz = $quiz;
	}

	/**
	 * Gets locker output.
	 *
	 * @return string
	 */
	abstract public function output();

	protected function enqueue() {}
}

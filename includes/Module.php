<?php
/**
 * Base module class
 *
 * @package WPQuiz
 */

namespace WPQuiz;

/**
 * Class Module
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
abstract class Module {

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Gets module ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Initializes module.
	 */
	abstract public function init();
}

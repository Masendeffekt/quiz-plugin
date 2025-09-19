<?php
/**
 * Storage interface
 *
 * @package WPQuiz
 */

namespace WPQuiz\Storages;

/**
 * Storage interface
 */
interface Storage {

	/**
	 * Checks if has value with given key.
	 *
	 * @param string $key Storage key.
	 * @return bool
	 */
	public function has( $key );

	/**
	 * Gets value with given key.
	 *
	 * @param string $key Storage key.
	 * @return mixed
	 */
	public function get( $key );

	/**
	 * Updates storage value.
	 *
	 * @param string $key   Storage key.
	 * @param mixed  $value Storage value.
	 */
	public function update( $key, $value );

	/**
	 * Deletes storage value.
	 *
	 * @param string $key Storage key.
	 */
	public function delete( $key );
}

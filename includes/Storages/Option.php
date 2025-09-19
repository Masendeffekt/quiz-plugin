<?php
/**
 * Option storage
 *
 * @package WPQuiz
 */

namespace WPQuiz\Storages;

/**
 * Class Option
 */
class Option implements Storage {

	/**
	 * Checks if has value with given key.
	 *
	 * @param string $key Storage key.
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return false === get_option( $key );
	}

	/**
	 * Gets value with given key.
	 *
	 * @param string $key Storage key.
	 *
	 * @return mixed
	 */
	public function get( $key ) {
		return get_option( $key );
	}

	/**
	 * Updates storage value.
	 *
	 * @param string $key   Storage key.
	 * @param mixed  $value Storage value.
	 */
	public function update( $key, $value ) {
		update_option( $key, $value );
	}

	/**
	 * Deletes storage value.
	 *
	 * @param string $key Storage key.
	 */
	public function delete( $key ) {
		delete_option( $key );
	}
}

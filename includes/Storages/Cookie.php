<?php
/**
 * Cookie storage
 *
 * @package WPQuiz
 */

namespace WPQuiz\Storages;

/**
 * Class Cookie
 */
class Cookie implements Storage {

	/**
	 * Expired time.
	 *
	 * @var int
	 */
	protected $expired_time = 2592000; // 86400 * 30.

	/**
	 * Checks if has value with given key.
	 *
	 * @param string $key Storage key.
	 * @return bool
	 */
	public function has( $key ) {
		return isset( $_COOKIE[ $key ] );
	}

	/**
	 * Gets value with given key.
	 *
	 * @param string $key Storage key.
	 * @return mixed
	 */
	public function get( $key ) {
		return isset( $_COOKIE[ $key ] ) ? $_COOKIE[ $key ] : null; // WPCS: sanitization ok.
	}

	/**
	 * Updates storage value.
	 * Should use when there is no output rendered or using output buffering.
	 *
	 * @param string $key   Storage key.
	 * @param mixed  $value Storage value.
	 */
	public function update( $key, $value ) {
		setcookie( $key, $value, time() + $this->expired_time, '/' );
	}

	/**
	 * Deletes storage value.
	 * Should use when there is no output rendered or using output buffering.
	 *
	 * @param string $key Storage key.
	 */
	public function delete( $key ) {
		setcookie( $key, '', time() - $this->expired_time );
	}
}

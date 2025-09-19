<?php
/**
 * MailServices manager
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription\MailServices;

/**
 * Class Manager
 */
class Manager {

	/**
	 * List of mail services.
	 *
	 * @var array
	 */
	protected static $mail_services = array();

	/**
	 * Adds mail service.
	 *
	 * @param MailService $mail_service MailService object.
	 */
	public static function add( MailService $mail_service ) {
		self::$mail_services[ $mail_service->get_name() ] = $mail_service;
	}

	/**
	 * Gets mail service object.
	 *
	 * @param string $name Mail service name.
	 *
	 * @return MailService|false
	 */
	public static function get( $name ) {
		if ( isset( self::$mail_services[ $name ] ) ) {
			return self::$mail_services[ $name ];
		}
		return false;
	}

	/**
	 * Gets all mail service objects.
	 *
	 * @return array
	 */
	public static function get_all() {
		return self::$mail_services;
	}

	/**
	 * Gets list of mail services.
	 *
	 * @return array
	 */
	public static function get_list_all() {
		$list = array();
		foreach ( self::$mail_services as $mail_service ) {
			$list[ $mail_service->get_name() ] = $mail_service->get_title();
		}
		return $list;
	}
}

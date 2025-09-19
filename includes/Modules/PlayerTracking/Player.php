<?php
/**
 * Player class
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\PlayerTracking;

use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\PlayDataTracking\Database as PlayDataDB;

/**
 * Class Player
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Player {

	/**
	 * Player ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public $user_id;

	/**
	 * User IP.
	 *
	 * @var string
	 */
	public $user_ip;

	/**
	 * Created at.
	 *
	 * @var string
	 */
	public $created_at;

	/**
	 * Updated at.
	 *
	 * @var string
	 */
	public $updated_at;

	/**
	 * Facebook user ID. Use for Facebook Quiz.
	 *
	 * @var string
	 */
	public $fb_user_id;

	/**
	 * Player email. Use for Facebook Quiz.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * Player first name. Use for Facebook Quiz.
	 *
	 * @var string
	 */
	public $first_name;

	/**
	 * Player last name. Use for Facebook Quiz.
	 *
	 * @var string
	 */
	public $last_name;

	/**
	 * Player gender. Use for Facebook Quiz.
	 *
	 * @var string
	 */
	public $gender;

	/**
	 * Player avatar URL. Use for Facebook Quiz.
	 *
	 * @var string
	 */
	public $picture;

	/**
	 * List of Facebook friends.
	 *
	 * @var array
	 */
	public $friends;

	/**
	 * Player type.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Gets play data.
	 *
	 * @param int $id Play data ID.
	 * @return Player|false
	 */
	public static function get( $id ) {
		$database = new Database();
		return $database->get( $id );
	}

	/**
	 * Player constructor.
	 *
	 * @param array $data Play data from the DB.
	 */
	public function __construct( array $data ) {
		$this->populate_data( $data );
	}

	/**
	 * Populates data.
	 *
	 * @param array $data Data from DB.
	 */
	protected function populate_data( array $data ) {
		foreach ( $data as $key => $value ) {
			switch ( $key ) {
				case 'id':
				case 'user_id':
					$this->$key = intval( $value );
					break;

				case 'friends':
					$parsed_data = json_decode( $value, true );
					$this->$key  = $parsed_data ? $parsed_data : $value;
					break;

				default:
					$this->$key = $value;
			}
		}
	}

	/**
	 * Gets object as array.
	 *
	 * @return array
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Gets display name.
	 *
	 * @return string
	 */
	public function get_display_name() {
		if ( $this->first_name ) {
			return $this->first_name . ' ' . $this->last_name;
		}

		if ( intval( $this->user_id ) ) {
			$user = get_user_by( 'ID', $this->user_id );
			return $user ? $user->display_name : '';
		}

		return __( 'Guest', 'wp-quiz-pro' );
	}
}

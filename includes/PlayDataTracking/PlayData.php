<?php
/**
 * PlayData class
 *
 * @package WPQuiz
 */

namespace WPQuiz\PlayDataTracking;

use WPQuiz\PostTypeQuiz;
use WPQuiz\Quiz;

/**
 * Class PlayData
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class PlayData {

	/**
	 * Play data ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Quiz ID.
	 *
	 * @var int
	 */
	public $quiz_id;

	/**
	 * Player ID.
	 *
	 * @var int
	 */
	public $player_id;

	/**
	 * Played at.
	 *
	 * @var string
	 */
	public $played_at;

	/**
	 * Number of correct answered.
	 *
	 * @var int
	 */
	public $correct_answered;

	/**
	 * Result ID.
	 *
	 * @var string
	 */
	public $result;

	/**
	 * Quiz type.
	 *
	 * @var string
	 */
	public $quiz_type;

	/**
	 * Quiz data.
	 *
	 * @var array
	 */
	public $quiz_data;

	/**
	 * Answered data.
	 *
	 * @var array|string
	 */
	public $answered_data;

	/**
	 * Quiz URL.
	 *
	 * @var string
	 */
	public $quiz_url;

	/**
	 * Quiz object.
	 *
	 * @var Quiz
	 */
	protected $quiz;

	/**
	 * Gets play data.
	 *
	 * @param int $id Play data ID.
	 * @return PlayData|false
	 */
	public static function get( $id ) {
		$database = new Database();
		return $database->get( $id );
	}

	/**
	 * PlayData constructor.
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
				case 'quiz_id':
				case 'player_id':
				case 'correct_answered':
					$this->$key = intval( $value );
					break;

				case 'quiz_data':
				case 'answered_data':
					$parsed_data = json_decode( $value, true );
					$this->$key  = ! is_null( $parsed_data ) ? $parsed_data : $value;
					break;

				default:
					$this->$key = $value;
			}
		}
	}

	/**
	 * Gets quiz object.
	 *
	 * @return Quiz|false
	 */
	public function get_quiz() {
		if ( ! $this->quiz instanceof Quiz ) {
			$this->quiz = PostTypeQuiz::get_quiz( $this->quiz_id );
		}
		return $this->quiz;
	}

	/**
	 * Gets object as array.
	 *
	 * @return array
	 */
	public function to_array() {
		return get_object_vars( $this );
	}
}

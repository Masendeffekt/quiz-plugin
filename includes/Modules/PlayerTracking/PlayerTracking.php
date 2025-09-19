<?php
/**
 * Player tracking module
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\PlayerTracking;

use WP_Error;
use WPQuiz\Helper;
use WPQuiz\Module;
use WPQuiz\Modules\PlayerTracking\Admin\PlayersPage;
use WPQuiz\Quiz;

/**
 * Class PlayerTracking
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class PlayerTracking extends Module {

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	protected $id = 'player_tracking';

	/**
	 * Initializes module.
	 */
	public function init() {
		if ( 'on' !== Helper::get_option( 'players_tracking' ) ) {
			return;
		}

		if ( is_admin() ) {
			$players_page = new PlayersPage();
			$players_page->init();
		}

		add_action( 'wp_quiz_inserting_play_data', array( $this, 'add_player_id_to_play_data' ), 10, 3 );
	}

	/**
	 * Adds player ID to play data.
	 *
	 * @param array $insert_data Play insert data.
	 * @param array $play_data   Unprocessed player data from REST request.
	 * @param Quiz  $quiz        Quiz object.
	 *
	 * @return array
	 */
	public function add_player_id_to_play_data( $insert_data, array $play_data, Quiz $quiz ) {
		$database      = new Database();
		$record_method = Helper::get_option( 'record_guest_method' );

		// Check if is old user.
		if ( is_user_logged_in() ) {
			$player_id = $database->user_id_exists( get_current_user_id() );
		} elseif ( 'cookie' === $record_method ) {
			$player_id = isset( $_COOKIE['wp_quiz_player_id'] ) ? intval( $_COOKIE['wp_quiz_player_id'] ) : 0;
		} else {
			$player_id = $database->user_ip_exists( Helper::get_current_ip() );
		}

		// Is old player, do not insert new player, just update the updated_at.
		if ( $player_id ) {
			$insert_data['player_id'] = $player_id;
			$database->update( $player_id, array( 'updated_at' => date( 'Y-m-d H:i:s' ) ) );
			return $insert_data;
		}

		// New player.
		$player_id = self::add_player( $quiz, $play_data );
		if ( intval( $player_id ) ) {
			$insert_data['player_id'] = $player_id;

			if ( 'cookie' === $record_method ) {
				setcookie( 'wp_quiz_player_id', $player_id );
			}
		}
		return $insert_data;
	}

	/**
	 * Adds player.
	 *
	 * @param Quiz  $quiz        Quiz object.
	 * @param array $player_data Player data.
	 *
	 * @return int|WP_Error Return player ID on success or WP_Error on failure.
	 */
	public static function add_player( Quiz $quiz, array $player_data ) {
		$insert_data = $quiz->get_quiz_type()->get_inserting_player_data( $quiz, $player_data );

		/**
		 * Allows changing inserting player data of a specific quiz type.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_data Player insert data.
		 * @param array $player_data Unprocessed player from REST request.
		 * @param Quiz  $quiz        Quiz object.
		 */
		$insert_data = apply_filters( "wp_quiz_{$quiz->get_quiz_type()->get_name()}_inserting_player_data", $insert_data, $player_data, $quiz );

		/**
		 * Allows changing inserting play data.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_data Play insert data.
		 * @param array $player_data Unprocessed player data from REST request.
		 * @param Quiz  $quiz        Quiz object.
		 */
		$insert_data = apply_filters( 'wp_quiz_inserting_player_data', $insert_data, $player_data, $quiz );

		if ( ! $insert_data ) {
			return new WP_Error( 'empty-play-data', __( 'Empty play data', 'wp-quiz-pro' ) );
		}

		$database  = new Database();
		$player_id = $database->add( $insert_data );

		if ( ! is_wp_error( $player_id ) ) {
			/**
			 * Fires after tracking player.
			 *
			 * @since 2.0.0
			 *
			 * @param int   $player_id    Player ID.
			 * @param array $player_data  Player insert data.
			 * @param array $request_data Unprocessed player data from REST request.
			 * @param Quiz  $quiz         Quiz object.
			 */
			do_action( 'wp_quiz_after_track_player', $player_id, $insert_data, $player_data, $quiz );
		}

		return $player_id;
	}
}

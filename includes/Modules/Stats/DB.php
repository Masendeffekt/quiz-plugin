<?php
/**
 * Stats Database class
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Stats;

use WPQuiz\Helper;
use WPQuiz\PostTypeQuiz;
use WPQuiz\Quiz;

/**
 * Class DB
 */
class DB {

	/**
	 * Group by date constant.
	 *
	 * @var string
	 */
	const GROUP_BY_DATE = 'date';

	/**
	 * Group by week constant.
	 *
	 * @var string
	 */
	const GROUP_BY_WEEK = 'week';

	/**
	 * Group by month constant.
	 *
	 * @var string
	 */
	const GROUP_BY_MONTH = 'month';

	/**
	 * Group by quiz id constant.
	 *
	 * @var string
	 */
	const GROUP_BY_QUIZ_ID = 'quiz_id';

	/**
	 * Group by quiz type constant.
	 *
	 * @var string
	 */
	const GROUP_BY_QUIZ_TYPE = 'quiz_type';

	/**
	 * Gets plays count.
	 *
	 * @param array $query_args {
	 *     Query args.
	 *
	 *     @type string $from      Y-m-d date. Ignore plays before this day.
	 *     @type string $to        Y-m-d date. Ignore plays after this day.
	 *     @type string $group_by  Group results by `date` (default), `week`, `month`, `quiz_id` or `quiz_type`.
	 *     @type int    $quiz_id   Quiz ID. If this is not empty, only get plays of this quiz.
	 *     @type string $quiz_type Quiz type. If this is not empty, only get plays of this quiz type.
	 * }
	 * @return array
	 */
	public static function get_plays_count( array $query_args = array() ) {
		global $wpdb;
		$query_args = wp_parse_args(
			$query_args,
			array(
				'from'      => '',
				'to'        => '',
				'group_by'  => '',
				'quiz_id'   => '',
				'quiz_type' => '',
			)
		);

		// Where clause.
		$where = array();
		if ( ! empty( $query_args['from'] ) ) {
			$where[] = $wpdb->prepare( 'DATE(played_at) >= %s', $query_args['from'] );
		}
		if ( ! empty( $query_args['to'] ) ) {
			$where[] = $wpdb->prepare( 'DATE(played_at) <= %s', $query_args['to'] );
		}
		if ( ! empty( $query_args['quiz_id'] ) ) {
			$where[] = $wpdb->prepare( 'quiz_id = %d', intval( $query_args['quiz_id'] ) );
		}
		if ( ! empty( $query_args['quiz_type'] ) ) {
			$where[] = $wpdb->prepare( 'quiz_type = %s', $query_args['quiz_type'] );
		}
		if ( $where ) {
			$where = 'WHERE ' . implode( ' AND ', $where );
		} else {
			$where = '';
		}

		// Group by and order clauses.
		switch ( $query_args['group_by'] ) {
			case self::GROUP_BY_WEEK:
				$group_by = 'GROUP BY YEARWEEK(played_at)';
				$order_by = 'ORDER BY YEARWEEK(played_at) ASC';
				$select   = 'COUNT(*) as count, YEARWEEK(played_at) as week';
				break;

			case self::GROUP_BY_MONTH:
				$group_by = 'GROUP BY YEAR(played_at), MONTH(played_at)';
				$order_by = 'ORDER BY YEAR(played_at) ASC, MONTH(played_at)';
				$select   = 'COUNT(*) as count, YEAR(played_at) as year, MONTH(played_at) as month';
				break;

			case self::GROUP_BY_QUIZ_ID:
				$group_by = 'GROUP BY quiz_id';
				$order_by = 'ORDER BY quiz_id ASC';
				$select   = 'COUNT(*) as count, quiz_id';
				break;

			case self::GROUP_BY_QUIZ_TYPE:
				$group_by = 'GROUP BY quiz_type';
				$order_by = 'ORDER BY quiz_type ASC';
				$select   = 'COUNT(*) as count, quiz_type';
				break;

			case self::GROUP_BY_DATE:
				$group_by = 'GROUP BY DATE(played_at)';
				$order_by = 'ORDER BY DATE(played_at) ASC';
				$select   = 'COUNT(*) as count, DATE(played_at) as date';
				break;

			default:
				$group_by = '';
				$order_by = 'ORDER BY played_at ASC';
				$select   = 'COUNT(*) as count, played_at';
		}

		$sql    = trim( "SELECT {$select} FROM {$wpdb->prefix}wp_quiz_play_data {$where} {$group_by} {$order_by}" );
		$result = $wpdb->get_results( $sql, ARRAY_A ); // WPCS: unprepared SQL, cache ok.
		return $result;
	}

	/**
	 * Gets quiz answer stats data from trivia or personality quiz.
	 *
	 * @param int|Quiz $quiz       Quiz ID or quiz object.
	 * @param array    $query_args {
	 *     Query args.
	 *
	 *     @type string $from Y-m-d date. Ignore plays before this day.
	 *     @type string $to   Y-m-d date. Ignore plays after this day.
	 * }
	 * @return array
	 */
	public static function get_answer_count_data( $quiz, array $query_args = array() ) {
		$quiz = PostTypeQuiz::get_quiz( $quiz );
		if ( ! in_array( $quiz->get_quiz_type()->get_name(), array( 'trivia', 'personality' ), true ) ) {
			return array();
		}

		global $wpdb;
		$query_args = wp_parse_args(
			$query_args,
			array(
				'from' => '',
				'to'   => '',
			)
		);

		// Where clause.
		$where = array();
		if ( ! empty( $query_args['from'] ) ) {
			$where[] = $wpdb->prepare( 'DATE(played_at) >= %s', $query_args['from'] );
		}
		if ( ! empty( $query_args['to'] ) ) {
			$where[] = $wpdb->prepare( 'DATE(played_at) <= %s', $query_args['to'] );
		}

		if ( $where ) {
			$where = 'WHERE ' . implode( ' AND ', $where );
		} else {
			$where = '';
		}

		$sql    = trim( "SELECT answered_data FROM {$wpdb->prefix}wp_quiz_play_data {$where}" );
		$items  = $wpdb->get_col( $sql ); // WPCS: db call ok, unprepared SQL, cache ok.
		$result = array();

		// Add current questions and answers to result.
		foreach ( $quiz->get_questions() as $qid => $question ) {
			$result[ $qid ] = array(
				'count'   => 0,
				'answers' => array(),
			);
			foreach ( $question['answers'] as $aid => $answer ) {
				$result[ $qid ]['answers'][ $aid ] = 0;
			}
		}

		// Loop through answered data and count answered questions and chosen answers.
		foreach ( $items as $item ) {
			$item = json_decode( $item, true );
			if ( ! is_array( $item ) ) {
				continue;
			}

			foreach ( $item as $qid => $value ) {
				if ( ! isset( $result[ $qid ] ) ) {
					continue;
				}

				$result[ $qid ]['count'] += 1;
				foreach ( $value['answers'] as $aid ) {
					if ( ! isset( $result[ $qid ]['answers'][ $aid ] ) ) {
						continue;
					}

					$result[ $qid ]['answers'][ $aid ] += 1;
				}
			}
		}

		return $result;
	}

	/**
	 * Gets quiz result stats data from fb quiz.
	 *
	 * @param int|Quiz $quiz       Quiz ID or quiz object.
	 * @param array    $query_args {
	 *     Query args.
	 *
	 *     @type string $from Y-m-d date. Ignore plays before this day.
	 *     @type string $to   Y-m-d date. Ignore plays after this day.
	 * }
	 * @return array
	 */
	public static function get_fb_quiz_result_count_data( $quiz, array $query_args = array() ) {
		$quiz = PostTypeQuiz::get_quiz( $quiz );
		if ( 'fb_quiz' !== $quiz->get_quiz_type()->get_name() ) {
			return array();
		}

		global $wpdb;
		$query_args = wp_parse_args(
			$query_args,
			array(
				'from' => '',
				'to'   => '',
			)
		);

		// Where clause.
		$where = array();
		if ( ! empty( $query_args['from'] ) ) {
			$where[] = $wpdb->prepare( 'DATE(played_at) >= %s', $query_args['from'] );
		}
		if ( ! empty( $query_args['to'] ) ) {
			$where[] = $wpdb->prepare( 'DATE(played_at) <= %s', $query_args['to'] );
		}

		if ( $where ) {
			$where = 'WHERE ' . implode( ' AND ', $where );
		} else {
			$where = '';
		}

		$sql    = trim( "SELECT result, COUNT(*) AS count FROM {$wpdb->prefix}wp_quiz_play_data {$where} GROUP BY result" );
		$items  = $wpdb->get_results( $sql ); // WPCS: db call ok, unprepared SQL, cache ok.
		$return = array();

		foreach ( $quiz->get_results() as $rid => $result ) {
			$return[ $rid ] = array(
				'title' => $result['title'],
				'count' => 0,
			);
		}

		foreach ( $items as $item ) {
			if ( ! isset( $return[ $item->result ] ) ) {
				continue;
			}

			$return[ $item->result ]['count'] = intval( $item->count );
		}

		return $return;
	}

	/**
	 * Gets top scored players.
	 *
	 * @param int $quiz_id Quiz ID.
	 * @param int $limit   Limit.
	 * @return array       Array of player IDs and scores.
	 */
	public static function get_top_scored_players( $quiz_id, $limit = 5 ) {
		global $wpdb;

		$players = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT player_id, correct_answered, id as play_data_id FROM {$wpdb->prefix}wp_quiz_play_data WHERE quiz_id = %d ORDER BY correct_answered DESC LIMIT %d",
				intval( $quiz_id ),
				intval( $limit )
			),
			ARRAY_A
		);

		return $players;
	}

	/**
	 * Normalizes plays query args.
	 *
	 * @param array $query_args Query args.
	 * @return array
	 */
	protected static function normalize_plays_query_args( array $query_args = array() ) {
		// Clean args.
		$new_args = array();
		foreach ( $query_args as $key => $value ) {
			if ( ! $value ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$new_args[ $key ] = self::normalize_plays_query_args( $value );
				continue;
			}
			$new_args[ $key ] = $value;
		}

		// Sort args by key.
		ksort( $new_args );

		return $new_args;
	}

	/**
	 * Gets cache key based on query args.
	 *
	 * @param array $query_args Query args.
	 * @return string
	 */
	protected static function get_cache_key( array $query_args = array() ) {
		$query_args = self::normalize_plays_query_args( $query_args );
		$cache_key  = 'all';
		foreach ( $query_args as $key => $value ) {
			$cache_key .= sprintf( '|%s:%s', $key, $value );
		}
		return $cache_key;
	}
}

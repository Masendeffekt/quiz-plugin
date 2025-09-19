<?php
/**
 * Quiz types storage
 *
 * @package WPQuiz
 */

namespace WPQuiz;

/**
 * Class QuizTypes
 */
class QuizTypeManager {

	/**
	 * Quiz types.
	 *
	 * @var array
	 */
	protected static $types = array();

	/**
	 * Adds quiz type.
	 *
	 * @param QuizType $quiz_type Quiz type object.
	 * @return bool
	 */
	public static function add( QuizType $quiz_type ) {
		if ( ! $quiz_type->get_name() ) {
			return false;
		}
		self::$types[ $quiz_type->get_name() ] = $quiz_type;
		return true;
	}

	/**
	 * Gets quiz type object.
	 *
	 * @param string $type Quiz type name.
	 * @return QuizType|false
	 */
	public static function get( $type ) {
		if ( isset( self::$types[ $type ] ) ) {
			return self::$types[ $type ];
		}
		return false;
	}

	/**
	 * Gets all quiz types.
	 *
	 * @param bool $disabled Include disabled quiz types in the result.
	 * @return array
	 */
	public static function get_all( $disabled = false ) {
		$types = self::$types;
		if ( ! $disabled ) {
			$types = array_filter( $types, array( __CLASS__, 'enabled_quiz_type_filter' ) );
		}
		return apply_filters( 'wp_quiz_types', $types, $disabled );
	}

	/**
	 * Filter for enabled quiz type only.
	 *
	 * @param QuizType $quiz_type Quiz type object.
	 * @return bool
	 */
	protected static function enabled_quiz_type_filter( QuizType $quiz_type ) {
		return $quiz_type->is_enabled();
	}

	/**
	 * Gets all quiz type titles.
	 *
	 * @param bool $disabled Include disabled quiz types in the result.
	 * @return array
	 */
	public static function get_titles( $disabled = false ) {
		$titles = array();
		$types  = self::get_all( $disabled );
		foreach ( $types as $name => $quiz_type ) {
			$titles[ $name ] = $quiz_type->get_title();
		}
		return $titles;
	}

	/**
	 * Gets default quiz type.
	 *
	 * @return string
	 */
	public static function get_default_quiz_type() {
		return 'trivia'; // TODO: add a setting to change this.
	}

	/**
	 * Gets quiz types support pay to play option.
	 *
	 * @return array
	 */
	public static function get_pay_to_play_quiz_types() {
		return apply_filters( 'wp_quiz_pay_to_play_quiz_types', array( 'trivia', 'personality' ) );
	}
}

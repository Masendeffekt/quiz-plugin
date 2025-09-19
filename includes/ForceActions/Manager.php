<?php
/**
 * Force actions manager
 *
 * @package WPQuiz
 */

namespace WPQuiz\ForceActions;

use CMB2;

/**
 * Class Manager
 */
class Manager {

	/**
	 * List of actions.
	 *
	 * @var array
	 */
	protected static $actions = array();

	/**
	 * Adds action.
	 *
	 * @param ForceAction $action Force action object object.
	 */
	public static function add( ForceAction $action ) {
		self::$actions[ $action->get_id() ] = $action;
	}

	/**
	 * Gets force action object.
	 *
	 * @param string $id Force action ID.
	 * @return ForceAction|false
	 */
	public static function get( $id ) {
		if ( isset( self::$actions[ $id ] ) ) {
			return self::$actions[ $id ];
		}
		return false;
	}

	/**
	 * Gets all force action objects.
	 *
	 * @return array
	 */
	public static function get_all() {
		return self::$actions;
	}

	/**
	 * Gets list of force actions.
	 *
	 * @return array
	 */
	public static function get_list_all() {
		$list = array();
		foreach ( self::$actions as $action ) {
			$list[ $action->get_id() ] = $action->get_title();
		}
		return $list;
	}

	/**
	 * Registers force actions options.
	 *
	 * @param CMB2   $cmb       CMB2 object.
	 * @param string $where     Where to register.
	 * @param string $quiz_type Quiz type.
	 */
	public static function register_force_actions_options( CMB2 $cmb, $where = 'settings', $quiz_type = '*' ) {
		$prefix  = '';
		$options = array( '0' => __( 'No Action', 'wp-quiz-pro' ) ) + self::get_list_all();
		$field   = array(
			'type'    => 'select',
			'name'    => __( 'Force action to see the results', 'wp-quiz-pro' ),
			'desc'    => __( 'Applies to Trivia or Personality quiz.', 'wp-quiz-pro' ),
			'options' => $options,
		);

		if ( 'meta_box' === $where ) {
			$prefix                    = 'wp_quiz_';
			$field['show_option_none'] = __( 'Use default value', 'wp-quiz-pro' );
		}

		$field['id'] = $prefix . 'force_action';

		$cmb->add_field( $field );

		foreach ( self::$actions as $action ) {
			if ( 'redirect' === $action->get_id() ) {
				continue;
			}
			$action->custom_options( $cmb, $where, $quiz_type );
		}
	}
}

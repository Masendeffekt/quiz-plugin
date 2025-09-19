<?php
/**
 * CMB2 field dependencies
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin\CMB2Custom;

use CMB2;
use CMB2_Field;

/**
 * Class FieldDependencies
 */
class FieldDependencies {

	/**
	 * Initializes.
	 *
	 * @param CMB2 $cmb CMB2 object.
	 */
	public static function init( CMB2 $cmb ) {
		foreach ( $cmb->prop( 'fields' ) as $id => $field_args ) {
			if ( ! empty( $field_args['dep'] ) ) {
				self::set_dependencies( $field_args );
			}
		}
	}

	/**
	 * Generate the dependency html for JavaScript.
	 *
	 * @param array $field_args Field args.
	 */
	public static function set_dependencies( &$field_args ) {
		if ( ! isset( $field_args['dep'] ) || empty( $field_args['dep'] ) ) {
			return;
		}

		$dependency = '';
		$relation   = key( $field_args['dep'] );

		if ( 'relation' === $relation ) {
			$relation = current( $field_args['dep'] );
			unset( $field_args['dep']['relation'] );
		} else {
			$relation = 'OR';
		}
		foreach ( $field_args['dep'] as $dependence ) {
			$compasrison = isset( $dependence[2] ) ? $dependence[2] : '=';
			$dependency .= '<span class="hidden" data-field="' . $dependence[0] . '" data-comparison="' . $compasrison . '" data-value="' . $dependence[1] . '"></span>';
		}

		$where                = 'group' === $field_args['type'] ? 'after_group' : 'after_field';
		$field_args[ $where ] = '<div class="wp-quiz-cmb-dependency hidden" data-relation="' . strtolower( $relation ) . '">' . $dependency . '</div>';
	}
}

<?php
/**
 * Select Option group field
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin\CMB2Custom;

use CMB2_Type_Select;
use CMB2_Utils;

/**
 * Class TypeSelectOptgroup
 */
class TypeSelectOptgroup extends CMB2_Type_Select {

	/**
	 * Generates html for concatenated items.
	 *
	 * @param array $args Optional arguments.
	 * @return string Concatenated html items.
	 */
	public function concat_items( $args = array() ) {
		$field              = $this->field;
		$value              = null !== $field->escaped_value() ? $field->escaped_value() : $field->get_default();
		$value              = CMB2_Utils::normalize_if_numeric( $value );
		$concatenated_items = '';

		$options     = array();
		$option_none = $field->args( 'show_option_none' );
		if ( $option_none ) {
			$options[''] = $option_none;
		}
		$options = $options + (array) $field->options();
		foreach ( $options as $key => $item ) {
			if ( ! is_array( $item ) ) {
				$concatenated_items .= $this->select_option(
					array(
						'value'   => $key,
						'label'   => $item,
						'checked' => $value == $key,
					)
				);
				continue;
			}

			$concatenated_items .= '<optgroup label="' . esc_attr( $key ) . '">';
			foreach ( $item as $opt_value => $opt_label ) {
				$concatenated_items .= $this->select_option(
					array(
						'value'   => $opt_value,
						'label'   => $opt_label,
						'checked' => $value == $opt_value,
					)
				);
			}
			$concatenated_items .= '</optgroup>';
		}

		return $concatenated_items;
	}
}

<?php
/**
 * The CMB2 functionality of the plugin.
 *
 * This class defines all code necessary to have setting pages and manager.
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin\CMB2Custom;

use CMB2;
use CMB2_Field;

/**
 * Class RenderCallbacks
 */
class RenderCallbacks {

	/**
	 * Set field arguments based on type.
	 *
	 * @param CMB2 $cmb CMB2 object.
	 */
	public static function pre_init( CMB2 $cmb ) {
		$fields = $cmb->prop( 'fields' );
		self::init_fields( $fields );
		$cmb->set_prop( 'fields', $fields );
	}

	/**
	 * Initializes fields.
	 *
	 * @param array $fields List of fields.
	 */
	public static function init_fields( array &$fields ) {
		foreach ( $fields as $id => &$field_args ) {
			$type = $field_args['type'];

			if ( in_array( $type, array( 'tab_container_open', 'tab_container_close', 'tab_open', 'tab_close', 'raw' ) ) ) {
				$field_args['save_field']    = false;
				$field_args['render_row_cb'] = array( __CLASS__, "render_{$type}" );
			} elseif ( 'notice' === $type ) {
				$field_args['save_field'] = false;
			}

			if ( ! empty( $field_args['dep'] ) ) {
				FieldDependencies::set_dependencies( $field_args );
			}

			if ( ! empty( $field_args['fields'] ) ) {
				self::init_fields( $field_args['fields'] );
			}
		}
	}

	/**
	 * Get the object type for the current page, based on the $pagenow global.
	 *
	 * @see CMB2->current_object_type()
	 *
	 * @return string Page object type name.
	 */
	public static function current_object_type() {
		global $pagenow;
		$type = 'post';

		if ( in_array( $pagenow, array( 'user-edit.php', 'profile.php', 'user-new.php' ), true ) ) {
			$type = 'user';
		}

		if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php' ), true ) ) {
			$type = 'comment';
		}

		if ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) ) {
			$type = 'term';
		}

		if ( defined( 'DOING_AJAX' ) && isset( $_POST['action'] ) && 'add-tag' === $_POST['action'] ) { // WPCS: csrf ok.
			$type = 'term';
		}

		return $type;
	}

	/**
	 * Render raw field.
	 *
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object.
	 * @return CMB2_Field
	 */
	public static function render_raw( $field_args, $field ) {

		if ( $field->args( 'file' ) ) {
			include $field->args( 'file' );
		} elseif ( $field->args( 'content' ) ) {
			echo $field->args( 'content' ); // WPCS: xss ok.
		}

		return $field;
	}

	/**
	 * Render tab container opening <div> for metabox.
	 *
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object.
	 * @return CMB2_Field
	 */
	public static function render_tab_container_open( $field_args, $field ) {
		?>
		<div id="<?php echo esc_attr( $field->prop( 'id' ) ); ?>" class="wp-quiz-panel-container">
			<div class="wp-quiz-tab-wrapper default-tab-wrapper wp-clearfix">
				<?php
				foreach ( $field->args( 'tabs' ) as $id => $tab ) :
					if ( empty( $tab ) || ! current_user_can( $tab['capability'] ) ) {
						continue;
					}
					?>
					<a href="#<?php echo esc_attr( $tab['base_id'] . $id ); ?>">
						<span class="<?php echo esc_attr( $tab['icon'] ); ?>"></span>
						<?php echo esc_html( $tab['title'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>

			<div class="wp-quiz-tab-content-wrapper">
		<?php
		return $field;
	}

	/**
	 * Render tab container closing <div>.
	 *
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object.
	 * @return CMB2_Field
	 */
	public static function render_tab_container_close( $field_args, $field ) {
		echo '</div><!-- /.wp-quiz-tab-content-wrapper -->';
		echo '</div><!-- /#' . esc_html( $field->prop( 'id' ) ) . ' -->';

		return $field;
	}

	/**
	 * Render tab content opening <div>.
	 *
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object.
	 * @return CMB2_Field
	 */
	public static function render_tab_open( $field_args, $field ) {
		echo '<div id="' . esc_attr( $field->prop( 'id' ) ) . '" class="wp-quiz-setting-panel">';

		return $field;
	}

	/**
	 * Render tab content closing <div>.
	 *
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object.
	 * @return CMB2_Field
	 */
	public static function render_tab_close( $field_args, $field ) {
		echo '</div><!-- /#' . esc_html( $field->prop( 'id' ) ) . ' -->';

		return $field;
	}
}

<?php
/**
 * New fields for CMB2
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin\CMB2Custom;

use CMB2_Field;
use CMB2_Types;
use WPQuiz\Helper;
use WPQuiz\Modules\Subscription\MailServices\AWeber;
use WPQuiz\Modules\Subscription\MailServices\MailService;
use WPQuiz\Modules\Subscription\MailServices\Manager;
use WPQuiz\PostTypeQuiz;
use WPQuiz\QuizType;
use WPQuiz\QuizTypeManager;

/**
 * Class CustomFields
 */
class CustomFields {

	/**
	 * Registers custom fields.
	 */
	public function register() {
		add_filter( 'cmb2_render_class_select_optgroup', array( $this, 'class_select_optgroup' ) );

		if ( ! has_action( 'cmb2_render_switch' ) ) {
			add_action( 'cmb2_render_switch', array( $this, 'render_switch' ), 10, 5 );
		}

		add_action( 'cmb2_render_quiz_content', array( $this, 'render_quiz_content' ), 10, 5 );

		add_action( 'cmb2_render_aweber', array( $this, 'render_aweber' ), 10, 5 );
	}

	/**
	 * Registers render class for select_optgroup type.
	 *
	 * @return string
	 */
	public function class_select_optgroup() {
		return '\\WPQuiz\\Admin\\CMB2Custom\\TypeSelectOptgroup';
	}

	/**
	 * Render switch field.
	 *
	 * @param CMB2_Field $field             The passed in `CMB2_Field` object.
	 * @param mixed      $escaped_value     The value of this field escaped.
	 *                                      It defaults to `sanitize_text_field`.
	 *                                      If you need the unescaped value, you can access it
	 *                                      via `$field->value()`.
	 * @param int        $object_id         The ID of the current object.
	 * @param string     $object_type       The type of object you are working with.
	 *                                      Most commonly, `post` (this applies to all post-types),
	 *                                      but could also be `comment`, `user` or `options-page`.
	 * @param object     $field_type_object This `CMB2_Types` object.
	 */
	public function render_switch( CMB2_Field $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$field->args['options'] = array(
			'off' => esc_html( $field->get_string( 'off', __( 'Off', 'wp-quiz-pro' ) ) ),
			'on'  => esc_html( $field->get_string( 'on', __( 'On', 'wp-quiz-pro' ) ) ),
		);
		$field->set_options();

		echo $field_type_object->radio_inline(); // WPCS: xss ok.
	}

	/**
	 * Render quiz content field.
	 *
	 * @param CMB2_Field $field             The passed in `CMB2_Field` object.
	 * @param mixed      $escaped_value     The value of this field escaped.
	 *                                      It defaults to `sanitize_text_field`.
	 *                                      If you need the unescaped value, you can access it
	 *                                      via `$field->value()`.
	 * @param int        $object_id         The ID of the current object.
	 * @param string     $object_type       The type of object you are working with.
	 *                                      Most commonly, `post` (this applies to all post-types),
	 *                                      but could also be `comment`, `user` or `options-page`.
	 * @param object     $field_type_object This `CMB2_Types` object.
	 */
	public function render_quiz_content( CMB2_Field $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		if ( 'post' !== $object_type ) {
			return;
		}
		$quiz_type = $field->args( 'quiz_type' );
		if ( ! $quiz_type instanceof QuizType ) {
			$quiz_type = QuizTypeManager::get( $quiz_type );
		}
		if ( ! $quiz_type ) {
			return;
		}
		$quiz = PostTypeQuiz::get_quiz( $object_id );
		if ( ! $quiz ) {
			return;
		}
		$quiz->set_quiz_type( $quiz_type );
		printf( '<div id="%1$s" class="wp-quiz-content-settings">', esc_attr( $field->prop( 'id' ) ) );
		$field->args( 'quiz_type' )->backend( $quiz );
		printf( '</div><!-- End #%s -->', esc_attr( $field->prop( 'id' ) ) );
		$quiz_type->enqueue_backend_scripts();
	}

	/**
	 * Render aweber field.
	 *
	 * @param CMB2_Field $field             The passed in `CMB2_Field` object.
	 * @param mixed      $escaped_value     The value of this field escaped.
	 *                                      It defaults to `sanitize_text_field`.
	 *                                      If you need the unescaped value, you can access it
	 *                                      via `$field->value()`.
	 * @param int        $object_id         The ID of the current object.
	 * @param string     $object_type       The type of object you are working with.
	 *                                      Most commonly, `post` (this applies to all post-types),
	 *                                      but could also be `comment`, `user` or `options-page`.
	 * @param CMB2_Types $field_type_object This `CMB2_Types` object.
	 */
	public function render_aweber( CMB2_Field $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$aweber = Manager::get( 'aweber' );
		$value  = wp_parse_args(
			$escaped_value,
			array(
				'listid'          => '',
			)
		);

		$is_connected = $aweber->is_connected();
		$lists        = $is_connected ? $aweber->get_lists() : array();
		$list_items   = '<option value="">' . esc_html__( '-- Choose a list --', 'wp-quiz-pro' ) . '</option>';
		foreach ( $lists as $list ) {
			$list_items .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $list['id'] ),
				selected( $list['id'], $value['listid'], false ),
				esc_html( $list['name'] )
			);
		}
		?>
		<div class="aweber-wrapper <?php echo $is_connected ? 'connected' : ''; ?>">

			<div class="aweber-unconnected-fields">
				<div class="notice notice-warning inline">
					<p><?php esc_html_e( 'Your AWeber account is not connected. Click Login button, Login and paste the verification code to the below textarea.', 'wp-quiz-pro' ); ?></p>
				</div>
				<a href="<?php echo $aweber->get_login_url(); ?>" class="button" target="_blank"><?php esc_html_e( 'Login', 'wp-quiz-pro' ); ?></a>

				<p class="aweber-verification-code-field">
					<textarea class="aweber-verification-code" style="width: 100%; height: auto;" placeholder="<?php esc_attr_e( 'Paste verification code here', 'wp-quiz-pro' ); ?>"></textarea>
					<button type="button" class="button aweber-connect-button"><?php esc_html_e( 'Connect', 'wp-quiz-pro' ); ?></button>
				</p>
			</div>

			<div class="aweber-connected-fields">
				<div class="notice notice-info inline">
					<p><?php esc_html_e( 'Your AWeber account is connected.', 'wp-quiz-pro' ); ?></p>
				</div>

				<?php $id = $field_type_object->_id( '_listid' ); ?>
				<p>
					<label for="<?php echo esc_attr( $id ); ?>"><strong><?php esc_html_e( 'List ID', 'wp-quiz-pro' ); ?></strong></label>
					<?php
					echo $field_type_object->select(
						array(
							'name'    => $field_type_object->_name( '[listid]' ),
							'id'      => $id,
							'value'   => $value['listid'],
							'options' => $list_items,
						)
					); // WPCS: xss ok.
					?>

					<button type="button" class="button button-large aweber-refresh-lists-button" style="vertical-align: middle;"><?php esc_html_e( 'Refresh', 'wp-quiz-pro' ); ?></button>
				</p>

				<button type="button" class="button aweber-disconnect-button"><?php esc_html_e( 'Disconnect', 'wp-quiz-pro' ); ?></button>
			</div>
		</div><!-- End .aweber-wrapper -->
		<?php
	}
}

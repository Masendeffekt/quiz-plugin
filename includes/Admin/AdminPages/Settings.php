<?php
/**
 * Settings page
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin\AdminPages;

use CMB2;
use WPQuiz\Admin\AdminHelper;
use WPQuiz\Admin\CMB2Custom\RenderCallbacks;
use WPQuiz\Helper;

/**
 * Class Settings
 */
class Settings {

	/**
	 * Page ID.
	 *
	 * @var string
	 */
	protected $page_id = 'wp-quiz-settings';

	/**
	 * Initializes.
	 */
	public function init() {
		add_action( 'cmb2_admin_init', array( $this, 'register_page' ) );
	}

	/**
	 * Registers page.
	 */
	public function register_page() {
		$cmb = new_cmb2_box(
			array(
				'id'           => $this->page_id,
				'title'        => esc_html__( 'WP Quiz Pro Settings', 'wp-quiz-pro' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => 'wp_quiz_pro_default_settings',
				'menu_title'   => esc_html__( 'Settings', 'cmb2' ),
				'parent_slug'  => 'edit.php?post_type=wp_quiz',
				'classes_cb'   => array( $this, 'get_page_classes' ),
			)
		);

		$tabs = $this->get_tabs();
		foreach ( $tabs as $index => $tab ) {
			$tabs[ $index ] = wp_parse_args(
				$tab,
				array(
					'icon'       => '',
					'title'      => '',
					'desc'       => '',
					'base_id'    => '',
					'capability' => 'manage_options',
				)
			);
		}

		$cmb->add_field(
			array(
				'id'   => $this->page_id . '-setting-panel-container',
				'type' => 'tab_container_open',
				'tabs' => $tabs,
			)
		);

		foreach ( $tabs as $id => $tab ) {

			if ( ! current_user_can( $tab['capability'] ) ) {
				continue;
			}

			$cmb->add_field(
				array(
					'id'   => $tab['base_id'] . $id,
					'type' => 'tab_open',
				)
			);

			$cmb->add_field(
				array(
					'id'   => 'tab_title_' . $id,
					'type' => 'title',
					'desc' => $tab['desc'],
					'name' => $tab['title'],
				)
			);

			AdminHelper::load_view( "admin-pages/{$id}.php", compact( 'cmb' ) );

			/**
			 * Add setting into specific tab of main metabox.
			 *
			 * The dynamic part of the hook name. $id, is the tab id.
			 *
			 * @param CMB2 $cmb CMB2 object.
			 */
			do_action( 'wp_quiz_settings_page_' . $id, $cmb );

			$cmb->add_field(
				array(
					'id'   => $this->page_id . '-setting-panel-' . $id . '-close',
					'type' => 'tab_close',
				)
			);
		}

		$cmb->add_field(
			array(
				'id'   => 'setting-panel-container-close-' . $this->page_id,
				'type' => 'tab_container_close',
			)
		);

		RenderCallbacks::pre_init( $cmb );
	}

	/**
	 * Add custom classes to page.
	 *
	 * @return array
	 */
	public function get_page_classes() {
		$classes = array( 'wp-quiz-settings-page-wrap' );
		return $classes;
	}

	/**
	 * Gets tabs.
	 *
	 * @return array
	 */
	protected function get_tabs() {
		$tabs = array(
			'general'          => array(
				'title'   => esc_html__( 'General', 'wp-quiz-pro' ),
				'desc'    => esc_html__( 'From here you can control general settings related to quizzes.', 'wp-quiz-pro' ),
				'base_id' => $this->page_id . '-setting-panel-',
				'icon'    => 'dashicons dashicons-admin-settings',
			),
			'defaults'         => array(
				'title'   => esc_html__( 'Defaults', 'wp-quiz-pro' ),
				'desc'    => esc_html__( 'These default values will be used while creating a new quiz.', 'wp-quiz-pro' ),
				'base_id' => $this->page_id . '-setting-panel-',
				'icon'    => 'dashicons dashicons-admin-generic',
			),
			'subscription'     => array(
				'title'   => esc_html__( 'Subscription', 'wp-quiz-pro' ),
				'desc'    => esc_html__( 'From here you can control the settings related to the Subscribption form shown at the end of the quiz.', 'wp-quiz-pro' ),
				'base_id' => $this->page_id . '-setting-panel-',
				'icon'    => 'dashicons dashicons-email-alt',
			),
			'google-analytics' => array(
				'title'   => esc_html__( 'Google Analytics', 'wp-quiz-pro' ),
				'desc'    => esc_html__( 'Track Quiz related events directly from the Google Analytics.', 'wp-quiz-pro' ),
				'base_id' => $this->page_id . '-setting-panel-',
				'icon'    => 'dashicons dashicons-chart-bar',
			),
			'quiz-types'       => array(
				'title'   => esc_html__( 'Quiz types', 'wp-quiz-pro' ),
				'desc'    => esc_html__( 'You can enable or disable quiz types from here.', 'wp-quiz-pro' ),
				'base_id' => $this->page_id . '-setting-panel-',
				'icon'    => 'dashicons dashicons-screenoptions',
			),
		);

		/**
		 * Allow developers to add new tabs into quiz settings page.
		 *
		 * @param array $tabs Array of tabs.
		 */
		return apply_filters( 'wp_quiz_settings_tabs', $tabs );
	}
}

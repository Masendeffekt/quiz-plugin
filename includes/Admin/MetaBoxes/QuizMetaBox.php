<?php
/**
 * Quiz meta box
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin\MetaBoxes;

use CMB2;
use WPQuiz\Admin\CMB2Custom\RenderCallbacks;
use WPQuiz\Helper;
use WPQuiz\PostTypeQuiz;
use WPQuiz\QuizType;
use WPQuiz\QuizTypeManager;

/**
 * Class QuizMetaBox
 */
class QuizMetaBox {

	/**
	 * Meta box ID.
	 *
	 * @var string
	 */
	protected $meta_box_id = 'wp_quiz_meta_box';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = '';

	/**
	 * QuizMetaBox constructor.
	 */
	public function __construct() {
		$this->post_type = PostTypeQuiz::get_name();
	}

	/**
	 * Initializes.
	 */
	public function init() {
		add_action( 'cmb2_admin_init', array( $this, 'register_meta_boxes' ), 1 );
		add_action( "add_meta_boxes_{$this->post_type}", array( $this, 'change_meta_boxes_order' ), 99 );
		// add_action( 'wp_insert_post_data', array( $this, 'pre_process_quiz_data' ), 10, 2 );
		add_action( "auto-draft_{$this->post_type}", array( $this, 'save_auto_draft_quiz' ) );
		add_action( "save_post_{$this->post_type}", array( $this, 'save_quiz_content' ) );
	}

	/**
	 * Pre processes quiz data before it is inserted into the database.
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 * @return array
	 */
	public function pre_process_quiz_data( array $data, array $postarr ) {
		if ( $this->post_type !== $data['post_type'] || empty( $postarr['ID'] ) ) {
			return $data; // Only do on updating quiz.
		}

		/**
		 * If the quiz contains links with target attribute, WordPress automatically adds ref="noopener noreferrer"
		 * to the links, without slashes. This makes the json structure broken.
		 * @see wp_targeted_link_rel_callback()
		 */
		$data['post_content'] = str_replace( 'rel="noopener noreferrer"', 'ref=\\\\\\"noopener noreferrer\\\\\\"', $data['post_content'] );

		$new_quiz_data = json_decode( wp_unslash( $data['post_content'] ), true );
		$old_quiz      = PostTypeQuiz::get_quiz( $postarr['ID'] );

		if ( ! empty( $new_quiz_data['questions'] ) ) {
			$old_questions = $old_quiz->get_questions();
			foreach ( $new_quiz_data['questions'] as $question_id => &$question ) {
				if ( ! isset( $old_questions[ $question_id ] ) ) {
					continue;
				}
				$question = wp_parse_args( $question, $old_questions[ $question_id ] );
			}
		}

		if ( ! empty( $new_quiz_data['results'] ) ) {
			$old_results = $old_quiz->get_results();
			foreach ( $new_quiz_data['results'] as $result_id => &$result ) {
				if ( ! isset( $old_results[ $result_id ] ) ) {
					continue;
				}
				$result = wp_parse_args( $result, $old_results[ $result_id ] );
			}
		}

		$data['post_content'] = wp_slash( wp_json_encode( $new_quiz_data ) );
		return $data;
	}

	/**
	 * Registers meta box.
	 */
	public function register_meta_boxes() {
		$quiz_types = QuizTypeManager::get_all();
		foreach ( $quiz_types as $quiz_type ) {
			$cmb = new_cmb2_box(
				array(
					'id'           => "{$this->meta_box_id}_{$quiz_type->get_name()}",
					// translators: Quiz type.
					'title'        => sprintf( __( '%s Options', 'wp-quiz-pro' ), $quiz_type->get_title() ),
					'object_types' => $this->post_type,
					'context'      => 'normal',
					'priority'     => 'high',
					'classes_cb'   => array( $this, 'get_meta_box_classes' ),
					'show_on_cb'   => function( CMB2 $cmb ) use ( $quiz_type ) {
						$screen = get_current_screen();
						if ( PostTypeQuiz::get_name() !== $screen->id ) {
							return false;
						}
						if ( 'add' === $screen->action ) {
							if ( empty( $_GET['wp_quiz_type'] ) ) { // WPCS: csrf ok.
								return false;
							}
							$type = wp_unslash( $_GET['wp_quiz_type'] ); // WPCS: csrf, sanitization ok.
							return $quiz_type->get_name() === $type;
						}
						$quiz_id = $cmb->object_id();
						return $quiz_type->get_name() === get_post_meta( $quiz_id, 'quiz_type', true );
					},
				)
			);

			$tabs = $this->get_tabs();
			foreach ( $tabs as $index => $tab ) {
				$tabs[ $index ] = wp_parse_args(
					$tab,
					array(
						'icon'       => '',
						'title'      => '',
						'base_id'    => '',
						'capability' => 'edit_posts',
					)
				);
			}

			$cmb->add_field(
				array(
					'id'   => "{$this->meta_box_id}-setting-panel-container",
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

				$quiz_type->tab_options( $id, $cmb );

				/**
				 * Add setting into specific tab of main metabox.
				 *
				 * The dynamic part of the hook name. $id, is the tab id.
				 *
				 * @param CMB2 $cmb CMB2 object.
				 */
				do_action( "wp_quiz_{$quiz_type->get_name()}_meta_box_settings_{$id}", $cmb );

				$cmb->add_field(
					array(
						'id'   => $this->meta_box_id . '-setting-panel-' . $id . '-close',
						'type' => 'tab_close',
					)
				);
			}

			$cmb->add_field(
				array(
					'id'   => "{$this->meta_box_id}-setting-panel-container-close",
					'type' => 'tab_container_close',
				)
			);

			RenderCallbacks::pre_init( $cmb );
		}
	}

	/**
	 * Add custom classes to metabox.
	 *
	 * @return array
	 */
	public function get_meta_box_classes() {
		$classes = array( 'wp-quiz-meta-box-wrap' );
		return $classes;
	}

	/**
	 * Gets tabs.
	 *
	 * @return array
	 */
	protected function get_tabs() {
		$tabs = array(
			'content'  => array(
				'icon'       => 'dashicons dashicons-format-aside',
				'title'      => esc_html__( 'Content', 'wp-quiz-pro' ),
				'capability' => 'edit_posts',
				'base_id'    => $this->meta_box_id . '-setting-panel-',
			),
			'styling'  => array(
				'icon'       => 'dashicons dashicons-admin-customizer',
				'title'      => esc_html__( 'Styling', 'wp-quiz-pro' ),
				'capability' => 'edit_posts',
				'base_id'    => $this->meta_box_id . '-setting-panel-',
			),
			'settings' => array(
				'icon'       => 'dashicons dashicons-admin-generic',
				'title'      => esc_html__( 'Settings', 'wp-quiz-pro' ),
				'capability' => 'edit_posts',
				'base_id'    => $this->meta_box_id . '-setting-panel-',
			),
		);

		/**
		 * Allow developers to add new tabs into quiz meta box.
		 *
		 * @param array $tabs Array of tabs.
		 */
		return apply_filters( 'wp_quiz_meta_box_tabs', $tabs );
	}

	/**
	 * Changes meta boxes order.
	 */
	public function change_meta_boxes_order() {
		global $wp_meta_boxes;
		$quiz_meta_box_key = 'wp_quiz_meta_box_' . ( isset( $_GET['wp_quiz_type'] ) ? $_GET['wp_quiz_type'] : 'trivia' ); // phpcs:ignore
		if ( ! isset( $wp_meta_boxes[ $this->post_type ]['normal']['high'][ $quiz_meta_box_key ] ) ) {
			return;
		}

		$new_meta_boxes = array( $quiz_meta_box_key => $wp_meta_boxes[ $this->post_type ]['normal']['high'][ $quiz_meta_box_key ] );
		unset( $wp_meta_boxes[ $this->post_type ]['normal']['high'][ $quiz_meta_box_key ] );
		$wp_meta_boxes[ $this->post_type ]['normal']['high'] = $new_meta_boxes + $wp_meta_boxes[ $this->post_type ]['normal']['high']; // phpcs:ignore
	}

	/**
	 * Saves auto draft quiz.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_auto_draft_quiz( $post_id ) {
		if ( isset( $_GET['wp_quiz_type'] ) ) {
			update_post_meta( $post_id, 'quiz_type', sanitize_text_field( wp_unslash( $_GET['wp_quiz_type'] ) ) );
		}
	}

	/**
	 * Saves the quiz content.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_quiz_content( $post_id ) {
		if ( empty( $_POST['quiz_content'] ) ) {
			return;
		}

		$new_quiz_data = json_decode( wp_unslash( $_POST['quiz_content'] ), true );
		$old_quiz      = PostTypeQuiz::get_quiz( $post_id );

		if ( ! empty( $new_quiz_data['questions'] ) ) {
			$old_questions = $old_quiz->get_questions();
			foreach ( $new_quiz_data['questions'] as $question_id => &$question ) {
				if ( ! isset( $old_questions[ $question_id ] ) ) {
					continue;
				}
				$question = wp_parse_args( $question, $old_questions[ $question_id ] );
			}
		}

		if ( ! empty( $new_quiz_data['results'] ) ) {
			$old_results = $old_quiz->get_results();
			foreach ( $new_quiz_data['results'] as $result_id => &$result ) {
				if ( ! isset( $old_results[ $result_id ] ) ) {
					continue;
				}
				$result = wp_parse_args( $result, $old_results[ $result_id ] );
			}
		}

		$save_data = wp_json_encode( $new_quiz_data );

		// Use direct SQL query to save the post_content to prevent unnecessary filters cause bugs when using wp_insert_post().
		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			array( 'post_content' => $save_data ),
			array( 'ID' => $post_id ),
			array( '%s' ),
			array( '%d' )
		);
	}
}

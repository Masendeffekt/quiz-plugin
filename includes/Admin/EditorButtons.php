<?php
/**
 * Class EditorButtons
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin;

use WP_Query;
use WPQuiz\PostTypeQuiz;

/**
 * Class EditorButtons
 */
class EditorButtons {

	/**
	 * Initializes.
	 */
	public function init() {
		add_filter( 'mce_buttons', array( $this, 'add_editor_button' ) );
		add_filter( 'mce_external_plugins', array( $this, 'add_editor_plugin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_script_vars' ) );
	}

	/**
	 * Adds editor button.
	 *
	 * @param array $buttons Editor buttons.
	 * @return array
	 */
	public function add_editor_button( $buttons ) {
		array_push( $buttons, 'wp_quiz_pro' );
		return $buttons;
	}


	/**
	 * Adds editor plugin.
	 *
	 * @param array $plugin_array Editor plugins.
	 * @return array
	 */
	public function add_editor_plugin( $plugin_array ) {
		if ( is_admin() ) {
			$plugin_array['wp_quiz_pro'] = wp_quiz()->admin_assets() . 'js/editor-plugin.js';
		}
		return $plugin_array;
	}

	/**
	 * Adds script vars.
	 */
	public function add_script_vars() {
		$quiz_choices = array();
		$query        = new WP_Query(
			array(
				'post_type'              => PostTypeQuiz::get_name(),
				'nopaging'               => true,
				'post_status'            => 'publish',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$quiz_choices[] = array(
					'text'  => html_entity_decode( $post->post_title ),
					'value' => $post->ID,
				);
			}
			wp_reset_postdata();
		}

		wp_localize_script(
			'editor',
			'WP_Quiz_Pro_Buttons',
			array(
				'quizChoices' => $quiz_choices,
				'i18n'        => array(
					'insert'            => __( 'Insert', 'wp-quiz-pro' ),
					'cancel'            => __( 'Cancel', 'wp-quiz-pro' ),
					'quizShortcode'     => __( 'Quiz shortcode', 'wp-quiz-pro' ),
					'selectQuiz'        => __( 'Select Quiz', 'wp-quiz-pro' ),
					'showQuestions'     => __( 'Show questions', 'wp-quiz-pro' ),
					'showQuestionsDesc' => __( 'Fill 2 to show 2 first questions. Fill -2 to hide 2 first questions', 'wp-quiz-pro' ),
					'quizzesShortcode'  => __( 'Quizzes shortcode', 'wp-quiz-pro' ),
					'numberOfQuizzes'   => __( 'Number of Quizzes', 'wp-quiz-pro' ),
				),
			)
		);
	}
}

<?php
/**
 * Shortcode [wp_quiz_pro]
 *
 * @package WPQuiz
 */

namespace WPQuiz\Shortcodes;

use WPQuiz\PostTypeQuiz;

/**
 * Class WPQuizProShortcode
 */
class WPQuizProShortcode {

	/**
	 * Shortcode name.
	 *
	 * @var string
	 */
	protected $shortcode_name = 'wp_quiz_pro';

	/**
	 * Registers shortcode.
	 */
	public function register() {
		add_shortcode( $this->shortcode_name, array( $this, 'render' ) );
		add_filter( 'the_content', array( $this, 'insert_to_content' ) );
	}

	/**
	 * Renders shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'           => get_the_ID(),
				'play_data_id' => '',
				'question'     => '',
			),
			$atts,
			$this->shortcode_name
		);

		if ( ! $atts['id'] ) {
			return '';
		}

		$post = get_post( $atts['id'] );
		if ( ! $post || PostTypeQuiz::get_name() !== $post->post_type ) {
			return "<!-- quiz #{$atts['id']} not found -->";
		}

		$quiz = PostTypeQuiz::get_quiz( $post );
		return $quiz->get_frontend_output( $atts );
	}

	/**
	 * Inserts shortcode to quiz content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function insert_to_content( $content ) {
		global $post;

		if ( ! is_single() ) {
			return $content;
		}

		if ( PostTypeQuiz::get_name() !== $post->post_type ) {
			return $content;
		}

		return '[wp_quiz_pro id="' . $post->ID . '"]';
	}
}

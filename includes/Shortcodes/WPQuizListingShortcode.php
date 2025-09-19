<?php
/**
 * Shortcode [wp_quiz_listing]
 *
 * @package WPQuiz
 */

namespace WPQuiz\Shortcodes;

use WP_Query;
use WPQuiz\Assets;
use WPQuiz\PostTypeQuiz;
use WPQuiz\Template;

/**
 * Class WPQuizListingShortcode
 */
class WPQuizListingShortcode {

	/**
	 * Shortcode name.
	 *
	 * @var string
	 */
	protected $shortcode_name = 'wp_quiz_listing';

	/**
	 * Registers shortcode.
	 */
	public function register() {
		add_shortcode( $this->shortcode_name, array( $this, 'render' ) );
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
				'num' => 30,
			),
			$atts,
			$this->shortcode_name
		);

		$query_args = array(
			'post_type'              => PostTypeQuiz::get_name(),
			'post_status'            => 'publish',
			'posts_per_page'         => $atts['num'],
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		);

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return '';
		}

		$assets = new Assets();
		$assets->enqueue_styles();

		ob_start();
		Template::load_template( 'quiz-listing.php', compact( 'query', 'atts' ) );
		$output = ob_get_clean();

		wp_reset_postdata();

		return $output;
	}
}

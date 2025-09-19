<?php
/**
 * Meta tags helpers
 *
 * @since 2.0.8
 *
 * @package WPQuiz
 */

namespace WPQuiz;

use WPQuiz\PlayDataTracking\PlayData;

/**
 * Class MetaTags
 */
class MetaTags {

	/**
	 * Prints meta url tags.
	 *
	 * @param array    $result    Result data.
	 * @param PlayData $play_data Play data.
	 */
	public static function url( array $result, PlayData $play_data ) {
		add_filter( 'wpseo_opengraph_url', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/url', '__return_empty_string' );
		$tags = sprintf( "<meta property=\"og:url\" content=\"%s\" />\n", esc_url( add_query_arg( 'wqtid', $play_data->id, Helper::get_current_url() ) ) );

		/**
		 * Allows changing meta url tags.
		 *
		 * @param array    $result    Result data.
		 * @param PlayData $play_data Play data.
		 */
		echo apply_filters( 'wp_quiz_meta_tags_url', $tags, $result, $play_data );
	}

	/**
	 * Prints meta title tags.
	 *
	 * @param array    $result    Result data.
	 * @param PlayData $play_data Play data.
	 */
	public static function title( array $result, PlayData $play_data ) {
		if ( 'trivia' === $play_data->quiz_type ) {
			$result['title'] = str_replace( array( '%%score%%', '%%total%%' ), array( $play_data->correct_answered, count( $play_data->quiz_data['questions'] ) ), $result['title'] );
		}

		add_filter( 'wpseo_opengraph_title', '__return_empty_string' );
		add_filter( 'wpseo_twitter_title', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/facebook/og_title', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/twitter/twitter_title', '__return_empty_string' );
		$tags  = sprintf( "<meta property=\"og:title\" content=\"%s\" />\n", esc_attr( $result['title'] ) );
		$tags .= sprintf( "<meta property=\"twitter:title\" content=\"%s\" />\n", esc_attr( $result['title'] ) );

		/**
		 * Allows changing meta title tags.
		 *
		 * @param array    $result    Result data.
		 * @param PlayData $play_data Play data.
		 */
		echo apply_filters( 'wp_quiz_meta_tags_title', $tags, $result, $play_data );
	}

	/**
	 * Prints meta description tags.
	 *
	 * @param array    $result    Result data.
	 * @param PlayData $play_data Play data.
	 */
	public static function description( array $result, PlayData $play_data ) {
		if ( 'trivia' === $play_data->quiz_type ) {
			$result['desc'] = str_replace( array( '%%score%%', '%%total%%' ), array( $play_data->correct_answered, count( $play_data->quiz_data['questions'] ) ), $result['desc'] );
		}

		add_filter( 'wpseo_opengraph_desc', '__return_empty_string' );
		add_filter( 'wpseo_twitter_description', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/facebook/og_description', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/twitter/twitter_description', '__return_empty_string' );
		$tags  = sprintf( "<meta property=\"og:description\" content=\"%s\" />\n", esc_attr( $result['desc'] ) );
		$tags .= sprintf( "<meta property=\"twitter:description\" content=\"%s\" />\n", esc_attr( $result['desc'] ) );

		/**
		 * Allows changing meta description tags.
		 *
		 * @param array    $result    Result data.
		 * @param PlayData $play_data Play data.
		 */
		echo apply_filters( 'wp_quiz_meta_tags_description', $tags, $result, $play_data );
	}

	/**
	 * Prints meta image tags.
	 *
	 * @param array    $result    Result data.
	 * @param PlayData $play_data Play data.
	 */
	public static function image( array $result, PlayData $play_data ) {
		add_filter( 'wpseo_opengraph_image', '__return_empty_string' );
		add_filter( 'wpseo_twitter_image', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/twitter/twitter_image', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/facebook/og_image', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/facebook/og_image_secure_url', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/facebook/og_image_width', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/facebook/og_image_height', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/facebook/og_image_alt', '__return_empty_string' );
		add_filter( 'rank_math/opengraph/facebook/og_image_type', '__return_empty_string' );
		$tags  = sprintf( "<meta property=\"og:image\" content=\"%s\" />\n", esc_url( $result['image'] ) );
		$tags .= sprintf( "<meta property=\"twitter:image\" content=\"%s\" />\n", esc_url( $result['image'] ) );

		/**
		 * Allows changing meta image tags.
		 *
		 * @param array    $result    Result data.
		 * @param PlayData $play_data Play data.
		 */
		echo apply_filters( 'wp_quiz_meta_tags_image', $tags, $result, $play_data );
	}
}

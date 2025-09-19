<?php
/**
 * Base quiz type
 *
 * @package WPQuizPro
 */

namespace WPQuiz;

use WP_Error;
use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\Traits\QuizTypeBackend;
use WPQuiz\Traits\QuizTypeFrontend;
use WPQuiz\Traits\QuizTypeStats;

/**
 * Class QuizType
 */
class QuizType {

	use QuizTypeBackend;
	use QuizTypeFrontend;
	use QuizTypeStats;

	/**
	 * Quiz type name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Quiz description.
	 *
	 * @var string
	 */
	protected $desc = '';

	/**
	 * Default enable this quiz type in settings.
	 *
	 * @var bool
	 */
	protected $default_enabled = true;

	/**
	 * Has results or not.
	 *
	 * @var bool
	 */
	protected $has_results = true;

	/**
	 * Has answers or not.
	 *
	 * @var bool
	 */
	protected $has_answers = true;

	/**
	 * Quiz type icon class.
	 *
	 * @var string
	 */
	protected $icon = 'dashicons dashicons-editor-help';

	/**
	 * Processed questions.
	 *
	 * @var array
	 */
	protected $processed_questions = array();

	/**
	 * Gets quiz type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Gets quiz type title.
	 *
	 * @return string
	 */
	public function get_title() {
		return ucfirst( $this->name );
	}

	/**
	 * Shows quiz type title.
	 *
	 * @param string $prefix Prefix.
	 * @param string $suffix Suffix.
	 * @param bool   $echo   Show the output or just return it.
	 * @return string
	 */
	public function show_title( $prefix = '', $suffix = '', $echo = true ) {
		$output = $prefix . $this->get_title() . $suffix;
		if ( ! $echo ) {
			return $output;
		}
		echo wp_kses_post( $output );
	}

	/**
	 * Gets quiz type description.
	 *
	 * @return string
	 */
	public function get_desc() {
		return $this->desc;
	}

	/**
	 * Shows quiz type description.
	 *
	 * @param string $prefix Prefix.
	 * @param string $suffix Suffix.
	 * @param bool   $echo   Show the output or just return it.
	 * @return string
	 */
	public function show_desc( $prefix = '', $suffix = '', $echo = true ) {
		$output = $prefix . $this->desc . $suffix;
		if ( ! $echo ) {
			return $output;
		}
		echo wp_kses_post( $output );
	}

	/**
	 * Gets icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return $this->icon;
	}

	/**
	 * Checks if this type is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return 'on' === Helper::get_option( 'enable_' . $this->name );
	}

	/**
	 * Checks if is active screen.
	 *
	 * @return bool
	 */
	protected function is_edit_screen() {
		return get_current_screen()->id === PostTypeQuiz::get_name();
	}

	/**
	 * Gets quiz default settings.
	 *
	 * @return array
	 */
	public function get_default_settings() {
		return array();
	}

	/**
	 * Gets default data for question.
	 *
	 * @return array
	 */
	public function get_default_question() {
		return array(
			'id'      => '',
			'title'   => '',
			'desc'    => '',
			'answers' => array(),
		);
	}

	/**
	 * Gets default data for answer.
	 *
	 * @return array
	 */
	public function get_default_answer() {
		return array(
			'id'      => '',
			'title'   => '',
			'image'   => '',
			'imageId' => '',
		);
	}

	/**
	 * Gets default data for result.
	 *
	 * @return array
	 */
	public function get_default_result() {
		return array(
			'id'      => '',
			'title'   => '',
			'image'   => '',
			'imageId' => '',
		);
	}

	/**
	 * Gets questions.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_questions( Quiz $quiz ) {
		return $quiz->get_questions();
	}

	/**
	 * Gets results.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_results( Quiz $quiz ) {
		return $quiz->get_results();
	}

	/**
	 * Gets settings.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_settings( Quiz $quiz ) {
		return $quiz->get_settings();
	}

	/**
	 * Gets processed questions.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_processed_questions( Quiz $quiz ) {
		if ( isset( $this->processed_questions[ $quiz->get_id() ] ) ) {
			return $this->processed_questions[ $quiz->get_id() ];
		}

		$questions = $this->get_questions( $quiz );

		// Questions order for list quiz.
		$orderby = $quiz->get_setting( 'question_orderby' );
		if ( 'random' === $orderby ) {
			shuffle( $questions );
		} elseif ( 'votes' === $orderby ) {
			uasort( $questions, array( $this, 'sort_questions_by_votes_callback' ) );
		}

		if ( ! empty( $quiz->displayed_question ) ) {
			if ( $quiz->displayed_question > 0 ) {
				$questions = array_slice( $questions, 0, $quiz->displayed_question );
			} else {
				$questions = array_slice( $questions, absint( $quiz->displayed_question ) );
			}
		} elseif ( 'on' === $quiz->get_setting( 'rand_questions' ) ) {
			$questions = Helper::shuffle_assoc( $questions );
		}

		$index = 0;
		foreach ( $questions as &$question ) {
			$question          = wp_parse_args( $question, $this->get_default_question() );
			$question['index'] = $index;
			$this->add_question_extra_data( $question );
			$index++;
		}

		$this->processed_questions[ $quiz->get_id() ] = $questions;

		return $questions;
	}

	/**
	 * Sort questions by votes callback.
	 *
	 * @param array $a Question A.
	 * @param array $b Question B.
	 * @return int
	 */
	public function sort_questions_by_votes_callback( $a, $b ) {
		$a['votesUp']   = isset( $a['votesUp'] ) ? intval( $a['votesUp'] ) : 0;
		$a['votesDown'] = isset( $a['votesDown'] ) ? intval( $a['votesDown'] ) : 0;
		$b['votesUp']   = isset( $b['votesUp'] ) ? intval( $b['votesUp'] ) : 0;
		$b['votesDown'] = isset( $b['votesDown'] ) ? intval( $b['votesDown'] ) : 0;

		$a_point = $a['votesUp'] - $a['votesDown'];
		$b_point = $b['votesUp'] - $b['votesDown'];
		if ( $a_point === $b_point ) {
			return 1;
		}
		return $a_point > $b_point ? -1 : 1;
	}

	/**
	 * Prints content in <head> section.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	public function wp_head( Quiz $quiz ) {
		if ( 'on' !== Helper::get_option( 'share_meta' ) ) {
			return;
		}

		$post = $quiz->get_post();
		global $wpseo_og;
		$og_desc      = str_replace( array( "\r", "\n" ), '', wp_strip_all_tags( $post->post_excerpt ) );
		$twitter_desc = $og_desc;

		if ( defined( 'WPSEO_VERSION' ) ) {
			remove_action( 'wpseo_head', array( $wpseo_og, 'opengraph' ), 30 );
			remove_action( 'wpseo_head', array( 'WPSEO_Twitter', 'get_instance' ), 40 );
			// Use description from yoast.
			$twitter_desc = get_post_meta( $post->ID, '_yoast_wpseo_twitter-description', true );
			$og_desc      = get_post_meta( $post->ID, '_yoast_wpseo_opengraph-description', true );
		}
		?>
		<meta name="twitter:title" content="<?php echo esc_attr( get_the_title( $post ) ); ?>">
		<meta name="twitter:description" content="<?php echo esc_attr( $twitter_desc ); ?>">
		<meta name="twitter:domain" content="<?php echo esc_url( site_url() ); ?>">
		<meta property="og:url" content="<?php echo esc_url( get_permalink( $post ) ); ?>" />
		<meta property="og:title" content="<?php echo esc_attr( get_the_title( $post ) ); ?>" />
		<meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>" />
		<?php
		if ( has_post_thumbnail( $post ) ) {
			$thumb_id        = get_post_thumbnail_id( $post );
			$thumb_url_array = wp_get_attachment_image_src( $thumb_id, 'full', true );
			$thumb_url       = $thumb_url_array[0];
			?>
			<meta name="twitter:card" content="summary_large_image">
			<meta name="twitter:image:src" content="<?php echo esc_url( $thumb_url ); ?>">
			<meta property="og:image" content="<?php echo esc_url( $thumb_url ); ?>" />
			<meta itemprop="image" content="<?php echo esc_url( $thumb_url ); ?>">
			<?php
		}
	}

	/**
	 * Gets player data to insert into DB.
	 *
	 * @param Quiz  $quiz        Quiz object.
	 * @param array $player_data Player data get from REST request.
	 * @return array|false       Return an array with keys corresponding to players table columns.
	 *                           Return `false` if do not want to track player data on this quiz type.
	 */
	public function get_inserting_player_data( Quiz $quiz, array $player_data ) {
		$insert_data = array(
			'user_id' => is_user_logged_in() ? get_current_user_id() : null,
			'user_ip' => Helper::get_current_ip(),
			'type'    => is_user_logged_in() ? 'user' : 'guest',
		);

		/**
		 * Allows changing player insert data.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_data Player insert data.
		 * @param array $player_data Unprocessed player data from REST request.
		 * @param Quiz  $quiz        Quiz object.
		 */
		return apply_filters( 'wp_quiz_player_insert_data', $insert_data, $player_data, $quiz );
	}

	/**
	 * Gets play data to insert into DB.
	 *
	 * @param Quiz  $quiz           Quiz object.
	 * @param array $player_data    Player data get from REST request.
	 * @return array|WP_Error|false Return an array with keys corresponding to plays table columns.
	 *                              Return `false` if do not want to track player data on this quiz type.
	 */
	public function get_inserting_play_data( Quiz $quiz, array $player_data ) {
		_doing_it_wrong( __METHOD__, esc_html__( 'This method must be overridden in quiz type classes', 'wp-quiz-pro' ), '2.0.0' );
		return false;
	}

	/**
	 * Shows play data detail.
	 *
	 * @param PlayData $play_data Play data.
	 * @param bool     $no_result Not show the result.
	 */
	public function show_tracking_data( PlayData $play_data, $no_result = false ) {}

	/**
	 * Gets quiz result email output.
	 *
	 * @param Quiz     $quiz      Quiz object.
	 * @param PlayData $play_data Play data.
	 * @return string
	 */
	public function quiz_result_email( Quiz $quiz, PlayData $play_data ) {
		return '';
	}
}

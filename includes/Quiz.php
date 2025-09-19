<?php
/**
 * Quiz class
 *
 * @package WPQuiz
 */

namespace WPQuiz;

use WPQuiz\Migration\Migration;
use WPQuiz\Modules\Stats\DB as Stats_DB;
use WP_Error;
use WP_Post;
use WPQuiz\PlayDataTracking\PlayData;

/**
 * Class Quiz
 */
class Quiz {

	/**
	 * Questions.
	 *
	 * @var array
	 */
	protected $questions = array();

	/**
	 * Results.
	 *
	 * @var array
	 */
	protected $results = array();

	/**
	 * Settings.
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Quiz type.
	 *
	 * @var QuizType
	 */
	protected $quiz_type;

	/**
	 * The post object.
	 *
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * Play data.
	 *
	 * @var PlayData
	 */
	public $play_data = null;

	/**
	 * Displayed question param retrieve from shortcode.
	 *
	 * @var int
	 */
	public $displayed_question = null;

	/**
	 * Quiz constructor.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function __construct( WP_Post $post ) {
		$this->post = $post;

		$quiz_type = get_post_meta( $post->ID, 'quiz_type', true );
		$this->set_quiz_type( $quiz_type );

		if ( '' !== get_post_meta( $post->ID, 'questions', true ) ) {
			$this->migrate_from_v1( $post );
			return;
		}

		$post_content = $post->post_content;
		$data         = json_decode( $post_content, true );
		if ( ! $data ) {
			// This is for v2.0.18 and older.
			$post_content = str_replace( array( '="', '/"' ), array( '=\\"', '/\\"' ), $post->post_content );
			$data         = json_decode( $post_content, true );
		}

		if ( $data && ! empty( $data['questions'] ) ) {
			$this->set_questions( $data['questions'] );
		}
		if ( $data && ! empty( $data['results'] ) ) {
			$this->set_results( $data['results'] );
		}

		$meta = get_post_meta( $post->ID );
		foreach ( $meta as $key => $value ) {
			if ( strpos( $key, 'wp_quiz_' ) !== 0 ) {
				continue;
			}
			$this->settings[ substr( $key, strlen( 'wp_quiz_' ) ) ] = maybe_unserialize( $value[0] );
		}
		$this->settings = wp_parse_args( $this->settings, $this->quiz_type->get_default_settings() );
	}

	/**
	 * Migrates quiz from version 1.
	 *
	 * @param WP_Post $post Post object.
	 */
	protected function migrate_from_v1( WP_Post $post ) {
		$quiz_type = get_post_meta( $post->ID, 'quiz_type', true );
		$questions = get_post_meta( $post->ID, 'questions', true );
		$results   = get_post_meta( $post->ID, 'results', true );
		$settings  = get_post_meta( $post->ID, 'settings', true );

		if ( $settings ) {
			$switch_fields = array(
				'rand_questions',
				'rand_answers',
				'restart_questions',
				'promote_plugin',
				'embed_toggle',
				'auto_scroll',
				'end_answers',
				'show_ads',
			);
			foreach ( $settings as $key => &$value ) {
				// Normalize on/off value.
				if ( in_array( $key, $switch_fields, true ) ) {
					$value = intval( $value ) ? 'on' : 'off';
				} elseif ( 'ad_codes' === $key ) {
					$value = explode( ',', $value );
				} elseif ( 'animation_in' === $key ) {
					$value = Migration::transition_animation_in( $value );
				} elseif ( 'animation_out' === $key ) {
					$value = Migration::transition_animation_out( $value );
				}
				update_post_meta( $post->ID, 'wp_quiz_' . $key, $value );
			}

			$this->settings = wp_parse_args( $settings, $this->quiz_type->get_default_settings() );

			delete_post_meta( $post->ID, 'settings' );
			unset( $settings );
		}

		$content = array(
			'questions' => array(),
			'results'   => array(),
		);

		$result_titles = array();
		if ( $results ) {
			foreach ( $results as $index => $result ) {
				$id                                = Helper::generate_random_string();
				$result['id']                      = $id;
				$result['index']                   = $index;
				$content['results'][ $id ]         = $result;
				$result_titles[ $result['title'] ] = $id;
			}
			delete_post_meta( $post->ID, 'results' );
			unset( $results );
		}

		if ( $questions ) {
			foreach ( $questions as $index => $question ) {
				$id                = Helper::generate_random_string();
				$question['id']    = $id;
				$question['index'] = $index;
				$has_image_answer  = false;

				if ( ! in_array( $question['mediaType'], array( 'image', 'video' ), true ) ) {
					$question['mediaType'] = 'image';
				}

				if ( ! empty( $question['answers'] ) ) {
					$new_answers = array();
					foreach ( $question['answers'] as $aindex => $answer ) {
						$aid             = Helper::generate_random_string();
						$answer['id']    = $aid;
						$answer['index'] = $aindex;

						if ( ! empty( $answer['image'] ) ) {
							$has_image_answer = true;
						}

						if ( 'personality' === $quiz_type ) {
							$answer_results    = array();
							$answer['results'] = ! empty( $answer['results'] ) ? (array) $answer['results'] : array();
							foreach ( $answer['results'] as $answer_result ) {
								if ( ! isset( $result_titles[ $answer_result['title'] ] ) ) {
									continue;
								}
								$answer_results[ $result_titles[ $answer_result['title'] ] ] = $answer_result['points'];
							}
							$answer['results'] = $answer_results;
						}

						$new_answers[ $aid ] = $answer;
					}
					$question['answers'] = $new_answers;
				}

				if ( $has_image_answer ) {
					$question['answerType'] = 'image';
				}

				$content['questions'][ $id ] = $question;
			}
			delete_post_meta( $post->ID, 'questions' );
			unset( $result_titles );
			unset( $questions );
		}

		$this->set_questions( $content['questions'] );
		$this->set_results( $content['results'] );

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'posts',
			array(
				'post_content' => wp_json_encode( $content ),
			),
			array( 'ID' => $post->ID )
		); // WPCS: db call ok, cache ok.

		if ( ! empty( $GLOBALS['post'] ) ) {
			$GLOBALS['post']->post_content = wp_json_encode( $content ); // WPCS: override ok.
		}

		unset( $content );
		unset( $quiz_type );
	}

	/**
	 * Converts quiz to array.
	 *
	 * @return array
	 */
	public function to_array() {
		$data = array(
			'id'             => $this->get_id(),
			'title'          => $this->get_title(),
			'type'           => $this->quiz_type->get_name(),
			'questions'      => $this->questions,
			'results'        => $this->results,
			'settings'       => $this->settings,
			'featured_image' => get_the_post_thumbnail_url( $this->get_id(), 'full' ),
		);

		/**
		 * Allows changing the data when convert quiz to array.
		 *
		 * @since 2.0.0
		 *
		 * @param array $data Quiz array data.
		 * @param Quiz  $quiz Quiz object.
		 */
		return apply_filters( 'wp_quiz_to_array', $data, $this );
	}

	/**
	 * Convert quiz to JSON string.
	 *
	 * @return string
	 */
	public function to_json() {
		return wp_json_encode( $this->to_array() );
	}

	/**
	 * Sets questions.
	 *
	 * @param array $questions Questions.
	 */
	public function set_questions( array $questions ) {
		$this->questions = $questions;
	}

	/**
	 * Gets questions.
	 *
	 * @return array
	 */
	public function get_questions() {
		return $this->questions;
	}

	/**
	 * Sets results.
	 *
	 * @param array $results Results.
	 */
	public function set_results( array $results ) {
		$this->results = $results;
	}

	/**
	 * Gets results.
	 *
	 * @return array
	 */
	public function get_results() {
		return $this->results;
	}

	/**
	 * Gets quiz type.
	 *
	 * @return QuizType
	 */
	public function get_quiz_type() {
		return $this->quiz_type;
	}

	/**
	 * Sets quiz type.
	 *
	 * @param string|QuizType $type Quiz type name or object.
	 * @return bool
	 */
	public function set_quiz_type( $type ) {
		if ( $type instanceof QuizType ) {
			$this->quiz_type = $type;
			return true;
		}
		$quiz_type = QuizTypeManager::get( $type );
		if ( ! $quiz_type ) {
			$quiz_type = QuizTypeManager::get( QuizTypeManager::get_default_quiz_type() );
		}
		if ( $quiz_type ) {
			$this->quiz_type = $quiz_type;
			return true;
		}

		// translators: quiz type.
		trigger_error( sprintf( esc_html__( 'Quiz type %s does not exist', 'wp-quiz-pro' ), esc_attr( $type ) ) );
		return false;
	}

	/**
	 * Gets post object.
	 *
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * Gets quiz ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->post->ID;
	}

	/**
	 * Gets quiz title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->post->post_title;
	}

	/**
	 * Gets quiz url.
	 *
	 * @param bool $raw Return the raw quiz URL.
	 * @return string
	 */
	public function get_url( $raw = false ) {
		$raw_url = get_permalink( $this->post );
		if ( $raw || is_singular( PostTypeQuiz::get_name() ) ) {
			return $raw_url;
		}

		global $wp;
		$current_url = home_url( $wp->request );
		$current_url = add_query_arg( 'wqtid', false, $current_url );
		return $current_url;
	}

	/**
	 * Gets quiz setting.
	 *
	 * @param string $name Setting name.
	 * @return mixed
	 */
	public function get_setting( $name ) {
		if ( isset( $this->settings[ $name ] ) ) {
			return $this->settings[ $name ];
		}
		$value                   = get_post_meta( $this->get_id(), 'wp_quiz_' . $name, true );
		$this->settings[ $name ] = $value;
		return $value;
	}

	/**
	 * Gets quiz settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		return $this->settings; // This do not return all settings.
	}

	/**
	 * Updates questions.
	 *
	 * @param array $questions New questions.
	 * @return int|WP_Error
	 */
	public function update_questions( array $questions ) {
		$data              = $this->post->post_content ? json_decode( $this->post->post_content, true ) : array();
		$data['questions'] = $questions;
		return wp_update_post(
			array(
				'ID'           => $this->post->ID,
				'post_content' => wp_json_encode( $data, JSON_UNESCAPED_UNICODE ),
			)
		);
	}

	/**
	 * Updates question.
	 *
	 * @param int   $question_id Question ID.
	 * @param array $question    New question.
	 * @return int|WP_Error
	 */
	public function update_question( $question_id, $question ) {
		$question['id']                  = $question_id;
		$this->questions[ $question_id ] = $question;
		return $this->update_questions( $this->questions );
	}

	/**
	 * Updates results.
	 *
	 * @param array $results New results.
	 * @return int|WP_Error
	 */
	public function update_results( array $results ) {
		$data            = $this->post->post_content ? json_decode( $this->post->post_content, true ) : array();
		$data['results'] = $results;
		return wp_update_post(
			array(
				'ID'           => $this->post->ID,
				'post_content' => wp_json_encode( $data ),
			)
		);
	}

	/**
	 * Votes question up.
	 *
	 * @param int $question_id Question ID.
	 * @return bool|int
	 */
	public function vote_question_up( $question_id ) {
		$questions = $this->get_questions();
		if ( ! isset( $questions[ $question_id ] ) ) {
			return false;
		}

		$question    = $questions[ $question_id ];
		$up_action   = "vote_question_up_{$this->get_id()}_{$question_id}";
		$down_action = "vote_question_down_{$this->get_id()}_{$question_id}";
		if ( ! Helper::current_user_can_vote( $up_action ) || ! Helper::current_user_can_vote( $down_action ) ) {
			return false;
		}

		$votes               = ! empty( $question['votesUp'] ) ? intval( $question['votesUp'] ) : 0;
		$question['votesUp'] = ++$votes;
		if ( ! $this->update_question( $question_id, $question ) ) {
			return false;
		}
		Helper::record_current_user_action( $up_action );
		return $votes;
	}

	/**
	 * Votes question down.
	 *
	 * @param int $question_id Question ID.
	 * @return bool|int
	 */
	public function vote_question_down( $question_id ) {
		$questions = $this->get_questions();
		if ( ! isset( $questions[ $question_id ] ) ) {
			return false;
		}

		$question    = $questions[ $question_id ];
		$up_action   = "vote_question_up_{$this->get_id()}_{$question_id}";
		$down_action = "vote_question_down_{$this->get_id()}_{$question_id}";
		if ( ! Helper::current_user_can_vote( $up_action ) || ! Helper::current_user_can_vote( $down_action ) ) {
			return false;
		}

		$votes                 = ! empty( $question['votesDown'] ) ? intval( $question['votesDown'] ) : 0;
		$question['votesDown'] = ++$votes;
		if ( ! $this->update_question( $question_id, $question ) ) {
			return false;
		}
		Helper::record_current_user_action( $down_action );
		return $votes;
	}

	/**
	 * Gets embed code.
	 *
	 * @param int $width  Iframe width.
	 * @param int $height Iframe height.
	 * @return string
	 */
	public function get_embed_code( $width = 600, $height = 800 ) {
		$site_url = get_site_url() . '/?wp_quiz_id=' . $this->get_id();
		return sprintf(
			'<iframe frameborder="0" width="%d" height="%d" src="%s"></iframe>',
			intval( $width ),
			intval( $height ),
			esc_url( $site_url )
		);
	}

	/**
	 * Gets quiz frontend output.
	 *
	 * @param array $args Custom arguments. See {@see QuizType::frontend()} for more details.
	 * @return string
	 */
	public function get_frontend_output( array $args = array() ) {
		return $this->quiz_type->frontend( $this, $args );
	}

	/**
	 * Gets play count.
	 *
	 * @return int
	 */
	public function get_play_count() {
		$counts = Stats_DB::get_plays_count( ( array( 'quiz_id' => $this->get_id() ) ) );
		if ( empty( $counts[0]['count'] ) ) {
			return 0;
		}
		return intval( $counts[0]['count'] );
	}

	/**
	 * Checks if subscribing is required to play this quiz.
	 *
	 * @since 2.1.7
	 *
	 * @return bool
	 */
	public function is_required_subscribing_to_play() {
		$value = $this->get_setting( 'subscribe_to_play' );
		if ( ! $value ) {
			$value = Helper::get_option( 'subscribe_to_play' );
		}
		return 'on' === $value;
	}
}

<?php
/**
 * Importer
 *
 * @package WPQuiz
 */

namespace WPQuiz;

use WP_Error;
use WP_Query;
use WPQuiz\Migration\Migration;

/**
 * Class Importer
 */
class Importer {

	/**
	 * Check flag to download images when import.
	 *
	 * @var bool
	 */
	protected $download_images = false;

	/**
	 * Quiz author ID.
	 *
	 * @var int
	 */
	protected $author = 0;

	/**
	 * Sets download images property.
	 *
	 * @param bool $value Value.
	 */
	public function set_download_images( $value ) {
		$this->download_images = ! empty( $value );
	}

	/**
	 * Sets author ID.
	 *
	 * @param int $author_id Author ID.
	 */
	public function set_author( $author_id ) {
		$this->author = intval( $author_id );
	}

	/**
	 * Imports quiz.
	 *
	 * @param array $data      Import data.
	 * @param bool  $force_new Force creating new quiz. Default is `false`.
	 * @return int|true|WP_Error Return quiz ID if new quiz is created.
	 *                           Return `true` if quiz exists.
	 *                           Return WP_Error if there is error.
	 */
	public function import_quiz( array $data, $force_new = false ) {
		$data = wp_parse_args(
			$data,
			array(
				'title'          => '',
				'type'           => QuizTypeManager::get_default_quiz_type(),
				'questions'      => array(),
				'results'        => array(),
				'settings'       => array(),
				'featured_image' => '',
				'author'         => $this->author,
			)
		);

		if ( ! $this->is_existed( $data ) || $force_new ) {
			error_log( 'Start creating quiz: ' . $data['title'] );

			// Create new quiz.
			$result = $this->create_quiz( $data );

			if ( is_wp_error( $result ) ) {
				error_log( 'Error creating quiz: ' . $result->get_error_message() );
			} else {
				error_log( 'Created quiz: ' . $result );
			}

			return $result;
		}

		error_log( 'Quiz exists: ' . $data['title'] );
		return true;
	}

	/**
	 * Checks if quiz exists.
	 *
	 * @param array $data Quiz data.
	 * @return bool
	 */
	protected function is_existed( array $data ) {
		$query = new WP_Query(
			array(
				'title'       => $data['title'],
				'post_type'   => PostTypeQuiz::get_name(),
				'post_status' => 'any',
				'meta_key'    => 'quiz_type', // WPCS: slow query ok.
				'meta_value'  => $data['type'], // WPCS: slow query ok.
			)
		);
		return $query->have_posts();
	}

	/**
	 * Creates quiz.
	 *
	 * @param array $data {
	 *     Quiz data.
	 *
	 *     @type string $title          Quiz title.
	 *     @type string $type           Quiz type.
	 *     @type array  $questions      Questions.
	 *     @type array  $results        Results.
	 *     @type array  $settings       Settings.
	 *     @type string $featured_image Featured image url.
	 * }
	 * @return int|WP_Error
	 */
	protected function create_quiz( array $data ) {
		$data = $this->parse_import_data( $data );

		$meta_input = array( 'quiz_type' => $data['type'] );
		foreach ( $data['settings'] as $key => $value ) {
			$meta_input[ "wp_quiz_{$key}" ] = $value;
		}

		$post_content = wp_json_encode(
			array(
				'questions' => $data['questions'],
				'results'   => $data['results'],
			)
		);

		$post_arr = array(
			'post_type'    => PostTypeQuiz::get_name(),
			'post_title'   => $data['title'],
			'post_content' => wp_slash( $post_content ),
			'post_author'  => isset( $data['author'] ) ? $data['author'] : null,
			'meta_input'   => $meta_input,
		);

		$quiz_id = wp_insert_post( $post_arr );
		if ( is_wp_error( $quiz_id ) ) {
			return $quiz_id;
		}

		if ( $data['featured_image'] && 'false' !== $data['featured_image'] ) {
			$image = Helper::download_image_file( $data['featured_image'] );
			if ( $image ) {
				$wp_filetype = wp_check_filetype( $image['path'] );
				$attachment  = array(
					'guid'           => $image['path'],
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => basename( $image['path'] ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);

				$attach_id = wp_insert_attachment( $attachment, $image['path'], $quiz_id );

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $image['path'] );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				// add featured image to post.
				add_post_meta( $quiz_id, '_thumbnail_id', $attach_id );
			}
		}

		return $quiz_id;
	}

	/**
	 * Parses import data.
	 *
	 * @param array $import_data Import data.
	 * @return array
	 */
	protected function parse_import_data( array $import_data ) {
		$data = wp_parse_args(
			$import_data,
			array(
				'title'          => '',
				'type'           => QuizTypeManager::get_default_quiz_type(),
				'questions'      => array(),
				'results'        => array(),
				'settings'       => array(),
				'featured_image' => '',
				'author'         => $this->author,
			)
		);

		if ( ! empty( $data['settings'] ) && ! empty( $data['questions'][0] ) ) {
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
			foreach ( $data['settings'] as $key => &$value ) {
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
			}
		}

		$result_titles = array();
		if ( ! empty( $data['results'] ) ) {

			$new_results = array();
			foreach ( $data['results'] as $index => $result ) {
				// Download image.
				if ( ! empty( $result['image'] ) && $this->download_images ) {
					$image = Helper::download_image_file( $result['image'] );
					if ( $image ) {
						$result['image']   = $image['url'];
						$result['imageId'] = $image['id'];
					}
				}

				if ( ! empty( $data['results'][0] ) ) {
					$rid             = Helper::generate_random_string();
					$result['id']    = $rid;
					$result['index'] = $index;
				} else {
					$rid = $index;
				}

				$new_results[ $rid ]               = $result;
				$result_titles[ $result['title'] ] = $rid;
			}

			$data['results'] = $new_results;
			unset( $new_results );
		}

		if ( $data['questions'] ) {

			$new_questions = array();
			foreach ( $data['questions'] as $index => $question ) {

				if ( ! in_array( $question['mediaType'], array( 'image', 'video' ), true ) ) {
					$question['mediaType'] = 'image';
				}

				if ( ! empty( $question['image'] ) && $this->download_images ) {
					$image = Helper::download_image_file( $question['image'] );
					if ( $image ) {
						$question['image']   = $image['url'];
						$question['imageId'] = $image['id'];
					}
				}

				if ( ! empty( $question['backImage'] ) && $this->download_images ) {
					$image = Helper::download_image_file( $question['backImage'] );
					if ( $image ) {
						$question['backImage']   = $image['url'];
						$question['backImageId'] = $image['id'];
					}
				}

				if ( ! empty( $question['answers'] ) ) {

					$new_answers = array();
					foreach ( $question['answers'] as $aindex => $answer ) {

						if ( ! empty( $answer['image'] ) && $this->download_images ) {
							$image = Helper::download_image_file( $answer['image'] );
							if ( $image ) {
								$answer['image']   = $image['url'];
								$answer['imageId'] = $image['id'];
							}
						}

						if ( ! empty( $question['answers'][0] ) ) {
							$aid             = Helper::generate_random_string();
							$answer['id']    = $aid;
							$answer['index'] = $aindex;

							if ( 'personality' === $data['type'] ) {
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
						} else {
							$aid = $aindex;
						}

						$new_answers[ $aid ] = $answer;
					}
					$question['answers'] = $new_answers;
					unset( $new_answers );
				}

				if ( ! empty( $data['questions'][0] ) ) {
					$qid               = Helper::generate_random_string();
					$question['id']    = $qid;
					$question['index'] = $index;
				} else {
					$qid = $index;
				}

				$new_questions[ $qid ] = $question;
			}

			$data['questions'] = $new_questions;
			unset( $new_questions );
		}

		unset( $result_titles );

		return $data;
	}

	/**
	 * Imports quizzes.
	 *
	 * @param array $data      Import data.
	 * @param bool  $force_new Force creating new quiz. Default is `false`.
	 */
	public function import_quizzes( array $data, $force_new = false ) {
		$download_images = $this->download_images;
		$author          = $this->author;
		foreach ( $data as $quiz ) {
			wp_quiz()->import_process->push_to_queue( compact( 'quiz', 'force_new', 'download_images', 'author' ) );
		}
		wp_quiz()->import_process->set_remain( count( $data ) );
		wp_quiz()->import_process->save()->dispatch();
	}

	/**
	 * Imports quizzes from file.
	 *
	 * @param string $import_file Full path to import file.
	 * @param bool   $force_new   Force creating new quiz. Default is `false`.
	 */
	public function import_quizzes_from_file( $import_file, $force_new = false ) {
		$data = file_get_contents( $import_file );
		$data = json_decode( $data, true );
		if ( ! $data ) {
			return;
		}
		$this->import_quizzes( $data, $force_new );
	}

	/**
	 * Imports settings.
	 *
	 * @param array $data Plugin settings.
	 */
	public function import_settings( array $data ) {
		update_option( 'wp_quiz_pro_default_settings', $data );
	}
}

<?php
/**
 * Quiz type frontend trait
 *
 * @package WPQuiz
 */

namespace WPQuiz\Traits;

use WPQuiz\Assets;
use WPQuiz\ForceActions\Manager;
use WPQuiz\Helper;
use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\Quiz;
use WPQuiz\Template;

/**
 * Trait QuizTypeFrontend
 */
trait QuizTypeFrontend {

	/**
	 * Enqueues frontend scripts.
	 *
	 * @param mixed $quiz Quiz object or null.
	 */
	public function enqueue_frontend_scripts( $quiz = null ) {
		// Make sure to run after wp_enqueue_scripts.
		if ( ! did_action( 'wp_enqueue_scripts' ) ) {
			$this->current_quiz = $quiz;
			add_action( 'wp_head', array( $this, 'enqueue_frontend_scripts' ), 20 );
			return;
		}
		if ( ! $quiz ) {
			$quiz = $this->current_quiz;
		}

		wp_enqueue_script( 'wp-util' );
		wp_enqueue_script( 'jquery.serialize-object' );
//		wp_enqueue_script( 'wp-quiz-babel-helpers' );
		wp_enqueue_script( 'wp-quiz' );

		wp_enqueue_script( 'wp-quiz-' . $this->name );
		wp_localize_script( 'wp-quiz-' . $this->name, $this->name . 'Quiz' . $quiz->get_id(), $this->get_js_data( $quiz ) );
	}

	/**
	 * Gets js data.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_js_data( Quiz $quiz ) {
		$data = array(
			'id'        => $quiz->get_id(),
			'type'      => $quiz->get_quiz_type()->get_name(),
			'name'      => $quiz->get_title(),
			'questions' => $this->get_processed_questions( $quiz ),
			'results'   => $this->get_results( $quiz ),
			'settings'  => $this->get_settings( $quiz ),
			'shareUrl'  => esc_url( get_permalink( $quiz->get_id() ) ),
			'i18n'      => array(
				'noAnswer' => __( 'No answer', 'wp-quiz-pro' ),
			),
		);

		if ( ! empty( $quiz->play_data ) ) {
			$data['playData'] = $quiz->play_data;
		}

		$data['answer_only'] = ! empty( $quiz->displayed_question ); // Handle answers only.

		return $data;
	}

	/**
	 * Loads template.
	 *
	 * @param string $template_file Template file path.
	 * @param array  $data          Data passed to template file.
	 */
	public function load_template( $template_file, array $data = array() ) {
		$path = Template::get_template_path( "quiz-types/{$this->name}/{$template_file}" );
		if ( $path ) {
			Template::load_template( "quiz-types/{$this->name}/{$template_file}", $data );
			return;
		}
		Template::load_template( "global/{$template_file}", $data );
	}

	/**
	 * Adds template hooks.
	 */
	protected function add_template_hooks() {
		add_action( "wp_quiz_before_{$this->name}_quiz", array( $this, 'custom_css' ) );
	}

	/**
	 * Removes template hooks.
	 */
	protected function remove_template_hooks() {
		remove_action( 'wp_quiz_before_quiz', array( $this, 'custom_css' ) );
	}

	/**
	 * Shows frontend template.
	 *
	 * @param Quiz  $quiz Quiz object.
	 * @param array $args {
	 *     Custom arguments.
	 *
	 *     @type int $play_data_id If play id is passed, it will show answered data and results.
	 * }
	 * @return string
	 */
	public function frontend( Quiz $quiz, array $args = array() ) {
		if ( ! empty( $args['play_data_id'] ) ) {
			$play_data       = PlayData::get( $args['play_data_id'] );
			$quiz->play_data = $play_data;
		}

		if ( ! empty( $args['question'] ) ) {
			$quiz->displayed_question = intval( $args['question'] );
		}

		$this->add_template_hooks();
		$this->enqueue_frontend_scripts( $quiz );

		// Enqueue css files in footer if they are not enqueued in head.
		$assets = new Assets();
		$assets->enqueue_styles();

		ob_start();

		echo '<div class="wq-quiz-wrapper" data-id="' . intval( $quiz->get_id() ) . '">';

		/**
		 * Fires before rendering quiz
		 *
		 * @since 2.0.0
		 *
		 * @param Quiz $quiz Quiz object.
		 */
		do_action( "wp_quiz_before_{$this->name}_quiz", $quiz );

		/**
		 * Fires before rendering quiz
		 *
		 * @since 2.0.0
		 *
		 * @param Quiz $quiz Quiz object.
		 */
		do_action( 'wp_quiz_before_quiz', $quiz );

		$this->load_template( 'quiz.php', compact( 'quiz', 'args' ) );

		/**
		 * Fires after rendering quiz
		 *
		 * @since 2.0.0
		 *
		 * @param Quiz $quiz Quiz object.
		 */
		do_action( "wp_quiz_after_{$this->name}_quiz", $quiz );

		/**
		 * Fires after rendering quiz
		 *
		 * @since 2.0.0
		 *
		 * @param Quiz $quiz Quiz object.
		 */
		do_action( 'wp_quiz_after_quiz', $quiz );

		echo '</div><!-- End .wq-quiz-wrapper -->';

		$output = ob_get_clean();

		$output = do_shortcode( $output );

		$this->remove_template_hooks();

		/**
		 * Allows changing quiz output.
		 *
		 * @param string $output   Quiz output.
		 * @param int    $quiz_id  Quiz ID.
		 * @param array  $settings Quiz settings.
		 * @param Quiz   $quiz     Quiz object.
		 */
		$output = apply_filters( 'wp_quiz_output', $output, $quiz->get_id(), $quiz->get_settings(), $quiz );

		if ( ! empty( $args['play_data_id'] ) ) {
			$quiz->play_data = null;
		}

		return $output;
	}

	/**
	 * Prints quiz custom css.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	public function custom_css( Quiz $quiz ) {
		$wrapper_selector = '.wq-quiz-wrapper[data-id="' . $quiz->get_id() . '"]';
		printf( '<style type="text/css" id="wq-%s-custom-css">', esc_attr( $this->name ) );

		echo "{$wrapper_selector} {\n"; // WPCS: xss ok.
		echo $this->get_default_css_variables( $quiz ); // WPCS: xss ok.
		echo "}\n";

		$this->load_template( 'custom-css.php', compact( 'quiz', 'wrapper_selector' ) );

		echo '</style>';
	}

	/**
	 * Gets quiz classes data.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_quiz_classes_data( Quiz $quiz ) {
		$question_layout = $this->get_question_layout( $quiz );
		$skin            = $quiz->get_setting( 'skin' ) ? $quiz->get_setting( 'skin' ) : 'traditional';

		$classes = array(
			'wq_quizCtr',
			$question_layout,
			$this->name . '_quiz',
			'wq-quiz',
			'wq-quiz-' . $quiz->get_id(),
			'wq-quiz-' . $this->name,
			'wq-layout-' . $question_layout,
			'wq-skin-' . $skin,
		);

		if ( $this->get_overall_timer( $quiz ) || $this->get_question_timer( $quiz ) ) {
			$classes[] = 'has-intro';
		}

		$end_answer   = $quiz->get_setting( 'end_answers' );
		$force_action = $quiz->get_setting( 'force_action' );
		if ( '' === $force_action ) {
			$force_action = Helper::get_option( 'force_action' );
		}

		if ( 'on' === $end_answer || $force_action && '0' !== $force_action ) {
			$classes[] = 'wq-end-answers';
		} else {
			$classes[] = 'wq-should-show-correct-answer';
		}

		return $classes;
	}

	/**
	 * Gets quiz classes attr.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function get_quiz_class_attr( Quiz $quiz ) {
		$classes = $this->get_quiz_classes_data( $quiz );

		/**
		 * Allows changing quiz classes.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes Quiz classes.
		 * @param Quiz  $quiz    Quiz object.
		 */
		$classes = apply_filters( "wp_quiz_{$this->name}_quiz_classes", $classes, $quiz );

		/**
		 * Allows changing quiz classes.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes Quiz classes.
		 * @param Quiz  $quiz    Quiz object.
		 */
		$classes = apply_filters( 'wp_quiz_quiz_classes', $classes, $quiz );

		return implode( ' ', $classes );
	}

	/**
	 * Gets questions classes data.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_questions_classes_data( Quiz $quiz ) {
		return array( 'wq-questions', 'wq_questionsCtr' );
	}

	/**
	 * Gets questions classes.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function get_questions_classes( Quiz $quiz ) {
		$classes = $this->get_questions_classes_data( $quiz );

		/**
		 * Allows changing questions classes of a quiz type.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes Questions classes.
		 * @param Quiz  $quiz    Quiz object.
		 */
		$classes = apply_filters( "wp_quiz_{$this->name}_questions_classes", $classes, $quiz );

		/**
		 * Allows changing questions classes.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes Questions classes.
		 * @param Quiz  $quiz    Quiz object.
		 */
		$classes = apply_filters( 'wp_quiz_questions_classes', $classes, $quiz );

		return implode( ' ', $classes );
	}

	/**
	 * Gets question classes data.
	 *
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @return array
	 */
	protected function get_question_classes_data( Quiz $quiz, array $question ) {
		return array( 'wq-question', 'wq_singleQuestionWrapper', 'wq-question-' . $question['id'] );
	}

	/**
	 * Gets question classes.
	 *
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @return string
	 */
	public function get_question_classes( Quiz $quiz, array $question ) {
		$classes = $this->get_question_classes_data( $quiz, $question );

		/**
		 * Allows changing question classes of a quiz type.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes  Question classes.
		 * @param Quiz  $quiz     Quiz object.
		 * @param array $question Question data.
		 */
		$classes = apply_filters( "wp_quiz_{$this->name}_question_classes", $classes, $quiz, $question );

		/**
		 * Allows changing question classes.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes  Question classes.
		 * @param Quiz  $quiz     Quiz object.
		 * @param array $question Question data.
		 */
		$classes = apply_filters( 'wp_quiz_question_classes', $classes, $quiz, $question );

		return implode( ' ', $classes );
	}

	/**
	 * Gets question explanation classes data.
	 *
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @return array
	 */
	protected function get_question_explanation_classes_data( Quiz $quiz, array $question ) {
		return array( 'wq-trivia-question-explanation', 'wq_triviaQuestionExplanation' );
	}

	/**
	 * Gets question explanation classes.
	 *
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @return string
	 */
	public function get_question_explanation_classes( Quiz $quiz, array $question ) {
		$classes = $this->get_question_explanation_classes_data( $quiz, $question );

		/**
		 * Allows changing question explanation classes of a quiz type.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes  Question explanation classes.
		 * @param Quiz  $quiz     Quiz object.
		 * @param array $question Question data.
		 */
		$classes = apply_filters( "wp_quiz_{$this->name}_question_explanation_classes", $classes, $quiz, $question );

		/**
		 * Allows changing question explanation classes.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes  Question explanation classes.
		 * @param Quiz  $quiz     Quiz object.
		 * @param array $question Question data.
		 */
		$classes = apply_filters( 'wp_quiz_question_explanation_classes', $classes, $quiz, $question );

		return implode( ' ', $classes );
	}

	/**
	 * Gets answers classes data.
	 *
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @return array
	 */
	protected function get_answers_classes_data( Quiz $quiz, array $question ) {
		return array( 'wq-answers', 'wq_answersWrapper' );
	}

	/**
	 * Gets answers classes.
	 *
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @return string
	 */
	public function get_answers_classes( Quiz $quiz, array $question ) {
		$classes = $this->get_answers_classes_data( $quiz, $question );

		/**
		 * Allows changing answers classes of a quiz type.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes  Answers classes.
		 * @param Quiz  $quiz     Quiz object.
		 * @param array $question Question data.
		 */
		$classes = apply_filters( "wp_quiz_{$this->name}_answers_classes", $classes, $quiz, $question );

		/**
		 * Allows changing answers classes.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes  Answers classes.
		 * @param Quiz  $quiz     Quiz object.
		 * @param array $question Question data.
		 */
		$classes = apply_filters( 'wp_quiz_answers_classes', $classes, $quiz, $question );

		return implode( ' ', $classes );
	}

	/**
	 * Gets answer classes data.
	 *
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @param array $answer   Answer data.
	 * @return array
	 */
	protected function get_answer_classes_data( Quiz $quiz, array $question, array $answer ) {
		$classes = array( 'wq-answer', 'wq_singleAnswerCtr' );
		if ( ! empty( $answer['image'] ) ) {
			$classes[] = 'wq_hasImage';
		}
		return $classes;
	}

	/**
	 * Gets answer classes.
	 *
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @param array $answer   Answer data.
	 * @return string
	 */
	public function get_answer_classes( Quiz $quiz, array $question, array $answer ) {
		$classes = $this->get_answer_classes_data( $quiz, $question, $answer );

		/**
		 * Allows changing answer classes of a quiz type.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes  Answer classes.
		 * @param Quiz  $quiz     Quiz object.
		 * @param array $question Question data.
		 * @param array $answer   Answer data.
		 */
		$classes = apply_filters( "wp_quiz_{$this->name}_answer_classes", $classes, $quiz, $question, $answer );

		/**
		 * Allows changing answer classes.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes  Answer classes.
		 * @param Quiz  $quiz     Quiz object.
		 * @param array $question Question data.
		 * @param array $answer   Answer data.
		 */
		$classes = apply_filters( 'wp_quiz_answer_classes', $classes, $quiz, $question, $answer );

		return implode( ' ', $classes );
	}

	/**
	 * Gets results classes data.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_results_classes_data( Quiz $quiz ) {
		return array( 'wq-results', 'wq_resultsCtr' );
	}

	/**
	 * Gets results classes.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function get_results_classes( Quiz $quiz ) {
		$classes = $this->get_results_classes_data( $quiz );

		/**
		 * Allows changing results classes of a quiz type.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes Results classes.
		 * @param Quiz  $quiz    Quiz object.
		 */
		$classes = apply_filters( "wp_quiz_{$this->name}_results_classes", $classes, $quiz );

		/**
		 * Allows changing results classes.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes Results classes.
		 * @param Quiz  $quiz    Quiz object.
		 */
		$classes = apply_filters( 'wp_quiz_results_classes', $classes, $quiz );

		return implode( ' ', $classes );
	}

	/**
	 * Gets result classes data.
	 *
	 * @param Quiz  $quiz   Quiz object.
	 * @param array $result Result data. Or question data in some quiz types.
	 * @return array
	 */
	protected function get_result_classes_data( Quiz $quiz, array $result ) {
		return array( 'wq-result', 'wq_singleResultWrapper' );
	}

	/**
	 * Gets result classes.
	 *
	 * @param Quiz  $quiz   Quiz object.
	 * @param array $result Result data. Or question data in some quiz types.
	 * @return string
	 */
	public function get_result_classes( Quiz $quiz, array $result ) {
		$classes = $this->get_result_classes_data( $quiz, $result );

		/**
		 * Allows changing result classes of a quiz type.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes Results classes.
		 * @param Quiz  $quiz    Quiz object.
		 * @param array $result  Result data.
		 */
		$classes = apply_filters( "wp_quiz_{$this->name}_result_classes", $classes, $quiz, $result );

		/**
		 * Allows changing result classes.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes Results classes.
		 * @param Quiz  $quiz    Quiz object.
		 * @param array $result  Result data.
		 */
		$classes = apply_filters( 'wp_quiz_result_classes', $classes, $quiz, $result );

		return implode( ' ', $classes );
	}

	/**
	 * Gets quiz data attributes.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function get_data_attrs( Quiz $quiz ) {
		$data   = array();
		$data[] = 'data-quiz-id="' . $quiz->get_id() . '"';

		$data = implode( ' ', $data );
		$data = apply_filters( 'wp_quiz_data_attrs', $data, $quiz->get_id(), $quiz->get_settings(), $quiz );

		return $data;
	}

	/**
	 * Gets css default variables output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function get_default_css_variables( Quiz $quiz ) {
		$variables = array(
			'--wq-question-width'  => '100%',
			'--wq-question-color'  => '#009cff',
			'--wq-question-height' => 'auto',
		);

		if ( $quiz->get_setting( 'bar_color' ) ) {
			$variables['--wq-bar-color'] = $quiz->get_setting( 'bar_color' );
		}

		if ( $quiz->get_setting( 'font_color' ) ) {
			$variables['--wq-font-color'] = $quiz->get_setting( 'font_color' );
		}

		if ( $quiz->get_setting( 'background_color' ) ) {
			$variables['--wq-background-color'] = $quiz->get_setting( 'background_color' );
		}

		if ( $quiz->get_setting( 'title_color' ) ) {
			$variables['--wq-title-color'] = $quiz->get_setting( 'title_color' );
		}

		if ( $quiz->get_setting( 'title_size' ) ) {
			$variables['--wq-title-size'] = $quiz->get_setting( 'title_size' );
		}

		if ( $quiz->get_setting( 'title_font' ) ) {
			$variables['--wq-title-font'] = $quiz->get_setting( 'title_font' );
		}

		/**
		 * Allows changing quiz style data.
		 *
		 * @since 2.0.0
		 *
		 * @param Quiz $quiz Quiz object.
		 */
		$variables = apply_filters( 'wp_quiz_css_variables', $variables, $quiz );

		$css = '';
		foreach ( $variables as $key => $value ) {
			$css .= sprintf( "%s: %s;\n", $key, $value );
		}
		return $css;
	}

	/**
	 * Gets question layout.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function get_question_layout( Quiz $quiz ) {
		return $quiz->get_setting( 'question_layout' ) ? $quiz->get_setting( 'question_layout' ) : 'single';
	}

	/**
	 * Gets overall timer.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return int
	 */
	public function get_overall_timer( Quiz $quiz ) {
		return 0;
	}

	/**
	 * Gets question timer.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return int
	 */
	public function get_question_timer( Quiz $quiz ) {
		return 0;
	}

	/**
	 * Gets overall timer output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function overall_timer( Quiz $quiz ) {
		ob_start();
		$this->load_template(
			'timer.php',
			array(
				'number'     => $quiz->get_setting( 'overall_time' ),
				'class_name' => 'wq-overall-timer',
			)
		);
		$output = ob_get_clean();

		/**
		 * Allows changing overall timer output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output Overall timer output.
		 * @param Quiz   $quiz   Quiz object.
		 */
		return apply_filters( 'wp_quiz_overall_timer', $output, $quiz );
	}

	/**
	 * Gets question timer output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function question_timer( Quiz $quiz ) {
		ob_start();
		$this->load_template(
			'timer.php',
			array(
				'number'     => $quiz->get_setting( 'countdown_timer' ),
				'class_name' => 'wq-question-timer',
			)
		);
		$output = ob_get_clean();

		/**
		 * Allows changing question timer output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output Question timer output.
		 * @param Quiz   $quiz   Quiz object.
		 */
		return apply_filters( 'wp_quiz_question_timer', $output, $quiz );
	}

	/**
	 * Gets quiz intro output.
	 *
	 * @param Quiz   $quiz    Quiz object.
	 * @param string $message Intro message.
	 * @return string
	 */
	public function quiz_intro( Quiz $quiz, $message = '' ) {
		ob_start();
		$this->load_template( 'intro.php', compact( 'quiz', 'message' ) );
		$output = ob_get_clean();

		/**
		 * Allows changing quiz intro output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output  Quiz intro output.
		 * @param Quiz   $quiz    Quiz object.
		 * @param string $message Quiz intro message.
		 */
		return apply_filters( 'wp_quiz_quiz_intro', $output, $quiz, $message );
	}

	/**
	 * Gets progress bar output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function progress_bar( Quiz $quiz ) {
		if ( ! empty( $quiz->displayed_question ) ) {
			return;
		}

		ob_start();
		$this->load_template( 'progress-bar.php' );
		$output = ob_get_clean();

		/**
		 * Allows changing progress bar output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output Progress bar output.
		 * @param Quiz   $quiz   Quiz object.
		 */
		return apply_filters( 'wp_quiz_progress_bar', $output, $quiz );
	}

	/**
	 * Gets frontend questions output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function quiz_questions( Quiz $quiz ) {
		$questions = $this->get_processed_questions( $quiz );
		if ( ! $questions ) {
			return '<p>' . esc_html__( 'There is no question', 'wp-quiz-pro' ) . '</p>';
		}

		ob_start();
		$this->load_template( 'questions/questions.php', compact( 'quiz', 'questions' ) );
		$output = ob_get_clean();

		/**
		 * Allows changing quiz questions output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output Quiz questions output.
		 * @param Quiz   $quiz   Quiz object.
		 */
		return apply_filters( 'wp_quiz_questions', $output, $quiz );
	}

	/**
	 * Gets question ads output.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 * @return string
	 */
	public function question_ads( array $question, Quiz $quiz ) {
		if ( 'on' !== $quiz->get_setting( 'show_ads' ) ) {
			return '';
		}
		$repeat_ads = 'on' === $quiz->get_setting( 'repeat_ads' );
		$ad_nth     = intval( $quiz->get_setting( 'ad_nth_display' ) );
		$ad_codes   = Helper::get_ad_codes( $quiz->get_id() );
		$number_ads = count( $ad_codes );

		if ( $number_ads < 1 || ! $ad_nth || 0 === $question['index'] || 0 !== $question['index'] % $ad_nth ) {
			return '';
		}

		$ad_index = intval( $question['index'] / $ad_nth ) - 1;
		if ( $repeat_ads && $ad_index >= $number_ads ) {
			$ad_index %= $number_ads;
		}

		if ( empty( $ad_codes[ $ad_index ] ) ) {
			return '';
		}

		$ad_code = $ad_codes[ $ad_index ];
		ob_start();
		$this->load_template(
			'questions/ad.php',
			array(
				'ad_code'  => $ad_code,
				'question' => $question,
				'quiz'     => $quiz,
			)
		);
		$output = ob_get_clean();

		/**
		 * Allows changing question ad output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output   Question ad output.
		 * @param string $ad_code  Ad code.
		 * @param array  $question Question data.
		 * @param Quiz   $quiz     Quiz object.
		 */
		return apply_filters( 'wp_quiz_question_ad', $output, $ad_code, $question, $quiz );
	}

	/**
	 * Gets question media output.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 * @return string
	 */
	public function question_media( array $question, Quiz $quiz ) {
		$type = empty( $question['mediaType'] ) ? 'image' : $question['mediaType'];
		ob_start();
		$this->load_template( "questions/question-{$type}.php", compact( 'question', 'quiz' ) );
		$output = ob_get_clean();

		/**
		 * Allows changing question media output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output   Question media output.
		 * @param array  $question Question data.
		 * @param Quiz   $quiz     Quiz object.
		 */
		return apply_filters( 'wp_quiz_question_media', $output, $question, $quiz );
	}

	/**
	 * Gets next question button output.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 * @return string
	 */
	public function next_question_button( array $question, Quiz $quiz ) {
		ob_start();
		$this->load_template( 'questions/next-button.php', compact( 'question', 'quiz' ) );
		$output = ob_get_clean();

		/**
		 * Allows changing next question button output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output   Next question button output.
		 * @param array  $question Question data.
		 * @param Quiz   $quiz     Quiz object.
		 */
		return apply_filters( 'wp_quiz_next_question_button', $output, $question, $quiz );
	}

	/**
	 * Gets question answers output.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 * @return string
	 */
	public function question_answers( array $question, Quiz $quiz ) {
		if ( empty( $question['answers'] ) || ! is_array( $question['answers'] ) ) {
			return '';
		}

		$answers = $question['answers'];
		if ( 'on' === $quiz->get_setting( 'rand_answers' ) ) {
			$answers = Helper::shuffle_assoc( $answers );
		}

		$type = ! empty( $question['answerType'] ) ? $question['answerType'] : 'text';
		ob_start();
		$this->load_template( "answers/answers-{$type}.php", compact( 'answers', 'question', 'quiz' ) );
		$output = ob_get_clean();

		/**
		 * Allows changing question answers output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output   Question answers output.
		 * @param array  $question Question data.
		 * @param Quiz   $quiz     Quiz object.
		 */
		return apply_filters( 'wp_quiz_question_answers', $output, $question, $quiz );
	}

	/**
	 * Gets quiz results output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function quiz_results( Quiz $quiz ) {
		$results = $quiz->get_results();
		if ( ! $results ) {
			return '';
		}

		ob_start();
		$this->load_template( 'results/results.php', compact( 'quiz', 'results' ) );
		$output = ob_get_clean();

		return apply_filters( 'wp_quiz_results', $output, $quiz );
	}

	/**
	 * Gets force action output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function force_action( Quiz $quiz ) {
		$force_action_id = $quiz->get_setting( 'force_action' ) ? $quiz->get_setting( 'force_action' ) : Helper::get_option( 'force_action' );
		$force_action    = Manager::get( $force_action_id );
		if ( ! $force_action ) {
			return '';
		}

		$output = $force_action->output( $quiz );
		if ( ! $output ) {
			return '';
		}

		$force_action->enqueue();

		$output = sprintf( '<div class="wq-force-action-container wq_quizForceActionCtr">%s</div><!-- End .wq-force-action-container -->', $output );

		/**
		 * Allows changing force action output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output Force action output.
		 * @param Quiz   $quiz   Quiz object.
		 */
		return apply_filters( 'wp_quiz_force_action', $output, $quiz );
	}

	/**
	 * Gets toggle embed output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function embed_toggle( Quiz $quiz ) {
		if ( Helper::is_embed() ) {
			return '';
		}

		ob_start();
		$this->load_template( 'embed-toggle.php', compact( 'quiz' ) );
		$output = ob_get_clean();

		/**
		 * Allows changing quiz embed toggle output.
		 *
		 * @param string $output   Force action HTML output.
		 * @param int    $quiz_id  Quiz ID.
		 * @param array  $settings Quiz settings.
		 * @param Quiz   $quiz     Quiz object.
		 */
		return apply_filters( 'wp_quiz_embed_toggle', $output, $quiz->get_id(), $quiz->get_settings(), $quiz );
	}

	/**
	 * Gets promote link output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function promote_link( Quiz $quiz ) {
		if ( Helper::is_embed() ) {
			return '';
		}

		ob_start();
		$this->load_template( 'promote-link.php', compact( 'quiz' ) );
		$output = ob_get_clean();

		/**
		 * Filters quiz promote link output.
		 *
		 * @param string $output   Promote link HTML output.
		 * @param int    $quiz_id  Quiz ID.
		 * @param array  $settings Quiz settings.
		 * @param Quiz   $quiz     Quiz object.
		 */
		return apply_filters( 'wp_quiz_promote_plugin', $output, $quiz->get_id(), $quiz->get_settings(), $quiz );
	}

	/**
	 * Gets restart quiz button output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function restart_quiz_button( Quiz $quiz ) {
		ob_start();
		$this->load_template( 'restart-quiz-button.php', compact( 'quiz' ) );
		$output = ob_get_clean();

		/**
		 * Allows changing restart quiz button output.
		 *
		 * @param string $output   Restart quiz button output.
		 * @param int    $quiz_id  Quiz ID.
		 * @param array  $settings Quiz settings.
		 * @param Quiz   $quiz     Quiz object.
		 */
		return apply_filters( 'wp_quiz_retake_button', $output, $quiz->get_id(), $quiz->get_settings(), $quiz );
	}

	/**
	 * Gets quiz share output.
	 *
	 * @param Quiz   $quiz Quiz object.
	 * @param string $text Share text.
	 * @return string
	 */
	public function sharing( Quiz $quiz, $text = '' ) {
		ob_start();
		$this->load_template( 'sharing.php', compact( 'quiz', 'text' ) );
		$output = ob_get_clean();

		/**
		 * Allows changing quiz sharing output.
		 *
		 * @param string $output   Quiz sharing output.
		 * @param int    $quiz_id  Quiz ID.
		 * @param array  $settings Quiz settings.
		 * @param Quiz   $quiz     Quiz object.
		 */
		return apply_filters( 'wp_quiz_shares', $output, $quiz->get_id(), $quiz->get_settings() );
	}

	/**
	 * Adds extra data for question.
	 *
	 * @param array $question Question data.
	 */
	public function add_question_extra_data( array &$question ) {}
}

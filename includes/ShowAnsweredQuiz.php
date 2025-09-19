<?php
/**
 * Show answered quiz
 *
 * @package WPQuiz
 */

namespace WPQuiz;

/**
 * Class ShowAnsweredQuiz
 */
class ShowAnsweredQuiz {

	/**
	 * Initializes.
	 */
	public function init() {
		add_filter( 'wp_quiz_quiz_classes', array( $this, 'change_quiz_classes' ), 10, 2 );
		add_filter( 'wp_quiz_trivia_question_classes', array( $this, 'change_trivia_question_classes' ), 10, 3 );
		add_filter( 'wp_quiz_personality_question_classes', array( $this, 'change_personality_question_classes' ), 10, 3 );
		add_filter( 'wp_quiz_trivia_answer_classes', array( $this, 'change_trivia_answer_classes' ), 10, 4 );
		add_filter( 'wp_quiz_personality_answer_classes', array( $this, 'change_personality_answer_classes' ), 10, 4 );
		add_filter( 'wp_quiz_result_classes', array( $this, 'change_result_classes' ), 10, 3 );
		add_filter( 'wp_quiz_trivia_result_score_text', array( $this, 'change_trivia_result_score_text' ), 10, 3 );
	}

	/**
	 * Changes quiz classes.
	 *
	 * @param array $classes Quiz classes.
	 * @param Quiz  $quiz    Quiz object.
	 * @return array
	 */
	public function change_quiz_classes( array $classes, Quiz $quiz ) {
		if ( ! in_array( $quiz->get_quiz_type()->get_name(), array( 'trivia', 'personality' ), true ) ) {
			return $classes;
		}

		if ( ! empty( $quiz->play_data ) ) {
			$classes[] = 'show-played';
		}

		return $classes;
	}

	/**
	 * Changes trivia question classes.
	 *
	 * @param array $classes  Question classes.
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @return array
	 */
	public function change_trivia_question_classes( array $classes, Quiz $quiz, array $question ) {
		if ( empty( $quiz->play_data ) ) {
			return $classes;
		}

		$classes[] = 'force-show';

		if ( empty( $quiz->play_data->answered_data[ $question['id'] ] ) ) {
			return $classes;
		}

		$answered_data = $quiz->play_data->answered_data[ $question['id'] ];
		$is_correct    = false;
		if ( ! empty( $answered_data['answers'] ) ) {
			$is_correct     = true;
			$count_answered = 0;

			foreach ( $question['answers'] as $aid => $answer ) {
				if ( ! empty( $answer['isCorrect'] ) && intval( $answer['isCorrect'] ) && ! in_array( $aid, $answered_data['answers'], true ) ) { // Did not answer a correct answer.
					$is_correct = false;
					break;
				}

				if ( ( empty( $answer['isCorrect'] ) || ! intval( $answer['isCorrect'] ) ) && in_array( $aid, $answered_data['answers'], true ) ) { // Did answer an incorrect answer.
					$is_correct = false;
					break;
				}

				$count_answered++;
			}

			if ( $count_answered < count( $answered_data['answers'] ) ) {
				$is_correct = false;
			}
		}

		$classes[] = 'wq_questionAnswered';
		$classes[] = $is_correct ? 'is-correct' : 'is-incorrect';

		return $classes;
	}

	/**
	 * Changes personality question classes.
	 *
	 * @param array $classes  Question classes.
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @return array
	 */
	public function change_personality_question_classes( array $classes, Quiz $quiz, array $question ) {
		if ( empty( $quiz->play_data ) ) {
			return $classes;
		}

		$classes[] = 'force-show';
		$classes[] = 'wq_questionAnswered';

		return $classes;
	}

	/**
	 * Changes trivia answer classes.
	 *
	 * @param array $classes  Quiz classes.
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @param array $answer   Answer data.
	 * @return array
	 */
	public function change_trivia_answer_classes( array $classes, Quiz $quiz, array $question, array $answer ) {
		if ( empty( $quiz->play_data ) ) {
			return $classes;
		}

		$quiz_data     = $quiz->play_data->quiz_data;
		$answered_data = $quiz->play_data->answered_data;
		$is_chosen     = ! empty( $answered_data[ $question['id'] ]['answers'] ) && in_array( $answer['id'], $answered_data[ $question['id'] ]['answers'], true );
		$is_correct    = ! empty( $quiz_data['questions'][ $question['id'] ]['answers'][ $answer['id'] ]['isCorrect'] ) && intval( $quiz_data['questions'][ $question['id'] ]['answers'][ $answer['id'] ]['isCorrect'] );

		if ( $is_chosen ) {
			$classes[] = 'chosen';

			if ( ! $is_correct ) {
				$classes[] = 'wq_incorrectAnswer';
			}
		}

		if ( $is_correct ) {
			$classes[] = 'wq_correctAnswer';
		}

		return $classes;
	}

	/**
	 * Changes personality answer classes.
	 *
	 * @param array $classes  Quiz classes.
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @param array $answer   Answer data.
	 * @return array
	 */
	public function change_personality_answer_classes( array $classes, Quiz $quiz, array $question, array $answer ) {
		if ( empty( $quiz->play_data ) ) {
			return $classes;
		}

		$answered_data = $quiz->play_data->answered_data;
		$is_chosen     = ! empty( $answered_data[ $question['id'] ]['answers'] ) && in_array( $answer['id'], $answered_data[ $question['id'] ]['answers'], true );

		if ( $is_chosen ) {
			$classes[] = 'chosen';
		}

		return $classes;
	}

	/**
	 * Changes result classes.
	 *
	 * @param array $classes  Result classes.
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $result   Result data.
	 * @return array
	 */
	public function change_result_classes( array $classes, Quiz $quiz, array $result ) {
		if ( ! in_array( $quiz->get_quiz_type()->get_name(), array( 'trivia', 'personality' ), true ) ) {
			return $classes;
		}

		if ( empty( $quiz->play_data ) ) {
			return $classes;
		}
		if ( empty( $quiz->play_data->result ) || $quiz->play_data->result !== $result['id'] && $quiz->play_data->result !== $result['title'] ) {
			return $classes;
		}

		$classes[] = 'chosen';
		return $classes;
	}

	/**
	 * Changes trivia result score text.
	 *
	 * @param string $text   Result score text.
	 * @param Quiz   $quiz   Quiz object.
	 * @param array  $result Result data.
	 * @return string
	 */
	public function change_trivia_result_score_text( $text, Quiz $quiz, array $result ) {
		if ( empty( $quiz->play_data ) ) {
			return $text;
		}

		if ( $text ) {
			return $text; // Do not add filter many times.
		}

		$text = sprintf(
			// translators: %1$s: number of correct answers, %2$s: total questions.
			esc_html__( 'You got %1$s out of %2$s', 'wp-quiz-pro' ),
			$quiz->play_data->correct_answered,
			count( $quiz->get_questions() )
		);

		return $text;
	}
}

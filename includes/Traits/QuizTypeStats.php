<?php
/**
 * Quiz type stats trait
 *
 * @package WPQuiz
 */

namespace WPQuiz\Traits;

use WPQuiz\Quiz;

/**
 * Trait QuizTypeStats
 */
trait QuizTypeStats {

	/**
	 * Gets chart type.
	 *
	 * @return string
	 */
	public function get_chart_type() {
		return 'BarChart';
	}

	/**
	 * Gets chart data.
	 *
	 * @param array $question   Question data.
	 * @param array $stats_data Stats data.
	 * @param Quiz  $quiz       Quiz object.
	 * @return array
	 */
	public function get_chart_data( array $question, array $stats_data, Quiz $quiz ) {
		$chart_data = array(
			array( __( 'Answer', 'wp-quiz-pro' ), __( 'count', 'wp-quiz-pro' ), array( 'role' => 'annotation' ), array( 'role' => 'style' ) ),
		);

		$global_color = '#76c77a';

		foreach ( $question['answers'] as $aid => $answer ) {
			$count        = ! empty( $stats_data[ $question['id'] ]['answers'][ $aid ] ) ? intval( $stats_data[ $question['id'] ]['answers'][ $aid ] ) : 0;
			$color        = ! empty( $answer['isCorrect'] ) && intval( $answer['isCorrect'] ) ? '#8bc34a' : '#ff9c7d';
			$chart_data[] = array( $answer['title'], $count, $count, 'personality' === $quiz->get_quiz_type()->get_name() ? $global_color : $color );
		}

		return $chart_data;
	}

	/**
	 * Gets chart options.
	 *
	 * @return array
	 */
	public function get_chart_options() {
		$options = array(
			'title'  => __( 'How many times answer is chosen?', 'wp-quiz-pro' ),
			'bar'    => array(
				'groupWidth' => '10',
			),
			'legend' => array(
				'position' => 'none',
			),
		);

		return $options;
	}
}

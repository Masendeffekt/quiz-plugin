<?php
/**
 * Template for quiz results
 *
 * @package WPQuiz
 *
 * @param Quiz  $quiz    Quiz object.
 * @param array $results Quiz results.
 */

use WPQuiz\Quiz;

$quiz_type = $quiz->get_quiz_type();

$quiz_type->load_template( 'results/results-open.php', compact( 'quiz' ) );

foreach ( $results as $index => $result ) {
	$result['index'] = $index;
	$quiz_type->load_template(
		'results/result.php',
		array(
			'result' => $result,
			'quiz'   => $quiz,
		)
	);
}

$quiz_type->load_template( 'results/results-close.php', compact( 'quiz' ) );

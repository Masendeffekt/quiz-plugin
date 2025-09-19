<?php
/**
 * Quiz types settings tab
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

use WPQuiz\QuizTypeManager;

$quiz_types = QuizTypeManager::get_all( true );
foreach ( $quiz_types as $name => $quiz_type ) {
	$cmb->add_field(
		array(
			'id'      => 'enable_' . $name,
			'name'    => $quiz_type->get_title(),
			'type'    => 'switch',
			'desc'    => $quiz_type->get_desc(),
			'default' => 'on',
		)
	);
}

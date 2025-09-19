<?php
/**
 * Google analytics tab
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

$default_options = \WPQuiz\Helper::get_default_options();

$cmb->add_field(
	array(
		'id'      => 'ga_tracking_id',
		'type'    => 'text',
		'name'    => __( 'Google Analytics Tracking ID', 'wp-quiz-pro' ),
		'desc'    => __( 'This option enables the quiz view tracking in the GA.', 'wp-quiz-pro' ),
		'default' => $default_options['ga_tracking_id'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'ga_no_print_code',
		'type'    => 'switch',
		'name'    => __( 'Do not print Google Analytics code', 'wp-quiz-pro' ),
		'desc'    => __( 'Turn on this option if Google Analytics code is printed by your own code.', 'wp-quiz-pro' ),
		'default' => $default_options['ga_no_print_code'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'ga_event_tracking',
		'type'    => 'switch',
		'name'    => __( 'Enable Google Analytics event tracking', 'wp-quiz-pro' ),
		'default' => $default_options['ga_event_tracking'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'ga_event_category',
		'type'    => 'text',
		'name'    => __( 'Event category', 'wp-quiz-pro' ),
		'default' => $default_options['ga_event_category'],
		'dep'     => array(
			array( 'ga_event_tracking', 'on' ),
		),
	)
);

$cmb->add_field(
	array(
		'id'      => 'ga_event_action',
		'type'    => 'text',
		'name'    => __( 'Event action', 'wp-quiz-pro' ),
		'default' => $default_options['ga_event_action'],
		'dep'     => array(
			array( 'ga_event_tracking', 'on' ),
		),
	)
);

$cmb->add_field(
	array(
		'id'      => 'ga_event_label',
		'type'    => 'text',
		'name'    => __( 'Event label', 'wp-quiz-pro' ),
		'desc'    => __( 'Use %QUIZ_NAME% for quiz name', 'wp-quiz-pro' ),
		'default' => $default_options['ga_event_label'],
		'dep'     => array(
			array( 'ga_event_tracking', 'on' ),
		),
	)
);

<?php
/**
 * Flip quiz styling options
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

$cmb->add_field(
	array(
		'type'    => 'radio_inline',
		'name'    => __( 'Choose skin', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_skin',
		'options' => array(
			'traditional' => __( 'Traditional skin', 'wp-quiz-pro' ),
			'flat'        => __( 'Modern flat skin', 'wp-quiz-pro' ),
		),
		'default' => 'traditional',
	)
);

$cmb->add_field(
	array(
		'type'    => 'colorpicker',
		'name'    => __( 'Questions font color', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_font_color',
		'default' => '#444',
	)
);

$cmb->add_field(
	array(
		'type'    => 'select',
		'name'    => __( 'Quiz size', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_size',
		'options' => array(
			'full'   => __( 'Full Width (responsive)', 'wp-quiz-pro' ),
			'custom' => __( 'Custom', 'wp-quiz-pro' ),
		),
	)
);

$cmb->add_field(
	array(
		'type' => 'text',
		'name' => __( 'Custom width', 'wp-quiz-pro' ),
		'id'   => 'wp_quiz_custom_width',
		'dep'  => array(
			array( 'wp_quiz_size', 'custom' ),
		),
	)
);

$cmb->add_field(
	array(
		'type' => 'text',
		'name' => __( 'Custom height', 'wp-quiz-pro' ),
		'id'   => 'wp_quiz_custom_height',
		'dep'  => array(
			array( 'wp_quiz_size', 'custom' ),
		),
	)
);

/**
 * Fires after registering flip styling tab.
 *
 * @since 2.0.0
 *
 * @param CMB2 $cmb CMB2 object.
 */
do_action( 'wp_quiz_flip_styling_tab', $cmb );

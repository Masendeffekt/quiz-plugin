<?php
/**
 * Defaults settings tab
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

use WPQuiz\Helper;

$default_options = Helper::get_default_options();

$cmb->add_field(
	array(
		'id'      => 'rand_questions',
		'type'    => 'switch',
		'name'    => __( 'Randomize Questions', 'wp-quiz-pro' ),
		'default' => $default_options['rand_questions'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'rand_answers',
		'type'    => 'switch',
		'name'    => __( 'Randomize Answers', 'wp-quiz-pro' ),
		'default' => $default_options['rand_answers'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'restart_questions',
		'type'    => 'switch',
		'name'    => __( 'Restart Questions', 'wp-quiz-pro' ),
		'default' => $default_options['restart_questions'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'promote_plugin',
		'type'    => 'switch',
		'name'    => __( 'Promote the plugin', 'wp-quiz-pro' ),
		'default' => $default_options['promote_plugin'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'embed_toggle',
		'type'    => 'switch',
		'name'    => __( 'Show embed code toggle', 'wp-quiz-pro' ),
		'default' => $default_options['embed_toggle'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'share_buttons',
		'type'    => 'pw_multiselect',
		'name'    => __( 'Share buttons', 'wp-quiz-pro' ),
		'options' => array(
			'fb' => __( 'Facebook', 'wp-quiz-pro' ),
			'tw' => __( 'Twitter', 'wp-quiz-pro' ),
			'vk' => __( 'VK', 'wp-quiz-pro' ),
		),
		'default' => $default_options['share_buttons'],
	)
);

$cmb->add_field(
	array(
		'id'         => 'countdown_timer',
		'type'       => 'text_small',
		'name'       => __( 'Countdown timer [Seconds/question]', 'wp-quiz-pro' ),
		'desc'       => __( 'Applies to Trivia quiz in multi page layout.', 'wp-quiz-pro' ),
		'attributes' => array(
			'type' => 'number',
			'min'  => 0,
			'step' => 1,
		),
		'default'    => $default_options['countdown_timer'],
	)
);

$cmb->add_field(
	array(
		'id'         => 'refresh_step',
		'type'       => 'text_small',
		'name'       => __( 'Reload page after Xth questions', 'wp-quiz-pro' ),
		'desc'       => __( 'Applies to Trivia, Personality and Swiper quizzes in multi pages layout.', 'wp-quiz-pro' ),
		'attributes' => array(
			'type' => 'number',
			'min'  => 0,
			'step' => 1,
		),
		'default'    => $default_options['refresh_step'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'auto_scroll',
		'type'    => 'switch',
		'name'    => __( 'Auto scroll to next question', 'wp-quiz-pro' ),
		'desc'    => __( 'Applies to Trivia and Personality quiz in single page layout.', 'wp-quiz-pro' ),
		'default' => $default_options['auto_scroll'],
	)
);

\WPQuiz\ForceActions\Manager::register_force_actions_options( $cmb, 'settings' );

$cmb->add_field(
	array(
		'id'      => 'result_method',
		'type'    => 'select',
		'name'    => __( 'Result Delivery Method', 'wp-quiz-pro' ),
		'desc'    => __( 'Applies to Trivia and Personality quiz.', 'wp-quiz-pro' ),
		'options' => array(
			'show'      => __( 'Show results', 'wp-quiz-pro' ),
			'show_send' => __( 'Show results and send email', 'wp-quiz-pro' ),
			'send'      => __( 'Email Results only', 'wp-quiz-pro' ),
		),
		'default' => $default_options['result_method'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'continue_as_btn',
		'type'    => 'switch',
		'name'    => __( 'Use Continue As button', 'wp-quiz-pro' ),
		'desc'    => __( 'Replaces Facebook login button with Continue As button. Applies to FB Quiz only.', 'wp-quiz-pro' ),
		'default' => $default_options['continue_as_btn'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'result_popup',
		'type'    => 'switch',
		'name'    => __( 'Show result in Popup', 'wp-quiz-pro' ),
		'desc'    => __( 'Applies to Trivia and Personality quiz only.', 'wp-quiz-pro' ),
		'default' => $default_options['result_popup'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'subscribe_to_play',
		'type'    => 'switch',
		'name'    => __( 'Subscribe to play', 'wp-quiz-pro' ),
		'desc'    => __( 'Applies to Trivia and Personality quiz only.', 'wp-quiz-pro' ),
		'default' => $default_options['subscribe_to_play'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'show_ads',
		'type'    => 'switch',
		'name'    => __( 'Show Ads', 'wp-quiz-pro' ),
		'default' => $default_options['show_ads'],
	)
);

$cmb->add_field(
	array(
		'id'      => 'ad_title',
		'type'    => 'text',
		'name'    => __( 'Advertisement title', 'wp-quiz-pro' ),
		'default' => $default_options['ad_title'],
		'dep'     => array(
			array( 'show_ads', 'on' ),
		),
	)
);

$cmb->add_field(
	array(
		'id'         => 'ad_nth_display',
		'type'       => 'text',
		'name'       => __( 'Show Ads after every nth question', 'wp-quiz-pro' ),
		'attributes' => array(
			'type' => 'number',
			'min'  => 0,
			'step' => 1,
		),
		'default'    => $default_options['ad_nth_display'],
		'dep'        => array(
			array( 'show_ads', 'on' ),
		),
	)
);

$cmb->add_field(
	array(
		'id'      => 'repeat_ads',
		'type'    => 'switch',
		'name'    => __( 'Repeat Ads', 'wp-quiz-pro' ),
		'default' => $default_options['repeat_ads'],
		'dep'     => array(
			array( 'show_ads', 'on' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'       => 'textarea_code',
		'name'       => __( 'Ad Codes', 'wp-quiz-pro' ),
		'id'         => 'ad_codes',
		'repeatable' => true,
		'default'    => array(),
		'dep'        => array(
			array( 'show_ads', 'on' ),
		),
	)
);

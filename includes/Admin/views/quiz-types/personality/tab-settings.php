<?php
/**
 * Personality quiz settings options
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

use WPQuiz\Helper;
use WPQuiz\QuizTypeManager;

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Randomize questions', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_rand_questions',
		'default' => Helper::get_option( 'rand_questions' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Randomize answers', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_rand_answers',
		'default' => Helper::get_option( 'rand_answers' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Restart questions', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_restart_questions',
		'default' => Helper::get_option( 'restart_questions' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Promote the plugin', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_promote_plugin',
		'default' => Helper::get_option( 'promote_plugin' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Show embed code toggle', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_embed_toggle',
		'default' => Helper::get_option( 'embed_toggle' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'pw_multiselect',
		'name'    => __( 'Share buttons', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_share_buttons',
		'options' => array(
			'fb' => __( 'Facebook', 'wp-quiz-pro' ),
			'tw' => __( 'Twitter', 'wp-quiz-pro' ),
			'vk' => __( 'VK', 'wp-quiz-pro' ),
		),
		'default' => Helper::get_option( 'share_buttons' ),
	)
);

$cmb->add_field(
	array(
		'id'         => 'wp_quiz_refresh_step',
		'type'       => 'text_small',
		'name'       => __( 'Reload page after Xth questions', 'wp-quiz-pro' ),
		'desc'       => __( 'Applies to multiple pages layout.', 'wp-quiz-pro' ),
		'attributes' => array(
			'type' => 'number',
			'min'  => 0,
			'step' => 1,
		),
		'default'    => Helper::get_option( 'refresh_step' ),
		'dep'        => array(
			array( 'wp_quiz_question_layout', 'multiple' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Auto scroll to next question', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_auto_scroll',
		'default' => Helper::get_option( 'auto_scroll' ),
	)
);

\WPQuiz\ForceActions\Manager::register_force_actions_options( $cmb, 'meta_box', 'personality' );

$cmb->add_field(
	array(
		'type'             => 'select',
		'name'             => __( 'Result Delivery Method', 'wp-quiz-pro' ),
		'id'               => 'wp_quiz_result_method',
		'desc'             => __( 'Applies to Trivia or Personality quiz.', 'wp-quiz-pro' ),
		'options'          => array(
			'show'      => __( 'Show results', 'wp-quiz-pro' ),
			'show_send' => __( 'Show results and send email', 'wp-quiz-pro' ),
			'send'      => __( 'Email Results only', 'wp-quiz-pro' ),
		),
		'show_option_none' => __( 'Use default value', 'wp-quiz-pro' ),
		'dep'              => array(
			array( 'wp_quiz_force_action', '1' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'             => 'radio_inline',
		'name'             => __( 'Show result in popup', 'wp-quiz-pro' ),
		'id'               => 'wp_quiz_result_popup',
		'show_option_none' => __( 'Use default value', 'wp-quiz-pro' ),
		'options'          => array(
			'yes' => __( 'Yes', 'wp-quiz-pro' ),
			'no'  => __( 'No', 'wp-quiz-pro' ),
		),
	)
);

if ( in_array( 'personality', QuizTypeManager::get_pay_to_play_quiz_types(), true ) ) {
	$cmb->add_field(
		array(
			'id'      => 'wp_quiz_pay_to_play',
			'type'    => 'switch',
			'name'    => __( 'Pay to play', 'wp-quiz-pro' ),
			'default' => 'off',
		)
	);

	$cmb->add_field(
		array(
			'id'   => 'wp_quiz_pay_to_play_amount',
			'type' => 'text',
			'name' => __( 'Pay amount', 'wp-quiz-pro' ),
			'dep'  => array(
				array( 'wp_quiz_pay_to_play', 'on' ),
			),
		)
	);
}

$cmb->add_field(
	array(
		'type'             => 'radio_inline',
		'name'             => __( 'Subscribe to play', 'wp-quiz-pro' ),
		'id'               => 'wp_quiz_subscribe_to_play',
		'show_option_none' => __( 'Use default value', 'wp-quiz-pro' ),
		'options'          => array(
			'on'  => __( 'Yes', 'wp-quiz-pro' ),
			'off' => __( 'No', 'wp-quiz-pro' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Show ads', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_show_ads',
		'default' => Helper::get_option( 'show_ads' ),
	)
);

$cmb->add_field(
	array(
		'id'      => 'wp_quiz_ad_title',
		'type'    => 'text',
		'name'    => __( 'Advertisement title', 'wp-quiz-pro' ),
		'default' => Helper::get_option( 'ad_title' ),
		'dep'     => array(
			array( 'wp_quiz_show_ads', 'on' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'       => 'text',
		'name'       => __( 'Ads after every nth question', 'wp-quiz-pro' ),
		'id'         => 'wp_quiz_ad_nth_display',
		'attributes' => array(
			'type' => 'number',
			'min'  => 0,
			'step' => 1,
		),
		'dep'        => array(
			array( 'wp_quiz_show_ads', 'on' ),
		),
		'default'    => Helper::get_option( 'ad_nth_display' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Repeat Ads', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_repeat_ads',
		'default' => Helper::get_option( 'repeat_ads' ),
		'dep'     => array(
			array( 'wp_quiz_show_ads', 'on' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'       => 'textarea_code',
		'name'       => __( 'Ad Codes', 'wp-quiz-pro' ),
		'id'         => 'wp_quiz_ad_codes',
		'repeatable' => true,
		'dep'        => array(
			array( 'wp_quiz_show_ads', 'on' ),
		),
	)
);

/**
 * Fires after registering personality settings tab.
 *
 * @since 2.0.0
 *
 * @param CMB2 $cmb CMB2 object.
 */
do_action( 'wp_quiz_personality_settings_tab', $cmb );

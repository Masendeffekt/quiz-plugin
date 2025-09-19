<?php
/**
 * Customc ss for flip quiz
 *
 * @package WPQuiz
 *
 * @var WPQuiz\Quiz $quiz             Quiz object.
 * @var string      $wrapper_selector Wrapper selector.
 */

$custom_width  = $quiz->get_setting( 'custom_width' );
$custom_height = $quiz->get_setting( 'custom_height' );

if ( 'custom' === $quiz->get_setting( 'size' ) ) {

	if ( $custom_width ) {
		echo "
			{$wrapper_selector} {
				--wq-question-width: {$custom_width}px;
			}

			@media screen and (max-width: {$custom_width}px) {
				{$wrapper_selector} .wq_singleQuestionWrapper { width:100% !important; height:auto !important; }
			}
		"; // WPCS: xss ok.
	}

	if ( $custom_height ) {
		$front_height = $custom_height - 52;
		echo "
			{$wrapper_selector} {
				--wq-question-height: {$custom_height}px;
			}

			{$wrapper_selector} img {
				height: 100% !important;
			}

			{$wrapper_selector} .front, {$wrapper_selector} .back { height: {$front_height}px !important; }
		"; // WPCS: xss ok.
	}
}

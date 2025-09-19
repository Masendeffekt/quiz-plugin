<?php
/**
 * Mail template for quiz result mail body
 *
 * @package WPQuiz
 */

/*
 * Replacements.
 *
 * %%site_name%%:        Site name.
 * %%subscriber_name%%:  Subscriber name.
 * %%subscriber_email%%: Subscriber email.
 * %%quiz_url%%:         Quiz URL.
 * %%quiz_name%%:        Quiz name.
 * %%quiz_result%%:      Quiz result.
 */

// translators: subscriber name.
echo '<p>' . sprintf( esc_html__( 'Hi %s.', 'wp-quiz-pro' ), '%%subscriber_name%%' ) . '</p>';

echo '%%quiz_result%%';

echo '<p style="text-align: center;"><a href="%%quiz_url%%" class="play-button">' . esc_html__( 'Quiz link', 'wp-quiz-pro' ) . '</a></p>';

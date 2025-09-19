<?php
/**
 * Mail template for quiz result mail body
 *
 * @package WPQuiz
 */

/*
 * Replacements.
 *
 * %%site_name%%:   Site name.
 * %%quiz_url%%:    Quiz URL.
 * %%quiz_name%%:   Quiz name.
 * %%quiz_result%%: Quiz result.
 */

echo '%%quiz_result%%';

echo '<p style="text-align: center;"><a href="%%quiz_url%%" class="play-button">' . esc_html__( 'Quiz link', 'wp-quiz-pro' ) . '</a></p>';

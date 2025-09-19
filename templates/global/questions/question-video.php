<?php
/**
 * Template for trivia question image
 *
 * @package WPQuiz
 *
 * @var array $question
 * @var Quiz  $quiz
 */

use WPQuiz\Quiz;
use WPQuiz\Helper;

if ( empty( $question['video'] ) ) {
	return;
}

?>
<div class="wq-question-video">
	<?php echo Helper::video_content( $question['video'], $question['imagePlaceholder'], true ); ?>
</div>

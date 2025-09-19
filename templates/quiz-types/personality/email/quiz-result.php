<?php
/**
 * Email template for personality quiz result
 *
 * @package WPQuiz
 * @var Quiz  $quiz
 * @var array $result
 * @var PlayData $play_data
 */

use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\Quiz;
?>
<div class="result">

	<div class="result__quiz-title"><?php echo esc_html( $quiz->get_title() ); ?></div>

	<div class="result__title"><?php echo esc_html( $result['title'] ); ?></div>

	<?php if ( ! empty( $result['desc'] ) ) : ?>
		<div class="result__desc"><?php echo wp_kses_post( $result['desc'] ); ?></div>
	<?php endif; ?>

	<?php if ( ! empty( $result['image'] ) ) : ?>
		<img class="result__image" src="<?php echo esc_url( $result['image'] ); ?>">
	<?php endif; ?>

</div>

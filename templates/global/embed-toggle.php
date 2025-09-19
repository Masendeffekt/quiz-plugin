<?php
/**
 * Quiz embed toggle template
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 */

$site_url   = get_site_url() . '/?wp_quiz_id=' . $quiz->get_id();
$embed_code = '<iframe frameborder="0" width="600" height="800" src="' . esc_url( $site_url ) . '"></iframe>';
?>
<!-- embed code -->
<div class="wq-embed-toggle wq_embedToggleQuizCtr">
	<a class="wq-embed-toggle-btn" href="#"><?php esc_html_e( 'Toggle embed code', 'wp-quiz-pro' ); ?></a>
</div>
<div class="wq-embed-quiz wq_embedToggleQuiz">
	<input class="wq-embed-quiz-input" type="text" readonly value="<?php echo esc_attr( $embed_code ); ?>" onClick="this.select();">
</div>
<!-- // embed code -->

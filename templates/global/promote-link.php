<?php
/**
 * Quiz promote link template
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 */

use WPQuiz\Helper;

$promote_url  = 'https://mythemeshop.com/plugins/wp-quiz-pro/';
$mts_username = Helper::get_option( 'mts_username' );
if ( $mts_username ) {
	$promote_url = add_query_arg( 'ref', $mts_username, $promote_url );
}
?>
<!-- promote link -->
<div class="wq_promoteQuizCtr">
	<a style="font-size: 11px;" href="<?php echo esc_url( $promote_url ); ?>" target="_blank"><?php esc_html_e( 'Powered by WP Quiz Pro', 'wp-quiz-pro' ); ?></a>
</div>
<!-- // promote link -->

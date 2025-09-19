<?php
/**
 * Template for embed quiz
 *
 * @package WPQuiz
 * @var \WPQuiz\Quiz $quiz
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'wq-embed-page' ); ?>>
	<?php echo $quiz->get_frontend_output(); // WPCS: xss ok. ?>

	<p style="text-align: right; font-size: 0.8em;">
		<?php
		printf(
			esc_html__( 'Quiz by %s', 'wp-quiz-pro' ),
			'<a href="' . esc_url( home_url( '/' ) ) . '" rel="dofollow">' . esc_html( get_bloginfo( 'name' ) ) . '</a>'
		);
		?>
	</p>

	<?php wp_footer(); ?>
</body>
</html>

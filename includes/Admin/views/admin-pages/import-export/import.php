<?php
/**
 * Template for import page
 *
 * @package WPQuiz
 * @var \WPQuiz\Admin\AdminPages\ImportExport $page;
 */

$demos           = $page->get_demos();
$import_page_url = $page->get_tab_url( 'import' );
?>
<div class="wq-boxes">
	<?php foreach ( $demos as $key => $demo ) : ?>

		<div class="wq-box wq-box--no-padding postbox">
			<div class="wq-box__body">
				<img src="<?php echo esc_url( $demo['image'] ); ?>" alt="" class="wq-box__image">
			</div>

			<div class="wq-box__footer">
				<form action="<?php echo esc_url( $import_page_url ); ?>" method="post">
					<input type="hidden" name="action" value="import-demo">
					<input type="hidden" name="demo" value="<?php echo esc_attr( $key ); ?>">

					<h2 class="wq-box__title"><?php echo esc_html( $demo['title'] ); ?></h2>
					<button class="wq-box__button button button-primary"><?php esc_html_e( 'Import', 'wp-quiz-pro' ); ?></button>
					<a href="<?php echo esc_url( $demo['link'] ); ?>" class="wq-box__button button" target="_blank"><?php esc_html_e( 'View demo', 'wp-quiz-pro' ); ?></a>
				</form>
			</div>
		</div>

	<?php endforeach; ?>
</div>

<br>
<hr>

<form action="<?php echo esc_url( $import_page_url ); ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="action" value="import-quizzes">
	<?php wp_nonce_field( 'wp_quiz_import_quizzes' ); ?>

	<h2><?php esc_html_e( 'Import quizzes', 'wp-quiz-pro' ); ?></h2>
	<p><input type="file" name="wp_quizzes"></p>
	<p><button class="button button-primary"><?php esc_html_e( 'Import quizzes', 'wp-quiz-pro' ); ?></button></p>
</form>

<br>
<hr>

<form action="<?php echo esc_url( $import_page_url ); ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="action" value="import-settings">
	<?php wp_nonce_field( 'wp_quiz_import_settings' ); ?>

	<h2><?php esc_html_e( 'Import settings', 'wp-quiz-pro' ); ?></h2>
	<p><input type="file" name="wp_settings"></p>
	<p><button class="button button-primary"><?php esc_html_e( 'Import settings', 'wp-quiz-pro' ); ?></button></p>
</form>

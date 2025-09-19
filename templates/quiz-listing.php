<?php
/**
 * Template for [wp_quiz_listing] shortcode
 *
 * @package WPQuiz
 * @var array    $atts
 * @var WP_Query $query
 */

use WPQuiz\QuizTypeManager;

?>
<div class="wp-quiz-listing">

	<?php
	while ( $query->have_posts() ) :
		$query->the_post();
		?>
		<article <?php post_class( 'latestPost excerpt' ); ?>>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="featured-thumbnail">
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="nofollow" class="post-image post-image-left">
						<?php the_post_thumbnail(); ?>
					</a>
				</div>
			<?php endif; ?>

			<header>

				<?php
				$quiz_type = get_post_meta( get_the_id(), 'quiz_type', true );
				$quiz_type = QuizTypeManager::get( $quiz_type );
				?>

				<h2 class="title front-view-title">
					<?php if ( $quiz_type ) : ?>
						<span class="thecategory"><?php echo esc_html( $quiz_type->get_title() ); ?></span>
					<?php endif; ?>
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
				</h2>

			</header>

		</article>
	<?php endwhile; ?>
</div>

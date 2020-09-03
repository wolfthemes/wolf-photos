<?php
/**
 * The Template for displaying the photo tags
 *
 * @author WolfThemes
 * @package WolfPhotos/Templates
 * @since 1.0.4
 */
get_header( 'photos' );
?>
	<div id="primary" class="content-area">
		<main id="content" class="clearfix" role="main">
		<?php if ( have_posts() ) : ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<?php wolf_photos_get_template_part( 'content', 'photo' ); ?>

			<?php endwhile; ?>

		<?php else : ?>

			<p><?php esc_html_e( 'No photo found.', 'wolf-photos' ); ?></p>

		<?php endif; // end have_posts() check ?>
		</div>
	</div>
<?php
get_footer( 'photos' );
?>
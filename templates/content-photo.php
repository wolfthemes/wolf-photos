<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<a href="<?php echo get_permalink( get_the_ID() ); ?>">
		<img src="<?php echo esc_url( wp_get_attachment_thumb_url( get_the_ID() ) ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>">
	</a>
</article>
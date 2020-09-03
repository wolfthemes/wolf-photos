<?php
/**
 * Photos frontend functions
 *
 * General functions available on frontend
 *
 * @author WolfThemes
 * @category Core
 * @package WolfPhotos/Frontend
 * @version 1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Apply watermark on desire image
 *
 * @param string $image
 * @param int $attachment_id
 * @param string $size
 */
function wlfp_apply_watermark_image_src( $image, $attachment_id, $size ) {

	$is_watermarked = get_post_meta( $attachment_id, '_wlfp_image_watermarked', true );

	if ( 'thumbnail' !== $size && $is_watermarked ) {

		$meta = wp_get_attachment_metadata( $attachment_id );

		if (
			isset( $meta['watermark'] ) && isset( $meta['watermark']['sizes'] ) && $image[0]
		) {
			//$size = 'large';

			// Fallback
			if ( ! isset( $meta['watermark']['sizes'][ $size ] ) ) {

				$size = 'large';

			} elseif ( ! isset( $meta['watermark']['sizes'][ 'large' ] ) ) {

				$size = 'medium_large';

			} elseif ( ! isset( $meta['watermark']['sizes'][ 'medium_large' ] ) ) {

				$size = 'medium';
			}

			$upload_dir = dirname( $image[0] );
			$w_image_path = $meta['watermark']['sizes'][ $size ]['file'];
			$w_image_url = $upload_dir . '/' . $w_image_path;
			//debug(  $w_image_url );
			$image[0] = $upload_dir . '/' . $meta['watermark']['sizes'][ $size ]['file'];

		} else {
			$image[0] = '';
		}
	}

	return $image;
}
add_filter( 'wp_get_attachment_image_src', 'wlfp_apply_watermark_image_src', 10, 3 );

/**
 * Rewrite attachment link
 *
 * @param string $link
 * @param int $post_id
 * @return string
 */
function wlfp_photo_attachment_link( $link, $post_id ) {

	$post = get_post( $post_id );

	return home_url( '/photo/' . $post->post_name );

}
add_filter( 'attachment_link', 'wlfp_photo_attachment_link', 20, 2 );

/**
 * Redirect single photo to custom URL if set
 */
function wflp_single_photo_buy_redirect() {

	if ( is_singular( 'attachment' ) ) {
		$post_id = get_the_ID();
		$do_redirection =  get_post_meta( $post_id, '_wlfp_image_buy_url_redirect', true );
		$redirect_url = get_post_meta( $post_id, '_wlfp_image_buy_url', true );

		if ( $do_redirection && $redirect_url ) {
			wp_redirect( $redirect_url, 301 );
			exit;
		}
	}

}
add_action( 'template_redirect', 'wflp_single_photo_buy_redirect' );

/**
 * Show photo in photo tag & photo category query by forcing the post_status to inherit
 */
function wlfp_show_tax_attachments( $query ) {
	if ( ( $query->is_tax( 'photo_tag' ) || $query->is_tax( 'photo_category' ) ) && $query->is_main_query() ) {
		$query->set( 'post_status', 'inherit' );
		$query->set( 'posts_per_page', -1 );
	}
}
add_action( 'pre_get_posts', 'wlfp_show_tax_attachments' );

if ( ! function_exists( 'get_photo_search_form' ) ) {

	/**
	 * Display image search form.
	 *
	 * Will first attempt to locate the image-searchform.php file in either the child or.
	 * the parent, then load it. If it doesn't exist, then the default search form.
	 * will be displayed.
	 *
	 * The default searchform uses html5.
	 *
	 * @subpackage	Forms
	 * @param bool $echo (default: true)
	 * @return string
	 */
	function get_photo_search_form( $echo = true  ) {
		ob_start();

		do_action( 'pre_get_photo_search_form'  );

		?>
		<form role="search" method="get" class="wolf-albums-photo-search" action="<?php echo esc_url( home_url( '/'  ) ); ?>">
			<label class="screen-reader-text" for="wolf-albums-photo-search-field"><?php _e( 'Search for:', 'wolf-photos' ); ?></label>
			<input type="search" id="wolf-albums-photo-search-field" class="search-field" placeholder="<?php echo esc_attr_x( 'Search photos&hellip;', 'placeholder', 'wolf-photos' ); ?>" value="<?php echo get_search_query(); ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label', 'wolf-photos' ); ?>" />
			<input type="submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'wolf-photos' ); ?>" />
			<input type="hidden" name="post_type" value="attachment" />
		</form>
		<?php

		$form = apply_filters( 'get_photo_search_form', ob_get_clean() );

		if ( $echo ) {
			echo $form;
		} else {
			return $form;
		}
	}
}

/**
 * 
 */
function wlfp_get_category_terms() {
	$cat_terms = array();

	if ( taxonomy_exists( 'photo_category' ) ) {
		$taxonomy_cat_terms = get_terms( 'photo_category', array( 'hide_empty' => false, ) );
		foreach ( $taxonomy_cat_terms as $taxonomy_cat_term ) {
			$cat_terms[] = $taxonomy_cat_term->slug;
		}
	}

	return $cat_terms;
}

/**
 * 
 */
function wlfp_get_tag_terms() {
	$tag_terms = array();

	if ( taxonomy_exists( 'photo_tag' ) ) {
		$taxonomy_tag_terms = get_terms( 'photo_tag', array( 'hide_empty' => false, ) );
		foreach ( $taxonomy_tag_terms as $taxonomy_tag_term ) {
			$tag_terms[] = $taxonomy_tag_term->slug;
		}
	}

	return $tag_terms;
}

/**
 * 
 */
function wlfp_default_tax_query() {
	$default_tax_query = array(
  		'relation' => 'OR',
		array(
			'taxonomy' => 'photo_tag',
			'field' => 'slug',
			'terms' => wlfp_get_tag_terms(),
		),

		array(
			'taxonomy' => 'photo_category',
			'field' => 'slug',
			'terms' => wlfp_get_category_terms(),
		),
	);

	return $default_tax_query;
}

function wlfp_search_qery_by_terms_args( $search_terms = array() ) {
	$args = array();

	$args['post_type'] = 'attachment';
	$args['post_status'] = 'inherit';
	$args['meta_key'] = '_wolf_views_count';
	$args['orderby'] = 'meta_value_num';
	$args['order'] = 'DESC';
	$args['tax_query'] = array(
			'relation' => 'OR',
  		array(
			'taxonomy' => 'photo_tag',
			'field' => 'slug',
			'terms' => $search_terms,
		),
		array(
			'taxonomy' => 'photo_category',
			'field' => 'slug',
			'terms' => $search_terms,
		),
	);

	return $args;
}

/**
 * Fix pagination issue
 *
 * @param object $query
 * @return object $query
 */
function wlfp_search_filter( $query ) {
	
	if ( ! is_admin() && $query->is_search && isset( $_GET['post_type'] ) && 'attachment' === $_GET['post_type'] ) {
		
		$search_term = esc_attr( $_GET['s'] );
		$search_terms = explode( ' ', $search_term );

		$query->set( 'post__not_in', array() );
		$query->set( 'post_type', 'attachment' );
		$query->set( 'post_status', 'inherit' );

		$query->set( 'meta_key', '_wolf_views_count' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'DESC' );

		$tax_query_by_terms = new WP_Query( wlfp_search_qery_by_terms_args( $search_terms ) );
		$tax_query = ( 0 === $tax_query_by_terms->post_count ) ? wlfp_default_tax_query() : $tax_query_by_terms;
		
		$query->is_404 = false; // never 404
		$query->set( 'tax_query', $tax_query );

		/* Add search by title if not term found */
		if ( 0 === $tax_query_by_terms->post_count ) {

			//debug( $query->request );

			//$query->is_search = true;
			//$query->query_vars['s'] = false;
			//$query->query['s'] = false;

			//$query->set( 'wlfp_title', $search_term );
			//add_filter( 'posts_where', 'wlfp_posts_where', 10, 2 );

			//$query->is_search = true;
			//$query->query_vars['s'] = true;
			//$query->query['s'] = true;
			
		} else {
			$query->is_search = false;
			$query->query_vars['s'] = false;
			$query->query['s'] = false;
		}
	}

	return $query;
}
add_filter( 'pre_get_posts', 'wlfp_search_filter', 9999 );

function wlfp_posts_where( $where, $wp_query ) {
	
	global $wpdb;
	
	$wlfp_title = esc_attr( strtolower( $wp_query->get( 'wlfp_title' ) ) );

	if ( $wlfp_title ) {

		$field = $wpdb->posts . '.post_title';
		$wlfp_title = esc_sql( $wlfp_title );
		$where .= " AND $field LIKE %$wlfp_title%";

		debug( $where );
	}
	
	return $where;
}

/**
 * Overlwrite title
 */
function wlfp_overwrite_page_title( $title ){

	if ( isset( $_GET['s'] ) && isset( $_GET['post_type'] ) && 'attachment' === $_GET['post_type'] ) {
		$s = esc_attr( $_GET['s'] );
		$title = sprintf( esc_html__( 'Search results for %s', 'wolf-photos' ), '&quot;' . esc_html( $s ) . '&quot;' );
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'wlfp_overwrite_page_title', 99999 );
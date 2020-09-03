<?php
/**
 * Photos register taxonomy
 *
 * @author WolfThemes
 * @category Core
 * @package WolfPhotos/Admin
 * @version 1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Photos Category */
$labels = array(
	'name' => esc_html__( 'Photos Categories', 'wolf-photos' ),
	'singular_name' => esc_html__( 'Photos Category', 'wolf-photos' ),
	'search_items' => esc_html__( 'Search Photos Categories', 'wolf-photos' ),
	'popular_items' => esc_html__( 'Popular Photos Categories', 'wolf-photos' ),
	'all_items' => esc_html__( 'All Photos Categories', 'wolf-photos' ),
	'parent_item' => esc_html__( 'Parent Photos Category', 'wolf-photos' ),
	'parent_item_colon' => esc_html__( 'Parent Photos Category:', 'wolf-photos' ),
	'edit_item' => esc_html__( 'Edit Photos Category', 'wolf-photos' ),
	'update_item' => esc_html__( 'Update Photos Category', 'wolf-photos' ),
	'add_new_item' => esc_html__( 'Add New Photos Category', 'wolf-photos' ),
	'new_item_name' => esc_html__( 'New Photos Category', 'wolf-photos' ),
	'separate_items_with_commas' => esc_html__( 'Separate photo categories with commas', 'wolf-photos' ),
	'add_or_remove_items' => esc_html__( 'Add or remove photo categories', 'wolf-photos' ),
	'choose_from_most_used' => esc_html__( 'Choose from the most used photo categories', 'wolf-photos' ),
	'menu_name' => esc_html__( 'Categories', 'wolf-photos' ),
);

$args = array(
	'update_count_callback' => '_update_count_callback_photo_cat',
	'labels' => $labels,
	'hierarchical' => true,
	'public' => true,
	'show_ui' => true,
	'query_var' => true,
	'rewrite' => array( 'slug' => 'photo-category', 'with_front' => false ),
);

register_taxonomy( 'photo_category', array( 'attachment' ), $args );


/* Photos Tags */
$labels = array(
	'name' => esc_html__( 'Photos Tags', 'wolf-photos' ),
	'singular_name' => esc_html__( 'Photos Tag', 'wolf-photos' ),
	'search_items' => esc_html__( 'Search Photos Tags', 'wolf-photos' ),
	'popular_items' => esc_html__( 'Popular Photos Tags', 'wolf-photos' ),
	'all_items' => esc_html__( 'All Photos Tags', 'wolf-photos' ),
	'parent_item' => esc_html__( 'Parent Photos Tag', 'wolf-photos' ),
	'parent_item_colon' => esc_html__( 'Parent Photos Tag:', 'wolf-photos' ),
	'edit_item' => esc_html__( 'Edit Photos Tag', 'wolf-photos' ),
	'update_item' => esc_html__( 'Update Photos Tag', 'wolf-photos' ),
	'add_new_item' => esc_html__( 'Add New Photos Tag', 'wolf-photos' ),
	'new_item_name' => esc_html__( 'New Photos Tag', 'wolf-photos' ),
	'separate_items_with_commas' => esc_html__( 'Separate photo tags with commas', 'wolf-photos' ),
	'add_or_remove_items' => esc_html__( 'Add or remove photo tags', 'wolf-photos' ),
	'choose_from_most_used' => esc_html__( 'Choose from the most used photo tags', 'wolf-photos' ),
	'menu_name' => esc_html__( 'Tags', 'wolf-photos' ),
);

$args = array(
	'hierarchical' => false,
	'labels' => $labels,
	'show_ui' => true,
	'update_count_callback' => '_update_count_callback_photo_tag',
	'query_var' => true,
	'rewrite' => array( 'slug' => 'photo-tag', 'with_front' => false ),
);

register_taxonomy( 'photo_tag', array( 'attachment' ), $args );

function _update_count_callback_photo_cat( $terms, $taxonomy ) {
	global $wpdb;
	foreach ( (array) $terms as $term) {
		do_action( 'edit_term_taxonomy', $term, $taxonomy );

		$args = array(
			'post_type' => 'attachment', //post type, I used 'product'
			'post_status' => 'inherit', // just tried to find all published post
			'posts_per_page' => -1,  //show all
			'tax_query' => array(
			'relation' => 'AND',
				array(
					'taxonomy' => 'photo_category',  //taxonomy name  here, I used 'product_cat'
					'field' => 'id',
					'terms' => array( $term )
				)
			)
		);

		$query = new WP_Query( $args);

		/*
		echo '<pre>';

		print_r($query->post_count);
		echo '</pre>';
		*/

		$count = (int)$query->post_count;

		$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $count ), array( 'term_taxonomy_id' => $term ) );
		do_action( 'edited_term_taxonomy', $term, $taxonomy );
	}
}

function _update_count_callback_photo_tag( $terms, $taxonomy ) {
	global $wpdb;

	foreach ( (array) $terms as $term) {
		do_action( 'edit_term_taxonomy', $term, $taxonomy );

		$args = array(
			'post_type' => 'attachment', //post type, I used 'product'
			'post_status' => 'inherit', // just tried to find all published post
			'posts_per_page' => -1,  //show all
			'tax_query' => array(
			'relation' => 'AND',
				array(
					'taxonomy' => 'photo_tag',  //taxonomy name  here, I used 'product_cat'
					'field' => 'id',
					'terms' => array( $term )
				)
			)
		);

		$query = new WP_Query( $args);

		/*
		echo '<pre>';

		print_r($query->post_count);
		echo '</pre>';
		*/

		$count = (int)$query->post_count;

		$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $count ), array( 'term_taxonomy_id' => $term ) );
		do_action( 'edited_term_taxonomy', $term, $taxonomy );
	}
}
<?php
/**
 * Photos core functions
 *
 * General core functions available on admin and frontend
 *
 * @author WolfThemes
 * @category Core
 * @package WolfPhotos/Core
 * @version 1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add image sizes
 *
 * These size will be ued for galleries and sliders
 *
 * @since 1.0.0
 */
function wlfp_add_image_sizes() {

	add_image_size( 'wlfp-photo', 640, 640, false );
}
add_action( 'init', 'wlfp_add_image_sizes' );

/**
 * Get options
 *
 * @param string $value
 * @param string $default
 * @return string
 */
function wolf_photos_get_option( $value, $default = null ) {

	$wolf_photos_settings = get_option( 'wolf_photos_settings' );

	if ( isset( $wolf_photos_settings[ $value ] ) && '' != $wolf_photos_settings[ $value ] ) {

		return $wolf_photos_settings[ $value ];

	} elseif ( $default ) {

		return $default;
	}
}

/**
 * Get the URL of an attachment from its id
 *
 * @param int $id
 * @param string $size
 * @return string $url
 */
function wlfp_get_url_from_attachment_id( $id, $size = 'thumbnail' ) {
	if ( is_numeric( $id ) ) {
		$src = wp_get_attachment_image_src( absint( $id ), $size );

		if ( isset( $src[0] ) ) {

			return esc_url( $src[0] );
		}
	}
}

/**
 * Get template part (for templates like the album-loop).
 *
 * @param mixed $slug
 * @param string $name (default: '')
 * @return void
 */
function wolf_photos_get_template_part( $slug, $name = '' ) {

	$template = '';

	$wolf_photos = WLFP();

	// Look in yourtheme/slug-name.php and yourtheme/wolf-photos/slug-name.php
	if ( $name )
		$template = locate_template( array( "{$slug}-{$name}.php", "{$wolf_photos->template_url}{$slug}-{$name}.php" ) );

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( $wolf_photos->plugin_path() . "/templates/{$slug}-{$name}.php" ) )
		$template = $wolf_photos->plugin_path() . "/templates/{$slug}-{$name}.php";

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/wolf-photos/slug.php
	if ( ! $template )
		$template = locate_template( array( "{$slug}.php", "{$wolf_photos->template_url}{$slug}.php" ) );

	if ( $template )
		load_template( $template, false );
}

/**
 * Get other templates (e.g. ticket attributes) passing attributes and including the file.
 *
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 */
function wolf_photos_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( $args && is_array($args) )
		extract( $args );

	$located = wolf_photos_locate_template( $template_name, $template_path, $default_path );

	do_action( 'wolf_photos_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'wolf_photos_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param mixed $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function wolf_photos_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	if ( ! $template_path ) $template_path = WLFP()->template_url;
	if ( ! $default_path ) $default_path = WLFP()->plugin_path() . '/templates/';

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters( 'wolf_photos_locate_template', $template, $template_name, $template_path );
}
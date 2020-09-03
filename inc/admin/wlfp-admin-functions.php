<?php
/**
 * Photos admin functions
 *
 * Functions available on admin
 *
 * @author WolfThemes
 * @category Core
 * @package WolfPhotos/Core
 * @version 1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wlpf_test_generate_watermarked_image( $attachment_id ) {

	$metadata = wp_get_attachment_metadata( $attachment_id );
	$stamp = wlfp_get_url_from_attachment_id( wolf_photos_get_option( 'watermark_image_id', WLFP_URI . '/assets/img/watermark.png' ), 'full' );
	$stamp = ( $stamp ) ? $stamp : WLFP_URI . '/assets/img/watermark.png';
	$repeat = wolf_photos_get_option( 'watermark_image_repeat' );
	$position = wolf_photos_get_option( 'watermark_image_position', 'center center' );
	$margin = wolf_photos_get_option( 'watermark_image_margin', 10 );

	$file = $metadata['file'];

	$uploads_dir = wp_upload_dir();
	$dest_folder = $uploads_dir['baseurl'];
	$dest_path = $uploads_dir['basedir'];
	$original_filename = basename( $dest_folder . '/' . $file );

	if ( isset( $metadata['sizes'] ) && isset( $metadata['file'] ) ) {

		$available_sizes = apply_filters(
			'wolf_photos_available_watermark_sizes',
			array( 'wlfp-photo', 'large', 'medium_large', 'medium', 'catalog', 'single' )
		);

		// delete previous watermarked image if any
		wlpf_delete_watermarked_image( $attachment_id );

		$new_name = wp_unique_filename( $dest_path, rand( 0, 999999 ) );

		foreach ( $metadata['sizes'] as $size => $prop ) {

			if ( in_array( $size, $available_sizes ) ) {

				$dir_name = dirname( $metadata['file'] );
				$original_file = $dest_path . '/' . $dir_name . '/' . $metadata['sizes'][ $size ]['file'];

				$new_file = str_replace( $original_filename, $new_name, $file ) . '_' . $size . '.jpg';

				$dest = $dest_path . '/' . $dir_name . '/' . $new_name . '_' . $size . '.jpg';

				//debug( $dest );
				if ( wlfp_watermark_image( $original_file, $dest, $stamp, $repeat, $position, $margin ) ) {
					$metadata['watermark']['sizes'][ $size ]['file'] = $new_name . '_' . $size . '.jpg';
					$metadata['watermark']['sizes'][ $size ]['filepath'] = $new_file;
					$metadata['watermark']['rootname'] = $new_name;
				}
			}

		} // endforeach
	}
	//debug( $metadata['sizes'] );
	return $metadata;
}

//wlpf_test_generate_watermarked_image( 163 );


/**
 * Generate Watermarked image
 *
 * @param array $metadata
 * @param int $attachment_id
 */
function wlpf_generate_watermarked_image( $metadata, $attachment_id ) {

	$stamp = wlfp_get_url_from_attachment_id( wolf_photos_get_option( 'watermark_image_id', WLFP_URI . '/assets/img/watermark.png' ), 'full' );
	$stamp = ( $stamp ) ? $stamp : WLFP_URI . '/assets/img/watermark.png';
	$repeat = wolf_photos_get_option( 'watermark_image_repeat' );
	$position = wolf_photos_get_option( 'watermark_image_position', 'center center' );
	$margin = wolf_photos_get_option( 'watermark_image_margin', 10 );

	$file = $metadata['file'];

	$uploads_dir = wp_upload_dir();
	$dest_folder = $uploads_dir['baseurl'];
	$dest_path = $uploads_dir['basedir'];
	$original_filename = basename( $dest_folder . '/' . $file );

	if ( isset( $metadata['sizes'] ) && isset( $metadata['file'] ) ) {

		$available_sizes = apply_filters(
			'wolf_photos_available_watermark_sizes',
			array( 'large', 'medium_large', 'medium' )
		);

		// delete previous watermarked image if any
		wlpf_delete_watermarked_image( $attachment_id );

		$new_name = wp_unique_filename( $dest_path, rand( 0, 999999 ) );

		foreach ( $metadata['sizes'] as $size => $prop ) {

			if ( in_array( $size, $available_sizes ) ) {

				$dir_name = dirname( $metadata['file'] );
				$original_file = $dest_path . '/' . $dir_name . '/' . $metadata['sizes'][ $size ]['file'];

				$new_file = str_replace( $original_filename, $new_name, $file ) . '_' . $size . '.jpg';

				$dest = $dest_path . '/' . $dir_name . '/' . $new_name . '_' . $size . '.jpg';

				//debug( $dest );
				if ( wlfp_watermark_image( $original_file, $dest, $stamp, $repeat, $position, $margin ) ) {
					$metadata['watermark']['sizes'][ $size ]['file'] = $new_name . '_' . $size . '.jpg';
					$metadata['watermark']['sizes'][ $size ]['filepath'] = $new_file;
					$metadata['watermark']['rootname'] = $new_name;
				}
			}

		} // endforeach
	}

	return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'wlpf_generate_watermarked_image', 10, 2 );

/**
 * Delete Watermarked images of anattachment
 *
 * @param array $metadata
 * @param int $attachment_id
 */
function wlpf_delete_watermarked_image( $attachment_id ) {

	$metadata = wp_get_attachment_metadata( $attachment_id );

	if ( isset( $metadata['watermark'] ) && isset( $metadata['watermark']['sizes'] ) ) {

		$uploads_dir = wp_upload_dir();
		$dest_path = $uploads_dir['basedir'];
		$dir_name = dirname( $metadata['file'] );

		foreach ( $metadata['watermark']['sizes'] as $size ) {

			$file = $dest_path . '/' . $dir_name . '/' . $size['file'];
			if ( is_file( $file ) ) {
				@unlink( $file );
			}
		}
	}
}

/**
 * Remove Watermarked images when an attachment is deleted by user
 *
 * @param int âttachment_id
 */
function wlpf_remove_attachment_watermarked_image( $attachment_id ) {
	wlpf_delete_watermarked_image( $attachment_id );
}
add_action( 'delete_attachment', 'wlpf_remove_attachment_watermarked_image' );

/**
 * Generate a watermarked image
 *
 * @param string $filename
 * @param string $dest destination filename
 * @param bool $repeat
 * @param string $position
 * @param int margin
 */
function wlfp_watermark_image( $filename, $dest, $stamp_filename, $repeat = true, $position = 'center center', $margin = 15 ) {

	$name = pathinfo( $filename, PATHINFO_FILENAME );
	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

	// Watermark
	$watermark = imagecreatefrompng( $stamp_filename );
	$watermark_width = imagesx( $watermark );
	$watermark_height = imagesy( $watermark );
	$image = imagecreatetruecolor( $watermark_width, $watermark_height );

	if ( 'jpg' == $ext || 'jpeg' == $ext ) {

		$image = imagecreatefromjpeg( $filename);

	} elseif ( 'png' == $ext ) {

		$image = imagecreatefrompng( $filename);

	} elseif ( 'gif' == $ext ) {

		$image = imagecreatefromgif( $filename);
	}

	// Original image size
	$image_w = imagesx( $image );
	$image_h = imagesy( $image );

	if ( $repeat ) {
		$img_paste_x = 0;

		while( $img_paste_x < $image_w ) {

			$img_paste_y = 0;

			while( $img_paste_y < $image_h ) {
				imagecopy( $image, $watermark, $img_paste_x, $img_paste_y, 0, 0, $watermark_width, $watermark_height );
				$img_paste_y += $watermark_height;
			}

			$img_paste_x += $watermark_width;
		}

	} else {

		$margin = 10;

		if ( 'center center' == $position ) {

			$dest_x = $image_w / 2 - $watermark_width / 2;
			$dest_y = $image_h / 2 - $watermark_height / 2;

		} elseif ( 'right top' == $position ) {

			$dest_x = 0 + $margin;
			$dest_y = 0 + $margin;

		} elseif ( 'right bottom' == $position ) {

			$dest_x = 0 + $margin;
			$dest_y = $image_h - $watermark_height - $margin;

		} elseif ( 'left top' == $position ) {

			$dest_x = $image_w - $watermark_height - $margin;
			$dest_y = 0 + $margin;

		} elseif ( 'left bottom' == $position ) {

			$dest_x = $image_w - $watermark_height - $margin;
			$dest_y = $image_h - $watermark_height - $margin;
		}

		imagecopy( $image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height );
	}

	// Result
	imagejpeg( $image, $dest, 80 );

	// Clear cache
	imagedestroy( $image );
	imagedestroy( $watermark );

	return true;
}

/**
 * Add custom meta to attachment
 *
 * @param array $form_fields
 * @param object $post
 * @return array $form_fields
 */
function wlfp_image_attachment_fields_to_edit( $form_fields, $post ) {

	$post_id = $post->ID;

	$form_fields['wlfp-image-source'] = array(
		'label' => esc_html__( 'Source', 'wolf-photos' ),
		'input' => 'text', // this is default if 'input' is omitted
		'value' => get_post_meta( $post_id, '_wlfp_image_source', true),
		'helps' => esc_html__( 'A credit for the image.', 'wolf-photos' ),
	);

	$form_fields['wlfp-image-source-url'] = array(
		'label' => esc_html__( 'Source URL', 'wolf-photos' ),
		'input' => 'text',
		'value' => get_post_meta( $post_id, '_wlfp_image_source_url', true),
		'helps' => esc_html__( 'A credit URL for the image.', 'wolf-photos' ),
	);

	$meta = get_post_meta( $post_id, '_wlfp_image_watermarked', true );

	$form_fields['wlfp-image-watermarked'] = array(
		'label' => esc_html__( 'Add Watermark', 'wolf-photos' ),
		'helps' => sprintf( wp_kses(
					__( 'You can upload your own watermark image in the <a href="%s">watermark options panel</a>.', 'wolf-photos' ),
					array( 'a' => array( 'href' => array() ) )
				),
			esc_url( admin_url( 'options-general.php?page=wolf-photos-settings' ) )
		),
		'input' => 'html',
		'html' => '<label for="attachments-' . $post_id.'-wlfp-image-watermarked"> '.
		'<input type="checkbox" id="attachments-' . $post_id . '-wlfp-image-watermarked" name="attachments[' . $post_id . '][wlfp-image-watermarked]" value="1"' . ( $meta ? ' checked="checked"' : '') . '></label>  ',
		'value' => $meta,
		//'helps' => 'Check for yes'
	);

	$metadata = wp_get_attachment_metadata( $post_id );

	$force_regenerate_thumbnails_url = ( class_exists( 'ForceRegenerateThumbnails' ) ) ? admin_url( 'tools.php?page=force-regenerate-thumbnails' ) : 'https://fr.wordpress.org/plugins/force-regenerate-thumbnails/';
	$target = ( class_exists( 'ForceRegenerateThumbnails' ) ) ? '': '_blank';
	$no_watermark_message = sprintf(
		wp_kses_post(
			__( 'Ther is no watermarked version of this image available yet. Use <a href="%s" target="%s">Force Generate Thumbnails</a> plugin to re-generate your thumbnails to create watermarked version of this image.', 'wolf-photos' ) ),
			esc_url( $force_regenerate_thumbnails_url ),
			esc_attr( $target )
		);

	if ( ! isset( $metadata['watermark'] ) ) {
		$form_fields['wlfp-image-watermarked']['helps'] .= '<br><strong>' . $no_watermark_message . '</strong>';
	}

	$form_fields['wlfp-image-buy-url'] = array(
		'label' => esc_html__( 'Purchase URL', 'wolf-photos' ),
		'input' => 'text',
		'value' => get_post_meta( $post_id, '_wlfp_image_buy_url', true),
		'helps' => esc_html__( 'Any URL where the Full Resolution Photo can be Purchased.', 'wolf-photos' ),
	);

	$meta = get_post_meta( $post_id, '_wlfp_image_buy_url_redirect', true );
	$form_fields['wlfp-image-buy-url-redirect'] = array(
		'label' => esc_html__( 'Redirect', 'wolf-photos' ),
		'helps' => esc_html__( 'Redirect Single Attachment Page to Buy URL', 'wolf-photos' ),
		'input' => 'html',
		'html' => '<label for="attachments-' . $post_id.'-wlfp-image-buy-url-redirect"> '.
		'<input type="checkbox" id="attachments-' . $post_id . '-wlfp-image-buy-url-redirect" name="attachments[' . $post_id . '][wlfp-image-buy-url-redirect]" value="1"' . ( $meta ? ' checked="checked"' : '') . '></label>  ',
		'value' => $meta,
		//'helps' => 'Check for yes'
	);

	$meta = get_post_meta( $post_id, '_wlfp_image_free_download', true );
	$form_fields['wlfp-image-free-download'] = array(
		'label' => esc_html__( 'Free Download', 'wolf-photos' ),
		'helps' => esc_html__( 'Allow Full Size Image Free Download (will overwrite purchase URL)', 'wolf-photos' ),
		'input' => 'html',
		'html' => '<label for="attachments-' . $post_id.'-wlfp-image-free-download"> '.
		'<input type="checkbox" id="attachments-' . $post_id . '-wlfp-image-free-download" name="attachments[' . $post_id . '][wlfp-image-free-download]" value="1"' . ( $meta ? ' checked="checked"' : '') . '></label>  ',
		'value' => $meta,
		//'helps' => 'Check for yes'
	);

	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'wlfp_image_attachment_fields_to_edit', null, 2 );

/**
 * Save attachment custom meta
 *
 * @param object $post
 * @param array $attachment
 * @return object $post
 */
function wlfp_image_attachment_fields_to_save( $post, $attachment ) {

	if ( isset( $attachment['wlfp-image-source'] ) ) {
		update_post_meta( $post['ID'], '_wlfp_image_source', esc_attr( $attachment['wlfp-image-source'] ) );
	}

	if ( isset( $attachment['wlfp-image-source-url'] ) ) {
		update_post_meta( $post['ID'], '_wlfp_image_source_url', esc_attr( $attachment['wlfp-image-source-url'] ) );
	}

	if ( isset( $attachment['wlfp-image-buy-url'] ) ) {
		update_post_meta( $post['ID'], '_wlfp_image_buy_url', esc_attr( $attachment['wlfp-image-buy-url'] ) );
	}

	if ( isset( $attachment['wlfp-image-watermarked'] ) ) {
		update_post_meta( $post['ID'], '_wlfp_image_watermarked', true );
	} else {
		delete_post_meta( $post['ID'], '_wlfp_image_watermarked', true );
	}

	if ( isset( $attachment['wlfp-image-buy-url-redirect'] ) ) {
		update_post_meta( $post['ID'], '_wlfp_image_buy_url_redirect', true );
	} else {
		delete_post_meta( $post['ID'], '_wlfp_image_buy_url_redirect', true );
	}

	if ( isset( $attachment['wlfp-image-free-download'] ) ) {
		update_post_meta( $post['ID'], '_wlfp_image_free_download', true );
	} else {
		delete_post_meta( $post['ID'], '_wlfp_image_free_download', true );
	}

	return $post;
}
add_filter( 'attachment_fields_to_save', 'wlfp_image_attachment_fields_to_save', null , 2 );


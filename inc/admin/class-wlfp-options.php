<?php
/**
 * Photos Options.
 *
 * @class WLFP_Options
 * @author WolfThemes
 * @category Admin
 * @package WolfPhotos/Admin
 * @version 1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WLFP_Options class.
 */
class WLFP_Options {
	/**
	 * Constructor
	 */
	public function __construct() {

		// Admin init hooks
		$this->admin_init_hooks();
	}

	/**
	 * Admin init
	 */
	public function admin_init_hooks() {

		// Set default options
		add_action( 'admin_init', array( $this, 'default_options' ) );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Add options menu
		add_action( 'admin_menu', array( $this, 'add_options_menu' ) );

		// Add options menu
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqeueue admin scripts
	 */
	public function admin_scripts() {

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
		wp_enqueue_script( 'wlfp-admin', WLFP_JS . '/admin/admin' . $suffix . '.js', array( 'jquery' ), WLFP_VERSION, true );

		// Global JS variables
		wp_localize_script( 'wlfp-admin', 'WLFPAdminParams', array(
				'chooseImage' => esc_html__( 'Choose an image', 'wolf-photos' ),
			)
		);
	}

	/**
	 * Add options menu
	 */
	public function add_options_menu() {

		add_options_page( esc_html__( 'Watermark', 'wolf-photos' ), esc_html__( 'Watermark', 'wolf-photos' ), 'edit_plugins', 'wolf-photos-settings', array( $this, 'options_form' ) );
	}

	/**
	 * Register options
	 */
	public function register_settings() {
		register_setting( 'wolf-photos-settings', 'wolf_photos_settings', array( $this, 'settings_validate' ) );
		add_settings_section( 'wolf-photos-settings', '', array( $this, 'section_intro' ), 'wolf-photos-settings' );
		add_settings_field( 'watermark_image_id', esc_html__( 'Watermark Image', 'wolf-photos' ), array( $this, 'setting_watermark_image_id' ), 'wolf-photos-settings', 'wolf-photos-settings' );
		add_settings_field( 'watermark_image_repeat', esc_html__( 'Repeat Image', 'wolf-photos' ), array( $this, 'setting_watermark_image_repeat' ), 'wolf-photos-settings', 'wolf-photos-settings' );
		add_settings_field( 'watermark_image_position', esc_html__( 'Image Position', 'wolf-photos' ), array( $this, 'setting_watermark_image_position' ), 'wolf-photos-settings', 'wolf-photos-settings' );
		add_settings_field( 'instructions', esc_html__( 'Instructions', 'wolf-photos' ), array( $this, 'setting_instructions' ), 'wolf-photos-settings', 'wolf-photos-settings' );
	}

	/**
	 * Validate options
	 *
	 * @param array $input
	 * @return array $input
	 */
	public function settings_validate( $input ) {

		$input['watermark_image_id'] = absint( $input['watermark_image_id'] );
		$input['watermark_image_repeat'] = esc_attr( $input['watermark_image_repeat'] );
		$input['watermark_image_position'] = esc_attr( $input['watermark_image_position'] );

		return $input;
	}

	/**
	 * Debug section
	 */
	public function section_intro() {
		// debug
		//global $options;
		// var_dump(get_option( '_wolf_events_watermark_image_id' ));
	}

	/**
	 * Page settings
	 *
	 * @return string
	 */
	public function setting_watermark_image_id() {
		// wolf_photos_settings[watermark_image_id]

		/**
		 * Image
		 */
		wp_enqueue_media();
		$image_id = absint( wolf_photos_get_option( 'watermark_image_id' ) );
		$image_url = wlfp_get_url_from_attachment_id( $image_id, 'large' );
		?>
		<input type="hidden" name="wolf_photos_settings[watermark_image_id]" value="<?php echo esc_attr( $image_id); ?>">
		<p>
			<span style="display:inline-block;background:#cecece no-repeat center center;">
				<img style="max-width:300px;<?php if ( ! $image_id ) echo 'display:none;'; ?>" class="wlfp-img-preview" src="<?php echo esc_url( $image_url ); ?>" alt="watermark_image">
			</span>
		</p>
		<a href="#" class="button wlfp-reset-img"><?php esc_html_e( 'Clear', 'wolf-photos' ); ?></a>
		<a href="#" class="button wlfp-set-img"><?php esc_html_e( 'Choose an image', 'wolf-photos' ); ?></a>
		<?php
	}

	/**
	 * Repeat
	 */
	public function setting_watermark_image_repeat() {
		?>
		<select name="wolf_photos_settings[watermark_image_repeat]">
			<option value="1" <?php selected( true,  wolf_photos_get_option( 'watermark_image_repeat' ) ); ?>><?php esc_html_e( 'Repeat', 'wolf-photos' ); ?></option>
			<option value="" <?php selected( '',  wolf_photos_get_option( 'watermark_image_repeat' ) ); ?>><?php esc_html_e( 'No Repeat', 'wolf-photos' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Position
	 */
	public function setting_watermark_image_position() {
		$settings = array(
			'center center' => esc_html__( 'Center Center', 'wolf-photos' ),
			'right top' => esc_html__( 'Right Top', 'wolf-photos' ),
			'right bottom' => esc_html__( 'Right Bottom', 'wolf-photos' ),
			'left top' => esc_html__( 'Left Top', 'wolf-photos' ),
			'left bottom' => esc_html__( 'Left Bottom', 'wolf-photos' ),
		);
		?>
		<select name="wolf_photos_settings[watermark_image_position]">
			<?php foreach ( $settings as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key,  wolf_photos_get_option( 'watermark_image_position' ) ); ?>><?php echo sanitize_text_field( $value ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Display additional instructions
	 */
	public function setting_instructions() {
		$force_regenerate_thumbnails_url = ( class_exists( 'ForceRegenerateThumbnails' ) ) ? admin_url( 'tools.php?page=force-regenerate-thumbnails' ) : 'https://fr.wordpress.org/plugins/force-regenerate-thumbnails/';
		$target = ( class_exists( 'ForceRegenerateThumbnails' ) ) ? '': '_blank';
		?>
		<p><?php esc_html_e( 'Your file must be a PNG image.', 'wolf-photos' ); ?></p>
		<p><?php printf(
		wp_kses_post(
			__( '<strong>Once your watermark image is set, you will have to regenerate your image thumbnails using <a href="%s" target="%s">Force Generate Thumbnails</a> plugin.</strong>', 'wolf-photos' ) ),
			esc_url( $force_regenerate_thumbnails_url ),
			esc_attr( $target )
		); ?>
		</p>
		<?php
	}

	/**
	 * Options form
	 */
	public function options_form() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Photo Options', 'wolf-photos' ); ?></h2>
			<form action="options.php" method="post">
				<?php settings_fields( 'wolf-photos-settings' ); ?>
				<?php do_settings_sections( 'wolf-photos-settings' ); ?>
				<p class="submit">
					<input name="save" type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wolf-photos' ); ?>">
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Set default options
	 */
	public function default_options() {

		// delete_option( 'wolf_photos_settings' );

		if ( false === get_option( 'wolf_photos_settings' )  ) {

			$default = array(
				'watermark_image_id' => null,
				'watermark_image_repeat' => true,
				'watermark_image_position' => 'center center',
			);

			add_option( 'wolf_photos_settings', $default );
		}
	}
} // end class

return new WLFP_Options();
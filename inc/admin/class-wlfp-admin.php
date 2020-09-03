<?php
/**
 * Photos Admin.
 *
 * @class WPLF_Admin
 * @author WolfThemes
 * @category Admin
 * @package WolfPhotos/Admin
 * @version 1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPLF_Admin class.
 */
class WPLF_Admin {
	/**
	 * Constructor
	 */
	public function __construct() {

		// Includes files
		$this->includes();

		// Admin init hooks
		$this->admin_init_hooks();
	}

	/**
	 * Perform actions on updating the theme id needed
	 */
	public function update() {

		if ( ! defined( 'IFRAME_REQUEST' ) && ! defined( 'DOING_AJAX' ) && ( get_option( 'wlfp_version' ) != WLFP_VERSION ) ) {

			// Update hook
			do_action( 'wlfp_do_update' );

			// Update version
			delete_option( 'wlfp_version' );
			add_option( 'wlfp_version', WLFP_VERSION );

			// After update hook
			do_action( 'wlfp_updated' );
		}
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {

		include_once( 'class-wlfp-options.php' );
		include_once( 'wlfp-admin-functions.php' );
	}

	/**
	 * Admin init
	 */
	public function admin_init_hooks() {

		// Plugin settings link
		add_filter( 'plugin_action_links_' . plugin_basename( WLFP_PATH ), array( $this, 'settings_action_links' ) );

		// Update version and perform stuf if needed
		add_action( 'admin_init', array( $this, 'update' ), 0 );
	}

	/**
	 * Add settings link in plugin page
	 */
	public function settings_action_links( $links ) {
		$setting_link = array(
			'<a href="' . admin_url( 'options-general.php?page=wolf-photos-settings' ) . '">' . esc_html__( 'Settings', 'wolf-photos' ) . '</a>',
		);
		return array_merge( $links, $setting_link );
	}
} // end class

return new WPLF_Admin();

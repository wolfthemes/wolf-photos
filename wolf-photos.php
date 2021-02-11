<?php
/**
 * Plugin Name: Photos
 * Plugin URI: https://github.com/wolfthemes/wolf-photos
 * Description: Add functionalities to attachement image: category and tags, custom fields and watermark. Designed to be used by WolfThemes Themes that support custom photo features.: category and tags, custom fields and watermark. Designed to be used by WolfThemes Themes that support custom photo features.: category and tags, custom fields and watermark. Designed to be used by WolfThemes Themes that support custom photo features.: category and tags, custom fields and watermark. Designed to be used by WolfThemes Themes that support custom photo features.
 * Version: 1.0.7
 * Author: WolfThemes
 * Author URI: https://wolfthemes.com
 * Requires at least: 5.0
 * Tested up to: 5.5
 *
 * Text Domain: wolf-photos
 * Domain Path: /languages/
 *
 * @package WolfPhotos
 * @category Core
 * @author WolfThemes
 *
 * Being a free product, this plugin is distributed as-is without official support.
 * Verified customers however, who have purchased a premium theme
 * at https://themeforest.net/user/Wolf-Themes/portfolio?ref=Wolf-Themes
 * will have access to support for this plugin in the forums
 * https://wolfthemes.ticksy.com/
 *
 * Copyright (C) 2013 Constantin Saguin
 * This WordPress Plugin is a free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * It is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * See https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Wolf_Photos' ) ) {
	/**
	 * Main Wolf_Photos Class
	 *
	 * Contains the main functions for Wolf_Photos
	 *
	 * @class Wolf_Photos
	 * @version 1.0.7
	 * @since 1.0.0
	 */
	class Wolf_Photos {

		/**
		 * @var string
		 */
		public $version = '1.0.7';

		/**
		 * @var Photos The single instance of the class
		 */
		protected static $_instance = null;

		/**
		 * @var the support forum URL
		 */
		private $support_url = 'https://help.wolfthemes.com/';

		/**
		 * @var string
		 */
		public $template_url;

		/**
		 * Main Photos Instance
		 *
		 * Ensures only one instance of Photos is loaded or can be loaded.
		 *
		 * @static
		 * @see WLFP()
		 * @return Photos - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Photos Constructor.
		 */
		public function __construct() {

			//return;

			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'wlfp_loaded' );
		}

		/**
		 * Hook into actions and filters
		 */
		private function init_hooks() {
			add_action( 'init', array( $this, 'init' ), 0 );
			register_activation_hook( __FILE__, array( $this, 'activate' ) );

			// Plugin update notifications
			add_action( 'admin_init', array( $this, 'plugin_update' ) );
		}

		/**
		 * Activation function
		 */
		public function activate() {

			if ( ! get_option( '_wolf_photos_flush_rewrite_rules_flag' ) ) {
				add_option( '_wolf_photos_flush_rewrite_rules_flag', true );
			}
		}

		/**
		 * Flush rewrite rules on plugin activation to avoid 404 error
		 */
		public function flush_rewrite_rules() {

			if ( get_option( '_wolf_photos_flush_rewrite_rules_flag' ) ) {
				flush_rewrite_rules();
				delete_option( '_wolf_photos_flush_rewrite_rules_flag' );
			}
		}

		/**
		 * Define WR Constants
		 */
		private function define_constants() {

			$constants = array(
				'WLFP_DEV' => false,
				'WLFP_DIR' => $this->plugin_path(),
				'WLFP_URI' => $this->plugin_url(),
				'WLFP_CSS' => $this->plugin_url() . '/assets/css',
				'WLFP_JS' => $this->plugin_url() . '/assets/js',
				'WLFP_SLUG' => plugin_basename( dirname( __FILE__ ) ),
				'WLFP_PATH' => plugin_basename( __FILE__ ),
				'WLFP_VERSION' => $this->version,
				'WLFP_SUPPORT_URL' => $this->support_url,
				'WLFP_DOC_URI' => 'https://docs.wolfthemes.com/documentation/plugins/' . plugin_basename( dirname( __FILE__ ) ),
				'WLFP_WOLF_DOMAIN' => 'wolfthemes.com',
			);

			foreach ( $constants as $name => $value ) {
				$this->define( $name, $value );
			}
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * What type of request is this?
		 * string $type ajax, frontend or admin
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {

			/**
			 * Functions used in frontend and admin
			 */
			include_once( 'inc/wlfp-core-functions.php' );

			if ( $this->is_request( 'admin' ) ) {
				include_once( 'inc/admin/class-wlfp-admin.php' );
			}

			if ( $this->is_request( 'ajax' ) ) {

			}

			if ( $this->is_request( 'frontend' ) ) {
				include_once( 'inc/frontend/wlfp-functions.php' );
			}
		}

		/**
		 * Init Photos when WordPress Initialises.
		 */
		public function init() {

			// Set up localisation
			$this->load_plugin_textdomain();

			// Variables
			$this->template_url = apply_filters( 'wolf_photos_url', 'wolf-photos/' );

			// Classes/actions loaded for the frontend and for ajax requests
			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {

				// Hooks
				add_filter( 'template_include', array( $this, 'template_loader' ) );
			}

			$this->register_taxonomy();
			$this->add_rewrite_rule();
			$this->flush_rewrite_rules();

			// Init action
			do_action( 'wolf_photos_init' );
		}

		/**
		 * Register taxonomy
		 */
		public function register_taxonomy() {
			include_once( 'inc/wlfp-register-taxonomy.php' );
		}

		/**
		 * Rewrite single attachment URL
		 */
		public function add_rewrite_rule() {
			add_rewrite_rule(
				'photo/([a-z0-9-_]+)/?', // ([^/]+)
				'index.php?attachment=$matches[1]',
				'top'
			);
		}

		/**
		 * Load a template.
		 *
		 * Handles template usage so that we can use our own templates instead of the themes.
		 *
		 * @param mixed $template
		 * @return string
		 */
		public function template_loader( $template ) {

			$find = array(); // nope! not used
			$file = '';

			if ( is_tax( 'photo_category' ) ) {

				$term = get_queried_object();

				$file   = 'taxonomy-' . $term->taxonomy . '.php';
				$find[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = $this->template_url . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = $file;
				$find[] = $this->template_url . $file;
			}

			if ( is_tax( 'photo_tag' ) ) {

				$term = get_queried_object();

				$file   = 'taxonomy-' . $term->taxonomy . '.php';
				$find[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = $this->template_url . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = $file;
				$find[] = $this->template_url . $file;
			}

			if ( $file ) {
				$template = locate_template( $find );
				if ( ! $template ) $template = $this->plugin_path() . '/templates/' . $file;
			}

			return $template;
		}

		/**
		 * Loads the plugin text domain for translation
		 */
		public function load_plugin_textdomain() {

			$domain = 'wolf-photos';
			$locale = apply_filters( 'wolf-photos', get_locale(), $domain );
			load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Plugin update
		 */
		public function plugin_update() {

			if ( ! class_exists( 'WP_GitHub_Updater' ) ) {
				include_once 'inc/admin/updater.php';
			}

			$repo = 'wolfthemes/wolf-photos';

			$config = array(
				'slug' => plugin_basename( __FILE__ ),
				'proper_folder_name' => 'wolf-photos',
				'api_url' => 'https://api.github.com/repos/' . $repo . '',
				'raw_url' => 'https://raw.github.com/' . $repo . '/master/',
				'github_url' => 'https://github.com/' . $repo . '',
				'zip_url' => 'https://github.com/' . $repo . '/archive/master.zip',
				'sslverify' => true,
				'requires' => '5.0',
				'tested' => '5.5',
				'readme' => 'README.md',
				'access_token' => '',
			);

			new WP_GitHub_Updater( $config );
		}
	} // end class
} // end class check

/**
 * Returns the main instance of wlfp to prevent the need to use globals.
 *
 * @return Wolf_Photos
 */
function WLFP() {
	return Wolf_Photos::instance();
}

WLFP(); // Go

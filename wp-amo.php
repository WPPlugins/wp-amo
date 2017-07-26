<?php
/*
Plugin Name: AMO for WP
Plugin URI:  http://www.associationsonline.com
Description: Connects popular elements of the AMO system to WordPress - Event Registration, Membership Directory, Member Portal Login, Classifieds, & more...
Version:     3.3.3
Author:      ArcStone
Author URI:  http://www.ArcStone.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

namespace ArcStone\AMO;

defined( 'ABSPATH' ) or die();

include 'autoload.php';

class WP_AMO {

	private static $instance = null;
	public static $installed_path = '';
	public static $installed_url = '';
	protected $amo;

	public static function get_instance() {

		if( is_null( self::$instance ) ) {
			self::$instance = new WP_AMO;
			self::$instance->setup();
			self::$instance->init_users();
			self::$instance->init_contentProtect();
		}
		return self::$instance;
	}

	public function setup() {

		/**
		 * Setup the paths
		 */
		if ( empty( self::$installed_path ) ) {
			self::$installed_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		}

		if ( empty( self::$installed_url ) ) {
			self::$installed_url = trailingslashit( plugin_dir_url( __FILE__ ) );
		}
		/**/

		/**
		 * Activation hooks
		 */
		register_activation_hook( __FILE__, array( '\ArcStone\AMO\Shortcodes', 'activation_hook' ) );
		// register_uninstall_hook( __FILE__, array( '\ArcStone\AMO\Shortcodes', 'uninstall_hook' ) );
		add_action( 'wpmu_new_blog', array( '\ArcStone\AMO\Shortcodes', 'wpmu_new_blog' ) );
		add_action( 'delete_blog', array( '\ArcStone\AMO\Shortcodes', 'delete_blog' ) );
		/**/

		if ( wpamo_enable_users() ) {
			register_activation_hook( __FILE__, array('\ArcStone\AMO\Users', 'activation_hook' ) );
			// register_uninstall_hook( __FILE__, array('\ArcStone\AMO\Users', 'uninstall_hook' ) );
		}

		/**
		 * Load CMB2.
		 *
		 * CMB2_LOADED will be defined if another plugin had loaded CMB2.
		 *
		 */
		if ( !defined( 'CMB2_LOADED' ) ) {
			if ( file_exists( self::$installed_path . 'includes/cmb2/init.php' ) ) {
				require_once self::$installed_path . 'includes/cmb2/init.php';
			} elseif ( file_exists( self::$installed_path . 'includes/CMB2/init.php' ) ) {
				require_once self::$installed_path . 'includes/CMB2/init.php';
			}
		}


		add_action( 'init', array( $this, 'init' ), 99999 );
		add_action( 'init', array( $this, 'init_shortcodes' ), 99999); // let's make this the last thing ever.


		/**
		 * Admin settings page
		 */
		add_filter( 'plugins_loaded', array( $this, 'amo_admin_page' ) );

	}

	public function init() {
		// define constants
		$api_key = \cmb2_get_option( 'amo_options', 'api_key'); // Get the API key from the database.
		define( __NAMESPACE__ . '\AMO_FORM_URL', 'https://admin.associationsonline.com/secure' ); // no trailing slash
		define( __NAMESPACE__ . '\AMO_API_KEY', $api_key );

		/**
		 * Display an admin warning if the API key has not been set.
		 */
		if ( (!defined( __NAMESPACE__ . '\AMO_API_KEY' ) || constant( __NAMESPACE__ . '\AMO_API_KEY' ) == '' ) && is_admin() ) {
			add_action( 'admin_notices', array( $this, 'no_key_notice') );
		}


	}

	/**
	 * Initialize AMO Shortcodes
	 */
	public function init_shortcodes() {

		$this->amo = new Shortcodes( __FILE__ );

	}


	public function init_users() {
		if ( wpamo_enable_users() ) {
			return Users::get_instance();
		} else {
			return false;
		}
	}

	public function init_contentProtect() {
		if ( wpamo_enable_protected_content() ) {
			return new ContentProtect();
		} else {
			return false;
		}

	}

	public function amo_admin_page() {
		if ( is_admin() && current_user_can( 'administrator' ) ) {
			Admin::set_plugin_path(  plugin_dir_url( __FILE__ ) );
			return Admin::get_instance();
		}
	}

	/**
	 * Display admin notice when API key setting is missing
	 */
	public function no_key_notice() {
	   echo '<div class="notice notice-error">
        		<p>Add your <a href="'.admin_url( 'admin.php?page=amo_options' ).'">AMO API key</a> to enable AMO shortcodes.</p>
    		</div>';
	}

}

function wpamo_enable_users() {
	if ( is_multisite() ) {
		return false;
	} else {
		return true;
	}
}

function wpamo_enable_protected_content() {
	return wpamo_enable_users();
}


function do_wpamo() {
	return WP_AMO::get_instance();
}

do_wpamo();

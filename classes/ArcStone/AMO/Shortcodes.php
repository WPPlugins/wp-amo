<?php

namespace ArcStone\AMO;

/**
 * Sets up the shortcodes modules
 *
 * @version 2.0
 */
Class Shortcodes {

	private static $amo_shortcodes_cache_db_version = '1.0';
	public $installed_base_url;
	public $installed_base_path;
	public $amo_css = 'no_bs'; // no bootstrap by default

	/**
	 * Construct and initialize AMO handler.
	 *
	 * Initialize constants, load shortcodes, enable css
	 *
	 * @param string $base_file. Output of `__FILE__`, this needs to be call in amo-plugin.php to get the correct path.
	 */
	public function __construct() {
		global $pagenow;

		$this->amo_css = \cmb2_get_option( 'amo_options', 'amo_css' );

		$this->installed_base_path = WP_AMO::$installed_path;
		$this->installed_base_url = WP_AMO::$installed_url;

		$this->load_shortcodes();
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ), 99999 );

		add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'));

		if(is_admin()  && ( $pagenow !== 'admin.php' || (isset($_GET['page']) && $_GET['page'] == 'amo_options')) ) {
			MediaButton::set_plugin_path( $this->installed_base_path );
			MediaButton::set_plugin_web_root( $this->installed_base_url );
			$media_button = MediaButton::get_instance();

			require_once $this->installed_base_path . 'admin/amo-shortcode-form.php';
			new AMO_Shortcode_Form(); // note: this class also controls jquery-ui.css enqueue. TODO: worth moving?
		}
	}

	/**
	 * Load shortcode handlers.
	 */
	public function load_shortcodes() {

		//AMO Announcements Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_announcements.php');

		//AMO Calendar Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_calendar.php');

		//AMO Classified Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_classifieds.php');

		//AMO Classified Ad Posting Button Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_classifieds_button.php');

		//AMO Committees Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_committees.php');

		// //AMO Committee Members Shortcode
        include( $this->installed_base_path . 'templates/amo_shortcode_committee_members.php');

		//AMO Donation Button Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_donation_button.php');

		//AMO Events Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_events.php');

		//AMO Individual Member Directory Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_member_directory.php');

		//AMO Individual Membership Application Button Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_individual_application_button.php');

		//AMO Jobs Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_jobs.php');

		//AMO Member Center Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_member_center.php');

		//AMO Member Center Login Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_member_center_login.php');

		// //AMO Member Types Shortcode
		// include('includes/amo_shortcode_member_types.php');

		//AMO Members - Individuals Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_members.php');

		//AMO Members - Organizations Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_members_organizations.php');

		//AMO Organization Member Directory Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_member_directory_organization.php');

		//AMO Organization Membership Application Button Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_organization_application_button.php');

		//AMO Random Banner Ad Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_random_banner.php');

		//AMO Resumes Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_resumes.php');

		//AMO Webpage Shortcode
		include( $this->installed_base_path . 'templates/amo_shortcode_webpage.php');
	}

	/**
	 * Enqueue styles.
	 *
	 * Triggered by `wp_enqueue_scripts` hook. The function loads styles based
	 * on the plugin's configuration settings.
	 */
	public function enqueue_scripts_styles () {

		wp_register_style( 'amo-styles', $this->installed_base_url . 'css/amo-styles.css' );
		wp_register_style( 'amo-bootstrap', $this->installed_base_url . 'css/bootstrap.css' );

		// load AMO CSS + Bootstrap helper
		if ( $this->amo_css === 'all_css' ) {
			wp_enqueue_style( 'amo-styles' );
			wp_enqueue_style( 'amo-bootstrap' );
		// Only load AMO CSS
		} else if ( $this->amo_css === 'no_bs' ) {
			wp_enqueue_style( 'amo-styles' );
		// Don't load any CSS
		} else {
			// maybe do something here one day, who knows.
		}

	}

	function admin_enqueue_scripts() {
		wp_enqueue_style( 'amo-admin-style', $this->installed_base_url . 'css/amo-admin-styles.css', array(), '2.2' );
	}

	/**
	 * Multisite new site creation
	 *
	 * @param int $blog_id. Site's blog ID value from the wp_blogs table
	 */
	public static function wpmu_new_blog( $blog_id ) {
		global $wpdb;

		$current_blogid = $wpdb->blogid;
		switch_to_blog( $blog_id );
		self::_do_activation();
		switch_to_blog( $current_blogid );
	}

	/**
	 * Multisite site deletion
	 *
	 * @param int $blog_id. Site's blog ID value from the wp_blogs table
	 */
	public static function delete_blog( $blog_id  ) {
		global $wpdb;

		$current_blogid = $wpdb->blogid;
		switch_to_blog( $blog_id );
		self::_do_uninstall();
		switch_to_blog( $current_blogid );
	}

	/**
	 * Plugin activation hook.
	 *
	 * @param int $networkwide. 1 if network wide plugin activation.
	 */
	public static function activation_hook( $networkwide ) {
		global $wpdb;

		/**
		 * For multisite network activation, loop through all installed sites
		 * and create cache table for each site.
		 */
	    if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	    	if ( $networkwide ) {
	    		$old_blog = $wpdb->blogid;
            	// Get all blog ids
            	$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            	foreach ($blogids as $blog_id) {
                	switch_to_blog($blog_id);
                	self::_do_activation();
            	}
            	switch_to_blog($old_blog);
	    		return;
	    	}
	    }

	    self::_do_activation();
	}

	/**
	 * Create cache table for current site.
	 *
	 * Note: call `switch_to_blog()` before running this for multisite network install
	 */
	private static function _do_activation () {
		global $wpdb;

		$table_name = $wpdb->prefix . 'amo_shortcodes_cache';

		$collate = $wpdb->collate;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
							`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
						  	`method` varchar(32) DEFAULT NULL,
							`request_url_hash` char(32) NOT NULL DEFAULT '',
							`response_json` TEXT NOT NULL,
							`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
							PRIMARY KEY  (`id`),
							UNIQUE KEY `request_url_hash` (`request_url_hash`)
							) 
							COLLATE {$collate}";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );

		add_option( 'amo_shortcodes_cache_db_version', self::$amo_shortcodes_cache_db_version );
	}

	/**
	 * Clean up database on uninstall
	 */
	public static function uninstall_hook() {
		global $wpdb;

	    if ( function_exists( 'is_multisite' ) && is_multisite() ) {
    		$old_blog = $wpdb->blogid;
        	// Get all blog ids
        	$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        	foreach ($blogids as $blog_id) {
            	switch_to_blog($blog_id);
            	self::_do_uninstall();
        	}
        	switch_to_blog($old_blog);
    		return;
	    }

	    self::_do_uninstall();
	}

	/**
	 * Delete database. Delete options.
	 *
	 * Note: call `switch_to_blog()` before running this for multisite network install
	 */
	private static function _do_uninstall() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}amo_shortcodes_cache" );

		delete_option( 'amo_api_key' );
		delete_option( 'amo_shortcodes_cache_db_version' );
		delete_option( 'amo_options' );
	}

	/**
	 * Static Singleton Factory Method
	 *
	 * @return AMO
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}

		return self::$instance;
	}
}

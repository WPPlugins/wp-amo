<?php
namespace ArcStone\AMO;

/**
 * CMB2-based settings page. 
 * Reference: https://github.com/WebDevStudios/CMB2-Snippet-Library/tree/master/options-and-settings-pages
 * 
 * @version 2.1
 */

Class Admin {
	
	private $key 				= 'amo_options';
	private $metabox_id 		= 'amo_options_metabox';
	protected $title 			= '';
	protected $options_page 	= '';
	private static $instance 	= null;
	private static $plugin_path = '';

	private function __construct() {
		$this->title = 'AMO Settings';
	}

	/**
	 * Returns the running object
	 *
	 * @return Admin
	 **/
	public static function get_instance() {
		if( is_null( self::$instance ) ) {
			self::$instance = new Admin();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Initiate our hooks
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
        add_action( 'cmb2_after_form', array( $this, 'settings_enqueue' ) );

        add_filter( 'cmb2_get_metabox_form_format', array( $this, 'cmb2_metabox_format'), 10, 3 );

	}


	/**
	 * Register our setting to WP
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	public static function set_plugin_path( $path ) {
		self::$plugin_path = $path;
	}

    public static function settings_enqueue() {
        wp_enqueue_script('jquery-ui-tabs');
    }
    
	/**
	 * Add menu options page
	 */
	public function add_options_page() {
		$this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ), 'dashicons-groups' );
		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}


	/**
	 * Admin page markup. Mostly handled by CMB2
	 */
	public function admin_page_display() {
		include WP_AMO::$installed_path . 'templates/admin-options-page.php';
	}

	

	/**
	 * Settings page meta boxes.
	 * 
	 * Handled by CMB2.
	 */
	public function add_options_page_metabox() {
		
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'do_save' ), 10, 2 );

		$cmb = new_cmb2_box( array(
								'id'			=>	$this->metabox_id,
								'hookup'		=>	false,
								'cmb_styles'	=>	false,
								'show_on'		=>	array(
													'key'	=>	'options-page',
													'value'	=>	array( $this->key, )
													),
								));

		$cmb->add_field( array(
							'name'	=>	'API Key',
							'desc'	=>	'',
							'id'	=>	'api_key',
							'type'	=>	'text',
						));

		if ( wpamo_enable_protected_content() ) {
			$select_pages_options = array();
			foreach ( get_pages() as $page ) {
				$select_pages_options[$page->ID] = $page->post_title;
			}

			$select_pages_options = array( '' => '&lt;None&gt;' ) + $select_pages_options;

			$cmb->add_field( array(
							'id'	=>	'no-access-logged-out',
							'name'	=>	'Restricted Content Page',
							'desc'	=>	'Selected page will be displayed on restrict AMO member pages. All visitors in the general public will see this page.',
							'type'	=>	'select',
							'options'	=>	$select_pages_options
							));

			$cmb->add_field( array(
							'id'	=>	'no-access-logged-in',
							'name'	=>	'Incorrect Member Type Page',
							'desc'	=>	'Selected page will be displayed when a logged in AMO user does not have the member type required to view the a page.',
							'type'	=>	'select',
							'options'	=> $select_pages_options
				));
		}

		


        // Display options form
        add_action( "cmb2_save_options-page_fields_{$this->metabox_id}_2", array( $this, 'do_save_css' ), 10, 2 );

        $cmb2 = new_cmb2_box( array(
                            'id'            =>  $this->metabox_id . '_2',
                            'hookup'        =>  false,
                            'cmb_styles'    =>  false,
                            'savefields'    =>  false,
                            'show_on'       =>  array(
                                                'key'   =>  'options-page',
                                                'value' =>  array( $this->key ) )
                            ));

        $cmb2->add_field( array( 
                            'name'  =>  'CSS Configuration',
                            'id'    =>  'amo_css',
                            'type'  =>  'select',
                            'default'   =>  'no_bs',
                            'options'   =>  array(
                                'no_bs'     =>  'AMO CSS Only',
                                'all_css'   =>  'AMO + Bootstrap',
                                'none'      =>  'No CSS'
                                )
                            ));


        $cmb2->add_field( array(
                            'name'  =>  'CSS Overrides',
                            'id'    =>  'css_overrides',
                            'type'  =>  'textarea'
                            ));

        // Syncing settings form
        add_action( "cmb2_save_options-page_fields_{$this->metabox_id}_syncing", array( $this, 'do_save_syncing' ), 10, 2);

        $cmb_syncing = new_cmb2_box( array(
	                                        'id'    		=>  $this->metabox_id . '_syncing',
	                                        'hookup'    	=>  false,
	                                        'cmb_styles'    =>  false,
	                                        'save_button'   =>  'Sync',
	                                        'show_on'       =>  array(
	                                                                'key'   =>  'options-page',
	                                                                'value' =>  array( $this->key ) 
	                                                                )
                                        ));

        add_action( "cmb2_save_options-page_fields_{$this->metabox_id}_clearcache", array( $this, 'do_clearcache'), 10, 2);

        $cmb_clearcache = new_cmb2_box( array(
        									'id'			=>	$this->metabox_id . '_clearcache',
        									'hookup'		=>	false,
        									'cmb_styles'	=>	false,
        									'save_button'	=>	'Clear Cache',
        									'show_on'		=>	array(
        															'key'	=>	'options-page',
        															'value'	=>	array( $this->key ) 
        															)
        									));

	}

    // https://github.com/WebDevStudios/CMB2/issues/130
    public function cmb2_metabox_format( $form_format, $object_id, $cmb  ) {

        if ( $object_id == 'amo_options' && $cmb->cmb_id == 'amo_options_metabox_syncing' ) {
            return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="submit-wrap"><input type="submit" name="submit-cmb" value="' . __( 'Sync', 'wpamo' ) . '" class="button-primary"></div></form>';
        } else if ( $object_id == 'amo_options' && $cmb->cmb_id == 'amo_options_metabox_clearcache' ) {
			return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="submit-wrap"><input type="submit" name="submit-cmb" value="' . __( 'Clear Cache', 'wpamo' ) . '" class="button-primary"></div></form>';
        } else {
            return $form_format;
        }

    }
	/**
	 * Process settings page save
	 * 
	 */
	public function do_save( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}

		// var_dump($object_id, $updated);die();

		if ( in_array('api_key', $updated) ) {
			$api_key = cmb2_get_option( $this->key, 'api_key' );
			$api = new API( $api_key );
			$valid_key = $api->checkKey();

			if ( $valid_key ) {
				// set the association's AMO websites url
				$this->_set_amo_website_url( $valid_key[0]['website_url'] );
				// success message
				add_settings_error( $this->key . '-notices', '', 'API Key updated.', 'updated' );
			} else {
				// error message.
				add_settings_error( $this->key . '-notices', '', $api->error );
			}
		}

		settings_errors( $this->key . '-notices' );
	}

    /**
     * Handle Settings CSS tab form save
     *
     */
    public function do_save_css( $object_id, $updated ) {

        if ( $object_id !== $this->key || empty( $updated ) ) {
            return;
        }

        add_settings_error( $this->key . '-notices', '', 'Display settings updated.', 'updated' );
        settings_errors( $this->key . '-notices' );
    }

    /**
     * Handle Settings page "sync" button
     */
    public function do_save_syncing( $object_id, $updated ) {
        $users = Users::get_instance();
        $results_roles = $users->sync_roles();
        $results_users = $users->sync_users();

        echo "<strong>Roles synced:</strong> {$results_roles['success']}<br/>";
        echo '<strong>Users added:</strong> ' . count( $results_users['added'] ) . '<br/>';
        echo '<strong>Users updated:</strong> ' . count( $results_users['updated'] ) . '<br/>';

        if ( count( $results_users['error'] ) > 0 ) {
            echo '<strong>Errors:</strong>';
            echo '<ul>';

            foreach ( $results_users['error'] as $user_error ) {
                
                echo "<li>{$user_error['userdata']['first_name']}: {$user_error['error']}</li>";
            }
            echo '</ul>';
        }

    }

    /**
     * Handle Settings page "clear cache" button
     */
    public function do_clearcache ( $object_id, $updated ) {
		// do cache clearing
		$api = new API( AMO_API_KEY );
		$results = $api->clearCache();

		// alway return success. $results will be false if the cache is already empty. 
		add_settings_error( $this->key . '-notices', '', 'Local AMO API cache has been cleared successfully.', 'updated' );		
    }

    private function _set_amo_website_url ( $url ) {
    	if ( get_option( 'wpamo_website_url' ) ){
    		update_option( 'wpamo_website_url', $url );
    	} else {
    		add_option( 'wpamo_website_url', $url, '', 'no' );
    	}
    }

	/**
	 * Public getter method for retrieving protected/private variables
	 * @param  string  $field Field to retrieve
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}
		throw new Exception( 'Invalid property: ' . $field );
	}
}
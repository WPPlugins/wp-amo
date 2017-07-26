<?php
namespace ArcStone\AMO;

class Users {
	private static $instance = null;
	private $installed_path = '';
	private function __construct() {}

	public static function get_instance() {

		if( is_null( self::$instance ) ) {
			self::$instance = new Users;
			self::$instance->init();
		}
		return self::$instance;
	}

	private function init() {
		add_shortcode( 'amo_user_login_form', array( $this, 'login_form_shortcode' ) );
		add_action( 'parse_request', array( $this, 'login_process_nojs' ));
		add_action( 'wp_ajax_amo_login', array( $this, 'login_process' ) );
		add_action( 'wp_ajax_nopriv_amo_login', array( $this, 'login_process' ) );
		add_action( 'wpamo_sync_cron', array( '\ArcStone\AMO\Users', 'sync_cron_hook' ));

		/**
		 * Limit what users can do
		 */
		add_action( 'after_setup_theme', array( $this, 'remove_admin_toolbar' ) ); // Remove admin toolbar
		add_action( 'init', array( $this, 'block_wp_admin' ) );
	}

	public static function activation_hook() {
		self::add_sync_cron();
	}

	public static function uninstall_hook() {
		delete_option( 'wpamo_website_url' );
		self::clear_sync_cron();
	}

	public function api_login_request( $username, $password ) {
		
		$api = new API( AMO_API_KEY );

		$params = array(
						'username'	=>	$username,
						'password'	=> hash('sha256', $password)
						);

		$results = $api->processRequest( 'AMOLogin', $params, false );

		if ( count( $results ) == 1 ) {
			return $results[0]['pk_association_individual'];
		} else {
			return false;			
		}

	}

	public function login_process_nojs() {
		if ( $_SERVER['REQUEST_URI'] == '/wpamo-login' ) {
			$this->login_process();
		}

		return;
	}

	public function login_process() {

		$error = false; 
		$response = '';


   		if ( isset( $_POST['destination'] ) && isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
   			$success_url = esc_url( $_POST['destination'] );

   			$amo_user_id = $this->api_login_request( $_POST['username'], $_POST['password']  );
	   		if ( !$amo_user_id ) {
	   			$error = true;
	   			$response = 'Invalid username/password';
	   		} else {
		   		if ( $this->do_login( $amo_user_id ) ) {

		   			$response = array( 'destination' => $success_url );

	   			} else {
	   				// user not found error
	   				$error = true;
	   				$response = 'AMO username not found in the database.';
	   			}	
	   		}

   			
   		} else {
   			// invalid login error
   			$error = true;
   			$response = 'Invalid request';
   		}

   		if ( defined( 'DOING_AJAX' ) ) {
   			header('content-type: application/json');	
   			echo json_encode( array( 'error' => $error, 'response' => $response ) );
   			wp_die();   			
   		} else {
   			if ( !$error ) {
   				wp_redirect( $success_url );
   				exit;
   			} else {
   				// this is kind of dirty, but will only fire for login attempts with javascript disabled.
   				// (AKA. probably only bots?)
   				die( $response );
   			}
   		}

	}

	public function login_form_shortcode( $atts ) {

		wp_register_script( 'amo-login-form', WP_AMO::$installed_url . '/js/login-form.js', array( 'jquery' ), '3.0', true );
		wp_enqueue_script( 'amo-login-form' );
		wp_localize_script( 'amo-login-form', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

		ob_start();
		include WP_AMO::$installed_path . 'templates/amo_shortcode_login_form.php';
		return ob_get_clean();
	}

	private function find_wp_user( $amo_id ) {
		global $wpdb;
		$usermeta_query = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'amo_pk_association_individual' AND meta_value = %d LIMIT 1";
		$usermeta_query_result = $wpdb->get_var( $wpdb->prepare( $usermeta_query, $amo_id ));

		if ( $usermeta_query_result ) {
			return get_user_by( 'id', $usermeta_query_result );
		} else {
			return false;
		}
	}

	private function do_login( $amo_id ) {

		$found_user = self::find_wp_user( $amo_id );

		if ( $found_user ) {
			$user_id = $found_user->ID;
			$user_login = $found_user->user_login;
			wp_set_current_user( $user_id, $user_login );
			wp_set_auth_cookie( $user_id );
			do_action( 'wp_login', $user_login, $found_user );
			return $found_user;
		} else {
			return false;
		}
	}


	public function remove_admin_toolbar() {
		if (!current_user_can('administrator') && !is_admin()) {
			show_admin_bar(false);
		}
	}

	public function block_wp_admin() {
		// limit wp-admin to users who can edit post (ie. Author or greater)
		if ( is_user_logged_in() && is_admin() && !current_user_can( 'edit_published_posts' ) && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_redirect( home_url() );
			exit;
		}
	}


	public static function sync_users() {
		global $wpdb;
		
		$results = array( 'added' => array(), 'updated' => array(), 'error' => array() );
		
		$api = new API( AMO_API_KEY );

		$users = $api->processRequest( 'AMOIndividuals', null, false );

		if ( $users ) {
			foreach ( $users as $user ) {

				$role = self::member_type_to_role( $user['member_type_name'] );


				// if the role is empty, assign this user as a contributor
				if ( empty( $role ) ) {
					$role = 'contributor';
				}

				$userdata = array(
								'user_login'	=>	$user['username'],
								'first_name'	=>	$user['first_name'],
								'last_name'		=>	$user['last_name'],
								'user_email'	=>	$user['email'],
								'role'			=>	$role
					);

				/**
				 * Find matching user 
				 */
				$user_query = new \WP_User_Query( array(
											'meta_key'	=>	'amo_pk_association_individual',
											'meta_value'	=>	$user['pk_association_individual']	
											));
				/**
				 * Update user
				 */
				if ( $user_result = $user_query->get_results() ) {

					// if fields have changed.
					if ( 
							$user_result[0]->user_login 	!= $user['username'] ||
							$user_result[0]->user_firstname != $user['first_name'] ||
							$user_result[0]->user_lastname 	!= $user['last_name'] || 
							$user_result[0]->user_email 	!= $user['email'] ||
							( !empty($user_result[0]->roles) && !in_array($role, $user_result[0]->roles) )
						) 
					{

						$userdata = array_merge( $userdata, array( 'ID' => $user_result[0]->ID ) );

                        // do not send the email changed email.
                        add_filter( 'send_email_change_email', '__return_false' );

						$user_id = wp_update_user( $userdata );

                        // user id is found
						if ( !is_wp_error( $user_id ) ) {
							$wpdb->update($wpdb->users, array('user_login' => $user['username']), array('ID' => $user_result[0]->ID));
							update_user_meta( $user_id, 'amo_pk_association_individual', $user['pk_association_individual'] );
							$results['updated'][] = $userdata;
						}
					}

		

				/**
				 * Insert User
				 */				
				} else {
					// generate a big huge random password
					$user_pass = wp_generate_password( 20, true, true );

					$userdata = array_merge( $userdata, array(
																'user_pass'		=>	$user_pass,
																));							

					$user_id = wp_insert_user( $userdata );	

					if ( !is_wp_error( $user_id ) ) {
						add_user_meta( $user_id, 'amo_pk_association_individual', $user['pk_association_individual'] );
						$results['added'][] = $userdata;
					}
				}

				/**
				 * Error updating or adding user
				 */
				if ( isset($user_id) && is_wp_error( $user_id ) ) {
					$results['error'][] = array(
											'userdata'	=>	$userdata,
											'error'		=>	$user_id->get_error_message() 
											);
				}
			}
		}


		return $results;

	}

	public static function sync_roles() {

		$results = array( 'success' => 0, 'error' => array());

		$api = new API( AMO_API_KEY );

		$roles = $api->processRequest( 'AMOMemberTypes', null, false );

		if ( $roles ) {
			foreach ( $roles as $role ) {
				
				$role_slug = self::member_type_to_role( $role['member_type_name'] );
				// technically, $role_slug could be empty in some error cases
				if ( !empty( $role_slug ) ) {
					$role_results = add_role(
										$role_slug,
										$role['member_type_name'],
										array( 'read'	=>	true )
									);

					if ( $role_results ) {
						$results['success']++;
					}
				}
				
			}
		}

		return $results;

	}

	public static function sync_users_and_roles() {

		self::sync_roles();
		self::sync_users();
	}

	public static function add_sync_cron() {

	  	$timestamp = wp_next_scheduled( 'wpamo_sync_cron' );

	  	if ( $timestamp === false ){
	  		wp_schedule_event( time(), 'hourly', 'wpamo_sync_cron' );
	  	}
	}

	public static function clear_sync_cron() {

		wp_clear_scheduled_hook( 'wpamo_sync_cron' );
	}

	public static function sync_cron_hook() {
		self::sync_users_and_roles();
	}

	private static function member_type_to_role ( $member_type_name ) {
		return sanitize_title( $member_type_name );
	}

}

<?php
namespace ArcStone\AMO;
use \WP;

class ContentProtect {

	private $available_roles = array();

	public function __construct() {

		$this->add_hooks();

		if ( is_admin() ) {
			$wp_roles = new \WP_Roles();
    		$this->available_roles = $wp_roles->roles;
			add_action( 'cmb2_admin_init', array( $this, 'metabox' ) );
		}
		
	}
	
	public function add_hooks() {
		// Filter the content and exerpts.
		add_filter( 'the_content',      array( $this, 'protect' ), 95 );
		add_filter( 'get_the_excerpt',  array( $this, 'protect' ), 95 );
		add_filter( 'the_excerpt',      array( $this, 'protect' ), 95 );
		add_filter( 'the_content_feed', array( $this, 'protect' ), 95 );
		add_filter( 'comment_text_rss', array( $this, 'protect' ), 95 );

		// Filter the comments template to make sure comments aren't shown to users without access.
		add_filter( 'comments_template', array( $this, 'protect' ), 95 );

		// Filter edit link, don't show it to unauthorized users
		add_filter( 'edit_post_link', array( $this, 'protect_edit_link' ), 95 );
	}

	public function remove_hooks() {
		// Filter the content and exerpts.
		remove_filter( 'the_content',      array( $this, 'protect' ), 95 );
		remove_filter( 'get_the_excerpt',  array( $this, 'protect' ), 95 );
		remove_filter( 'the_excerpt',      array( $this, 'protect' ), 95 );
		remove_filter( 'the_content_feed', array( $this, 'protect' ), 95 );
		remove_filter( 'comment_text_rss', array( $this, 'protect' ), 95 );

		// Filter the comments template to make sure comments aren't shown to users without access.
		remove_filter( 'comments_template', array( $this, 'protect' ), 95 );

		// Filter edit link, don't show it to unauthorized users
		remove_filter( 'edit_post_link', array( $this, 'protect_edit_link' ), 95 );
	}

	public function protect( $content ) {
		$can_vew = false;
		$post_id = get_the_ID();

		$protect_roles_selected = get_post_meta( $post_id, 'amo_content_protect_roles_selected', true);

		/** The page is not protected, just show it */
		if ( empty( $protect_roles_selected ) ) {
			return $content;
		} else {
			$can_view = $this->can_user_view_content( get_current_user_id(), $post_id, $protect_roles_selected);

			/** User _can_ view the page, show it */
			if ( $can_view ) {
				return $content;
			} else {

				$this->remove_hooks();

				$is_user_logged_in = is_user_logged_in();
				$no_access_logged_out = cmb2_get_option('amo_options', 'no-access-logged-out');
				$no_access_logged_in = cmb2_get_option('amo_options', 'no-access-logged-in');
				
				if ( $is_user_logged_in && $no_access_logged_in ) {
					$content = get_page( $no_access_logged_in )->post_content;
				} elseif ( $is_user_logged_in && !$no_access_logged_in ) {
					ob_start();
					include( WP_AMO::$installed_path . 'templates/no-access-logged-in.php' );
					$content = ob_get_clean();
				} elseif ( !$is_user_logged_in && $no_access_logged_out ) {
					$content = get_page( $no_access_logged_out )->post_content;
				} else {
					ob_start();
					include( WP_AMO::$installed_path . 'templates/no-access-logged-out.php' );
					$content = ob_get_clean();
				}

				// apply WP styling
				$content = apply_filters( 'the_content', $content );


				$this->add_hooks();

				return $content;
				
			}
		}
		
	}

	/**
	 * This function protects the edit link so that users without permission do not accidentally see it
	 */
	public function protect_edit_link( $content ) {
		$can_view = $this->can_user_view_content();
		if ( $can_view ) {
			return $content;
		} else {
			return '';
		}
	}

	public function metabox() {
		$role_options = $this->convert_roles_to_options( $this->available_roles );

		$prefix = 'amo_content_protect_';
		$cmb = \new_cmb2_box( array(
							'id'			=>	$prefix . 'metabox',
							'title'			=>	'Content Restriction',
							'object_types'	=>	array( 'page', 'post', ),
							'context'		=>	'normal',
							'priority'		=>	'high',
							'show_names'	=>	'true'
							)
		);


		$cmb->add_field( array(
							'name'		=>	'Restrict Content',
							'desc'		=>	'Only selected roles will have access to the content. If none selected, content will be public.',
							'id'		=>	$prefix . 'roles_selected',
							'type'		=>	'multicheck',
							'options'	=>	$this->convert_roles_to_options( $this->available_roles )
						));
	}

	private function convert_roles_to_options( $roles ) {

		$options = array();

		if ( $roles ) {
			foreach ( $roles as $role => $details ) {
				$options[$role] = $details['name'];
			}
		}

		return $options;
	}

	private function can_user_view_content( $user_id = '', $post_id = '', $roles = array() ) {

		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}

		if ( !$post_id ) {
			$post_id = \get_the_ID();
		}

		if ( empty( $roles ) ) {
			$roles = get_post_meta( $post_id, 'amo_content_protect_roles_selected', true);
		}

		// the post is restricted
		if ( is_array( $roles ) ) {

			$post = get_post( $post_id );

			// if it's a feed or user is not loggedin, never viewable
			if ( is_feed() || !is_user_logged_in() ) {
				return false;

			// if is post's author or user can view restricted content, always viewable
			} elseif ( $post->post_author == $user_id || user_can( $user_id, 'restrict_content' ) ) {
				return true;

			} else {

				$can_view = false;
				$user = new \WP_User( $user_id );

				// does user have role
				foreach( $roles as $role ) {
					if ( in_array($role, $user->roles ) ){
						$can_view = true;
					}
				}

				return $can_view;
			}


		// if no roles set, content is viewable by anyone 
		} else {
			return true;
		}

	}
}
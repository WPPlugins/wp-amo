<?php
namespace ArcStone\AMO;

Class MediaButton {

	private static $instance 	= null;
	private static $plugin_path = '';
	private static $plugin_web_root = '';

	private function __construct() {}

	/**
	 * Returns the running object
	 *
	 * @return Admin
	 **/
	public static function get_instance() {
		if( is_null( self::$instance ) ) {
			self::$instance = new MediaButton();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	public static function set_plugin_web_root( $path ) {
		self::$plugin_web_root = $path;
	}

	public static function set_plugin_path( $path ) {
		self::$plugin_path = $path;
	}

	public function hooks() {
        add_action( 'media_buttons', array ( $this, 'add_amo_button' ) );
        add_action( 'admin_footer', array( $this, 'footer_scripts' ) );
        add_filter( 'amo_shortcode_form', array($this, 'add_shortcodes_form_fields'), 10, 1 );
	}


    public static function footer_scripts() {
        // add the shortcodes to the javascript output
        $available_shortcodes = self::shortcodes();
        include( self::$plugin_path . 'admin/js/admin-shortcodes.js.php' );

    }

    public function add_amo_button() {
        echo '<a data-toggle="modal" data-target="#wp-amo-shortcodes-modal" title="AMO Shortcodes" href="#" class="button wp-amo-media-buttons" id="wp-amo-media-button"><img src="'.self::$plugin_web_root.'/images/icon-128x128.png"></a>';
    }


    /**
     * Add default shortcode fields
     *
     * @param $shortcode_form
     *
     * @return mixed
     */
    public function add_shortcodes_form_fields( $shortcode_form )
    {
        $shortcode_form = $this->get_default_wordpress_shortcodes( $shortcode_form );
        return $shortcode_form;
    }

    /**
     * Get the default WordPress shortcodes
     *
     * @param $shortcode_form
     *
     * @return mixed
     */
    private function get_default_wordpress_shortcodes( $shortcode_form )
    {
		$shortcode_form['amo_announcements'][] = apply_filters('amo_shortcode_form_add_select', 'filter', 'Filter: ', array('' => '', 'future' => 'Future', '90' => 'Next 90 Days', 'past' => 'Past'));
		$shortcode_form['amo_announcements'][] = apply_filters('amo_shortcode_form_add_date', 'date_start', 'Date Start: ');
        $shortcode_form['amo_announcements'][] = apply_filters('amo_shortcode_form_add_date', 'date_end', 'Date End: ');
		$shortcode_form['amo_announcements'][] = apply_filters('amo_shortcode_form_add_select', 'sort_by', 'Sort Order: ', array('asc' => 'Ascending', 'desc' => 'Descending'));
		$shortcode_form['amo_announcements'][] = apply_filters('amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');

	    $shortcode_form['amo_calendar'][] = apply_filters('amo_shortcode_form_add_text', 'event-type', 'Event Type ID: ');
	    $shortcode_form['amo_calendar'][] = apply_filters('amo_shortcode_form_add_checkbox', 'show-announcements', 'Display Announcements: ', '1');
	    $shortcode_form['amo_calendar'][] = apply_filters('amo_shortcode_form_add_checkbox', 'show-events', 'Display Events: ', '1');

		$shortcode_form['amo_classifieds'][] = apply_filters('amo_shortcode_form_add_select', 'filter', 'Filter: ', array('' => '', 'current' => 'Current', 'past' => 'Past'));
		$shortcode_form['amo_classifieds'][] = apply_filters('amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');
		$shortcode_form['amo_classifieds'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'display_member_info', 'Display Member Info' );

		$shortcode_form['amo_classifieds_button'][] = apply_filters('amo_shortcode_form_add_text', 'button_value', 'Button Value', 'Submit A Classified Ad');

		$shortcode_form['amo_committees'][] = apply_filters('amo_shortcode_form_add_text', 'pk_association_committee', 'Committee ID: ');
		$shortcode_form['amo_committees'][] = apply_filters('amo_shortcode_form_add_text', 'committee_name', 'Committee Name: ');
		$shortcode_form['amo_committees'][] = apply_filters('amo_shortcode_form_add_checkbox', 'website_display_yn', 'Display on Website: ', '1');
		$shortcode_form['amo_committees'][] = apply_filters('amo_shortcode_form_add_checkbox', 'show_description_yn', 'Show Committee Description: ', '1');
		$shortcode_form['amo_committees'][] = apply_filters('amo_shortcode_form_add_checkbox', 'show_website_link_yn', 'Show Committee Website Link: <br />(if available)', '1');
		$shortcode_form['amo_committees'][] = apply_filters('amo_shortcode_form_add_checkbox', 'show_members_yn', 'Show Committee Members: ', '1');
		$shortcode_form['amo_committees'][] = apply_filters('amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');

		$shortcode_form['amo_committee_members'][] = apply_filters('amo_shortcode_form_add_text', 'pk_association_committee', 'Committee ID: ');

		$shortcode_form['amo_donation_button'][] = apply_filters('amo_shortcode_form_add_text', 'button_value', 'Button Value', 'Make Donation');

		$shortcode_form['amo_events'][] = apply_filters('amo_shortcode_form_add_select', 'filter', 'Filter: ', array('' => '', 'future' => 'Future', '90' => 'Next 90 Days', 'past' => 'Past'));
        $shortcode_form['amo_events'][] = apply_filters('amo_shortcode_form_add_date', 'date_start', 'Date Start: ');
        $shortcode_form['amo_events'][] = apply_filters('amo_shortcode_form_add_date', 'date_end', 'Date End: ');
        $shortcode_form['amo_events'][] = apply_filters('amo_shortcode_form_add_text', 'pk_association_event_type', 'Event Type ID: ');
		$shortcode_form['amo_events'][] = apply_filters('amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');
		$shortcode_form['amo_events'][] = apply_filters('amo_shortcode_form_add_select', 'display_event_yn', 'Display Events: ', array('' => '', 'Yes' => 'Yes', 'No' => 'No'));

		$shortcode_form['amo_individual_application_button'][] = apply_filters('amo_shortcode_form_add_text', 'button_value', 'Button Value: ', 'Membership Application');

		$shortcode_form['amo_jobs'][] = apply_filters('amo_shortcode_form_add_select', 'filter', 'Filter: ', array('' => '', 'current' => 'Current', 'past' => 'Past'));
		$shortcode_form['amo_jobs'][] = apply_filters('amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');

		$shortcode_form['amo_member_center'][] = apply_filters('amo_shortcode_form_add_text', 'width', 'Width:', '100%');
		$shortcode_form['amo_member_center'][] = apply_filters('amo_shortcode_form_add_text', 'height', 'Height:', '1000');

		$shortcode_form['amo_member_center_login'][] = apply_filters('amo_shortcode_form_add_text', 'button_value', 'Button Value: ', 'Login');
		$shortcode_form['amo_member_center_login'][] = apply_filters('amo_shortcode_form_add_select', 'form_target', 'Form Target: ', array('_blank' => 'New Window', '_parent' => 'Same Window'));

		// $shortcode_form['amo_member_types'][] = apply_filters('amo_shortcode_form_add_select', 'member_type_group', 'Group: ', array('individual' => 'Individual', 'organization' => 'Organization'));
		// $shortcode_form['amo_member_types'][] = apply_filters('amo_shortcode_form_add_text', 'member_type_name', 'Member Type Name: ');
  //       $shortcode_form['amo_member_types'][] = apply_filters('amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');
		// $shortcode_form['amo_member_types'][] = apply_filters('amo_shortcode_form_add_text', 'page_number', 'Page Number To Display: ');

		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'first_name', 'First Name: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'last_name', 'Last Name: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'org_name', 'Organization Name: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'city', 'City: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'state', 'State: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'zip', 'Zip Code: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'county_name', 'County Name: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_checkbox', 'association_member_yn', 'Association Member: ', '1');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'pk_association_member_type', 'Member Type ID: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'month_offset', 'New Member Month Offset: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');
		$shortcode_form['amo_members'][] = apply_filters('amo_shortcode_form_add_text', 'pk_association_category', 'Categories (comma separated):');
		$shortcode_form['amo_members'][] = apply_filters( 'amo_shortcode_form_add_divide', '' );
		$shortcode_form['amo_members'][] = apply_filters( 'amo_shortcode_form_add_info', '<strong>Disable Output</strong><br/>' );
		$shortcode_form['amo_members'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_address', 'Address' );
		$shortcode_form['amo_members'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_city', 'City' );
		$shortcode_form['amo_members'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_state', 'State' );
		$shortcode_form['amo_members'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_zip', 'Zip' );
		$shortcode_form['amo_members'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_URL', 'URL' );
		$shortcode_form['amo_members'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_email', 'Email' );
		$shortcode_form['amo_members'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_phone', 'Phone' );

		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_text', 'pk_association_member_type', 'Member Type ID: ');
		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');
		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_divide', '' );
		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_info', '<strong>Disable Output</strong><br/>' );
		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_address', 'Address' );
		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_city', 'City' );
		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_state', 'State' );
		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_zip', 'Zip' );
		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_phone', 'Phone' );
		$shortcode_form['amo_member_directory'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_bio', 'Bio' );

		$shortcode_form['amo_member_directory_organization'][] = apply_filters('amo_shortcode_form_add_text', 'pk_association_member_type', 'Member Type ID: ');
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_divide', '' );
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_info', '<strong>Disable Output</strong><br/>' );
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_bio', 'Bio' );
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_logo', 'Logo' );
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_address', 'Address' );
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_city', 'City' );
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_state', 'State' );
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_zip', 'Zip' );
		$shortcode_form['amo_member_directory_organization'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_phone', 'Phone' );

		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_text', 'org_name', 'Organization Name: ');
		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_text', 'city', 'City: ');
		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_text', 'state', 'State: ');
		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_text', 'zip', 'Zip Code: ');
		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_text', 'county_name', 'County Name: ');
		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_checkbox', 'org_association_member_yn', 'Association Member: ', '1');
		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_text', 'pk_association_member_type', 'Member Type ID: ');
		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_text', 'month_offset', 'New Member Month Offset: ');
		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');
		$shortcode_form['amo_members_organizations'][] = apply_filters('amo_shortcode_form_add_text', 'pk_association_category', 'Categories (comma separated):');
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_divide', '' );
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_info', '<strong>Disable Output</strong><br/>' );
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_bio', 'Bio' );
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_logo', 'Logo' );
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_address', 'Address' );
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_city', 'City' );
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_state', 'State' );
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_zip', 'Zip' );
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_URL', 'URL' );
		$shortcode_form['amo_members_organizations'][] = apply_filters( 'amo_shortcode_form_add_checkbox', 'disable_phone', 'Phone' );


		$shortcode_form['amo_organization_application_button'][] = apply_filters('amo_shortcode_form_add_text', 'button_value', 'Button Value: ', 'Organization Membership Application');

		$shortcode_form['amo_random_banner'][] = apply_filters('amo_shortcode_form_add_select', 'image_alignment', 'Image Alignment: ', array('aligncenter' => 'Center', 'alignleft' => 'Left', 'alignright' => 'Right', 'alignnone' => 'None'));
		$shortcode_form['amo_random_banner'][] = apply_filters('amo_shortcode_form_add_select', 'banner_type', 'Banner Type: ', array('Content' => 'Wide Banner', 'Sidebar' => 'Sidebar Ad'));

		$shortcode_form['amo_resumes'][] = apply_filters('amo_shortcode_form_add_select', 'filter', 'Filter: ', array('' => '', 'current' => 'Current', 'past' => 'Past'));
		$shortcode_form['amo_resumes'][] = apply_filters('amo_shortcode_form_add_text', 'page_per', 'Records Per Page: ');

		$shortcode_form['amo_webpage'][] = apply_filters('amo_shortcode_form_add_text', 'pk_association_webpage', 'Webpage ID:', '');
		$shortcode_form['amo_webpage'][] = apply_filters('amo_shortcode_form_add_text', 'width', 'Width:', '100%');
		$shortcode_form['amo_webpage'][] = apply_filters('amo_shortcode_form_add_text', 'height', 'Height:', '1000');
		$shortcode_form['amo_webpage'][] = apply_filters('amo_shortcode_form_add_select', 'scrolling', 'Scrolling: ', array('yes' => 'Yes', 'no' => 'No'));

		$shortcode_form['amo_user_login_form'][] = apply_filters('amo_shortcode_form_add_text', 'after_login_url', 'After Login URL');
		$shortcode_form['amo_user_login_form'][] = apply_filters('amo_shortcode_form_add_text', 'after_logout_url', 'After Logout URL');

		$shortcode_form = apply_filters( 'wpamo_shortcode_form', $shortcode_form );

        return $shortcode_form;
    }

	public static function shortcodes() {
        // Default WordPress Shortcode
		$shortcode_tags['amo_announcements'] = 'AMO Announcements';
		$shortcode_tags['amo_calendar'] = 'AMO Calendar';
		$shortcode_tags['amo_classifieds'] = 'AMO Classifieds';
		$shortcode_tags['amo_classifieds_button'] = 'AMO Classifieds - Public Posting Button';
		$shortcode_tags['amo_committees'] = 'AMO Committees';
		$shortcode_tags['amo_committee_members'] = 'AMO Committee Members';
		$shortcode_tags['amo_donation_button'] = 'AMO Donation Button';
        $shortcode_tags['amo_events'] = 'AMO Events';
		$shortcode_tags['amo_individual_application_button'] = 'AMO Individual Member Application Button';
		$shortcode_tags['amo_jobs'] = 'AMO Job Board';
		$shortcode_tags['amo_member_center'] = 'AMO Member Center - Iframe';
		$shortcode_tags['amo_member_center_login'] = 'AMO Member Center Login';
		$shortcode_tags['amo_member_directory'] = 'AMO Member Directory - Individuals';
		$shortcode_tags['amo_member_directory_organization'] = 'AMO Member Directory - Organizations';
		// $shortcode_tags['amo_member_types'] = 'AMO Member Types';
        $shortcode_tags['amo_members'] = 'AMO Members - Individuals';
		$shortcode_tags['amo_members_organizations'] = 'AMO Members - Organizations';
		$shortcode_tags['amo_organization_application_button'] = 'AMO Organization Member Application Button';
		$shortcode_tags['amo_random_banner'] = 'AMO Random Banner Ad';
		$shortcode_tags['amo_resumes'] = 'AMO Resumes';
		$shortcode_tags['amo_webpage'] = 'AMO Webpage - Iframe';

		$shortcode_tags['amo_user_login_form'] = 'AMO User Login Form';

		$shortcode_tags = apply_filters( 'wpamo_shortcode_tags', $shortcode_tags );
		ksort( $shortcode_tags );

        return $shortcode_tags;
	}
}

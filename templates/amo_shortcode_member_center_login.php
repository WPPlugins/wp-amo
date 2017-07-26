<?php
namespace ArcStone\AMO;
//AMO MEMBER CENTER LOGIN SHORTCODE

function amo_shortcode_member_center_login( $atts ){

	$website_url = get_option( 'wpamo_website_url' );

	$amo_attribute = shortcode_atts( array(
        'amo_method' => 'AMOAssociation',
		'button_value' => 'Login',
		'form_target' => '',
    ), $atts );

	$css_classes = array();
	$results_display = '';
	$api = new API ( AMO_API_KEY );

	$results = $api->processRequest( 'AMOAssociation' );

	if(empty($results)){
		$css_classes = array( 'amo-error' );
   		$results_display = 'There Has Been An Issue';
	} else {
		$css_classes = array( 'amo-form-wrapper' );
		$results_display = '<form id="amo_member_center_login-form" action="' . AMO_FORM_URL . '/site_signin2.cfm" method="post" target="' . esc_attr( $amo_attribute['form_target'] ) . '">
				<input type="hidden" name="pk_association" value="' . esc_attr( $results[0]['pk_association'] ). '">
				<input type="hidden" name="pk_association_event" value="">
				<input type="hidden" name="pk_association_group_discussion" value="">

			
				<div class="amo-form-group">
					<label for="amo_member_center_login-username">Please Enter Your Username</label>	
					<input type="text" name="username" value="" id="amo_member_center_login-username" class="amo-form-control" maxlength="100" required="yes" message="Please Enter Your Username">
				</div>

			
				<div class="amo-form-group">
					<label for="amo_member_center_login-password">Please Enter Your Password</label>
					<input type="password" name="password" value="" id="amo_member_center_login-password" class="amo-form-control" required="required" message="Please Enter Your Password">
				</div>
				
				<button type="submit" class="btn btn-primary btn-amo">' .$amo_attribute['button_value']. '</button>
			</form>
			<a href="' . esc_url ( $website_url . '/site_forgot_password.cfm', 'http') . '" class="amo-password_reset">Reset Password</a>';

	}

	return AMODiv::do_output( $results_display, 'amo_member_center_login', $css_classes );

}

add_shortcode( 'amo_member_center_login', __NAMESPACE__ . '\\amo_shortcode_member_center_login');
?>
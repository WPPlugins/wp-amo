<?php
namespace ArcStone\AMO;
//AMO DONATION BUTTON SHORTCODE

function amo_shortcode_donation_button( $atts ){

	$amo_attribute = shortcode_atts( array(
										'button_value' => 'Make Donation',
    								), $atts );

	$css_classes = array();
	$results_display = '';

	$api = new API ( AMO_API_KEY );
	$results = $api->processRequest( 'AMOAssociation' );

	if(empty($results)){
		$css_classes = array( 'amo-error' );
	   	$results_display = 'There Has Been An Issue';
	} else {
		foreach ($results as $results) {
			$results_display .= '<form action="' . AMO_FORM_URL . '/donation.cfm" method="post" target="_blank">';
			$results_display .= '<input type="hidden" name="pk_association" value="' . esc_attr( $results['pk_association'] ) . '">';
			$results_display .= '<button type="submit" class="btn btn-primary btn-amo">' .$amo_attribute['button_value']. '</button>';
			$results_display .= '</form>';
		}
	}

	return AMODiv::do_output( $results_display, 'amo_donation_button', $css_classes );
}

	add_shortcode( 'amo_donation_button', __NAMESPACE__ . '\\amo_shortcode_donation_button' );
?>

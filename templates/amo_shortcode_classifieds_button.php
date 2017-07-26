<?php
namespace ArcStone\AMO;
//AMO CLASSIFIED - SUBMIT POSTING BUTTON SHORTCODE

function amo_shortcode_classifieds_button( $atts ){

	$amo_attribute = shortcode_atts( array(
        'amo_method' => 'AMOAssociation',
		
		'button_value' => 'Submit A Classified Ad',
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
			if ( isset( $results['classified_public_posting'] ) && $results['classified_public_posting'] == '0') {
				$results_display .= 'This Association Is Not Setup For Classified Ad Postings';
			} else {

				if ( isset( $results['classified_amount_nonmember'] ) && $results['classified_amount_nonmember'] > 0) {
					$results_display .= '<form action="' . AMO_FORM_URL . '/classified_ads_posting.cfm" method="post" target="_blank">';
				 	$results_display .= '<input type="hidden" name="pk_association" value="' .$results['pk_association']. '">';
				 	$results_display .= '<button type="submit" class="btn btn-primary btn-amo">' .$amo_attribute['button_value']. '</button>';
				 	$results_display .= '</form>';
				} else {
					$results_display .= '<form action="http://' .$results['website_url']. '/site_classified_ads_posting.cfm" method="post" target="_blank">';
					$results_display .= '<input type="hidden" name="pk_association" value="' .$results['pk_association']. '">';
					$results_display .= '<input type="hidden" name="pk_association_webpage_menu" value="0">';
					$results_display .= '<input type="hidden" name="pk_association_webpage" value="0">';
					$results_display .= '<button type="submit" class="btn btn-primary btn-amo">' .$amo_attribute['button_value']. '</button>';
					$results_display .= '</form>';
				}
			}
		}

	}

	return AMODiv::do_output( $results_display, 'amo_classifieds_button', $css_classes );

}

add_shortcode( 'amo_classifieds_button', __NAMESPACE__ . '\amo_shortcode_classifieds_button');
?>

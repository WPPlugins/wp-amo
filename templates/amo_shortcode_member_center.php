<?php
namespace ArcStone\AMO;
//AMO MEMBER CENTER SHORTCODE

function amo_shortcode_member_center( $atts ){

	$amo_attribute = shortcode_atts( array(
        'amo_method' => 'AMOAssociation',
		'amo_api_url' => (get_option('amo_api_url') == '') ? "" : get_option('amo_api_url'),
        'amo_api_key' => (get_option('amo_api_key') == '') ? "" : get_option('amo_api_key'),
		'width' => '100%',
		'height' => '1000',
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
			//$results_display .= '<iframe id="amo_member_center-iframe" src="' . esc_url( $results['website_url'] . '/site_member_home_framed.cfm?framed=1', 'http') . '" width="' . esc_attr( $amo_attribute['width'] ) . '" height="'  . esc_attr( $amo_attribute['height'] ) . '" frameborder="0" scrolling="yes"></iframe>';
			$results_display .= '<iframe id="amo_member_center-iframe" src="' . esc_url($results['member_center_url']) . '" width="' . esc_attr( $amo_attribute['width'] ) . '" height="'  . esc_attr( $amo_attribute['height'] ) . '" frameborder="0" scrolling="yes"></iframe>';
		}
	}

	return AMODiv::do_output( $results_display, 'amo_member_center', $css_classes );	
}

add_shortcode( 'amo_member_center', __NAMESPACE__ . '\\amo_shortcode_member_center' );
?>
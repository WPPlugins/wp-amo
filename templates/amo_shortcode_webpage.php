<?php
namespace ArcStone\AMO;
//AMO MEMBER CENTER SHORTCODE

function amo_shortcode_webpage( $atts ){

// 'amo_method' => 'AMOAssociation',

	$amo_attribute = shortcode_atts( array(    
		'width' => '100%',
		'height' => '1000',
		'pk_association_webpage' => '0',
		'scrolling' => 'yes',
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
			$results_display .= '<iframe id="amo_webpage-iframe" src="' . esc_url( $results['website_url'] . '/site_page_framed.cfm?pk_association_webpage=' .$amo_attribute['pk_association_webpage'], 'http' ) . '" width="' .$amo_attribute['width']. '" height="'  .$amo_attribute['height']. '" frameborder="0" scrolling="' .$amo_attribute['scrolling']. '"></iframe>';
		}
	}

	return AMODiv::do_output( $results_display, 'amo_webpage', $css_classes );
}

add_shortcode( 'amo_webpage', __NAMESPACE__ . '\\amo_shortcode_webpage');
?>
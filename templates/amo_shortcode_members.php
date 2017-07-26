<?php
namespace ArcStone\AMO;
//AMO MEMBERS - INDIVIDUALS SHORTCODE

function amo_shortcode_members( $atts ){

	$amo_attribute = shortcode_atts( array(
		'first_name' 							=> '',
		'last_name' 							=> '',
		'org_name' 								=> '',
		'city' 										=> '',
		'state' 									=> '',
		'zip' 										=> '',
		'county_name' 						=> '',
		'pk_association_member_type' => '',
		'association_member_yn' 	=> '',
		'month_offset' 						=> '',
		'page_per' 								=> '',
		'page_number' 						=> '',
		'pk_association_category'	=>	'',
		'directory_exclude_yn'		=> 0
    ), $atts );

	$css_classes = array();
	$results_display = '';
	$api = new API ( AMO_API_KEY );

	$api->setCurrentPage( get_query_var( 'page' ) );
	$results = $api->processRequest( 'AMOIndividuals', $amo_attribute );

	if(empty($results)){
		$css_classes = array( 'amo-no_results' );
   		$results_display = 'There Are No Matching Records';
	} else {
		foreach ($results as $results) {
			$results_display .= '<div class="amo_members-member amo_wrapper-item">';
			$results_display .= '<h4 class="amo-subtitle">' . $results['first_name'].' '.$results['last_name'] . '</h4>';

			if(!empty($results['org_name'])){
				$results_display .= ''.$results['org_name'].'<br />';
			}

			if ( !isset( $atts['disable_address'] ) || $atts['disable_address'] != '1' ) {
				if(!empty($results['address'])){
					$results_display .= ''.$results['address'].'';
				}
				if(!empty($results['address2'])){
					$results_display .= ', '.$results['address2'].'';
				}
				$results_display .= '<br />';
			}

			if( !empty($results['city']) && ( !isset( $atts['disable_city'] ) || $atts['disable_city'] != '1' ) ){
				$results_display .= ''.$results['city'].', ';
			}
			if(!empty($results['state']) && ( !isset( $atts['disable_state'] ) || $atts['disable_state'] != '1' ) ){
				$results_display .= ''.$results['state'].' ';
			}
			if(!empty($results['zip_int']) && ( !isset( $atts['disable_zip'] ) || $atts['disable_zip'] != '1' ) ){
				$results_display .= ''.$results['zip_int'];
			}

			if ( !isset( $atts['disable_phone']) || $atts['disable_phone'] != '1' ) {
				$results_display .= '<br/>';
				if(!empty($results['phone']) and !empty($results['phone_ext_int']) and !empty($results['phone_mobile'])){
					$results_display .= ''.$results['phone'].' Ext: '.$results['phone_ext_int'].' | '.$results['phone_mobile'].' - Mobile';
				} elseif(!empty($results['phone']) and !empty($results['phone_ext_int']) and empty($results['phone_mobile'])){
					$results_display .= ''.$results['phone'].' Ext: '.$results['phone_ext_int'];
				} elseif(!empty($results['phone']) and empty($results['phone_ext_int']) and !empty($results['phone_mobile'])){
					$results_display .= ''.$results['phone'].' | '.$results['phone_mobile'].' - Mobile';
				} elseif(!empty($results['phone']) and empty($results['phone_ext_int']) and empty($results['phone_mobile'])){
					$results_display .= ''.$results['phone'];
				} elseif(empty($results['phone']) and empty($results['phone_ext_int']) and !empty($results['phone_mobile'])){
					$results_display .= ''.$results['phone_mobile'].' - Mobile';
				}
			}

			if(!empty($results['email']) && ( !isset($atts['disable_email']) || $atts['disable_email'] != '1') ){
				$results_display .= '<br/><a href="mailto:' . esc_attr( $results['email'] ) .'">'.$results['email'].'</a>';
			}

			if(!empty($results['website']) && ( !isset($atts['disable_url']) || $atts['disable_url'] != '1' )){
				$results_display .= '<br/><a href="' . esc_url( $results['website'], 'http' ) . '" target="_blank">'.$results['website'].'</a>';
			}


			$results_display .= '</div>';
		}

		if ( $api->paginated === true ) {
			$results_display .= AMODiv::paginate_links( $api->current_page, $api->per_page, $api->total_results );
		}
	}

	return AMODiv::do_output( $results_display, 'amo_members', $css_classes );

}

add_shortcode( 'amo_members', __NAMESPACE__ . '\\amo_shortcode_members');

?>

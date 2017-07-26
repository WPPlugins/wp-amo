<?php
//AMO MEMBERS SHORTCODE
namespace ArcStone\AMO;
function amo_shortcode_committee_members( $atts ){

	$amo_attribute = shortcode_atts( array(
		'pk_association_committee' => '',
    ), $atts );

	$this_pk_association_committee = $amo_attribute['pk_association_committee'] ?: filter_var( trim($_GET['id']), FILTER_SANITIZE_NUMBER_INT);

	if (empty($this_pk_association_committee)) {
		if (!$this_pk_association_committee = $amo_attribute['pk_association_committee']) {
        	$css_classes = array( 'amo-no_results' );
	        $results_display = '<hr>Cannot Retrieve Committee Members - No Committee Was Specified<hr>';
    	    return AMODiv::do_output( $results_display, 'amo_committees', $css_classes );
    	}
	}

    $results_display = '';
    $css_classes = [];

    $api = new API ( AMO_API_KEY );
	$api->setCurrentPage( get_query_var('page') );
    $api_results = $api->processRequest( 'AMOCommitteeMembers/' . $this_pk_association_committee );

    if(empty($api_results)){
        $css_classes = array( 'amo-no_results' );
        $results_display = 'There Are Currently No Committee Members<br><br><a href="" onclick="history.go(-1);">Back</a>';
    } else {
        foreach ($api_results as $results) {
            $results_display .= '<div class="amo_committees-member amo_wrapper-item">';
            $results_display .= '<h4 class="amo-subtitle"> ' . $results['first_name'].' '.$results['last_name'].'</h4>';

            if(!empty($results['org_name'])){
                $results_display .= ''.$results['org_name'].'<br />';
            }
            if(!empty($results['address'])){
                $results_display .= ''.$results['address'].'';
            }
            if(!empty($results['address2'])){
                $results_display .= ', '.$results['address2'].'';
            }
            $results_display .= '<br />';

            if(!empty($results['city'])){
                $results_display .= ''.$results['city'].', ';
            }
            if(!empty($results['state'])){
                $results_display .= ''.$results['state'].' ';
            }
            if(!empty($results['zip_int'])){
                $results_display .= ''.$results['zip_int'].'';
            }
            if(!empty($results['city']) || !empty($results['state']) || !empty($results['zip_int'])) {
                $results_display .= '<br />';
            }

            if(!empty($results['phone']) && !empty($results['phone_ext_int']) && !empty($results['phone_mobile'])){
                $results_display .= ''.$results['phone'].' Ext: '.$results['phone_ext_int'].' | '.$results['phone_mobile'].' - Mobile <br />';
            }elseif( !empty($results['phone']) && !empty($results['phone_ext_int']) && empty($results['phone_mobile'])){
                $results_display .= ''.$results['phone'].' Ext: '.$results['phone_ext_int'].'<br />';
            }elseif(!empty($results['phone']) and empty($results['phone_ext_int']) and !empty($results['phone_mobile'])){
                $results_display .= ''.$results['phone'].' | '.$results['phone_mobile'].' - Mobile <br />';
            }elseif(!empty($results['phone']) and empty($results['phone_ext_int']) and empty($results['phone_mobile'])){
                $results_display .= ''.$results['phone'].'<br />';
            }elseif(empty($results['phone']) and empty($results['phone_ext_int']) and !empty($results['phone_mobile'])){
                $results_display .= ''.$results['phone_mobile'].' - Mobile <br />';
            }

            if(!empty($results['email'])){
                $results_display .= '<a href="mailto:' . esc_attr( $results['email'] ) . '">'.$results['email'].'</a><br />';
            }

            if(!empty($results['website'])){
                $results_display .= '<a href="' . esc_url( $results['website'], 'http' ) . '" target="_blank">'.$results['website'].'</a>';
            }

            $results_display .= '</div>';
        }

        $results_display .= '<a href="" onclick="history.go(-1);">Back</a>';
    }

    return AMODiv::do_output($results_display, 'amo_committees', $css_classes);
}

add_shortcode( 'amo_committee_members', __NAMESPACE__ . '\\amo_shortcode_committee_members' );

?>

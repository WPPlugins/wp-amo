<?php
namespace ArcStone\AMO;
//AMO MEMBER TYPES SHORTCODE

function amo_shortcode_comittees( $atts ){

	$amo_attribute = shortcode_atts( array(
		'committee_name' => '',
		'website_display_yn' => '',
		'page_per' => '',
		'page_number' => '',
		'show_description_yn' => '',
		'show_website_link_yn' => '',
		'show_members_yn' => '',
    ), $atts );

 	$this_pk_association_committee = ( isset( $_GET['id'] ) ) ? filter_var( trim($_GET['id']), FILTER_SANITIZE_NUMBER_INT) : '';

 	$css_classes = array();
	$results_display = '';
	$api = new API ( AMO_API_KEY );

	// results list
	if(empty($this_pk_association_committee)){	

		$api->setCurrentPage( get_query_var('page') );

		$amo_attribute = array_merge( array( 'pk_association_committee' => 0 ), $amo_attribute );

		$results = $api->processRequest( 'AMOCommittees', $amo_attribute );

		if(empty($results)){
			$css_classes = array( 'amo-no_results' );
			$results_display = 'There Are Currently No Committees Designated For This Association';
		} else {
			foreach ($results as $results) {
				$results_display .= '<div class="amo_committees-committee amo_wrapper-item">';
				$results_display .= '<h4 class="amo-subtitle">' .$results['committee_name']. '</h4>';	

				if(!empty($amo_attribute['show_description_yn']) && !empty($results['committee_desc']) ){
					$results_display .= $results['committee_desc'].'';
				}
				
				if(!empty($amo_attribute['show_website_link_yn']) && !empty($results['committee_link'])){
					$results_display .= '<br /><a href="'. esc_url( $results['committee_link'], 'http' ) .'" target="_blank">Committee Website</a>';
				}
				
				if(!empty($amo_attribute['show_members_yn'])){
					$results_display .= '<br /><a href="?id=' . esc_attr( $results['pk_association_committee'] ) . '">View Committee Members</a>';
				}
				$results_display .= '</div>';
			}

			if ( $api->paginated === true ) {
				$results_display .= AMODiv::paginate_links( $api->current_page, $api->per_page, $api->total_results );	
			}	
		}

        return AMODiv::do_output( $results_display, 'amo_committees', $css_classes );

        // individual entry
	} else {

	    return do_shortcode('[amo_committee_members pk_association_committee="'.$this_pk_association_committee.'"][/amo_committee_members]');

	}

}

add_shortcode( 'amo_committees', __NAMESPACE__ . '\\amo_shortcode_comittees' );
?>
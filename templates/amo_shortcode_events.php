<?php
namespace ArcStone\AMO;
//AMO EVENTS SHORTCODE

function amo_shortcode_events( $atts ){

	$amo_attribute = shortcode_atts( array(
		'date_start' => '',
		'date_end' => '',
		'filter' => '',
		'pk_association_event_type' => '',
		'display_event_yn' => '',
		'page_per' => '',
		'page_number' => '',
    ), $atts );

	if( isset( $amo_attribute['display_event_yn'] ) && $amo_attribute['display_event_yn']  == 'Yes' ){
		$this_display_event_yn = '1';
	} elseif ( isset( $amo_attribute['display_event_yn'] ) && $amo_attribute['display_event_yn'] == 'No'){
		$this_display_event_yn = '0';
	} else {
		$this_display_event_yn = $amo_attribute['display_event_yn'];
	}

	$css_classes = array();
	$results_display = '';
	$api = new API ( AMO_API_KEY );

	$api->setCurrentPage( get_query_var( 'page' ) );
	$results = $api->processRequest( 'AMOEvents', $amo_attribute );


	if(empty($results)){
		$css_classes = array( 'amo-no_results' );
	   	$results_display = 'There Are Currently No Events';
	} else {
		foreach ($results as $results) {

			$old_start_time = strtotime($results['date_start']);
			$new_start_time = date("F j, Y, g:i a", $old_start_time);

			if (strpos($new_start_time, ', 12:00 am') !== false) {
    			$new_start_time_final = substr($new_start_time, 0, -10);
			}else{
				$new_start_time_final = $new_start_time;
			}

			$old_end_time = strtotime($results['date_end']);
			$new_end_time = date("F j, Y, g:i a", $old_end_time);

			if (strpos($new_end_time, ', 12:00 am') !== false) {
    			$new_end_time_final = substr($new_end_time, 0, -10);
			}else{
				$new_end_time_final = $new_end_time;
			}

			// Looped output - only show the From/To if the end datetime is different than the start.
			if ($new_start_time_final === $new_end_time_final){
			$results_display .= '
							<div class="amo_events-event amo_wrapper-item">'.
								'<h4 class="amo-subtitle">' . $results['event_name'] . '</h4>' .
								$new_start_time_final . '<br />'.
								'<a href="' . esc_url( $results['registration_link'], 'http' ) . '" target="_blank">Register Now</a>'.
							'</div>';
			}else{
				$results_display .= '
							<div class="amo_events-event amo_wrapper-item">'.
								'<h4 class="amo-subtitle">' . $results['event_name'] . '</h4>' .
								'From ' . $new_start_time_final . ' to ' . $new_end_time_final . '<br />'.
								'<a href="' . esc_url( $results['registration_link'], 'http' ) . '" target="_blank">Register Now</a>'.
							'</div>';
			}
		}

		if ( $api->paginated === true ) {
			$results_display .= AMODiv::paginate_links( $api->current_page, $api->per_page, $api->total_results );	
		}
	}

	return AMODiv::do_output( $results_display, 'amo_events', $css_classes );

}

add_shortcode( 'amo_events', __NAMESPACE__ . '\\amo_shortcode_events' );
?>
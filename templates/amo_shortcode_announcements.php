<?php
namespace ArcStone\AMO;
//AMO ANNOUNCEMENTS SHORTCODE

function amo_shortcode_announcements( $atts ){

	$amo_attribute = shortcode_atts( array(
		'announcement_date_start' => '',
		'announcement_date_end' => '',
    ), $atts );

	$pk_association_announcement = ( isset( $_GET['id'] ) ) ? filter_var( trim( $_GET['id'] ), FILTER_VALIDATE_INT) : '';

	$results_display = '';
	$css_classes = array();
	$api = new API( AMO_API_KEY );	
	

	if( empty($pk_association_announcement) ) {

		$api->setCurrentPage( get_query_var('page') );
		$results = $api->processRequest( 'AMOAnnouncements', $amo_attribute );
		
		if(empty($results)){
			$css_classes = array( 'amo-no_results' );
		   	$results_display = 'There Are Currently No Announcements';
		} else {
			foreach ($results as $results) {

				$date_start_output = strtotime($results['announcement_date_start']);
				$new_start_time = date("F j, Y, g:i a", $date_start_output);

				if (strpos($new_start_time, ', 12:00 am') !== false) {
	    			$new_start_time_final = substr($new_start_time, 0, -10);
				}else{
					$new_start_time_final = $new_start_time;
				}


				$date_end_output = strtotime($results['announcement_date_end']); 
				$new_end_time = date("F j, Y, g:i a", $date_end_output);

				if (strpos($new_end_time, ', 12:00 am') !== false) {
    				$new_end_time_final = substr($new_end_time, 0, -10);
				}else{
					$new_end_time_final = $new_end_time;
				}

				$results_display .= '<div class="amo_events-event amo_wrapper-item">'.'<h4 class="amo-subtitle">' .$results['announcement_title']. '</h4>';
								
								if( !empty($date_end_output)) {
									$results_display .= 'From ' . $new_start_time_final . ' to ' . $new_end_time_final . '<br />';
								}else{
									$results_display .= $new_start_time_final . '<br />';
								}

							$results_display .= '<a href="?id=' .$results['pk_association_announcement']. '">Click for Details</a>'.
							'</div>';
			}

			if ( $api->paginated === true ) {
			$results_display .= AMODiv::paginate_links( $api->current_page, $api->per_page, $api->total_results );	
		}

		}


		

	} else {
		$results = $api->processRequest( 'AMOAnnouncements/' . $pk_association_announcement, $atts );

		if( empty($results) ){
				$css_classes = array( 'amo-no_results' );
			   	$results_display = 'There Are Currently No Announcement';
		} else {
			foreach ($results as $results) {
				$date_start_output = $results['announcement_date_start'];
				$date_end_output = $results['announcement_date_end'];
				
				$results_display .= '<div class="amo_events-event amo_wrapper-item">'.'<h4 class="amo-subtitle">' .$results['announcement_title']. '</h4>';

				if( !empty($date_end_output)) {
						$results_display .= 'From ' . substr($date_start_output, 0, -9). ' to ' . substr($date_end_output, 0, -9) . '<br />';
				}else{
						$results_display .= substr($date_start_output, 0, -9) . '<br />';
				}


				$results_display .= '<br />' . $results['announcement_description'];
				$results_display .= '<br /><a href="" onclick="history.go(-1);">Back to Announcements</a></div>';
				
			}


		}
	}

	return AMODiv::do_output( $results_display, 'amo_announcements', $css_classes );
}

add_shortcode( 'amo_announcements', __NAMESPACE__ . '\\amo_shortcode_announcements');

?>

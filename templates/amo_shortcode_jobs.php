<?php
namespace ArcStone\AMO;
//AMO JOBS SHORTCODE

function amo_shortcode_jobs( $atts ){

	
	$amo_attribute = shortcode_atts( array(
		'filter' => '',
		'page_per' => '',
		'page_number' => '',
    ), $atts );

	$this_pk_association_job = ( isset( $_GET['id'] ) ) ? filter_var( trim($_GET['id']), FILTER_SANITIZE_NUMBER_INT ) : '';

	$css_classes = array();
	$results_display = '';
	$api = new API ( AMO_API_KEY );

	if(empty($this_pk_association_job)){

		$api->setCurrentPage( get_query_var( 'page' ) );
		$results = $api->processRequest( 'AMOJobs', $amo_attribute );
	
		if(empty($results)){
			$css_classes = array('amo-no_results');
	   		$results_display = 'There Are Currently No Jobs';
		} else {
			foreach ($results as $results) {
				$results_display .= '<div class="amo_jobs-job amo_wrapper-item">
										<h4 class="amo-subtitle">' .$results['position']. '</h4>';
										
										$results_display .= $results['job_company'];
										if(!empty($results['job_city'])){
											$results_display .= ' - '.$results['job_city'].'';
										}
										if(!empty($results['job_state'])){
											$results_display .= ', '.$results['job_state'].'';
										}
					
									$results_display .= '<br />Posted: ' .date("m-d-Y", strtotime($results['date_posted']));
									$results_display .= '<br /><a href="?id=' . esc_attr( $results['pk_association_job'] ). '">View Details</a>';
				$results_display .= '</div>';
			}

			if ( $api->paginated === true ) {
				$results_display .= AMODiv::paginate_links( $api->current_page, $api->per_page, $api->total_results );	
			}
		}

	} else{

		$results = $api->processRequest( 'AMOJob/' . $this_pk_association_job );


		if(empty($results)){
			$results_display = '<hr>This Job is Currently Empty<br><br><a href="" onclick="history.go(-1);">Back</a>';
		} else {
			foreach ($results as $results) {
				$results_display .= '<div class="amo_jobs-job">
										<h2 class="amo-title">Job Details</h2>
										<p><span class="amo-label">Position</span>:<br />' .$results['position']. '</p>
										<p><span class="amo-label">Company</span>:<br />' .$results['job_company']. '';
									
										if(!empty($results['job_city'])){
											$results_display .= ' - '.$results['job_city'].'';
										}
										if(!empty($results['job_state'])){
											$results_display .= ', '.$results['job_state'].' ';
										}
										if(!empty($results['job_country'])){
											$results_display .= ', '.$results['job_country'].'';
										}
										$results_display .= '</p>';
								    
								    	if(!empty($results['job_custom_field_label_1']) and !empty($results['job_custom_field_type_1']) and !empty($results['job_custom_field_display_yn_1'])){
								    		$results_display .= '<p><span class="amo-label">'.$results['job_custom_field_label_1'].'</span>:<br />'.$results['custom_field_value_1']. '</p>';
								    	}
										if(!empty($results['job_custom_field_label_2']) and !empty($results['job_custom_field_type_2']) and !empty($results['job_custom_field_display_yn_2'])){
											$results_display .= '<p><span class="amo-label">'.$results['job_custom_field_label_2'].'</span>:<br />'.$results['custom_field_value_2']. '</p>';
										}
										if(!empty($results['job_custom_field_label_3']) and !empty($results['job_custom_field_type_3']) and !empty($results['job_custom_field_display_yn_3'])){
											$results_display .= '<p><span class="amo-label">'.$results['job_custom_field_label_3'].'</span>:<br />'.$results['custom_field_value_3']. '</p>';
										}
										
										if(!empty($results['job_custom_field_label_4']) and !empty($results['job_custom_field_type_4']) and !empty($results['job_custom_field_display_yn_4'])){
											$results_display .= '<p><span class="amo-label">'.$results['job_custom_field_label_4'].'</span>:<br />'.$results['custom_field_value_4']. '</p>';
										}
										
										if(!empty($results['job_custom_field_label_5']) and !empty($results['job_custom_field_type_5']) and !empty($results['job_custom_field_display_yn_5'])){
											$results_display .= '<p><span class="amo-label">'.$results['job_custom_field_label_5'].'</span>:<br />'.$results['custom_field_value_5']. '</p>';
										}

										$results_display .= '<hr><span class="amo-label">Job Description</span>:<br />' .$results['job_description'];
										$results_display .= '<hr><h4 class="amo-subtitle">Contact Information</h4>';
									
										if(!empty($results['contact_name'])){
											$results_display .= ''.$results['contact_name'].'<br />';
										}
									
										if(!empty($results['contact_address1'])){
											$results_display .= ''.$results['contact_address1'].'<br />';
										}
										
										if(!empty($results['contact_address2'])){
											$results_display .= ''.$results['contact_address2'].'<br />';
										}
									
										if(!empty($results['contact_city'])){
											$results_display .= ''.$results['contact_city'].'';
										}
										
										if(!empty($results['contact_state'])){
											$results_display .= ', '.$results['contact_state'].' ';
										}
										
										if(!empty($results['contact_zip'])){
											$results_display .= ''.$results['contact_zip'].'';
										}
										
										if(!empty($results['contact_country'])){
											$results_display .= ', '.$results['contact_country'].'';
										}
										if(!empty($results['city']) or !empty($results['state']) or !empty($results['zip_int']) or !empty($results['contact_country'])) {
											$results_display .= '<br />';
										}

										$results_display .= 'Phone: ';
										if(!empty($results['contact_phone'])){
											$results_display .= ''.$results['contact_phone'].'<br />';}else{$results_display .= 'NA<br />';
										}
										
										$results_display .= 'Fax: ';
										if(!empty($results['contact_fax'])){
											$results_display .= ''.$results['contact_fax'].'<br />';
										}else{
											$results_display .= 'NA<br />';
										}
										if(!empty($results['contact_email'])){
											$results_display .= '<a href="mailto:' . esc_attr( $results['contact_email'] ) . '">' .$results['contact_email']. '</a>';
										}

				$results_display .= '</div>';
			}
			
			$results_display .= '<hr><a href="" onclick="history.go(-1);">Back</a>';

		}

	}

	return AMODiv::do_output( $results_display, 'amo_jobs', $css_classes );

}

add_shortcode( 'amo_jobs', __NAMESPACE__ . '\\amo_shortcode_jobs');

?>

<?php
namespace ArcStone\AMO;
//AMO RESUMES SHORTCODE

function amo_shortcode_resumes( $atts ){

  //       'amo_method' => 'AMOResumes',
		// 'amo_method_details' => 'AMOResume',

	$amo_attribute = shortcode_atts( array(
		'filter' => '',
		'page_per' => '',
		'page_number' => '',
    ), $atts );

	$this_pk_association_resume = ( isset( $_GET['id'] ) ) ? filter_var( trim($_GET['id']), FILTER_SANITIZE_NUMBER_INT ) : '';

	$css_classes = array();
	$results_display = '';
	$api = new API ( AMO_API_KEY );

	if(empty($this_pk_association_resume)){

		$api->setCurrentPage( get_query_var( 'page' ) );	
		$results = $api->processRequest( 'AMOResumes', $amo_attribute );

		if(empty($results)){
			$css_classes = array( 'amo-no_results' );
	   		$results_display = 'There Are Currently No Resumes';
		} else {
			foreach ($results as $results) {
				$results_display .= '<div class="amo_resumes-resume amo_wrapper-item">
										<h4 class="amo-subtitle">' .$results['resume_title']. '</h4>'.
										$results['resume_job_title'] . 
										'<br />Posted: ' .date("m-d-Y", strtotime($results['date_posted'])) .
										'<br /><a href="?id=' . esc_attr( $results['pk_association_resume'] ) . '">View Details</a>
									</div>';
			}

			if ( $api->paginated === true ) {
				$results_display .= AMODiv::paginate_links( $api->current_page, $api->per_page, $api->total_results );	
			}

		}

	} else {


		$results = $api->processRequest( 'AMOResume/' . $this_pk_association_resume );

		if(empty($results)){
			$css_classes = array( 'amo-no_results' );
		   	$results_display = 'This Resumes is Currently Empty<br><br><a href="" onclick="history.go(-1);">Back</a>';
		} else {
			foreach ($results as $results) {
				$results_display .= '<h4 class="amo-subtitle">Resume Details</h4>';
				$results_display .= '<p><span class="amo-label">Resume Title:</span>' .$results['resume_title']. '</p>';
				$results_display .= '<p><span class="amo-label">Desired Job Title:</span>' .$results['resume_job_title']. '</p>';
			    if(!empty($results['resume_custom_field_label_1']) and !empty($results['resume_custom_field_type_1']) and !empty($results['resume_custom_field_display_yn_1'])){
			    	$results_display .= '<p><span class="amo-label">'.$results['resume_custom_field_label_1'].':</span> '.$results['custom_field_value_1']. '</p>';
			    }
				if(!empty($results['resume_custom_field_label_2']) and !empty($results['resume_custom_field_type_2']) and !empty($results['resume_custom_field_display_yn_2'])){
					$results_display .= '<p><span class="amo-label">'.$results['resume_custom_field_label_2'].':</span> '.$results['custom_field_value_2']. '</p>';
				}
				if(!empty($results['resume_custom_field_label_3']) and !empty($results['resume_custom_field_type_3']) and !empty($results['resume_custom_field_display_yn_3'])){
					$results_display .= '<p><span class="amo-label">'.$results['resume_custom_field_label_3'].':</span> '.$results['custom_field_value_3']. '</p>';
				}
				if(!empty($results['resume_custom_field_label_4']) and !empty($results['resume_custom_field_type_4']) and !empty($results['resume_custom_field_display_yn_4'])){
					$results_display .= '<p><span class="amo-label">'.$results['resume_custom_field_label_4'].':</span> '.$results['custom_field_value_4']. '</p>';
				}
				if(!empty($results['resume_custom_field_label_5']) and !empty($results['resume_custom_field_type_5']) and !empty($results['resume_custom_field_display_yn_5'])){
					$results_display .= '<p><span class="amo-label">'.$results['resume_custom_field_label_5'].':</span> '.$results['custom_field_value_5']. '</p>';
				}

				$results_display .= '<hr><span class="amo-label">Resume Description:</span> ' .$results['resume_description']. '';
				$results_display .= '<hr><span class="amo-label">Contact Information:</span> ';
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
					$results_display .= ''.$results['contact_phone'].'<br />';
				}else{
					$results_display .= 'NA<br />';
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
			}
			
			$results_display .= '<hr><a href="" onclick="history.go(-1);">Back</a>';

		}

	}

	return AMODiv::do_output( $results_display, 'amo_resumes', $css_classes );

}

add_shortcode( 'amo_resumes', __NAMESPACE__ . '\\amo_shortcode_resumes');

?>
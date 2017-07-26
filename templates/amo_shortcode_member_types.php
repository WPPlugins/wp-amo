<?php
//AMO MEMBER TYPES SHORTCODE

function amo_shortcode_member_types( $atts ){

$amo_attribute = shortcode_atts( array(
        'amo_method' => 'AMOMemberTypes',
		'amo_api_url' => (get_option('amo_api_url') == '') ? "" : get_option('amo_api_url'),
        'amo_api_key' => (get_option('amo_api_key') == '') ? "" : get_option('amo_api_key'),
		'member_type_name' => '',
		'member_type_group' => '',
		'page_per' => '',
		'page_number' => '',
    ), $atts );

$results_api = "{$amo_attribute['amo_api_url']}/api/index.cfm/{$amo_attribute['amo_method']}?apikey={$amo_attribute['amo_api_key']}&member_type_name={$amo_attribute['member_type_name']}&member_type_group={$amo_attribute['member_type_group']}&page_per={$amo_attribute['page_per']}&page_number={$amo_attribute['page_number']}";

$ch = curl_init($results_api);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	//curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$results = curl_exec($ch);

	if (!$results) {
    	var_dump(curl_error($ch));
	}

$results = json_decode($results, true);

if(empty($results)){
   return '<hr>There Are No Matching Records<hr>';
}
else {
	foreach ($results as $results)
	{
	$results_display .= '<hr>' .$results['pk_association_member_type']. ' - ' .$results['member_type_name'].'';
	}

	 $results_display .= '<hr>';

	 return "{$results_display}";
	}

}

add_shortcode( 'amo_member_types','amo_shortcode_member_types');

//AMO MEMBER TYPES DDL SHORTCODE

function amo_shortcode_member_types_ddl( $atts ){

$amo_attribute = shortcode_atts( array(
        'amo_method' => 'AMOMemberTypes',
		'amo_api_url' => (get_option('amo_api_url') == '') ? "" : get_option('amo_api_url'),
        'amo_api_key' => (get_option('amo_api_key') == '') ? "" : get_option('amo_api_key'),
		'member_type_name' => '',
		'member_type_group' => '',
		'page_per' => '',
		'page_number' => '',
    ), $atts );

$results_api = "{$amo_attribute['amo_api_url']}/api/index.cfm/{$amo_attribute['amo_method']}?apikey={$amo_attribute['amo_api_key']}&member_type_name={$amo_attribute['member_type_name']}&member_type_group={$amo_attribute['member_type_group']}&page_per={$amo_attribute['page_per']}&page_number={$amo_attribute['page_number']}";

$results = file_get_contents($results_api);
$results = json_decode($results, true);

if(empty($results)){
   return '';
}
else {
	foreach ($results as $results)
	{
	$results_display .= '<option value="' .$results['pk_association_member_type']. '">' .$results['member_type_name'].'</option>';
	}

	 return "{$results_display}";
	}

}

add_shortcode( 'amo_member_types_ddl','amo_shortcode_member_types_ddl');

?>

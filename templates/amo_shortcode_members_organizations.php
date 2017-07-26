<?php
namespace ArcStone\AMO;
//AMO MEMBERS - ORGANIZATION SHORTCODE

function amo_shortcode_members_organizations( $atts ){

	$amo_attribute = shortcode_atts( array(
		'org_name' => '',
		'city' => '',
		'state' => '',
		'zip' => '',
		'county_name' => '',
		'pk_association_member_type' => '',
		'org_association_member_yn' => '',
		'month_offset' => '',
		'page_per' => '',
		'page_number' => '',
		'pk_association_category'	=>	'',
    ), $atts );

	$css_classes = array();
	$results_display = '';
	$api = new API ( AMO_API_KEY );

	$api->setCurrentPage( get_query_var( 'page' ) );
	$results = $api->processRequest( 'AMOOrganizations', $amo_attribute );

	if(empty($results)){
		$css_classes = array( 'amo-no_results' );
   		$results_display = 'There Are No Matching Records';
	} else {
		foreach ($results as $results): 

			ob_start();
			?>

			<div class="amo_members_organizations-member amo_wrapper-item">

				<?php if ( !isset( $atts['disable_logo'] ) ): ?>
					<div class="amo-profile-thumb">
					<?php if ( !empty( $results['org_image_thumbnail'] ) ): ?>
							<img src="<?php echo $results['org_image_thumbnail'] ?>" alt="<?php echo $results['org_name'] ?>"> 
					<?php endif; ?>
					</div>
				<?php endif; ?>
				<ul>
					<li class="amo-profile-name"><h4 class="amo-subtitle"><?php echo $results['org_name']; ?></h4></li>

					<?php if ( !isset( $atts['disable_bio'] ) && !empty( $results['org_bio'] )): ?>
						<li class="amo-profile-bio"><?php echo $results['org_bio'] ?><hr></li>
					<?php endif; ?>

						<?php if ( !isset( $atts['disable_address'] ) || $atts['disable_address'] != '1' ): ?>

							<li class="amo-profile-street">

							<?php 
							if(!empty($results['org_street_address'])){
								echo $results['org_street_address'];
							}
							if(!empty($results['org_street_address2'])){
								echo ', '.$results['org_street_address2'];
							}
							?>
							</li>
						<?php 
						endif; 
						
						if ( !empty($results['org_street_city']) || !empty($results['org_street_state']) || !empty($results['org_street_zip_int']) ): ?>
							<li class="amo-profile-city">
							<?php 
							if(!empty($results['org_street_city'])  && ( !isset( $atts['disable_city'] ) || $atts['disable_city'] != '1' ) ){
								echo $results['org_street_city'].', ';
							}
							if(!empty($results['org_street_state'])  && ( !isset( $atts['disable_state'] ) || $atts['disable_state'] != '1' ) ){
								echo $results['org_street_state'].' ';
							}
							if(!empty($results['org_street_zip_int']) && ( !isset( $atts['disable_zip'] ) || $atts['disable_zip'] != '1' ) ){
								echo $results['org_street_zip_int'];
							}	
							?>
							</li>
						<?php 
						endif;

						if ( !isset( $atts['disable_phone']) || $atts['disable_phone'] != '1' ): ?>
							<li class="amo-profile-phone">
							<?php
							if(!empty($results['org_phone']) and !empty($results['org_tollfree'])){
								echo $results['org_phone'].' | ' .$results['org_tollfree'];
							} elseif(!empty($results['org_phone']) and empty($results['org_tollfree'])){
								echo $results['org_phone'];
							}elseif(empty($results['org_phone']) and !empty($results['org_tollfree'])){
								echo $results['org_tollfree'];
							}
							?>
							</li>
						<?php endif;

						if(!empty($results['org_website'])  && ( !isset($atts['disable_url']) || $atts['disable_url'] != '1' ) ) {
							echo '<li class="amo-profile-website"><a href="'. esc_url( $results['org_website'], 'http' ) . '" target="_blank">'.$results['org_website'].'</a></li>';
						}
						?>

					</ul>
			</div>
			<?php
			$results_display .= ob_get_clean();
		endforeach;



		if ( $api->paginated === true ) {
			$results_display .= AMODiv::paginate_links( $api->current_page, $api->per_page, $api->total_results );	
		}
		
	}

	return AMODiv::do_output( $results_display, 'amo_members_organizations', $css_classes );
}

add_shortcode( 'amo_members_organizations', __NAMESPACE__ . '\\amo_shortcode_members_organizations');
?>
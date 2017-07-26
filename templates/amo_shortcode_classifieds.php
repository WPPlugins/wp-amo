<?php

namespace ArcStone\AMO;

//AMO CLASSIFIEDS SHORTCODE

function amo_shortcode_classifieds( $atts ) {

    $amo_attribute = shortcode_atts( array(
        'filter'              => '',
        'page_per'            => '',
        'page_number'         => '',
        'display_member_info' => ''
    ), $atts );

    // sanitize!
    $this_pk_association_classified          = ( isset( $_GET['id'] ) ) ?
        filter_var( trim( $_GET['id'] ), FILTER_SANITIZE_NUMBER_INT ) : '';
    $this_pk_association_classified_category = ( isset( $_REQUEST['pk_association_classified_category'] ) ) ?
        filter_var( trim( $_REQUEST['pk_association_classified_category'] ), FILTER_SANITIZE_URL ) : '';
    $this_keyword                            = ( isset( $_REQUEST['keyword'] ) ) ?
        filter_var( trim( $_REQUEST['keyword'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : '';
    $this_passed_search                      = ( isset( $_REQUEST['search'] ) ) ?
        filter_var( trim( $_REQUEST['search'] ), FILTER_SANITIZE_NUMBER_INT ) : '';

    $css_classes     = array();
    $results_display = '';
    $api             = new API ( AMO_API_KEY );

    // Display an individual result
    if ( ! empty( $this_pk_association_classified ) ) {
        $results = $api->processRequest( 'AMOClassified/' . $this_pk_association_classified );

        if ( empty( $results ) ) {
            $css_classes     = array( 'amo-no_results' );
            $results_display = 'This Classified Ads is Currently Empty.<br><br><a href="" class="amo-link-back" onclick="history.go(-1);">Back</a>';
        } else {
            foreach ( $results as $results ) {
                $results_display .= '<h2 class="amo-title">Classified Ad Details</h2><hr>';
                $results_display .= '<p><span class="amo-classifieds-title">' . $results['ad_title'] . '</span></p>';
                $results_display .= '<p class="amo-classifieds-description">' . $results['ad_description'] . '</p>';
                $results_display .= '<hr><div id="amo-classifieds-contact"<h4 class="amo-subtitle">Contact Information:</h4>';

                if ( ! empty( $results['contact_name'] ) ) {
                    $results_display .= '' . $results['contact_name'] . '<br />';
                }

                if ( $amo_attribute['display_member_info'] === '1' ) {
                    if ( ! empty( $results['contact_address1'] ) ) {
                        $results_display .= '' . $results['contact_address1'] . '<br />';
                    }

                    if ( ! empty( $results['contact_address2'] ) ) {
                        $results_display .= '' . $results['contact_address2'] . '<br />';
                    }

                    if ( ! empty( $results['contact_city'] ) ) {
                        $results_display .= '' . $results['contact_city'] . '';
                    }

                    if ( ! empty( $results['contact_state'] ) ) {
                        $results_display .= ', ' . $results['contact_state'] . ' ';
                    }

                    if ( ! empty( $results['contact_zip'] ) ) {
                        $results_display .= '' . $results['contact_zip'] . '';
                    }

                    if ( ! empty( $results['contact_country'] ) ) {
                        $results_display .= ', ' . $results['contact_country'] . '';
                    }

                    if ( ! empty( $results['city'] ) || ! empty( $results['state'] ) ||
                         ! empty( $results['zip_int'] ) || ! empty( $results['contact_country'] )
                    ) {
                        $results_display .= '<br />';
                    }

                    $results_display .= 'Phone: ';
                    if ( ! empty( $results['contact_phone'] ) ) {
                        $results_display .= '' . $results['contact_phone'] . '<br />';
                    } else {
                        $results_display .= 'NA<br />';
                    }

                    $results_display .= 'Fax: ';
                    if ( ! empty( $results['contact_fax'] ) ) {
                        $results_display .= '' . $results['contact_fax'] . '<br />';
                    } else {
                        $results_display .= 'NA<br />';
                    }

                    if ( ! empty( $results['contact_email'] ) ) {
                        $results_display .= '<a href="mailto:' . $results['contact_email'] . '">' .
                                            $results['contact_email'] . '</a>';
                    }
                }
            }

            $results_display .= '<hr></div><a href="" class="amo-link-back" onclick="history.go(-1);">Back</a>';
        }
        // Search form
    } else {

        $results = $api->processRequest( 'AMOClassifiedCategories' );

        $results_display = '
			<h4 class="amo-form-title">Search Classified Ads</h4>
			<form id="amo_classifieds-search_form" action="" method="post">
				<input type="hidden" name="search" value="1" />
				<div class="amo-form-group">
					<label for="amo_classifieds-category_select">Select A Category:</label>
					<select id="amo_classifieds-category_select" name="pk_association_classified_category" class="form-control">
						<option value=""></option>';

        if ( ! empty( $results ) ) {
            foreach ( $results as $results ) {
                $results_display .= '<option value="' . esc_attr( $results['pk_association_classified_category'] ) .
                                    '">' . $results['category_name'] . '</option>';
            }
        }

        $results_display .= '
                  </select>
				</div>
				
				<div class="amo-form-group">
					<label for="amo-classifieds-keyword">Keyword:</label>
					<input id="amo-classifieds-keyword" type="text" name="keyword" value="" class="form-control" />
				</div>

				<button type="submit" class="btn btn-primary btn-amo">Get Classified Ads</button>
			</form>';

        $search_params = array(
            'pk_association_classified_category' => $this_pk_association_classified_category,
            'keyword'                            => $this_keyword,
        );
        $search_params = array_merge( $amo_attribute, $search_params );

        $current_page = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
        $api->setCurrentPage( $current_page );

        $results = $api->processRequest( 'AMOClassifieds', $search_params );

        if ( empty( $results ) ) {
            $css_classes     = array( 'amo-no_results' );
            $results_display = 'There Are No Classified Ads Matching Your Search Criteria.<hr>
                <a href="" class="amo-link-back" onclick="history.go(-1);">Back</a>';
        } else {
            foreach ( $results as $results ) {
                $results_display .= '<div class="amo_classifieds-classified amo_wrapper-item">';
                $results_display .= '<h4 class="amo-subtitle">' . $results['ad_title'] . '</h4>';

                if ( ! empty( $results['contact_name'] ) ) {
                    $results_display .= '<div class="amo-classifieds-contact">' . $results['contact_name'] . '</div>';
                }

                $results_display .= '<div class="amo-classifieds-posted">Posted: ' .
                                    date( "m-d-Y", strtotime( $results['date_posted'] ) ) . '</div>';
                $results_display .= '<a href="?id=' . esc_attr( $results['pk_association_classified'] ) .
                                    '" class="amo-link-details">View Details</a><br>';
                $results_display .= '</div>';
            }

            $results_display .= '<a href="" class="amo-link-back" onclick="history.go(-1);">Back</a>';
        }

        if ( $api->paginated === true ) {
            $url_params      = array(
                'pk_association_classified_category' => $this_pk_association_classified_category,
                'keyword'                            => $this_keyword,
                'search'                             => $this_passed_search
            );
            $results_display .= AMODiv::paginate_links( $api->current_page, $api->per_page, $api->total_results,
                $url_params );
        }
    }

    return AMODiv::do_output( $results_display, 'amo_classifieds', $css_classes );
}

add_shortcode( 'amo_classifieds', __NAMESPACE__ . '\\amo_shortcode_classifieds' );

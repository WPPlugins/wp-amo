<?php

namespace ArcStone\AMO;

/**
 * AMO INDIVIDUAL MEMBER DIRECTORY SHORTCODE
 *
 * @param array $atts
 *
 * @return string
 */
function amo_shortcode_member_directory( $atts ) {

    $amo_attribute = shortcode_atts( array(
        'pk_association_member_type' => '',
        'page_per'                   => '',
        'directory_exclude_yn'       => 0
    ), $atts );

    $this_pk_association_individual = ( isset( $_GET['id'] ) ) ?
        filter_var( trim( $_GET['id'] ), FILTER_SANITIZE_NUMBER_INT ) :
        '';
    $this_filtered_text             = ( isset( $_REQUEST['filtered_text'] ) ) ?
        filter_var( $_REQUEST['filtered_text'], FILTER_SANITIZE_STRING ) : '';
    $this_column                    = ( isset( $_REQUEST['column'] ) ) ?
        filter_var( trim( $_REQUEST['column'] ), FILTER_SANITIZE_URL ) : '';
    $this_search                    = ( isset( $_REQUEST['search'] ) ) ?
        filter_var( trim( $_REQUEST['search'] ), FILTER_SANITIZE_URL ) : '0';

    if ( $this_column == '' ) {
        $this_all = 'selected';
    } elseif ( $this_column == 'all' ) {
        $this_all = 'selected';
    } else {
        $this_all = '';
    }

    if ( $this_column == 'first_name' ) {
        $this_first_name = 'selected';
    } else {
        $this_first_name = '';
    }

    if ( $this_column == 'last_name' ) {
        $this_last_name = 'selected';
    } else {
        $this_last_name = '';
    }

    if ( $this_column == 'org_name' ) {
        $this_org_name = 'selected';
    } else {
        $this_org_name = '';
    }

    if ( $this_column == 'city' ) {
        $this_city = 'selected';
    } else {
        $this_city = '';
    }

    if ( $this_column == 'state' ) {
        $this_state = 'selected';
    } else {
        $this_state = '';
    }

    $form_display = '<form id="amo_member_directory-search_form" action="' . get_the_permalink() . '" method="post">
						<input type="hidden" name="search" value="1" />
						<p>To see a listing of all active members leave all fields blank and select Search.</p>

						<div class="amo-form-group">
							<label for="filtered_text">Search For:</label>
							<input type="text" name="filtered_text" value="' . esc_attr( $this_filtered_text ) . '" id="filtered_text" class="form-control">
						</div>
						<div class="amo-form-group">
							<label for="search-column">In This Field:</label>
							<select id="search-column" name="column" class="form-control">
								<option value="all" ' . $this_all . '>Search All Fields</option>
								<option value="first_name" ' . $this_first_name . '>First Name</option>
								<option value="last_name" ' . $this_last_name . '>Last Name</option>
								<option value="org_name" ' . $this_org_name . '>Organization</option>
								<option value="city" ' . $this_city . '>City</option>
								<option value="state" ' . $this_state . '>State</option>
							</select>
						</div>
						<div class="amo-form-group">
							<button type="submit" class="btn btn-primary btn-amo">Search</button>
						</div>
					</form>';

    $css_classes            = array();
    $results_display        = '';
    $results_display_detail = '';
    $api                    = new API ( AMO_API_KEY );

    if ( $this_column == 'all' ) {
        $search_array = array(
            'column'        => 'all',
            'filtered_text' => $this_filtered_text,
            'search'        => $this_search
        );
    } elseif ( $this_column ) {
        $search_array = array( $this_column => $this_filtered_text );
    } else {
        $search_array = array();
    }

    $params = array_merge( $amo_attribute, $search_array );

    $api->setCurrentPage( get_query_var( 'page' ) );

    if ( ! empty( $this_pk_association_individual ) ) {

        if ( isset( $atts['disable_bio'] ) && $atts['disable_bio'] == '1' ) {
            $results_display_detail .= 'No bio available.';
        } else {

            $results_detail = $api->processRequest( 'AMOIndividual/' . $this_pk_association_individual );

            if ( empty( $results_detail ) ) {
                $results_display_detail .= 'This Member Biography is Currently Empty<hr><a href="' .
                                           get_the_permalink() . '">Back</a>';
            } else {
                foreach ( $results_detail as $results_detail ) {

                    if ( ! empty( $results_detail['image_name'] ) ) {
                        $results_display_detail .= '<img src="' . esc_url( $results_detail['image'], 'http' ) .
                                                   '"><hr></p>';
                    }

                    $results_display_detail .= '<h4 class="amo-subtitle">';
                    if ( ! empty( $results_detail['salutation'] ) ) {
                        $results_display_detail .= '' . $results_detail['salutation'] . ' ';
                    }
                    if ( ! empty( $results_detail['first_name'] ) ) {
                        $results_display_detail .= '' . $results_detail['first_name'] . '';
                    }
                    if ( ! empty( $results_detail['middle_name'] ) ) {
                        $results_display_detail .= ' ' . $results_detail['middle_name'] . '';
                    }
                    if ( ! empty( $results_detail['last_name'] ) ) {
                        $results_display_detail .= ' ' . $results_detail['last_name'] . '';
                    }
                    if ( ! empty( $results_detail['suffix'] ) ) {
                        $results_display_detail .= ' ' . $results_detail['suffix'] . '';
                    }
                    $results_display_detail .= '</h4>';

                    if ( ! empty( $results_detail['org_name'] ) ) {
                        $results_display_detail .= '' . $results_detail['org_name'] . '<br />';
                    }

                    if ( ! isset( $atts['disable_address'] ) || $atts['disable_address'] != '1' ) {
                        if ( ! empty( $results_detail['address'] ) and ! empty( $results_detail['address2'] ) ) {
                            $results_display_detail .= '' . $results_detail['address'] . ', ' .
                                                       $results_detail['address2'] . '<br />';
                        } elseif ( ! empty( $results_detail['address'] ) ) {
                            $results_display_detail .= '' . $results_detail['address'] . '<br />';
                        }
                    }

                    if ( ! empty( $results_detail['city'] ) &&
                         ( ! isset( $atts['disable_city'] ) || $atts['disable_city'] != '1' )
                    ) {
                        $results_display_detail .= '' . $results_detail['city'] . ' ';
                    }

                    if ( ! empty( $results_detail['state'] ) &&
                         ( ! isset( $atts['disable_state'] ) || $atts['disable_state'] != '1' )
                    ) {
                        $results_display_detail .= ', ' . $results_detail['state'] . ' ';
                    }

                    if ( ! empty( $results_detail['zip'] ) &&
                         ( ! isset( $atts['disable_zip'] ) || $atts['disable_zip'] != '1' )
                    ) {
                        $results_display_detail .= '  ' . $results_detail['zip'] . ' ';
                    }

                    if ( ! empty( $results_detail['city'] ) or ! empty( $results_detail['state'] ) or
                         ! empty( $results_detail['zip'] )
                    ) {
                        $results_display_detail .= '<br />';
                    }

                    if ( ! isset( $atts['disable_phone'] ) || $atts['disable_phone'] != '1' ) {
                        if ( ! empty( $results_detail['phone'] ) and ! empty( $results_detail['phone_ext'] ) ) {
                            $results_display_detail .= '' . $results_detail['phone'] . ' Ext. ' .
                                                       $results_detail['phone_ext'] . '<br />';
                        } elseif ( ! empty( $results_detail['phone'] ) ) {
                            $results_display_detail .= '' . $results_detail['phone'] . '<br />';
                        }
                    }

                    if ( ! empty( $results_detail['email'] ) ) {
                        $results_display_detail .= '<a href="mailto:' . $results_detail['email'] . '">' .
                                                   $results_detail['email'] . '</a><br />';
                    }
                    if ( ! empty( $results_detail['website'] ) ) {
                        $results_display_detail .= '<a href="http://' . $results_detail['website'] .
                                                   '" target="_blank">' . $results_detail['website'] . '</a>';
                    }

                    if ( ! empty( $results_detail['facebook_url'] ) or ! empty( $results_detail['linkedin_url'] ) or
                         ! empty( $results_detail['twitter_url'] )
                    ) {
                        $results_display_detail .= '<br />';
                    }

                    if ( ! empty( $results_detail['facebook_url'] ) ) {
                        $results_display_detail .= '<a href="' . esc_url( $results_detail['facebook_url'], 'http' ) .
                                                   '" target="_blank"><img src="https://admin.associationsonline.com/images/facebook_icon_16.png" height="16" width="16" alt="Facebook"></a>&nbsp;&nbsp;';
                    }
                    if ( ! empty( $results_detail['linkedin_url'] ) ) {
                        $results_display_detail .= '<a href="' . esc_url( $results_detail['linkedin_url'], 'http' ) .
                                                   '" target="_blank"><img src="https://admin.associationsonline.com/images/linked-in_icon_16.png" height="16" width="16" alt="Facebook"></a>&nbsp;&nbsp;';
                    }
                    if ( ! empty( $results_detail['twitter_url'] ) ) {
                        $results_display_detail .= '<a href="' . esc_url( $results_detail['twitter_url'], 'http' ) .
                                                   '" target="_blank"><img src="https://admin.associationsonline.com/images/twitter_icon_16.png" height="16" width="16" alt="Facebook"></a>&nbsp;&nbsp;';
                    }

                    if ( ! empty( $results_detail['bio'] ) ) {
                        $results_display_detail .= '<p>' . $results_detail['bio'] . '</p>';
                    }
                }

                $results_display_detail .= '<hr><a href="' . get_the_permalink() . '">Back</a>';
            }
        }

        $results_display = "{$results_display_detail}";
        // search form
    } else {
        $results = $api->processRequest( 'AMOIndividuals', $params );

        $results_display .= '<div class="table-responsive">
								<table class="table table-hover">
									<tr>
										<th width="30%"><span class="amo-label">Name<br />Organization</span></th>';
        if (
            ( ! isset( $atts['disable_address'] ) || $atts['disable_address'] != "1" ) &&
            ( ! isset( $atts['disable_city'] ) || $atts['disable_city'] != "1" ) &&
            ( ! isset( $atts['disable_state'] ) || $atts['disable_state'] != "1" ) &&
            ( ! isset( $atts['disable_zip'] ) || $atts['disable_zip'] != "1" )
        ) {
            $results_display .= '<th width="35%"><span class="amo-label">Address<br />City, State Zip</span></th>';
        }

        if ( ! isset( $atts['disable_phone'] ) || $atts['disable_phone'] != "1" ) {
            $results_display .= '<th width="20%"><span class="amo-label">Phone<br />Mobile Phone</span></th>';
        }

        if ( ! isset( $atts['disable_bio'] ) || $atts['disable_bio'] != "1" ) {
            $results_display .= '<th width="15%">&nbsp;</th>';
        }

        $results_display .= '</tr>';

        if ( empty( $results ) ) {
            $results_display .= '<tr><td colspan="4">There Are No Members Matching Your Search Criteria</td></tr></table></div>';
        } else {
            foreach ( $results as $results ) {
                $results_display .= '<tr>
									<td valign="top">';
                if ( ! empty( $results['email'] ) and ! empty( $results['suffix'] ) ) {
                    $results_display .= '<a href="mailto:' . esc_attr( $results['email'] ) . '">' .
                                        $results['first_name'] . ' ' . $results['last_name'] . ' ' .
                                        $results['suffix'] . '</a>';
                } elseif ( ! empty( $results['email'] ) and empty( $results['suffix'] ) ) {
                    $results_display .= '<a href="mailto:' . esc_attr( $results['email'] ) . '">' .
                                        $results['first_name'] . ' ' . $results['last_name'] . '</a>';
                } elseif ( empty( $results['email'] ) and ! empty( $results['suffix'] ) ) {
                    $results_display .= '' . $results['first_name'] . ' ' . $results['last_name'] . ', ' .
                                        $results['suffix'] . '';
                } else {
                    $results_display .= '' . $results['first_name'] . ' ' . $results['last_name'] . '';
                }

                $results_display .= '<br />' . $results['org_name'] . '
									</td>';

                if ( ( ! isset( $atts['disable_address'] ) || $atts['disable_address'] != "1" ) &&
                     ( ! isset( $atts['disable_city'] ) || $atts['disable_city'] != "1" ) &&
                     ( ! isset( $atts['disable_state'] ) || $atts['disable_state'] != "1" ) &&
                     ( ! isset( $atts['disable_zip'] ) || $atts['disable_zip'] != "1" )
                ) {
                    $results_display .= '<td>';

                    if ( ! empty( $results['address'] ) &&
                         ( ! isset( $atts['disable_address'] ) || $atts['disable_address'] != "1" )
                    ) {
                        $results_display .= $results['address'];
                        if ( ! empty( $results['address2'] ) ) {
                            $results_display .= ', ' . $results['address2'] . '';
                        }
                        $results_display .= '<br />';
                    }

                    if ( ! empty( $results['city'] ) &&
                         ( ! isset( $atts['disable_city'] ) || $atts['disable_city'] != "1" )
                    ) {
                        $results_display .= '' . $results['city'] . ', ';
                    }

                    if ( ! empty( $results['state'] ) &&
                         ( ! isset( $atts['disable_state'] ) || $atts['disable_state'] != "1" )
                    ) {
                        $results_display .= '' . $results['state'] . ' ';
                    }
                    if ( ! empty( $results['zip_int'] ) &&
                         ( ! isset( $atts['disable_zip'] ) || $atts['disable_zip'] != "1" )
                    ) {
                        $results_display .= '' . $results['zip_int'] . '';
                    }

                    $results_display .= '</td>';
                }

                if ( ( ! isset( $atts['disable_phone'] ) || $atts['disable_phone'] != "1" ) ) {
                    $results_display .= '<td>';

                    if ( ! empty( $results['phone'] ) ) {
                        $results_display .= $results['phone'];
                        if ( ! empty( $results['phone_ext_int'] ) ) {
                            $results_display .= ', Ext. ' . $results['phone_ext_int'] . '';
                        }
                        $results_display .= '<br />' . $results['phone_mobile'];
                    }

                    $results_display .= '</td>';
                }

                if ( ! isset( $atts['disable_bio'] ) || $atts['disable_bio'] != "1" ) {
                    if ( ! empty( $results['bio'] ) ) {
                        $results_display .= '<td><a href="?id=' . esc_attr( $results['pk_association_individual'] ) .
                                            '"><button type="button" class="btn btn-info btn-xs btn-amo">Bio</button></a></td></tr>';
                    } else {
                        $results_display .= '<td><a href="?id=' . esc_attr( $results['pk_association_individual'] ) .
                                            '"><button type="button" class="btn btn-default btn-xs btn-amo">Bio</button></a></td></tr>';
                    }
                }
            }

            $results_display .= '</table>';

            if ( $api->paginated === true ) {
                $results_display .= AMODiv::paginate_links( $api->current_page, $api->per_page, $api->total_results,
                    $search_array );
            }

            $results_display .= '</div>';
        }

        $results_display = "{$form_display}{$results_display}";
    }

    return AMODiv::do_output( $results_display, 'amo_member_directory', $css_classes );
}

add_shortcode( 'amo_member_directory', __NAMESPACE__ . '\\amo_shortcode_member_directory' );

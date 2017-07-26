<?php

namespace ArcStone\AMO;

/**
 * AMO MEMBER DIRECTORY - ORGANIZATION SHORTCODE
 *
 * @param array $atts
 *
 * @return string
 */
function amo_shortcode_member_directory_organization($atts)
{
    $amo_attribute = shortcode_atts(array(
        'pk_association_member_type' => '',
        'page_per'                   => ''
    ), $atts);

    $this_pk_association_organization = (isset($_GET['id'])) ?
        filter_var(trim($_GET['id']), FILTER_SANITIZE_NUMBER_INT) : '';
    $this_filtered_text               = (isset($_REQUEST['filtered_text'])) ?
        filter_var($_REQUEST['filtered_text'], FILTER_SANITIZE_STRING) : '';
    $this_column                      = (isset($_REQUEST['search-column'])) ?
        filter_var(trim($_REQUEST['search-column']), FILTER_SANITIZE_URL) : '';
    $this_search                      = (isset($_REQUEST['search'])) ?
        filter_var(trim($_REQUEST['search']), FILTER_SANITIZE_URL) : '0';

    if ($this_column == '' || $this_column == 'all') {
        $this_all = 'selected';
    } else {
        $this_all = '';
    }

    if ($this_column == 'org_name') {
        $this_org_name = 'selected';
    } else {
        $this_org_name = '';
    }

    if ($this_column == 'city') {
        $this_city = 'selected';
    } else {
        $this_city = '';
    }

    if ($this_column == 'state') {
        $this_state = 'selected';
    } else {
        $this_state = '';
    }

    if ($this_column == 'all') {
        $search_array = array(
            'search-column' => 'all',
            'filtered_text' => $this_filtered_text,
            'search'        => $this_search
        );
    } else {
        $search_array = array($this_column => $this_filtered_text);
    }

    $css_classes            = array();
    $results_display        = '';
    $results_display_detail = '';

    $api = new API (AMO_API_KEY);

    if (!empty($this_pk_association_organization)) {

        $results_detail = $api->processRequest('AMOOrganization/' . $this_pk_association_organization);

        if (empty($results_detail)) {
            $css_classes            = array('amo-no_results');
            $results_display_detail .= 'This Member Biography is Currently Empty<hr><a href="" onclick="history.go(-1);">Back</a>';
        } else {
            foreach ($results_detail as $results_detail) {
                $results_display_detail .= '<div class="row"><div class="col-lg-6 col-md-6 col-sm-6">';

                if (!isset($atts['disable_logo']) && !empty($results_detail['org_image'])) {
                    $results_display_detail .= '<img src="' . $results_detail['org_image'] . '"><hr>';
                }

                $results_display_detail .= '<h4 class="amo-subtitle">' . $results_detail['org_name'] . '</h4><p>';
                if (!empty($results_detail['org_phone'])) {
                    $results_display_detail .= '' . $results_detail['org_phone'] . '<br />';
                }
                if (!empty($results_detail['org_tollfree'])) {
                    $results_display_detail .= '' . $results_detail['org_tollfree'] . '<br />';
                }
                if (!empty($results_detail['org_fax'])) {
                    $results_display_detail .= 'Fax: ' . $results_detail['org_fax'] . '<br />';
                }
                if (!empty($results_detail['org_website'])) {
                    $results_display_detail .= '<a href="' . esc_url($results_detail['org_website'], 'http') .
                                               '" target="_blank">' . $results_detail['org_website'] . '</a>';
                }

                if (!empty($results_detail['facebook_url']) or !empty($results_detail['linkedin_url']) or
                    !empty($results_detail['twitter_url'])
                ) {
                    $results_display_detail .= '<br />';
                }

                if (!empty($results_detail['org_facebook_url'])) {
                    $results_display_detail .= '<a href="' . esc_url($results_detail['org_facebook_url'], 'http') .
                                               '" target="_blank"><img src="https://admin.associationsonline.com/images/facebook_icon_16.png" height="16" width="16" alt="Facebook"></a>&nbsp;&nbsp;';
                }
                if (!empty($results_detail['org_linkedin_url'])) {
                    $results_display_detail .= '<a href="' . esc_url($results_detail['org_linkedin_url'], 'http') .
                                               '" target="_blank"><img src="https://admin.associationsonline.com/images/linked-in_icon_16.png" height="16" width="16" alt="Facebook"></a>&nbsp;&nbsp;';
                }
                if (!empty($results_detail['org_twitter_url'])) {
                    $results_display_detail .= '<a href="' . esc_url($results_detail['org_twitter_url'], 'http') .
                                               '" target="_blank"><img src="https://admin.associationsonline.com/images/twitter_icon_16.png" height="16" width="16" alt="Facebook"></a>&nbsp;&nbsp;';
                }

                $results_display_detail .= '</p></div>';

                $results_display_detail .= '<div class="col-lg-6 col-md-6 col-sm-6">';
                $results_display_detail .= '<p><b>Street Address</b>:<br />';
                if (!empty($results_detail['org_street_address']) and !empty($results_detail['org_street_address2'])) {
                    $results_display_detail .= '' . $results_detail['org_street_address'] . ', ' .
                                               $results_detail['org_street_address2'] . '<br />';
                } elseif (!empty($results_detail['org_street_address'])) {
                    $results_display_detail .= '' . $results_detail['org_street_address'] . '<br />';
                }
                if (!empty($results_detail['org_street_city'])) {
                    $results_display_detail .= '' . $results_detail['org_street_city'] . ' ';
                }
                if (!empty($results_detail['org_street_state'])) {
                    $results_display_detail .= ', ' . $results_detail['org_street_state'] . ' ';
                }
                if (!empty($results_detail['org_street_zip'])) {
                    $results_display_detail .= '  ' . $results_detail['org_street_zip'] . ' ';
                }
                if (!empty($results_detail['org_street_city']) or !empty($results_detail['org_street_state']) or
                    !empty($results_detail['org_street_zip'])
                ) {
                    $results_display_detail .= '<br />';
                }

                $results_display_detail .= '</p>';

                $results_display_detail .= '<p><b>Mailing Address</b>:<br />';
                if (!empty($results_detail['org_mailing_address']) and
                    !empty($results_detail['org_mailing_address2'])
                ) {
                    $results_display_detail .= '' . $results_detail['org_mailing_address'] . ', ' .
                                               $results_detail['org_mailing_address2'] . '<br />';
                } elseif (!empty($results_detail['org_mailing_address'])) {
                    $results_display_detail .= '' . $results_detail['org_mailing_address'] . '<br />';
                }
                if (!empty($results_detail['org_mailing_city'])) {
                    $results_display_detail .= '' . $results_detail['org_mailing_city'] . ' ';
                }
                if (!empty($results_detail['org_mailing_state'])) {
                    $results_display_detail .= ', ' . $results_detail['org_mailing_state'] . ' ';
                }
                if (!empty($results_detail['org_mailing_zip'])) {
                    $results_display_detail .= '  ' . $results_detail['org_mailing_zip'] . ' ';
                }
                if (!empty($results_detail['org_mailing_city']) or !empty($results_detail['org_mailing_state']) or
                    !empty($results_detail['org_mailing_zip'])
                ) {
                    $results_display_detail .= '';
                }

                $results_display_detail .= '</p>';

                if (!isset($atts['disable_bio']) && !empty($results_detail['org_bio'])) {
                    $results_display_detail .= '<p>' . $results_detail['org_bio'] . '</p>';
                }

                $results_display_detail .= '</div>';

                $results_display_detail .= '</div>';
            }

            $results_display_detail .= '<hr><a href="" onclick="history.go(-1);">Back</a>';
        }

        $results_display = "{$results_display_detail}";
    } else {

        $params = array_merge($amo_attribute, $search_array);
        $api->setCurrentPage(get_query_var('page'));
        $results = $api->processRequest('AMOOrganizations', $params);

        $form_display = '<form id="amo_member_directory_organization-search_form" action="' . get_the_permalink() . '" method="post">
			<input type="hidden" name="search" value="1" />
			<p>To see a listing of all active members leave all fields blank and select Search.</p>

			<div class="amo-form-group">
				<label for="filtered_text">Search For:</label>
				<input type="text" name="filtered_text" value="' . esc_attr($this_filtered_text) . '" id="filtered_text" class="form-control">
			</div>


			<div class="amo-form-group">
				<label for="search-column">In This Field:</label>
				<select name="search-column" class="form-control">
					<option value="all" ' . $this_all . '>Search All Fields</option>
					<option value="org_name" ' . $this_org_name . '>Organization</option>
					<option value="city" ' . $this_city . '>City</option>
					<option value="state" ' . $this_state . '>State</option>
				</select>
			</div>
			<div class="amo-form-group">
				<button type="submit" class="btn btn-primary btn-amo">Search</button>
			</div>

		</form>';

        $results_display .= '
			<div class="table-responsive">
				<table class="table table-hover">
					<tr>
						<th width="30%"><span class="amo-label">Name<br />Organization</span></th>
						<th width="35%"><span class="amo-label">Address<br />City, State Zip Code</span></th>
						<th width="20%"><span class="amo-label">Phone<br />Mobile Phone</span></th>
						<th width="15%">&nbsp;</th>
					</tr>';

        if (empty($results)) {
            $results_display .= '<tr><td colspan="4">There Are No Members Matching Your Search Criteria</td></tr></table></div>';
        } else {
            foreach ($results as $results) {
                $results_display .= '<tr><td valign="top" width="34%">';
                $results_display .= '' . $results['org_name'] . '<br />';
                if (!empty($results['org_website'])) {
                    $results_display .= '<a href="http://' . $results['org_website'] . '" target="_blank">' .
                                        $results['org_website'] . '</a>';
                }
                $results_display .= '&nbsp;</td>';
                $results_display .= '<td width="39%">' . $results['org_street_address'] . '';
                if (!empty($results['org_street_address2'])) {
                    $results_display .= ', ' . $results['org_street_address2'] . '';
                }
                $results_display .= '<br />';
                if (!empty($results['org_street_city'])) {
                    $results_display .= '' . $results['org_street_city'] . ', ';
                }
                if (!empty($results['org_street_state'])) {
                    $results_display .= '' . $results['org_street_state'] . ' ';
                }
                if (!empty($results['org_street_zip_int'])) {
                    $results_display .= '' . $results['org_street_zip_int'] . '';
                }
                $results_display .= '</td>';
                $results_display .= '<td nowrap width="19%">' . $results['org_phone'] . '&nbsp;<br />';
                $results_display .= '' . $results['org_tollfree'] . '&nbsp;</td>';
                $results_display .= '<td width="8%" align="center"><a href="?id=' .
                                    $results['pk_association_organization'] .
                                    '"><button type="button" class="btn btn-default btn-xs btn-amo">View</button></a></td></tr>';
            }

            $results_display .= '</table>';

            if ($api->paginated === true) {
                $results_display .= AMODiv::paginate_links($api->current_page, $api->per_page, $api->total_results,
                    $search_array);
            }

            $results_display .= '</div>';
        }

        $results_display = "{$form_display}{$results_display}";
    }

    return AMODiv::do_output($results_display, 'amo_member_directory_organization', $css_classes);
}

add_shortcode('amo_member_directory_organization', __NAMESPACE__ . '\\amo_shortcode_member_directory_organization');


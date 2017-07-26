<?php

namespace ArcStone\AMO;

//AMO DONATION BUTTON SHORTCODE

function amo_shortcode_random_banner($atts)
{
    $amo_attribute = shortcode_atts(array(
        'image_alignment' => 'aligncenter',
        'banner_type'     => '',
    ), $atts);

    $css_classes     = array();
    $results_display = '';

    $api     = new API (AMO_API_KEY);
    $results = $api->processRequest('AMORandomBanner', [
        'banner_type' => $amo_attribute['banner_type'],
    ], false);

    if (empty($results)) {
        $css_classes     = array('amo-no_results');
        $results_display = 'No results';
    } else {

        foreach ($results as $results) {
            $img_html = '<img src="' . esc_url($results['banner_link'], 'http') . '" alt="' .
                        esc_attr($results['banner_link_alt']) . '" class="' .
                        esc_attr($amo_attribute['image_alignment']) . '" />';

            if (!empty($results['banner_url']) && filter_var($results['banner_url'], FILTER_VALIDATE_URL)) {
                $results_display .= sprintf('<a href="%s">%s</a>', esc_url($results['banner_url'], 'http'), $img_html);
            } else {
                $results_display .= $img_html;
            }
        }
    }

    return AMODiv::do_output($results_display, 'amo_random_banner', $css_classes);
}

add_shortcode('amo_random_banner', __NAMESPACE__ . '\\amo_shortcode_random_banner');

<?php

namespace ArcStone\AMO\Shortcodes;

use ArcStone\AMO\AMODiv;
use ArcStone\AMO\API;
use ArcStone\AMO\WP_AMO;

/**
 * AMO CALENDAR SHORTCODE
 *
 * @package ArcStone\AMO\Shortcodes
 */
class Calendar {

	public static $oneTimeAssets = false;

	/**
	 * Hook to render shortcode content.
	 *
	 * @param array $atts User defined attributes in shortcode tag.
	 *
	 * @return string HTML
	 */
	public static function render( $atts = [] ) {

		$atts = shortcode_atts( [
			'show-events'        => false,
			'show-announcements' => false,
			// Event Type is an empty string field. Should we retrieve these choices from the API?
			'event-type'         => '',
		], $atts );

		// Add calendar styles
		wp_enqueue_style( 'jqueryui-base-theme', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.min.css', [], '1.12.1' );
		wp_enqueue_style( 'fullcalendar-basic-style', '//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.css', [], '3.2.0' );
		wp_enqueue_style( 'fullcalendar-print-style', '//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.print.min.css', [], '3.2.0', 'print' );
		// Add calendar scripts
		wp_enqueue_script( 'moment', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js', [], '2.17.1', true );
		wp_enqueue_script( 'fullcalendar', '//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.js', [
			'jquery',
			'moment',
		], '3.2.0', true );
		wp_enqueue_script( 'jqueryui', '//code.jquery.com/ui/1.12.1/jquery-ui.min.js', [ 'jquery' ], '1.12.1', true );
		wp_enqueue_script( 'amo-calendar', WP_AMO::$installed_url . 'js/calendar.js', [ 'fullcalendar', 'jqueryui' ] );

		$divAttr = [
			'class'                   => "fullcalendar amo_wrapper-item",
			'data-api-key'            => \ArcStone\AMO\AMO_API_KEY,
			'data-base-url'           => API::$api_url,
			'data-show-events'        => $atts['show-events'],
			"data-event-type"         => $atts['event-type'],
			'data-show-announcements' => $atts['show-announcements'],
		];

		$content = '<div' . static::attributes( $divAttr ) . '></div>';

		if ( ! static::$oneTimeAssets ) {
			static::$oneTimeAssets = true;
			$content               .= <<<HTML
<div id="dialog" style="display:none">
  <div id="event-desc"></div>
  <p id="event-link" align="center" style="display:none">
    <a class="btn btn-primary"
  	   style="width:130px;"
  	   target="_self">Event Details</a>
  </p>
</div>

<div id="spinner">
  <div id="overlay" class="ui-widget-overlay ui-front"></div>
  <div class="spinner">
    <div class="bounce1"></div>
    <div class="bounce2"></div>
    <div class="bounce3"></div>
  </div>
</div>
HTML;
		}

		return AMODiv::do_output( $content, null, [ 'amo_calendar' ] );
	}

	/**
	 * Build an HTML attribute string from an array.
	 * Adapted from LaravelCollective\Html;
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	protected static function attributes( $attributes ) {
		$html = [];
		foreach ( (array) $attributes as $key => $value ) {
			if ( ! empty( $value ) ) {
				$element = $key . '="' . htmlentities( $value, ENT_QUOTES, 'UTF-8' ) . '"';
				if ( ! is_null( $element ) ) {
					$html[] = $element;
				}
			}
		}

		return count( $html ) > 0 ? ' ' . implode( ' ', $html ) : '';
	}
}

add_shortcode( 'amo_calendar', [ Calendar::class, 'render' ] );

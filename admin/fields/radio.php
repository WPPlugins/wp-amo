<?php

/**
 * Adds the WordPress filter to add the radio button element
 *
 * Class Shortcode_Form_Field_Radio
 */
class Shortcode_Form_Field_Radio
{
    /**
     * Name of the filter
     */
    const FILTER = 'amo_shortcode_form_add_radio';

    /**
     * Create the filter for the form element
     */
    public function __construct()
    {
        add_filter(self::FILTER, array($this, 'add_form_element'), 10, 3);
    }

	/**
	 * @param $name
	 * @param $title
	 * @param array $options
     * @param $defaultValue
	 *
	 * @return string
	 */
	public function add_form_element( $name, $title, $options = array() )
    {
	    $html = sprintf('<tr><td>%s</td><td>',$title);

	    if(!empty($options))
	    {
		    foreach($options as $k => $v)
		    {
			    $html .= sprintf('<input type="radio" name="%s" value="%s">%s</option>',
				    $name,
				    $k,
				    $v);
		    }
	    }

	    $html .= '</td></tr>';

	    return $html;
    }
}
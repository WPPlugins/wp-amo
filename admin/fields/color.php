<?php

/**
 * Adds the WordPress filter to add the color element
 *
 * Class Shortcode_Form_Field_Color
 */
class Shortcode_Form_Field_Color
{
    /**
     * Name of the filter
     */
    const FILTER = 'amo_shortcode_form_add_color';

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
	 * @param string $value
	 *
	 * @return string
	 */
	public function add_form_element($name, $title, $value = '')
    {
	    return sprintf('<tr><td>%s</td><td><input type="color" id="%s" name="%s" value="%s" /></td></tr>',
		    $title,
		    $name,
		    $name,
		    $value);
    }
}
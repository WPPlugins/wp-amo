<?php

/**
 * Adds the WordPress filter to add the date element
 *
 * Class Shortcode_Form_Field_Date
 */
class Shortcode_Form_Field_Date
{
    /**
     * Name of the filter
     */
    const FILTER = 'amo_shortcode_form_add_date';

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
	    return sprintf('<tr><td>%s</td><td><input type="date" id="%s" name="%s" value="%s" /></td></tr>',
		    $title,
		    $name,
		    $name,
		    $value);
    }
}
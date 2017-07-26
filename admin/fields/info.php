<?php

/**
 * Adds the WordPress filter to add the info element
 *
 * Class Shortcode_Form_Field_Info
 */
class Shortcode_Form_Field_Info
{
    /**
     * Name of the filter
     */
    const FILTER = 'amo_shortcode_form_add_info';

    /**
     * Create the filter for the form element
     */
    public function __construct()
    {
        add_filter(self::FILTER, array($this, 'add_form_element'), 10, 1);
    }

	/**
	 * @param $content
	 *
	 * @return string
	 */
	public function add_form_element($content)
    {
	    return '<tr><td colspan="2">'.$content.'</td></tr>';
    }
}
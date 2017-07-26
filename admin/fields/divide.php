<?php

/**
 * Adds the WordPress filter to add the divide element
 *
 * Class Shortcode_Form_Field_Divide
 */
class Shortcode_Form_Field_Divide
{
    /**
     * Name of the filter
     */
    const FILTER = 'amo_shortcode_form_add_divide';

    /**
     * Create the filter for the form element
     */
    public function __construct()
    {
        add_filter(self::FILTER, array($this, 'add_form_element'));
    }

	/**
	 * @return string
	 */
	public function add_form_element()
    {
	    return '<tr><td colspan="2"><hr/></td></tr>';
    }
}
<?php

/**
 * Adds the WordPress filter to add the textarea element
 *
 * Class Shortcode_Form_Field_Textarea
 */
class Shortcode_Form_Field_Textarea
{
    /**
     * Name of the filter
     */
    const FILTER = 'amo_shortcode_form_add_textarea';

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
	public function add_form_element( $name, $title, $value = '' )
    {
	    return sprintf('<tr><td>%s</td><td><textarea id="%s" name="%s">%s</textarea></td></tr>',
		    $title,
		    $name,
		    $name,
		    $value);
    }
}
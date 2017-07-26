<?php

/**
 * Adds the WordPress filter to add the checkbox element
 *
 * Class Shortcode_Form_Field_Checkbox
 */
class Shortcode_Form_Field_Checkbox
{
    /**
     * Name of the filter
     */
    const FILTER = 'amo_shortcode_form_add_checkbox';

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
	 * @param bool $checkedDefault
	 *
	 * @return string
	 */
	public function add_form_element( $name, $title, $checkedDefault = false )
    {
        $checked = '';
        if($checkedDefault)
        {
            $checked = 'checked';
        }

        return sprintf('<tr><td>%s</td><td><input type="checkbox" id="%s" name="%s" value="1" %s /></td></tr>',
            $title,
            $name,
            $name,
            $checked);
    }
}
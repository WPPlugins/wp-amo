<?php
namespace ArcStone\AMO;
/**
 * AMO Shortcode Field
 *
 * To process the shortcode field data
 */
class AMO_Shortcode_Field
{
    private $optionName = false;

    private $errors = array();

    public function __construct( $optionName )
    {
        $this->optionName = $optionName;
    }

    /**
     * Get the errors array
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     * Process of a new field
     *
     * @param $post
     * @return bool
     */
    public function add_new_field( $post )
    {
        if(! wp_verify_nonce($post['_wpnonce'], 'amo-shortcode-nonce-form') )
        {
            return false;
        }

        $options = get_option($this->optionName);

        $field = array();

        if(isset($post['shortcode']))
        {
            if(!empty($post['field-type']))
            {
                $field['type'] = sanitize_text_field($post['field-type']);
            } else {
                $this->errors[] = 'Please select a field type.';
            }

            if($field['type'] != 'divide' && $field['type'] != 'info')
            {
                if(!empty($post['field-shortcode-attribute']))
                {
                    $field['attribute'] = sanitize_text_field($post['field-shortcode-attribute']);
                } else {
                    $this->errors[] = 'Please enter the shortcode attribute.';
                }

                if(!empty($post['field-title']))
                {
                    $field['title'] = sanitize_text_field($post['field-title']);
                } else {
                    $this->errors[] = 'Please enter a title for the attribute.';
                }
            }

            $field['order'] = sanitize_text_field($post['field-order']);
            $field['default-value'] = sanitize_text_field($post['field-default-value']);

            switch($field['type'])
            {
                case 'checkbox':
                    if(isset($post['field-default-checked']) && $post['field-default-checked'] == 1)
                    {
                        $field['default-value'] = 1;
                    } else {
                        $field['default-value'] = 0;
                    }
                    break;

                case 'textarea':
                case 'info':
                    $field['default-value'] = sanitize_text_field($post['field-default-textarea-value']);
                    break;

                case 'radio':
                case 'select':
                    $processOptions = true;

                    foreach($post['field-options-value'] as $optionValue)
                    {
                        if(empty($optionValue) && $processOptions)
                        {
                            $processOptions = false;
                            $this->errors[] = 'Please enter a value for the ' . $field['type'] . ' options.';
                        }
                    }

                    foreach($post['field-options-text'] as $optionText)
                    {
                        if(empty($optionText) && $processOptions)
                        {
                            $processOptions = false;
                            $this->errors[] = 'Please enter text for the ' . $field['type'] . ' options.';
                        }
                    }

                    if($processOptions)
                    {
                        $field['options-value'] = array_map('sanitize_text_field', $post['field-options-value']);
                        $field['options-text'] = array_map('sanitize_text_field', $post['field-options-text']);
                    }
                    break;
            }

            $options[$post['shortcode']]['fields'][$post['field_id']] = $field;
        }

        if(empty($this->errors))
        {
            return update_option($this->optionName, $options);
        } else {
            return false;
        }
    }

    /**
     * Delete the field from the shortcode option
     *
     * @param $shortcode
     * @param $field
     *
     * @return bool
     */
    public function delete_field( $shortcode, $field )
    {
        $options = get_option($this->optionName);

        if(isset($options[$shortcode]['fields'][$field]))
        {
            unset($options[$shortcode]['fields'][$field]);

            return update_option($this->optionName, $options);
        }
    }

    /**
     * Get the fields for a certain shortcode
     *
     * @param $shortcode
     *
     * @return mixed
     */
    public function get_fields_shortcode($shortcode)
    {
        $options = get_option($this->optionName);

        if(isset($options[$shortcode]['fields']))
        {
            return $options[$shortcode]['fields'];
        }
    }

    /**
     * Get a certain field from a shortcode
     *
     * @param $shortcode
     * @param $field
     *
     * @return mixed
     */
    public function get_field($shortcode, $field)
    {
        $options = get_option($this->optionName);

        if(isset($options[$shortcode]['fields'][$field]))
        {
            return $options[$shortcode]['fields'][$field];
        }
    }

    /**
     * @param $field
     * @return mixed|string|void
     */
    public function process_field( $field )
    {
        $formString = '';

        switch(strtolower($field['type']))
        {
            case 'checkbox':
                $formString = apply_filters('amo_shortcode_form_add_checkbox', $field['attribute'], $field['title'], $field['default-value']);
            break;

            case 'color':
                $formString = apply_filters('amo_shortcode_form_add_color', $field['attribute'], $field['title'], $field['default-value']);
            break;

            case 'date':
                $formString = apply_filters('amo_shortcode_form_add_date', $field['attribute'], $field['title'], $field['default-value']);
            break;

            case 'divide':
                $formString = apply_filters('amo_shortcode_form_add_divide', '');
            break;

            case 'info':
                $formString = apply_filters('amo_shortcode_form_add_info', $field['default-value']);
            break;

            case 'radio':
                $options = $this->process_options($field['options-value'], $field['options-text']);
                $formString = apply_filters('amo_shortcode_form_add_radio', $field['attribute'], $field['title'], $options);
            break;

            case 'select':
                $options = $this->process_options($field['options-value'], $field['options-text']);
                $formString = apply_filters('amo_shortcode_form_add_select', $field['attribute'], $field['title'], $options);
            break;

            case 'text':
                $formString = apply_filters('amo_shortcode_form_add_text', $field['attribute'], $field['title'], $field['default-value']);
            break;

            case 'textarea':
                $formString = apply_filters('amo_shortcode_form_add_textarea', $field['attribute'], $field['title'], $field['default-value']);
            break;
        }

        return $formString;
    }

    /**
     * @param $values
     * @param $text
     *
     * @return array
     */
    private function process_options( $values, $text)
    {
        $options = array();

        foreach($values as $key => $val)
        {
            $options[$val] = $text[$key];
        }

        return $options;
    }
}
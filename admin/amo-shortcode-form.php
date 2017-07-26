<?php
namespace ArcStone\AMO;
require_once dirname(__FILE__) .'/includes/class-amo-shortcode-field.php';

/**
 * This class is used to create the form elements when the user clicks on a shortcode to add to the content editor
 *
 * Class Admin_Shortcode_Form
 */
class AMO_Shortcode_Form
{
    /**
     * @var string
     */
    private $optionName = 'amo-shortcode-fields';

    /**
     * @var AMO_Shortcode_Field
     */
    private $shortcodeField;

    /**
     * Create the javascript code for the form element
     * Import the javascript and the styling for the form dialog
     * Override the developer set form elements with the form created by the user in the settings page
     */
    public function __construct()
    {
        add_action('admin_footer', array($this, 'shortcode_dialog'));
        add_action('admin_enqueue_scripts', array($this, 'add_dialog_box_scripts'));

        $this->shortcodeField = new AMO_Shortcode_Field( $this->optionName );

        $this->register_filters();
    }

    /**
     * Register the filters for the form elements
     *
     * @since 1.0.0
     */
    private function register_filters()
    {
        foreach(glob(dirname(__FILE__).'/fields/*.php') as $formFields)
        {
            if(file_exists($formFields))
            {
                require_once $formFields;
                $split = explode('/', $formFields);
                $className = 'Shortcode_Form_Field_'.ucfirst(str_replace('.php', '', end($split)));
                if(class_exists($className))
                {
                    new $className();
                }
            }
        }
    }

    /**
     * Add the shortcode dialog to the footer
     */
    public function shortcode_dialog()
    {
        echo '<div id="shortcode-dialog" title="Shortcode Form" style="width:500px;">
                <form class="shortcode-dialog-form"></form></div>';

        echo '<script type="text/javascript">
        var shortcodes_form = new Object();';

        $shortcode_form = array();
        $shortcode_form = apply_filters('amo_shortcode_form', $shortcode_form);

        $shortcode_form = array_merge( $this->get_shortcode_field_options(), $shortcode_form);

        if(!empty($shortcode_form))
        {
            foreach($shortcode_form as $tag => $form)
            {
                if(is_array($form))
                {
                    $form = implode('', $form);
                }

	            echo "shortcodes_form['{$tag}'] = '{$form}';";
            }
        }

        echo '</script>';
    }

    /**
     * Get the shortcode field options
     */
    private function get_shortcode_field_options()
    {
        $shortcodeFields = array();

        $tags = get_option( $this->optionName );

        if(!empty($tags))
        {
            foreach($tags as $shortcode => $tag)
            {
                $shortcodeFields[$shortcode] = array();

                if(!empty($tag['fields']))
                {
                    foreach($tag['fields'] as $field)
                    {
                        $shortcodeFields[$shortcode][] = $this->shortcodeField->process_field($field);
                    }
                }
            }
        }

        return $shortcodeFields;
    }

    /**
     * Add the jquery scripts to the admin area
     */
    public function add_dialog_box_scripts()
    {        
        wp_enqueue_script( 'jquery-ui-dialog', false, array('jquery') );
        wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
	    wp_enqueue_style( 'shortcode-button-style', plugin_dir_url(__FILE__).'css/shortcode-button-style.css', array(), '2.2');
    }
}
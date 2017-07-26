/**
 * The admin area settings page needs to show and hide different elements depending on the chosen field type
 */
$j=jQuery.noConflict();

$j(document).ready(function() {
    $j('#field-type').on('change', function(){
        var val = $j(this).val();

        // On change hide the hidden options fields
        // Reset the input and textarea elements
        // Remove any checked attributes from checkboxes
        $j('.field-options .hidden').hide();
        $j('.field-options').find('input[type=text], textarea').val('');
        $j('.field-options').find('input[type=checkbox]').removeAttr('checked');

        $j('#shortcode-attribute-option').removeClass('hidden').show();
        $j('#field-title-option').removeClass('hidden').show();

        switch( val )
        {
            case 'checkbox':
                $j('#checkbox-options').show();
            break;

            case 'color':
            case 'date':
            case 'text':
                $j('#default-value-options').show();
            break;

            case 'divide':
                $j('#shortcode-attribute-option').hide();
                $j('#field-title-option').hide();
            break;

            case 'info':
                $j('#shortcode-attribute-option').hide();
                $j('#field-title-option').hide();
                $j('#textarea-default-value-options').show();
            break;

            case 'textarea':
                $j('#textarea-default-value-options').show();
            break;

            case 'radio':
            case 'select':
                $j('#multi-options').show();
            break;
        }
    });

    $j('.add-multiple-option').on('click', function(){
        var clone = $j('#field-options-clone').clone();
        clone.removeClass('hidden').addClass('field-options').removeAttr('id');

        $j(this).parent().prev().after(clone);
    });

    $j('.field-options').on('click', '.remove-multiple-option', function(){
       $j(this).parent().remove();
    });

    $j('#list-fields-table tbody').sortable({
        axis: 'y',
        update: function (event, ui)
        {
            var fieldOrder = [];
            $j('#list-fields-table tbody tr').each(function(i){
                fieldOrder[i] = $j(this).attr('id');
            });

            $j.ajax({
                data: { action : 'amo_shortcode_menu_ajax_action', fieldid : fieldOrder, shortcode : $j('#shortcode').val() },
                type: 'POST',
                url: ajaxurl,
                success: function(data, textStatus)
                {
                    $j('.order_cell').each(function(i){
                        $j(this).html(i + 1);
                    });
                }
            });
        }
    });
});
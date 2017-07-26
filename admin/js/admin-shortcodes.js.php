<script type="text/javascript">
	

	jQuery(document).ready( function($) {
		html = '<div id="wp-amo-shortcodes-modal">' +
					'<div class="modal-dialog modal-md">' +
							'<ul class="amo-shortcodes-list">' +

							<?php foreach( $available_shortcodes as $code => $title ) :
								echo "'<li><a href=\"#\" data-dismiss=\"modal\" data-amo-shortcode=\"$code\" class=\"amo-shortcode-button\">" . esc_html( $title ) . "</a></li>'+";
							endforeach; ?>

							'</ul>' +
					'</div>' +
				'</div>';

		$('body').append(html);

        $('#wp-amo-shortcodes-modal').dialog({ autoOpen: false, title: 'AMO Shortcodes Generator'});
        $(document).on( 'click', '.wp-amo-media-buttons', function(e) {
            e.preventDefault();
            $('#wp-amo-shortcodes-modal').dialog('open');
        });
		
        $('.amo-shortcode-button').click( function(e) {
			e.preventDefault();

			v = $(this).data('amo-shortcode');

            var win = window.dialogArguments || opener || parent || top; // use WP's send_to_editor() function below
            var content = '';
            var dialogForm = '<table>';

           	if(shortcodes_form[v] != undefined) {
                dialogForm += shortcodes_form[v];

                if(dialogForm != '<table>') {
                    dialogForm += '</table>';
                    $('.shortcode-dialog-form').empty();
                    $('.shortcode-dialog-form').append(dialogForm);

                    $("#shortcode-dialog").dialog({
                        width: 600,
                        resizable: false,
                        buttons: {
                            "Add Shortcode": function(){
                                var formArray = jQuery('.shortcode-dialog-form').serializeArray();

                                if(formArray.length > 0)
                                {
                                    content = '[' + v;
                                    $(formArray).each(function(i){
                                        content += ' ' + jQuery(this)[0].name + '="'+ jQuery(this)[0].value +'"';
                                    });

                                    content += '][/'+v+']';
                                }

                                win.send_to_editor( content );

                                $( this ).dialog( "close" );
                                $('#wp-amo-shortcodes-modal').dialog('close');
                            }
                        }
                    });
                } else {
                	content = '[' + v + '][/' + v + ']';
                    win.send_to_editor( content );
                }
            } else {
                content = '[' + v + '][/' + v + ']';
                win.send_to_editor( content );
            }
		} );

	});
</script>


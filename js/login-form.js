jQuery(document).ready( function($) {

	$('#amo-login_form').submit( function(e) {
		e.preventDefault();

		// $(this).find('input[type=submit]').prop('disabled', true);

		$.post(ajax_object.ajax_url, $(this).serialize(), function( response ) {

			if ( response.error == true ){
				$('#amo-login_error').html(response.response).show();
				// $(this).find('input[type=submit]').prop('disabled', false);
			} else if ( typeof response.response.destination != 'undefined' && response.response.destination != '' ) {
				location.href = response.response.destination;
			} else {
				location.href = location.href;
			}
		});
	})
});
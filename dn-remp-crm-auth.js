jQuery( function( $ ) {
	$( '.remp_login_form' ).on( 'submit', function( event ) {
		event.preventDefault();

		var form = $( this );

		form.addClass( 'remp_login_form__loading' );

		$.ajax( {
			url: form.attr( 'action' ),
			type: 'post',
			dataType: 'json',
			data: {
				email: form.find( 'input[type="email"]' ).val(),
				password: form.find( 'input[type="password"]' ).val()
			},
			success: function( response ) {
				document.cookie = 'n_token=' + response.access.token + ';max-age=31536000;samesite=strict';
				window.location.reload();
			},
			error: function( jqxhr ) {
				alert( jqxhr.responseJSON.message );
			},
			complete: function() {
				form.removeClass( remp_login_form__loading );
			}
		} );
	} );
} );
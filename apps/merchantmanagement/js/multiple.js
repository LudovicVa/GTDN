require(['jquery'], function() {
	$( document ).on( 'click', '.btn-add', function ( event ) {
		event.preventDefault();

		var field = $(this).closest( '.form-group' );
		var field_new = field.clone();

		$(this)
			.toggleClass( 'btn-default' )
			.toggleClass( 'btn-add' )
			.toggleClass( 'btn-danger' )
			.toggleClass( 'btn-remove' )
			.html( '-' );

		field_new.find( 'input' ).val( '' );
		field_new.insertAfter( field );
	} );

	$( document ).on( 'click', '.btn-remove', function ( event ) {
		event.preventDefault();
		$(this).closest( '.form-group' ).remove();
	} );
});
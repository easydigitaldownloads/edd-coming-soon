jQuery( function ( $ ) {
	$( '#edd_coming_soon' ).change( function () {
		var $container = $( '#edd_coming_soon_container' ),
			visible = $container.is( ':visible' );

		if ( $( this ).prop( 'checked' ) ) {
			if ( !visible )
				$container.slideDown( 'fast' );
		}
		else if ( visible )
			$container.slideUp( 'fast' );
	} );
} );
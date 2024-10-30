(function ($) {
	'use strict';

	$(
		function () {

			jQuery( "form#purgeall a" ).click(
				function (e) {

					if ( confirm( mt_cache_manager.purge_confirm_string ) === true ) {
						// Continue submitting form.
					} else {
						e.preventDefault();
					}

				}
			);
function nginx_show_option( selector ) {

				jQuery( '#' + selector ).on(
					'change',
					function () {

						if ( jQuery( this ).is( ':checked' ) ) {

							jQuery( '.' + selector ).show();

						} else {
							jQuery( '.' + selector ).hide();
						}

					}
				);

			}

			nginx_show_option( 'enable_purge' );
		}
	);
})( jQuery );

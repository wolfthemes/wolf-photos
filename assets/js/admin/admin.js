/**
 * Watermark Image Upload
 *
 */
var WolfPhotosAdmin = WolfPhotosAdmin || {},
	WolfPhotosAdminParams = WolfPhotosAdminParams || {},
	console = console || {};

/* jshint -W062 */
WolfPhotosAdmin = function ( $ ) {

	'use strict';

	return {

		init : function () {
			this.uploadButtons();
		},

		uploadButtons : function () {

			// Open Media Manager
			$( document ).on( 'click', '.wlfp-set-img', function( e ) {
				e.preventDefault();
				var $el = $( this ).parent();
				var uploader = wp.media({
					title : WolfPhotosAdminParams.chooseImage,
					library : {
						type : 'image/png'
					},
					multiple : false
				} )
				.on( 'select', function(){
					var selection = uploader.state().get('selection');
					var attachment = selection.first().toJSON();
					$('input', $el).val(attachment.id);
					$('img', $el).attr('src', attachment.url).show();
				} )
				.open();
			} );

			// Reset Image
			$( document ).on( 'click', '.wlfp-reset-img', function(){

				$( this ).parent().find( 'input' ).val( '' );
				$( this ).parent().find( '.wlfp-img-preview' ).hide();
				return false;

			} );
		}
	};

}( jQuery );

;( function( $ ) {

	'use strict';
	WolfPhotosAdmin.init();

} )( jQuery );
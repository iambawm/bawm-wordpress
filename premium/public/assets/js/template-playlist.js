(function( $ ) {
	'use strict';	

	var aiovg = window.aiovg_template_playlist || window.aiovg_premium;

	/**
	 * Called when the page has loaded.
	 */
	$(function() {

		$( '.aiovg-videos-template-playlist' ).each(function() {
			var $gallery = $( this );
			var $currentItem = $gallery.find( '.aiovg-active' );				
			var params = $gallery.data( 'params' );	

			$currentItem.find( '.aiovg-image' ).append( '<span class="aiovg-label-now-playing">' + aiovg.i18n.now_playing + '</span>' );

			// Change video.
			$gallery.on( 'click', '.aiovg-item-video', function( e ) {
				e.preventDefault();

				$currentItem = $( this );

				if ( $currentItem.hasClass( 'aiovg-active' ) ) return false;

				$gallery.find( '.aiovg-active' ).removeClass( 'aiovg-active' );
				$currentItem.addClass( 'aiovg-active' );

				$gallery.find( '.aiovg-label-now-playing' ).remove();
				$currentItem.find( '.aiovg-image' ).append( '<span class="aiovg-label-now-playing">' + aiovg.i18n.now_playing + '</span>' );

				// Player
				var src = $currentItem.data( 'src' );
				$gallery.find( 'iframe' ).attr( 'src', src );
			}); 

			// Enable click event on child elements.
			$gallery.on( 'click', 'a', function( event ) {
				event.stopPropagation();  			
			});

			// Autoplay next video.
			if ( params.autoadvance ) {
				window.addEventListener( 'message', function( event ) {
					if ( event.data.message == 'aiovg-video-ended' && event.data.id == params.uid ) {
						if ( $currentItem.is( ':last-child' ) ) {
							if ( params.loop ) {
								$gallery.find( '.aiovg-item-video' ).eq(0).trigger( 'click' );
							}
						} else {
							$currentItem.next( '.aiovg-item-video' ).trigger( 'click' );
						}		
					}
				});
			}
		});		

	});

})( jQuery );

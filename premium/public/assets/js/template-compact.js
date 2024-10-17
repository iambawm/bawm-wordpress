(function( $ ) {
	'use strict';	

	var aiovg = window.aiovg_template_compact || window.aiovg_premium;

	/**
	 * Called when the page has loaded.
	 */
	$(function() {		
		
		$( '.aiovg-videos-template-compact' ).each(function() {
			var $gallery = $( this );
			var $currentItem = $gallery.find( '.aiovg-active' );				
			var $moreButton = $gallery.find( '.aiovg-link-more' );	
			var params = $gallery.data( 'params' );	

			$currentItem.find( '.aiovg-responsive-container' ).append( '<span class="aiovg-label-now-playing">' + aiovg.i18n.now_playing + '</span>' );

			// Change Video.
			$gallery.on( 'click', '.aiovg-item-video', function( event ) {
				event.preventDefault();

				$currentItem = $( this );

				if ( $currentItem.hasClass( 'aiovg-active' ) ) return false;
	
				var id = $currentItem.data( 'id' );
				
				$gallery.find( '.aiovg-active' ).removeClass( 'aiovg-active' );
				$currentItem.addClass( 'aiovg-active' );

				$gallery.find( '.aiovg-label-now-playing' ).remove();
				$currentItem.find( '.aiovg-responsive-container' ).append( '<span class="aiovg-label-now-playing">' + aiovg.i18n.now_playing + '</span>' );
	
				// Player
				var src = $currentItem.data( 'src' );
				$gallery.find( 'iframe' ).attr( 'src', src );
	
				// Title
				if ( params.show_title ) {
					var title = $currentItem.find( '.aiovg-hidden-title' ).html();
					$gallery.find( '.aiovg-player-title' ).html( title );
				}
	
				// Playlists
				if ( params.show_playlist_button ) {
					$gallery.find( 'aiovg-playlist-button' ).replaceWith( '<aiovg-playlist-button post_id="' + id + '"></aiovg-playlist-button>' );
				}

				// Likes / Dislikes
				if ( params.show_like_button ) {
					$gallery.find( 'aiovg-like-button' ).replaceWith( '<aiovg-like-button post_id="' + id + '"></aiovg-like-button>' );
				}				

				// Description
				if ( params.show_description ) {
					var description = $currentItem.find( '.aiovg-hidden-description' ).html();
					$gallery.find( '.aiovg-player-description' ).html( description );
				}
	
				// Scroll to Top.
				$( 'html, body' ).animate({
					scrollTop: $gallery.offset().top - parseInt( aiovg.scroll_to_top_offset )
				}, 500 );
				
				// Load More.
				if ( params.autoadvance ) {
					if ( $currentItem.is( ':last-child' ) && $moreButton.is( ':visible' ) ) {
						$moreButton.trigger( 'click' );
					}
				}
			}); 

			// Enable click event on child elements.
			$gallery.on( 'click', 'button:not(.aiovg-link-more), a', function( event ) {
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

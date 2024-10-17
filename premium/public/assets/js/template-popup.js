(function( $ ) {
	'use strict';	

	/**
	 * Called when the page has loaded.
	 */
	$(function() {

		// Player: Init Popup.
		$( '.aiovg-video-template-popup' ).each(function() {
			var $this = $( this );			
			var ratio = $this.data( 'player_ratio' );

			$this.magnificPopup({				
				type: 'iframe',
				iframe: {
					markup: '<div class="mfp-iframe-scaler" style="padding-top: ' + ratio + ';">' +
						'<div class="mfp-close"></div>' +
						'<iframe class="mfp-iframe" frameborder="0" scrolling="no" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>' +								
					'</div>',
					patterns: {
						youtube: {
						  	index: 'youtubedotcom/',
						  	id: 'v=',
						  	src: '//www.youtube.com/embed/%id%?autoplay=1'
						},
						vimeo: {
						  	index: 'vimeodotcom/',
						  	id: '/',
						  	src: '//player.vimeo.com/video/%id%?autoplay=1'
						}					
					}
				},
				mainClass: 'aiovg aiovg-template-popup aiovg-magnific-popup mfp-fade',
				closeOnBgClick: false
			});					
		});

		// Gallery: Init Popup.
		$( '.aiovg-videos-template-popup' ).each(function() {
			var $gallery = $( this );	
			var $currentItem = null;
			var params = $gallery.data( 'params' );

			var iframe_markup = '<div class="mfp-iframe-scaler" style="padding-top: ' + params.player_ratio + ';">' +
				'<div class="mfp-close"></div>' +
				'<iframe class="mfp-iframe" frameborder="0" scrolling="no" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>' +								
			'</div>';

			iframe_markup += '<div class="mfp-bottom-bar">';
			iframe_markup += '<h2 class="mfp-title"></h2>';

			iframe_markup += '<div class="aiovg-content-after-player aiovg-margin-top aiovg-hide-if-empty">';

			if ( params.show_playlist_button ) {	
				iframe_markup += '<aiovg-playlist-button></aiovg-playlist-button>';
			}

			if ( params.show_like_button ) {	
				iframe_markup += '<aiovg-like-button></aiovg-like-button>';
			}			

			iframe_markup += '</div>';

			iframe_markup += '<div class="mfp-description"></div>';
			iframe_markup += '</div>';

			// Init popup.
			$gallery.magnificPopup({
				type: 'iframe',
				iframe: {
					markup: iframe_markup,
				},
				mainClass: 'aiovg aiovg-template-popup aiovg-magnific-popup mfp-fade',
				delegate: '.aiovg-popup-item',
				allowHTMLInStatusIndicator: true,
  				allowHTMLInTemplate: true,
				closeOnBgClick: false,				
				callbacks: { // To assign title, description	
					markupParse: function( template, values, item ) {
						if ( params.show_title ) {							
							values.title = item.el.find( '.aiovg-hidden-title' ).html();
						}	
						
						if ( params.show_description ) {	
							values.description = item.el.find( '.aiovg-hidden-description' ).html();	
						}			
					},
					change: function() {
						$currentItem = $gallery.find( '.aiovg-popup-item' ).eq( $.magnificPopup.instance.index );
						let id = $currentItem.data( 'id' );

						if ( params.show_playlist_button ) {
							this.content.find( 'aiovg-playlist-button' ).replaceWith( '<aiovg-playlist-button post_id="' + id + '"></aiovg-playlist-button>' );
						}

						if ( params.show_like_button ) {	
							this.content.find( 'aiovg-like-button' ).replaceWith( '<aiovg-like-button post_id="' + id + '"></aiovg-like-button>' );
						}
					}																		
				},
				gallery: {
					enabled: true
				}
			});
			
			// Enable click event on child elements.
			$gallery.on( 'click', '.aiovg-grid a', function( event ) {
				event.stopPropagation();            
			});

			// Autoplay next video.
			if ( params.autoadvance ) {
				window.addEventListener( 'message', function( event ) {
					if ( event.data.message == 'aiovg-video-ended' && event.data.id == params.uid ) {
						if ( $currentItem.is( ':last-child' ) ) {
							if ( params.loop ) {
								$.magnificPopup.instance.next();
							}
						} else {
							$.magnificPopup.instance.next();
						}
					}
				});
			}
		});		

		// Close on background click
		$( document ).on( 'click', '.aiovg-template-popup', function() {
			$.magnificPopup.close();
		});

		// Enable click event on child elements
		$( document ).on( 'click', '.aiovg-template-popup a, .aiovg-template-popup button, .aiovg-template-popup input', function( event ) {
			event.stopPropagation();            
		});

	});

})( jQuery );

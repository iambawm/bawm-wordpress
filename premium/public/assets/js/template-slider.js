(function( $ ) {
	'use strict';	

	var aiovg = window.aiovg_template_slider || window.aiovg_premium;

	/**
	 * Init slick slider.
	 */
	function initSlick( $gallery ) {
		$gallery.addClass( 'aiovg-slick-initialized' );

		var $slick = $gallery.find( '.aiovg-slick' ); 
		var params = $gallery.data( 'params' );			
		
		// Slick			
		$slick.on( 'init', function( event, slick ) {
			$gallery.find( '.aiovg-slick-arrows' ).show();
		}).slick({
			appendArrows: $gallery.find( '.aiovg-slick-arrows' ),
			prevArrow: $gallery.find( '.aiovg-slick-prev' ),
			nextArrow: $gallery.find( '.aiovg-slick-next' ),
			appendDots: $gallery.find( '.aiovg-section-videos' ),
			dotsClass: 'aiovg-slick-dots',
			customPaging: function( slider, i ) {					
				return '<div class="aiovg-slick-dot" style="color: ' + params.dot_color + '; font-size: ' + params.dot_size + '" role="button">&#9679;</div>';
			}
		});

		return $slick;
	}	

	/**
	 * Called when the page has loaded.
	 */
	$(function() {	

		// Layout: Classic.
		$( '.aiovg-slider-layout-classic' ).each(function() {
			var $gallery = $( this );
			initSlick( $gallery );
		});

		// Layout: Popup.
		$( '.aiovg-slider-layout-popup' ).each(function() {
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

			// Init slider.
			var $slick = initSlick( $gallery );				

			// Init popup.
			$gallery.magnificPopup({				
				type: 'iframe',
				iframe: {
					markup: iframe_markup,
				},
				mainClass: 'aiovg aiovg-template-slider aiovg-magnific-popup mfp-fade',				
				delegate: '.aiovg-item-video',
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
						$currentItem = $gallery.find( '.aiovg-item-video' ).eq( $.magnificPopup.instance.index );
						let id = $currentItem.data( 'id' );
						
						if ( params.show_playlist_button ) {	
							this.content.find( 'aiovg-playlist-button' ).replaceWith( '<aiovg-playlist-button post_id="' + id + '"></aiovg-playlist-button>' );
						}

						if ( params.show_like_button ) {	
							this.content.find( 'aiovg-like-button' ).replaceWith( '<aiovg-like-button post_id="' + id + '"></aiovg-like-button>' );
						}
					},
					open: function() {
						if ( params.slider_autoplay ) {
							$slick.slick( 'slickPause' );
						}
					},
					close: function() {
						if ( params.slider_autoplay ) {
							$slick.slick( 'slickPlay' );
						}
					}																		
				},
				gallery: {
				  	enabled: true
				}
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
								$.magnificPopup.instance.next();
							}
						} else {
							$.magnificPopup.instance.next();
						}
					}
				});
			}
		});	
		
		$( document ).on( 'click', '.aiovg-template-slider', function() {
			// Close on background click
			$.magnificPopup.close();
		});
		
		$( document ).on( 'click', '.aiovg-template-slider a, .aiovg-template-slider button, .aiovg-template-slider input', function( event ) {
			// Enable click event on child elements
			event.stopPropagation();            
		});

		// Layout: Compact.
		$( '.aiovg-slider-layout-compact' ).each(function() {
			var $gallery = $( this );			
			var $currentItem = $gallery.find( '.aiovg-active' );
			var params = $gallery.data( 'params' );		

			$currentItem.find( '.aiovg-responsive-container' ).append( '<span class="aiovg-label-now-playing">' + aiovg.i18n.now_playing + '</span>' );

			// Init slider.
			initSlick( $gallery );
			
			// Change video.
			$gallery.on( 'click', '.aiovg-item-video', function( event ) {
				event.preventDefault();

				$currentItem = $( this );

				if ( $currentItem.hasClass( 'aiovg-active' ) ) return false;				
	
				var id = $currentItem.data( 'id' );
				var $cloned = $gallery.find( '.aiovg-item-video-' + id );

				$gallery.find( '.aiovg-active' ).removeClass( 'aiovg-active' );
				$cloned.addClass( 'aiovg-active' );

				$gallery.find( '.aiovg-label-now-playing' ).remove();				
				$cloned.find( '.aiovg-responsive-container' ).append( '<span class="aiovg-label-now-playing">' + aiovg.i18n.now_playing + '</span>' );
	
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
		
		// Layout: Inline.
		$( '.aiovg-slider-layout-inline' ).each(function() {
			var $gallery = $( this );
			var params = $gallery.data( 'params' );			
			var forceAutoplay = false;
			var isLastSlide = false;

			// Init slider.
			var $slick = initSlick( $gallery );
			
			// On before slide change.
			$slick.on( 'beforeChange', function( event, slick, currentSlide, nextSlide ) {	
				if ( currentSlide == nextSlide ) {
					return false;
				}	

				$gallery.find( 'iframe' ).remove();
				
				var $currentItem = $( slick.$slides[ nextSlide ] );

				var src = $currentItem.data( 'src' );
				if ( forceAutoplay ) {
					forceAutoplay = false;

					var url = new URL( src );

					var searchParams = url.searchParams;
					searchParams.set( 'autoplay', 1 );

					url.search = searchParams.toString();

					src = url.toString();
				}

				$currentItem.find( '.aiovg-player' ).html( '<iframe src="' + src + '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>' );					
			});	

			// On after slide change.
			$slick.on( 'afterChange', function( event, slick, currentSlide ) {
				isLastSlide = false;

				if ( currentSlide == slick.$slides.length - 1 ) {
					isLastSlide = true;
				}
			});
			
			// Autoplay next video.
			if ( params.autoadvance ) {
				window.addEventListener( 'message', function( event ) {
					if ( event.data.message == 'aiovg-video-ended' && event.data.id == params.uid ) {
						if ( isLastSlide ) {
							if ( params.loop ) {
								forceAutoplay = true;
								$slick.slick( 'slickGoTo', 0 );
							}
						} else {
							forceAutoplay = true;
							$slick.slick( 'slickNext' );
						}		
					}
				});
			}
		});	

		// Gutenberg: On block init.
		if ( typeof wp !== 'undefined' && typeof wp['hooks'] !== 'undefined' ) {
			var intervalHandler;
			var retryCount;

			wp.hooks.addFilter( 'aiovg_block_init', 'aiovg/videos', function( attributes ) {				
				if ( attributes.template == 'slider' ) {
					if ( retryCount > 0 ) {
						clearInterval( intervalHandler );
					}

					retryCount = 0;

					intervalHandler = setInterval(function() {
						retryCount++;
						
						var $sliders = $( '.aiovg-videos-template-slider:not(.aiovg-slick-initialized)' );

						if ( $sliders.length > 0 || retryCount >= 10 ) {
							clearInterval( intervalHandler );
							retryCount = 0;

							$sliders.each(function() {		
								initSlick( $( this ) );
							});
						}
					}, 1000);
				}
			});
		}

	});

})( jQuery );

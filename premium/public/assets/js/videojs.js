(function( $ ) {
	'use strict';

	/**
	 * Init Ads.
	 */
	function initAds( $el, config ) {
		// Vars
		var playerId    = config.player_id;	
		var player      = config.player;
		var settings    = config.settings;	
		var container   = null;	
		var startEvent  = 'click';		
		var initialized = false;

		// Init IMA SDK.
		var initIma = function() {
			if ( initialized ) return false;
			initialized = true;
			
			player.ima.initializeAdDisplayContainer();
			container.removeEventListener( startEvent, initIma );
		};

		// On ad event.
		var onAdEvent = function( event ) {
			switch ( event.type ) {
				case google.ima.AdEvent.Type.STARTED:
					// Companion ads.
					if ( settings.ads.companion ) {
						initCompanionAds( event );
					}
					break;

				case google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED:
					if ( player && ! player.ended() && player.paused() ) {
						player.play();
					}					
					break;
			}			
		};

		// On ads manager loaded.
		var onAdsManagerLoaded = function() {
			var events = [
				google.ima.AdEvent.Type.ALL_ADS_COMPLETED,
				google.ima.AdEvent.Type.CLICK,
				google.ima.AdEvent.Type.COMPLETE,
				google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED,
				google.ima.AdEvent.Type.FIRST_QUARTILE,
				google.ima.AdEvent.Type.LOADED,
				google.ima.AdEvent.Type.MIDPOINT,
				google.ima.AdEvent.Type.PAUSED,
				google.ima.AdEvent.Type.RESUMED,
				google.ima.AdEvent.Type.STARTED,
				google.ima.AdEvent.Type.THIRD_QUARTILE
			];

			for ( var index = 0; index < events.length; index++ ) {
				player.ima.addEventListener( events[ index ], onAdEvent );
			}
		};			

		// Remove controls from the player on iPad to stop native controls from stealing
		// our click.
		try {
			var contentPlayer = document.getElementById( playerId + '_html5_api' );

			if ( ( navigator.userAgent.match( /iPad/i ) || navigator.userAgent.match( /Android/i ) ) &&	contentPlayer.hasAttribute( 'controls' ) ) {
				contentPlayer.removeAttribute( 'controls' );
			}
		} catch ( error ) {
			/** console.log( error ); */
		}

		// Start ads when the video player is clicked, but only the first time it's
		// clicked.
		if ( navigator.userAgent.match( /iPhone/i ) ||	navigator.userAgent.match( /iPad/i ) ||	navigator.userAgent.match( /Android/i ) ) {
			startEvent = 'touchend';
		}

		// ...
		var options = {
			id: playerId,
			adTagUrl: getVastUrl( player, settings ),
			adsManagerLoadedCallback: onAdsManagerLoaded
		};

		switch ( settings.ads.vpaidMode ) {
			case 'enabled':
				options.vpaidMode = google.ima.ImaSdkSettings.VpaidMode.ENABLED;
				break;

			case 'insecure':
				options.vpaidMode = google.ima.ImaSdkSettings.VpaidMode.INSECURE;
				break;

			case 'disabled':
				options.vpaidMode = google.ima.ImaSdkSettings.VpaidMode.DISABLED;
				break;
		}	

		player.ima( options );

		container = document.getElementById( playerId );
		container.addEventListener( startEvent, initIma );
		player.one( 'play', initIma );
	}

	/**
	 * Init companion ads.
	 */
	function initCompanionAds( event ) {
		var ad = event.getAd();					
		var elements = [];

		try {
			elements = window.AIOVGGetCompanionElements();
		} catch ( error ) { 
			/** console.log( error ); */
		}
		
		if ( elements.length ) {		
			var criteria = new google.ima.CompanionAdSelectionSettings();
			criteria.resourceType = google.ima.CompanionAdSelectionSettings.ResourceType.ALL;
			criteria.creativeType = google.ima.CompanionAdSelectionSettings.CreativeType.ALL;
			criteria.sizeCriteria = google.ima.CompanionAdSelectionSettings.SizeCriteria.SELECT_NEAR_MATCH;        
			
			for ( var i = 0; i < elements.length; i++ ) {													
				var id     = elements[ i ].id;
				var width  = elements[ i ].width;
				var height = elements[ i ].height;
				
				try {
					// Get a list of companion ads for an ad slot size and CompanionAdSelectionSettings.
					var companionAds = ad.getCompanionAds( width, height, criteria );
					var companionAd  = companionAds[0];
				
					// Get HTML content from the companion ad.
					var content = companionAd.getContent();
			
					// Write the content to the companion ad slot.
					var div = document.getElementById( id );
					div.innerHTML = content;
				} catch ( error ) { 
					/** console.log( error ); */
				}				
			}	
		}
	}

	/**
	 * Get VAST URL.
	 */
	function getVastUrl( player, settings ) {
		var url = settings.ads.tagUrl;

		url = url.replace( '[domain]', encodeURIComponent( settings.site_url ) );
		url = url.replace( '[player_width]', player.currentWidth() );
		url = url.replace( '[player_height]', player.currentHeight() );
		url = url.replace( '[random_number]', Date.now() );
		url = url.replace( '[timestamp]', Date.now() );
		url = url.replace( '[page_url]', encodeURIComponent( window.location ) );
		url = url.replace( '[referrer]', encodeURIComponent( document.referrer ) );
		url = url.replace( '[ip_address]', settings.ip_address );
		url = url.replace( '[post_id]', settings.post_id );
		url = url.replace( '[post_title]', encodeURIComponent( settings.post_title ) );
		url = url.replace( '[post_excerpt]', encodeURIComponent( settings.post_excerpt ) );
		url = url.replace( '[video_file]', encodeURIComponent( player.currentSrc() ) );
		url = url.replace( '[video_duration]', player.duration() || '' );
		url = url.replace( '[autoplay]', settings.player.autoplay || false );

		return url;
	}

	/**
	 * Called when the page has loaded.
	 */
	$(function() {
		
		// Add premium player features.
		$( '.aiovg-player-standard' ).on( 'player.init', function( event, config ) {
			// Init ads.
			if ( config.settings.hasOwnProperty( 'ads' ) ) {
				initAds( $( this ), config );
			}					
		});

	});
	
})( jQuery );

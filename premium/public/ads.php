<?php

/**
 * Ads.
 *
 * @link       https://plugins360.com
 * @since      1.5.7
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Public_Ads class.
 *
 * @since 1.5.7
 */
class AIOVG_Premium_Public_Ads {

	/**
	 * Get things started.
	 *
	 * @since 1.5.7
	 */
	public function __construct() {		
		// Register shortcode(s)
		add_shortcode( "companion", array( $this, "display_companion_ads" ) );		
	}

	/**
	 * Filters the player page URL.
	 * 
	 * @since  1.5.7
	 * @param  string $url     Player page URL.
	 * @param  int    $post_id Post ID.
	 * @param  array  $atts    Array of video attributes.
	 * @return string $url     Modified Player page URL.
	 */
	public function player_page_url( $url, $post_id, $atts = array() ) {
		if ( isset( $atts['ads'] ) && 'false' == $atts['ads'] ) {
			$url = add_query_arg( 'ads', 0, $url );
		}

		if ( isset( $atts['vast_url'] ) ) {
			$url = add_query_arg( 'vast_url', urlencode( $atts['vast_url'] ), $url );
		}
		
		return $url;		
	}

	/**
	 * Filters the player settings.
	 * 
	 * @since  3.3.1
	 * @param  array $settings Player settings.
	 * @param  array $params   Player params.
	 * @return array $settings Filtered player settings.
	 */
	public function videojs_player_settings( $settings, $params ) {
		$ads_settings = get_option( 'aiovg_ads_settings' );

		// Check if the ads enabled
		$enabled = true;

		if ( isset( $params['ads'] ) ) {
			if ( 0 == (int) $params['ads'] ) {
				$enabled = false;
			}				
		} else {		
			if ( 'aiovg_videos' == $params['post_type'] ) {					
				$disable_ads = get_post_meta( $params['post_id'], 'disable_ads', true );
				if ( ! empty( $disable_ads ) ) {
					$enabled = false;
				}						
			}			
		}

		if ( ! $enabled ) {
			return $settings;
		}

		// Get VAST URL
		$vast_url = '';

		if ( isset( $params['vast_url'] ) ) {
			$vast_url = $params['vast_url'];				
		} else {		
			if ( 'aiovg_videos' == $params['post_type'] ) {	
				$override_vast_url = get_post_meta( $params['post_id'], 'override_vast_url', true );
				if ( $override_vast_url ) {
					$vast_url = get_post_meta( $params['post_id'], 'vast_url', true );
				}					
			}			
		}

		if ( empty( $vast_url ) ) {					
			$vast_url = $ads_settings['vast_url'];

			if ( empty( $vast_url ) ) {
				return $settings;
			}
		}		

		// ...
		$settings['ads'] = array(
			'enabled'   => true,
			'tagUrl'    => aiovg_sanitize_url( $vast_url ),
			'vpaidMode' => sanitize_text_field( $ads_settings['vpaid_mode'] ),
			'companion' => ( 0 == $ads_settings['use_gpt'] ? true : false )
		);

		$settings['site_url'] = aiovg_sanitize_url( home_url() );
		$settings['post_title'] = $params['post_id'] > 0 ? sanitize_text_field( get_the_title( $params['post_id'] ) ) : '';
		$settings['post_excerpt'] = $params['post_id'] > 0 ? sanitize_text_field( aiovg_get_excerpt( $params['post_id'], 160, '', false ) ) : '';
		$settings['ip_address'] = aiovg_get_ip_address();
				
		return $settings;		
	}

	/**
	 * Filters the player settings.
	 * 
	 * @since  3.3.1
	 * @param  array $settings Player settings.
	 * @param  array $params   Player params.
	 * @return array $settings Filtered player settings.
	 */
	public function vidstack_player_settings( $settings, $params ) {
		$settings = $this->videojs_player_settings( $settings, $params );

		if ( isset( $settings['ads'] ) ) {
			$settings['player']['ads'] = $settings['ads'];
			unset( $settings['ads'] );
		}			
		
		return $settings;	
	}	

	/**
	 * Filters the player settings.
	 * 
	 * @since  3.3.1
	 * @param  array $settings Player settings.
	 * @return array $settings Filtered player settings.
	 */
	public function iframe_videojs_player_settings( $settings ) {
		$ads_settings = get_option( 'aiovg_ads_settings' );

		// Check if the ads enabled
		$enabled = true;

		if ( isset( $_GET['ads'] ) ) {
			if ( 0 == (int) $_GET['ads'] ) {
				$enabled = false;
			}				
		} else {		
			if ( 'aiovg_videos' == $settings['post_type'] ) {					
				$disable_ads = get_post_meta( $settings['post_id'], 'disable_ads', true );
				if ( ! empty( $disable_ads ) ) {
					$enabled = false;
				}						
			}			
		}

		if ( ! $enabled ) {
			return $settings;
		}	
		
		// Get VAST URL
		$vast_url = '';

		if ( isset( $_GET['vast_url'] ) ) {
			$vast_url = $_GET['vast_url'];				
		} else {		
			if ( 'aiovg_videos' == $settings['post_type'] ) {	
				$override_vast_url = get_post_meta( $settings['post_id'], 'override_vast_url', true );
				if ( $override_vast_url ) {
					$vast_url = get_post_meta( $settings['post_id'], 'vast_url', true );
				}					
			}			
		}

		if ( empty( $vast_url ) ) {
			$vast_url = $ads_settings['vast_url'];
		}

		if ( empty( $vast_url ) ) {
			return $settings;
		}	

		// ...
		$settings['ads'] = array(
			'enabled'   => true,
			'tagUrl'    => aiovg_sanitize_url( $vast_url ),
			'vpaidMode' => sanitize_text_field( $ads_settings['vpaid_mode'] ),
			'companion' => ( 0 == $ads_settings['use_gpt'] ? true : false )
		);   

		$settings['site_url'] = aiovg_sanitize_url( home_url() );
		$settings['post_title'] = $settings['post_id'] > 0 ? sanitize_text_field( get_the_title( $settings['post_id'] ) ) : '';
		$settings['post_excerpt'] = $settings['post_id'] > 0 ? sanitize_text_field( aiovg_get_excerpt( $settings['post_id'], 160, '', false ) ) : '';
		$settings['ip_address'] = aiovg_get_ip_address();

		return $settings;		
	}

	/**
	 * Filters the player settings.
	 * 
	 * @since  3.3.1
	 * @param  array $settings Player settings.
	 * @return array $settings Filtered player settings.
	 */
	public function iframe_vidstack_player_settings( $settings ) {
		$settings = $this->iframe_videojs_player_settings( $settings );

		if ( isset( $settings['ads'] ) ) {
			$settings['player']['ads'] = $settings['ads'];
			unset( $settings['ads'] );
		}	

		return $settings;	
	}

	/**
	 * Enqueue necessary scripts.
	 *
	 * @since 2.4.0
	 * @param array $settings Player settings.
	 */
	public function videojs_player_scripts( $settings ) {
		if ( ! isset( $settings['ads'] ) ) {
			return;
		}

		// Enqueue style dependencies
		wp_enqueue_style( 
			AIOVG_PLUGIN_SLUG . '-contrib-ads', 
			AIOVG_PLUGIN_URL . 'vendor/videojs/plugins/contrib-ads/videojs-contrib-ads.min.css', 
			array(), 
			'7.5.2',
			'all' 
		);

		wp_enqueue_style( 
			AIOVG_PLUGIN_SLUG . '-ima', 
			AIOVG_PLUGIN_URL . 'vendor/videojs/plugins/ima/videojs.ima.min.css', 
			array(), 
			'2.3.0', 
			'all' 
		);

		// Enqueue script dependencies
		wp_enqueue_script( 
			AIOVG_PLUGIN_SLUG . '-ima-sdk', 
			'https://imasdk.googleapis.com/js/sdkloader/ima3.js', 
			array(), 
			AIOVG_PLUGIN_VERSION, 
			array( 'strategy' => 'defer' ) 
		);

		wp_enqueue_script( 
			AIOVG_PLUGIN_SLUG . '-contrib-ads', 
			AIOVG_PLUGIN_URL . 'vendor/videojs/plugins/contrib-ads/videojs-contrib-ads.min.js', 
			array( AIOVG_PLUGIN_SLUG . '-videojs' ), 
			'7.5.2', 
			array( 'strategy' => 'defer' ) 
		);

		wp_enqueue_script( 
			AIOVG_PLUGIN_SLUG . '-ima', 
			AIOVG_PLUGIN_URL . 'vendor/videojs/plugins/ima/videojs.ima.min.js', 
			array( AIOVG_PLUGIN_SLUG . '-videojs' ), 
			'2.3.0', 
			array( 'strategy' => 'defer' ) 
		);

		wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-premium-player' );
	}

	/**
	 * Load the necessary styles in the player header.
	 *
	 * @since 3.3.1
	 * @param array $settings Player settings array.
	 */
	public function iframe_videojs_player_styles( $settings ) {
		if ( ! isset( $settings['ads'] ) ) {
			return;
		}
		?>
		<link rel="stylesheet" href="<?php echo AIOVG_PLUGIN_URL; ?>vendor/videojs/plugins/contrib-ads/videojs-contrib-ads.min.css?v=7.5.2" />
		<link rel="stylesheet" href="<?php echo AIOVG_PLUGIN_URL; ?>vendor/videojs/plugins/ima/videojs.ima.min.css?v=2.3.0" />   
        <?php
	}

	/**
	 * Load the necessary scripts in the player footer.
	 *
	 * @since 3.3.1
	 * @param array $settings Player settings.
	 */
	public function iframe_videojs_player_scripts( $settings ) {
		if ( ! isset( $settings['ads'] ) ) {
			return;
		}	
		?>
        <script src="https://imasdk.googleapis.com/js/sdkloader/ima3.js?v=<?php echo AIOVG_PLUGIN_VERSION; ?>" type="text/javascript" defer></script>
		<script src="<?php echo AIOVG_PLUGIN_URL; ?>vendor/videojs/plugins/contrib-ads/videojs-contrib-ads.min.js?v=7.5.2" type="text/javascript" defer></script>
		<script src="<?php echo AIOVG_PLUGIN_URL; ?>vendor/videojs/plugins/ima/videojs.ima.min.js?v=2.3.0" type="text/javascript" defer></script>
		<script type="text/javascript">
			// Init ads.
			function initAds( player, settings ) {
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
  				// our click
				try {
					var contentPlayer = document.getElementById( 'player_html5_api' );

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
					id: 'player',
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

				container = document.getElementById( 'player' );
				container.addEventListener( startEvent, initIma );
				player.one( 'play', initIma );
			}
			
			// Init companion ads.
			function initCompanionAds( event ) {
				var ad = event.getAd();
				var elements = [];

				try {
					elements = window.top.AIOVGGetCompanionElements();
				} catch ( error ) { 
					/** console.log( error ); */
				}
				
				if ( elements.length ) {		
					var criteria = new google.ima.CompanionAdSelectionSettings();
					criteria.resourceType = google.ima.CompanionAdSelectionSettings.ResourceType.ALL;
					criteria.creativeType = google.ima.CompanionAdSelectionSettings.CreativeType.ALL;
					criteria.sizeCriteria = google.ima.CompanionAdSelectionSettings.SizeCriteria.SELECT_NEAR_MATCH;        
					
					for ( var i = 0; i < elements.length; i++ ) {													
						var id = elements[ i ].id;
						var width = elements[ i ].width;
						var height = elements[ i ].height;
						
						try {
							// Get a list of companion ads for an ad slot size and CompanionAdSelectionSettings
							var companionAds = ad.getCompanionAds( width, height, criteria );
							var companionAd  = companionAds[0];
						
							// Get HTML content from the companion ad.
							var content = companionAd.getContent();
					
							// Write the content to the companion ad slot.
							var div = window.top.document.getElementById( id );
							div.innerHTML = content;
						} catch ( error ) {
							/** console.log( error ); */
						}				
					}	
				}
			}

			// Get VAST URL.
			function getVastUrl( player, settings ) {
				var url = decodeURIComponent( settings.ads.tagUrl );

				url = url.replace( '[domain]', encodeURIComponent( settings.site_url ) );
				url = url.replace( '[player_width]', player.currentWidth() );
				url = url.replace( '[player_height]', player.currentHeight() );
				url = url.replace( '[random_number]', Date.now() );
				url = url.replace( '[timestamp]', Date.now() );
				url = url.replace( '[page_url]', encodeURIComponent( window.top.location ) );
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

			// ...
			window.addEventListener( 'player.init', function( event ) {
       			var player   = event.detail.player;
				var settings = event.detail.settings;

				initAds( player, settings );						
			});
		</script>
        <?php		
	}	

	/**
	 * Load the necessary scripts in the player footer.
	 *
	 * @since 3.3.1
	 * @param array $settings Player settings.
	 */
	public function iframe_vidstack_player_scripts( $settings ) {
		if ( ! isset( $settings['player']['ads'] ) ) {
			return false;
		}	
		?>
		<script type="text/javascript">
			// Init Ads
			function initAds( player, settings ) {
				player.ads.config.tagUrl = getVastUrl( player, settings );
						
				var loaded = false;

				player.ads.on( 'loaded', () => {
					if ( loaded ) return false;
					loaded = true;                        

					var adsManager = player.ads.manager;

					var playButton = document.createElement( 'button' );
					playButton.type = 'button';
					playButton.className = 'plyr__control plyr__control--overlaid';
					playButton.style.display = 'none';
					playButton.innerHTML = '<svg aria-hidden="true" focusable="false"><use xlink:href="#plyr-play"></use></svg><span class="plyr__sr-only">Play</span>';
					
					document.querySelector( '.plyr__ads' ).appendChild( playButton );                        

					playButton.addEventListener( 'click', function() {
						playButton.style.display = 'none';
						adsManager.resume();
					});

					adsManager.addEventListener( google.ima.AdEvent.Type.STARTED, function( event ) {
						if ( settings.player.ads.companion ) {
							initCompanionAds( event );
						}								
					});

					adsManager.addEventListener( google.ima.AdEvent.Type.PAUSED, function( event ) {
						playButton.style.display = '';
					});
				});
			}

			// Init companion ads.
			function initCompanionAds( event ) {
				var ad = event.getAd();
				var elements = [];

				try {
					elements = window.top.AIOVGGetCompanionElements();
				} catch ( error ) { 
					/** console.log( error ); */
				}
				
				if ( elements.length ) {		
					var criteria = new google.ima.CompanionAdSelectionSettings();
					criteria.resourceType = google.ima.CompanionAdSelectionSettings.ResourceType.ALL;
					criteria.creativeType = google.ima.CompanionAdSelectionSettings.CreativeType.ALL;
					criteria.sizeCriteria = google.ima.CompanionAdSelectionSettings.SizeCriteria.SELECT_NEAR_MATCH;        
					
					for ( var i = 0; i < elements.length; i++ ) {													
						var id = elements[ i ].id;
						var width = elements[ i ].width;
						var height = elements[ i ].height;
						
						try {
							// Get a list of companion ads for an ad slot size and CompanionAdSelectionSettings
							var companionAds = ad.getCompanionAds( width, height, criteria );
							var companionAd  = companionAds[0];
						
							// Get HTML content from the companion ad.
							var content = companionAd.getContent();
					
							// Write the content to the companion ad slot.
							var div = window.top.document.getElementById( id );
							div.innerHTML = content;
						} catch ( error ) { 
							/** console.log( error ); */
						}				
					}	
				}
			}

			// Get VAST  URL.
			function getVastUrl( player, settings ) {
				var url = decodeURIComponent( settings.player.ads.tagUrl );

				url = url.replace( '[domain]', encodeURIComponent( settings.site_url ) );
				url = url.replace( '[player_width]', player.elements.container.offsetWidth );
				url = url.replace( '[player_height]', player.elements.container.offsetHeight );
				url = url.replace( '[random_number]', Date.now() );
				url = url.replace( '[timestamp]', Date.now() );
				url = url.replace( '[page_url]', encodeURIComponent( window.top.location ) );
				url = url.replace( '[referrer]', encodeURIComponent( document.referrer ) );
				url = url.replace( '[ip_address]', settings.ip_address );
				url = url.replace( '[post_id]', settings.post_id );
				url = url.replace( '[post_title]', encodeURIComponent( settings.post_title ) );
				url = url.replace( '[post_excerpt]', encodeURIComponent( settings.post_excerpt ) );
				url = url.replace( '[video_file]', encodeURIComponent( player.source ) );
				url = url.replace( '[video_duration]', player.duration || '' );
				url = url.replace( '[autoplay]', settings.player.autoplay || false );
				
				return url;
			}
			
			window.addEventListener( 'player.init', function( event ) {
       			var player   = event.detail.player;
				var settings = event.detail.settings;

				initAds( player, settings );						
			});
		</script>
        <?php		
	}

	/**
	 * Output the shortcode [companion].
	 *
	 * @since 1.5.7
	 * @param array $atts An associative array of attributes.
	 */
	public function display_companion_ads( $atts ) {
		$ads_settings = get_option( 'aiovg_ads_settings' );		

		$defaults = array(
			'id'           => aiovg_get_uniqid(),
			'width'        => '',
			'height'       => '',
			'ad_unit_path' => ''
		);

		$attributes = shortcode_atts( $defaults, $atts, 'companion' );
		
		$content = '';
		
		if ( ! empty( $attributes['width'] ) && ! empty( $attributes['height'] ) ) {		
			if ( ! empty( $ads_settings['use_gpt'] ) ) {
				wp_enqueue_script( 
					AIOVG_PLUGIN_SLUG . '-gpt-proxy', 
					'https://imasdk.googleapis.com/js/sdkloader/gpt_proxy.js', 
					array(), 
					AIOVG_PLUGIN_VERSION,
					array( 'strategy' => 'defer' )
				);
			}
			
			ob_start();
			include AIOVG_PLUGIN_DIR . 'premium/public/templates/companion.php';
			return ob_get_clean();			
		}
		
		return $content;	
	}

	/**
	 * Load the GPT library if necessary.
	 *
	 * @since 1.5.7
	 */
	public function wp_print_scripts() {	
		$ads_settings = get_option( 'aiovg_ads_settings' );

		if ( 0 == $ads_settings['use_gpt'] ) return;
		?>
        <script type='text/javascript'>
       		var googletag = googletag || {};
       		googletag.cmd = googletag.cmd || [];

       		(function() {
         		var gads = document.createElement( 'script' );
         		gads.async = true;
         		gads.type = 'text/javascript';
         		gads.src = '//www.googletagservices.com/tag/js/gpt.js';

         		var node = document.querySelector( 'script' );
         		node.parentNode.insertBefore( gads, node );
       		})();
     	</script>
        <?php
	}

	/**
	 * Load the necessary scripts in the site footer.
	 *
	 * @since 1.5.7
	 */
	public function wp_print_footer_scripts() {	
		$ads_settings = get_option( 'aiovg_ads_settings' );

		if ( 0 == $ads_settings['use_gpt'] ) :
		?>
        <script type='text/javascript'>
			(function( $ ) {
				'use strict';
				
				window.AIOVGGetCompanionElements = function() {					
					var elements = [];
					
					jQuery( '.aiovg-companion' ).each(function() {										
						elements.push({
							id: jQuery( this ).attr( 'id' ),				
							width: parseInt( jQuery( this ).data( 'width' ) ),
							height: parseInt( jQuery( this ).data( 'height' ) )
						});							
					});
					
					return elements;					
				};			
			})( jQuery );
		</script>
        <?php
		endif;		
	}	

}

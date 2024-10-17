(function( $ ) {
	'use strict';	

	// Load script files.
	var loadScript = ( file ) => {
		return new Promise(( resolve, reject ) => { 
			if ( document.querySelector( '#' + file.id ) !== null ) {
				resolve();
				return false;
			}

			const script = document.createElement( 'script' );

			script.id  = file.id;
			script.src = file.src;

			script.onload  = () => resolve();
			script.onerror = () => reject();

			document.body.appendChild( script );
		});
	}

	/**
	 * Called when the page has loaded.
	 */
	$(function() {
		
		// Load the required script files.
		var plugin_url = aiovg_premium.plugin_url;
		var plugin_version = aiovg_premium.plugin_version;

		var scripts = [			
			{ 
				selector: '.aiovg-videos-template-compact', 
				id: 'all-in-one-video-gallery-template-compact-js',
				src: plugin_url + 'public/assets/js/template-compact.min.js?ver=' + plugin_version 
			},
			{ 
				selector: '.aiovg-videos-template-playlist', 
				id: 'all-in-one-video-gallery-template-playlist-js',
				src: plugin_url + 'public/assets/js/template-playlist.min.js?ver=' + plugin_version 
			},
			{ 
				selector: '.aiovg-video-template-popup', 
				id: 'all-in-one-video-gallery-template-popup-js',
				src: plugin_url + 'public/assets/js/template-popup.min.js?ver=' + plugin_version 
			},
			{ 
				selector: '.aiovg-videos-template-popup', 
				id: 'all-in-one-video-gallery-template-popup-js',
				src: plugin_url + 'public/assets/js/template-popup.min.js?ver=' + plugin_version 
			},
			{ 
				selector: '.aiovg-videos-template-slider', 
				id: 'all-in-one-video-gallery-template-slider-js',
				src: plugin_url + 'public/assets/js/template-slider.min.js?ver=' + plugin_version 
			}
		];

		for ( var i = 0; i < scripts.length; i++ ) {
			var script = scripts[ i ];
			if ( document.querySelector( script.selector ) !== null ) {
				loadScript( script );
			}
		}			

	});

})( jQuery );

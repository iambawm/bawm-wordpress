(function( $ ) {
	'use strict';

	/**
	 * Convert base64/URLEncoded data component to raw binary data held in a string.
	 */
	function convertDataURItoBlob( dataURI ) {
		var byteString;
	  
		if ( dataURI.split( ',' )[0].indexOf( 'base64' ) >= 0 ) {
			byteString = atob( dataURI.split( ',' )[1] );
		} else {
			byteString = unescape( dataURI.split( ',' )[1] );
		}

		// Separate out the mime component.
		var mimeString = dataURI.split( ',' )[0].split( ':' )[1].split( ';' )[0];
	  
		// Write the bytes of the string to a typed array.
		var ia = new Uint8Array( byteString.length );
	  
		for ( var i = 0; i < byteString.length; i++ ) {
		  	ia[ i ] = byteString.charCodeAt( i );
		}
	  
		return new Blob([ ia ], {
		  	type: mimeString
		});
	}

	/**
	 * Resolve relative file paths as absolute URLs.
	 */
	function resolveURL( url ) {
		if ( ! url ) return url;

		if ( url.indexOf( '://' ) > 0 || url.indexOf( '//' ) === 0 ) {
			return url;
		}
		
		if ( url.indexOf( '/' ) === 0 ) {
			url = aiovg_admin.site_url + url;
		} else {
			url = aiovg_admin.site_url + '/' + url;
		}

		return url;
	}

	/**
	 * Called when the page has loaded.
	 */
	$(function() {

		// Vars
		var videoURL = resolveURL( $( '#aiovg-mp4' ).val() );

		// Dashboard: Toggle fields based on the selected slider layout.
		$( '#aiovg-shortcode-form-videos select[name=slider_layout]' ).on( 'change', function() {			
			var value = $( this ).val();			
			$( '#aiovg-shortcode-form-videos' ).aiovgReplaceClass( /\aiovg-slider-layout-\S+/ig, 'aiovg-slider-layout-' + value );
		}).trigger( 'change' );				

		// Videos: Capture Image.
		if ( $( '#aiovg-html5-thumbnail-generator' ).length ) {
			// Init "Capture Image" Popup.
			var modalBody = document.getElementById( 'aiovg-thumbnail-generator-modal-body' ).innerHTML;

			$( '#aiovg-html5-thumbnail-generator' ).magnificPopup({
				items: {
					src: '#aiovg-thumbnail-generator-modal',
					type: 'inline'				
				},
				callbacks: {
					open: function() {
						document.getElementById( 'aiovg-thumbnail-generator-modal-body' ).innerHTML = modalBody;
						videoURL = resolveURL( document.getElementById( 'aiovg-mp4' ).value );

						if ( videoURL ) {
							var videoEl       = document.getElementById( 'aiovg-thumbnail-generator-player' );
							var canvasEl      = document.getElementById( 'aiovg-thumbnail-generator-canvas' );
							var canvasContext = canvasEl.getContext( '2d' );
							var seekTo        = document.getElementById( 'aiovg-thumbnail-generator-seekto' );
							var captureButton = document.getElementById( 'aiovg-thumbnail-generator-button' );

							// Set the video source.
							videoEl.src = videoURL;

							// Load the video and show it.
							videoEl.load();

							// On video playback failed.
							videoEl.addEventListener( 'error', function() {
								var html = '<div class="aiovg-notice aiovg-notice-error">' + aiovg_premium_admin.i18n.thumbnail_generator_cors_error + '</div>';
								$( '#aiovg-thumbnail-generator-modal-body' ).prepend( html );
							}, true	);

							// Load metadata of the video to get video duration and dimensions.
							videoEl.addEventListener( 'loadedmetadata', function() {
								var duration = videoEl.duration;
								var options  = '';

								// Set canvas dimensions same as video dimensions.
								canvasEl.width  = videoEl.videoWidth;
								canvasEl.height = videoEl.videoHeight;

								// Set options in dropdown at 4 seconds interval.
								for ( var i = 0; i < Math.floor( duration ); i = i + 4 ) {
									options += '<option value="' + i + '">' + i + '</option>';
								}
								
								seekTo.innerHTML = options;
								
								// Enable the dropdown and the "Capture This Scene" button.
								seekTo.disabled = false;
								captureButton.disabled = false;

								// On changing the duration dropdown, seek the video to that duration.
								seekTo.addEventListener( 'change', function() {
									videoEl.currentTime = $( this ).val();
									
									// Seeking might take a few milliseconds, so disable the dropdown and the "Capture This Scene" button.
									seekTo.disabled = true;
									captureButton.disabled = true;
								});

								// On seeking video to the specified duration is complete.
								videoEl.addEventListener( 'timeupdate', function() {									
									// Re-enable the dropdown and the "Capture This Scene" button.
									seekTo.disabled = false;
									captureButton.disabled = false;
								});

								// On clicking the "Capture This Scene" button, set the video in the canvas and download the base-64 encoded image data.
								var isCaptureButtonClicked = false;

								captureButton.addEventListener( 'click', function() {	
									if ( isCaptureButtonClicked ) return false;
									isCaptureButtonClicked = true;
									
									var buttonLabel = captureButton.innerHTML;
									captureButton.innerHTML = '<div class="spinner"></div>';
									
									canvasContext.drawImage( videoEl, 0, 0, videoEl.videoWidth, videoEl.videoHeight );

									var canvasDataURL = '';

									try {
										canvasDataURL = canvasEl.toDataURL();
									} catch ( error ) {
										captureButton.innerHTML = buttonLabel;

										seekTo.disabled  = true;
										captureButton.disabled = true;

										var html = '<div class="aiovg-notice aiovg-notice-error">' + aiovg_premium_admin.i18n.thumbnail_generator_cors_error + '</div>';
										$( '#aiovg-thumbnail-generator-modal-body' ).prepend( html );
										
										return false;
									}

									var formData = new FormData();

									formData.append( 'action', 'aiovg_upload_base64_image' );
									formData.append( 'video', videoURL );
									formData.append( 'index', $( '#aiovg-thumbnail-generator .aiovg-item-thumbnail' ).length );
									formData.append( 'security', aiovg_admin.ajax_nonce );

									var blob = convertDataURItoBlob( canvasDataURL );
									formData.append( 'image_data', blob, 'temp.png' );

									var xmlhttp;

									if ( window.XMLHttpRequest ) {
										xmlhttp = new XMLHttpRequest();
									} else {
										xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
									}

									xmlhttp.onreadystatechange = function() {				
										if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
											captureButton.innerHTML = buttonLabel;

											if ( xmlhttp.responseText != '' ) {
												$( xmlhttp.responseText ).insertBefore( '#aiovg-html5-thumbnail-generator' );
												$( '#aiovg-thumbnail-generator input[type=radio]:last' ).prop( 'checked', true ).trigger( 'change' );
											}					
									
											if ( $( '#aiovg-thumbnail-generator .aiovg-item-thumbnail' ).length > 0 ) {
												$( '#aiovg-thumbnail-generator .aiovg-header' ).html( aiovg_premium_admin.i18n.thumbnail_generator_select_image );
												$( '#aiovg-thumbnail-generator .aiovg-footer' ).show();
											} else {
												$( '#aiovg-thumbnail-generator .aiovg-header' ).html( aiovg_premium_admin.i18n.thumbnail_generator_capture_image );
												$( '#aiovg-thumbnail-generator .aiovg-footer' ).hide();
											}

											$.magnificPopup.close();
										}
									};

									xmlhttp.open( 'POST', ajaxurl );
									xmlhttp.send( formData );
								});
							});
						} else {
							// Set error.
							var html = '<div class="aiovg-notice aiovg-notice-error">' + aiovg_premium_admin.i18n.thumbnail_generator_video_not_found + '</div>';
							$( '#aiovg-thumbnail-generator-modal-body' ).prepend( html );
						}
					},
					close: function() {
						document.getElementById( 'aiovg-thumbnail-generator-modal-body' ).innerHTML = '';
					}
				}
			});			
		}		

		// Videos: Generate images using FFMPEG.
		$( '#aiovg-mp4' ).on( 'blur file.uploaded', function() {
			if ( ! aiovg_premium_admin.ffmpeg_enabled ) return false;

			var __videoURL = resolveURL( $( this ).val() );

			if ( ! __videoURL || __videoURL == videoURL ) {
				return false;
			}

			videoURL = __videoURL;

			var html = '<span class="spinner"></span> ';
			html += '<span>' + aiovg_premium_admin.i18n.thumbnail_generator_processing + '</span>';

			$( '#aiovg-thumbnail-generator' ).addClass( 'aiovg-processing' )
				.find( '.aiovg-header' )
				.html( html );

			var data = {
				'action': 'aiovg_ffmpeg_generate_images',
				'url': videoURL,
				'security': aiovg_admin.ajax_nonce
			};
			
			$.post( ajaxurl, data, function( response ) {
				$( '#aiovg-thumbnail-generator' ).removeClass( 'aiovg-processing' );
				$( '#aiovg-thumbnail-generator .aiovg-header' ).html( '' );
				$( '#aiovg-thumbnail-generator .aiovg-item-thumbnail' ).remove();

				if ( '' != response ) {
					if ( $( '#aiovg-html5-thumbnail-generator' ).length ) {
						$( response ).insertBefore( '#aiovg-html5-thumbnail-generator' );
					} else {
						$( '#aiovg-thumbnail-generator .aiovg-body' ).html( response );
					}		

					if ( $( '#aiovg-image' ).val() == '' ) {
						$( '#aiovg-thumbnail-generator input[type=radio]:first' ).prop( 'checked', true ).trigger( 'change' );
					}						
				}					
		
				if ( $( '#aiovg-thumbnail-generator .aiovg-item-thumbnail' ).length > 0 ) {
					$( '#aiovg-thumbnail-generator .aiovg-header' ).html( aiovg_premium_admin.i18n.thumbnail_generator_select_image );
					$( '#aiovg-thumbnail-generator .aiovg-footer' ).show();
				} else {
					html = '<span class="aiovg-text-error">' + aiovg_premium_admin.i18n.ffmpeg_thumbnail_generation_failed + '</span> ';
					html += '<span>' + aiovg_premium_admin.i18n.thumbnail_generator_capture_image + '</span>';

					$( '#aiovg-thumbnail-generator .aiovg-header' ).html( html );
					$( '#aiovg-thumbnail-generator .aiovg-footer' ).hide();
				}				
			});			
		});
		
		// Videos: On image selected.
		$( '#aiovg-thumbnail-generator' ).on( 'click', '.aiovg-item-thumbnail', function() {
			$( this ).find( 'input[type=radio]' ).prop( 'checked', true ).trigger( 'change' );
		});

		// Videos: Bind selected image URL in the "Image" field.
		$( '#aiovg-thumbnail-generator' ).on( 'change', 'input[type=radio]', function() {			
			var value = $( '#aiovg-thumbnail-generator input[type=radio]:checked' ).val();
			$( '#aiovg-image' ).val( value );
		});

		// Videos: Toggle custom VAST URL field.
		$( '#aiovg-override_vast_url' ).on( 'change', function() {			
			if ( this.checked ) {
				$( '#aiovg-vast_url' ).show()
			} else {
				$( '#aiovg-vast_url' ).hide();
			}
		}).trigger( 'change' );

		// Settings: Toggle fields based on the selected slider layout.
		$( '#aiovg-videos-settings tr.slider_layout select' ).on( 'change', function() {			
			var value = $( this ).val();			
			$( '#aiovg-videos-settings' ).aiovgReplaceClass( /\aiovg-slider-layout-\S+/ig, 'aiovg-slider-layout-' + value );
		}).trigger( 'change' );		

		// Settings: Test FFMPEG.
		$( '#aiovg-test-ffmpeg-button' ).on( 'click', function() {
			var data = {
				'action': 'aiovg_ffmpeg_status',				
				'ffmpeg_path': $( '#aiovg-ffmpeg-path' ).val(),
				'security': aiovg_admin.ajax_nonce
			};

			if ( data.ffmpeg_path == '' ) {
				return false;
			}

			$( '#aiovg-ffmpeg-status' ).html( '<span class="spinner"></span>' );
		
			$.post( ajaxurl, data, function( response ) {
				var html = '';

				if ( response.status == 'success' ) {
					html = '<span class="aiovg-text-success">' + response.message + '</span>';
				} else {
					html = '<span class="aiovg-text-error">' + response.message + '</span>';
				}

				$( '#aiovg-ffmpeg-status' ).html( html );
			}, 'json' );
		});

		// Automations: Toggle fields based on the selected service.
		$( '.aiovg-automations-field-service .aiovg-automations-field' ).on( 'change', function( event ) { 
            event.preventDefault();

			var value = $( '.aiovg-automations-field-service .aiovg-automations-field:checked' ).val();

			$( '#aiovg-automations-sources .aiovg-table' ).aiovgReplaceClass( /\aiovg-automations-service-\S+/ig, 'aiovg-automations-service-' + value );
			$( '.aiovg-automations-fields-' + value + ' .aiovg-automations-field-type .aiovg-automations-field' ).trigger( 'change' );
		});

		// Automations: Toggle fields based on the selected source type.
		$( '.aiovg-automations-field-type .aiovg-automations-field' ).on( 'change', function( event ) { 
            event.preventDefault();
 
			var value = $( this ).val();
			$( '#aiovg-automations-sources .aiovg-table' ).aiovgReplaceClass( /\aiovg-automations-type-\S+/ig, 'aiovg-automations-type-' + value );
		});

		// Automations: Test Run.
		$( document ).on( 'click', '.aiovg-automations-preview', function( event ) {
			event.preventDefault();
			
			$( '#aiovg-automations-preview-modal .aiovg-modal-body' ).html( aiovg_premium_admin.i18n.loading_api_data );

			var data = {
				'action': 'aiovg_automations_test_run',				
				'service': $( '.aiovg-automations-field-service .aiovg-automations-field:checked' ).val(),
				'video_date': $( '#aiovg-video_date' ).val(),
				'security': aiovg_admin.ajax_nonce
			};

			data.type = $( '.aiovg-automations-fields-' + data.service + ' .aiovg-automations-field-type .aiovg-automations-field' ).val();			
			data.src = $( '.aiovg-automations-fields-' + data.service + ' .aiovg-automations-field-' + data.type + ' .aiovg-automations-field' ).val();
			
			if ( data.service == 'vimeo' ) {
				data.username = $( '.aiovg-automations-fields-vimeo .aiovg-automations-field-username .aiovg-automations-field' ).val();
				data.filter_tag = $( '.aiovg-automations-fields-vimeo .aiovg-automations-field-filter_tag .aiovg-automations-field' ).val();
				data.filter_tag_exclude = $( '.aiovg-automations-fields-vimeo .aiovg-automations-field-filter_tag_exclude .aiovg-automations-field' ).val();
				data.include_subfolders = $( '.aiovg-automations-fields-vimeo .aiovg-automations-field-include_subfolders .aiovg-automations-field:checked' ).val();
			}
			
			$.post( ajaxurl, data, function( response ) {
				$( '#aiovg-automations-preview-modal .aiovg-modal-body' ).html( response );
			});
		});		

		// Videos Widget: Toggle fields based on the selected slider layout.
		$( document ).on( 'change', '.aiovg-widget-form-videos .aiovg-widget-input-slider_layout', function() {			
			var value = $( this ).val();	
			$( this ).closest( '.aiovg-widget-form-videos' ).aiovgReplaceClass( /\aiovg-slider-layout-\S+/ig, 'aiovg-slider-layout-' + value );
		});

		// Gutenberg: Toggle fields based on the selected videos template.
		if ( typeof wp !== 'undefined' && typeof wp['hooks'] !== 'undefined' ) {
			wp.hooks.addFilter( 'aiovg_block_toggle_controls', 'aiovg/videos', function( value, control, attributes ) {
				switch ( control ) {
					case 'columns':
						if ( attributes.template == 'slider' && attributes.slider_layout == 'player' ) {
							value = false;
						}

						if ( attributes.template == 'playlist' ) {
							value = false;
						}
						break;

					case 'link_title':
					case 'show_player_title':
					case 'show_player_description':
					case 'show_player_like_button':
					case 'show_player_playlist_button':
						if ( attributes.template == 'classic' || attributes.template == 'playlist' ) {
							value = false;
						} else {
							if ( attributes.template == 'slider' && attributes.slider_layout == 'thumbnails' ) {
								value = false;
							}
						}
						break;

					case 'show_pagination':
						value = ( attributes.template == 'slider' || attributes.template == 'playlist' || attributes.template == 'compact' ) ? false : true;
						break;

					case 'show_more':
					case 'more_label':
						value = ( attributes.template == 'compact' ) ? true : false;
						break;

					case 'slider_layout':
					case 'arrows':
					case 'arrow_size':					
					case 'arrow_radius':
					case 'arrow_top_offset':
					case 'arrow_left_offset':
					case 'arrow_right_offset':
					case 'arrow_bg_color':
					case 'arrow_icon_color':
					case 'dots':
					case 'dot_size':
					case 'dot_color':
						value = ( attributes.template == 'slider' ) ? true : false;
						break;

					case 'slider_autoplay':
					case 'autoplay_speed':
						value = ( attributes.template == 'slider' && attributes.slider_layout != 'both' ) ? true : false;
						break;

					case 'playlist_position':
					case 'playlist_color':
					case 'playlist_width':
					case 'playlist_height':
						value = ( attributes.template == 'playlist' ) ? true : false;
						break;
						
					case 'autoadvance':
						if ( attributes.template == 'classic' ) {
							value = false;
						} else {
							if ( attributes.template == 'slider' && attributes.slider_layout == 'thumbnails' ) {
								value = false;
							}
						}
						break;
				}

				return value;
			});
		}

	});	

})( jQuery );

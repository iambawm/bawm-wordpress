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
			url = aiovg_form.site_url + url;
		} else {
			url = aiovg_form.site_url + '/' + url;
		}

		return url;
	}	

	/**
	 * Set field error.
	 */
	function setFieldError( name, message ) {
		$( '#aiovg-field-' + name ).addClass( 'aiovg-field-invalid' )
			.find( '.aiovg-field-error' )
			.html( message );
	}

	/**
	 * Clear field error.
	 */
	function clearFieldError( name ) {
		$( '#aiovg-field-' + name ).removeClass( 'aiovg-field-invalid' )
			.find( '.aiovg-field-error' )
			.html( '' );
	}

	/**
	 * Reset file uploader.
	 */
	function resetUploader( name ) {
		$( '#aiovg-field-' + name + ' .aiovg-media-uploader' ).removeClass( 'aiovg-uploading' )
			.find( '.aiovg-upload-progress' )
			.html( '' );
	}		

	/**
	 * Called when the page has loaded.
	 */
	$(function() {

		// Vars
		var videoURL = resolveURL( $( '#aiovg-mp4' ).val() );

		// Video Form: Insert the Magic Field.
		if ( aiovg_form.magic_field ) {
			// Post via AJAX.			 
			var data = {
				'action': 'aiovg_public_get_magic_field',
				'security': aiovg_form.ajax_nonce
			};
	
			$.post( aiovg_form.ajax_url, data, function( response ) {
				if ( response ) {
					$( '#aiovg-form-video' ).append( response );
				}
			});
		}

		// Video Form: Toggle fields based on the selected video source type.
		$( '#aiovg-type', '#aiovg-form-video' ).on( 'change', function( event ) { 
			event.preventDefault();
 
			var type = $( this ).val();
			
			$( '.aiovg-toggle-fields' ).hide();
			$( '.aiovg-type-' + type ).show();
		}).trigger( 'change' );

		// Video Form: Init File Uploader.
		$( '.aiovg-media-uploader', '#aiovg-form-video' ).each(function() { 
			// Config
			var $this = $( this );
			var field = $this.find( '.aiovg-button-upload' ).data( 'format' );
			var $uploadProgress = $this.find( '.aiovg-upload-progress' );			

			var config = {
				runtimes: 'html5,html4',
				browse_button: 'aiovg-button-upload-' + field,
				container: 'aiovg-field-' + field,
				file_data_name: 'async-upload',
				multiple_queues: false,
				max_file_count: 1,
				url: aiovg_form.ajax_url,
				multipart: true,
				multi_selection: false,
				multipart_params: {
					action: 'aiovg_public_upload_media',
					security: aiovg_form.ajax_nonce,					
					post_id: $( '#aiovg-post_id' ).val(),
					field: field
				}			
			};

			if ( field == 'image' ) { // Is Image?
				config.filters = {
					max_file_size: aiovg_form.max_upload_size,
					mime_types: [{ 
						title: aiovg_form.i18n.allowed_files, 
						extensions: aiovg_form.supported_image_formats
					}]
				};
			} else { // Is Video?
				config.chunk_size = aiovg_form.chunk_size;

				config.filters = {
					max_file_size: aiovg_form.max_upload_size,
					mime_types: [{ 
						title: aiovg_form.i18n.allowed_files, 
						extensions: aiovg_form.supported_video_formats
					}]
				};
			}

			// Create the uploader and pass the config from above.
			var uploader = new plupload.Uploader( config );		
			uploader.init();

			// A file was added in the queue.
			uploader.bind( 'FilesAdded', function( up, files ) {
				clearFieldError( field );

				$( '#aiovg-field-' + field + ' .aiovg-media-uploader' ).addClass( 'aiovg-uploading' )
					.find( '.aiovg-upload-progress' )
					.html( aiovg_form.i18n.please_wait );

				up.refresh();
				up.start();

				// Abort Upload.
				$( '#aiovg-field-' + field + ' .aiovg-upload-cancel' ).off( 'click' ).on( 'click', function( event ) {
					event.preventDefault();

					up.stop();
					up.removeFile( files[0] );
					up.refresh();

					resetUploader( field );
				});
			});

			// Update upload progress.
			uploader.bind( 'UploadProgress', function( up, file ) {			
				var status = aiovg_form.i18n.upload_status.replace( '%d', file.percent );

				if ( file.percent == 100 ) {
					status += ' ' + aiovg_form.i18n.please_wait;
				}

				$uploadProgress.html( status );
			});

			// Handle upload error.
			uploader.bind( 'Error', function( up, error ) {
				var message = error.message;

				switch ( error.code ) {
					case -600:
						message = aiovg_form.i18n.invalid_file_size; 
						break;
				}

				resetUploader( field );
				setFieldError( field, message );
			});

			// A file was uploaded.
			uploader.bind( 'FileUploaded', function( up, file, response ) {
				resetUploader( field );

				if ( response.status == '200' ) {
					// This is your ajax response, update the DOM with it or something...
					try {
						var json = $.parseJSON( response.response );

						if ( json.success ) {
							$( '#aiovg-' + field ).val( json.data.url ).trigger( 'file.uploaded' );							
						} else {
							up.trigger( 'error',  { code: null, message: json.data } );
						}
					} catch ( error ) {
						up.trigger( 'error',  { code: null, message: json.response } );
					}
				} else {
					up.trigger( 'error',  { code: response.code, message: aiovg_form.i18n.unknown_error } );
				}
			});
		});		
		
		// Thumbnail Generator: Capture Image.
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
								var html = '<div class="aiovg-notice aiovg-notice-error">' + aiovg_form.i18n.thumbnail_generator_cors_error + '</div>';
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

									$( this ).before( '<div class="aiovg-spinner"></div>&nbsp;' );
									
									canvasContext.drawImage( videoEl, 0, 0, videoEl.videoWidth, videoEl.videoHeight );
									var canvasDataURL = '';

									try {
										canvasDataURL = canvasEl.toDataURL();
									} catch ( error ) {
										$( '#aiovg-thumbnail-generator-modal-body' ).find( '.aiovg-spinner' ).remove();

										seekTo.disabled = true;
										captureButton.disabled = true;

										var html = '<div class="aiovg-notice aiovg-notice-error">' + aiovg_form.i18n.thumbnail_generator_cors_error + '</div>';
										$( '#aiovg-thumbnail-generator-modal-body' ).prepend( html );
										
										return false;
									}

									var formData = new FormData();

									formData.append( 'action', 'aiovg_upload_base64_image' );
									formData.append( 'video', videoURL );
									formData.append( 'index', $( '#aiovg-thumbnail-generator .aiovg-item-thumbnail' ).length );
									formData.append( 'security', aiovg_form.ajax_nonce );

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
											$( '#aiovg-thumbnail-generator-modal-body' ).find( '.aiovg-spinner' ).remove();
											
											if ( xmlhttp.responseText != '' ) {
												$( xmlhttp.responseText ).insertBefore( '#aiovg-html5-thumbnail-generator' );
												$( '#aiovg-thumbnail-generator input[type=radio]:last' ).prop( 'checked', true ).trigger( 'change' );
											}					
									
											if ( $( '#aiovg-thumbnail-generator .aiovg-item-thumbnail' ).length > 0 ) {
												$( '#aiovg-thumbnail-generator .aiovg-header' ).html( aiovg_form.i18n.thumbnail_generator_select_image );
												$( '#aiovg-thumbnail-generator .aiovg-footer' ).show();
											} else {
												$( '#aiovg-thumbnail-generator .aiovg-header' ).html( aiovg_form.i18n.thumbnail_generator_capture_image );
												$( '#aiovg-thumbnail-generator .aiovg-footer' ).hide();
											}

											$.magnificPopup.close();
										}
									};

									xmlhttp.open( 'POST', aiovg_form.ajax_url );
									xmlhttp.send( formData );
								});
							});
						} else {
							// Set error.
							var html = '<div class="aiovg-notice aiovg-notice-error">' + aiovg_form.i18n.thumbnail_generator_video_not_found + '</div>';
							$( '#aiovg-thumbnail-generator-modal-body' ).prepend( html );
						}
					},
					close: function() {
						document.getElementById( 'aiovg-thumbnail-generator-modal-body' ).innerHTML = '';
					}
				}
			});
		}

		// Thumbnail Generator: Generate images using FFMPEG.
		$( '#aiovg-mp4' ).on( 'blur file.uploaded', function() {
			if ( ! aiovg_form.ffmpeg_enabled ) {
				return false;
			}

			var __videoURL = resolveURL( $( this ).val() );

			if ( ! __videoURL || __videoURL == videoURL ) {
				return false;
			}

			videoURL = __videoURL;

			var html = '<span class="aiovg-spinner"></span> ';
			html += '<span>' + aiovg_form.i18n.thumbnail_generator_processing + '</span>';

			$( '#aiovg-thumbnail-generator' ).show()
				.addClass( 'aiovg-processing' )
				.find( '.aiovg-header' )
				.html( html );

			var data = {
				'action': 'aiovg_ffmpeg_generate_images',
				'url': videoURL,
				'security': aiovg_form.ajax_nonce
			};
			
			$.post( aiovg_form.ajax_url, data, function( response ) {
				$( '#aiovg-thumbnail-generator' )
					.removeClass( 'aiovg-processing' )
					.find( '.aiovg-header' )
					.html( '' );

				$( '#aiovg-thumbnail-generator .aiovg-item-thumbnail' ).remove();					

				if ( response ) {
					if ( aiovg_form.html5_thumbnail_generator_enabled ) {
						$( response ).insertBefore( '#aiovg-html5-thumbnail-generator' );
					} else {
						$( '#aiovg-thumbnail-generator .aiovg-body' ).html( response );
					}
					
					if ( $( '#aiovg-image' ).val() == '' ) {
						$( '#aiovg-thumbnail-generator input[type=radio]:first' ).prop( 'checked', true ).trigger( 'change' );
					}
				}					
		
				if ( $( '#aiovg-thumbnail-generator .aiovg-item-thumbnail' ).length > 0 ) {
					$( '#aiovg-thumbnail-generator .aiovg-header' ).html( aiovg_form.i18n.thumbnail_generator_select_image );
					$( '#aiovg-thumbnail-generator .aiovg-footer' ).show();
				} else {
					var html = '';

					if ( aiovg_form.html5_thumbnail_generator_enabled ) {
						html = '<span class="aiovg-field-error">' + aiovg_form.i18n.ffmpeg_thumbnail_generation_failed + '</span> <span>' + aiovg_form.i18n.thumbnail_generator_capture_image + '</span>';
					} else {
						html = '<span class="aiovg-field-error">' + aiovg_form.i18n.ffmpeg_thumbnail_generation_failed + '</span>';
					}

					$( '#aiovg-thumbnail-generator .aiovg-header' ).html( html );
					$( '#aiovg-thumbnail-generator .aiovg-footer' ).hide();
				}					
			});			
		});		

		// Thumbnail Generator: On image selected.
		$( '#aiovg-thumbnail-generator' ).on( 'click', '.aiovg-item-thumbnail', function() {
			$( this ).find( 'input[type=radio]' ).prop( 'checked', true ).trigger( 'change' );
		});

		// Thumbnail Generator: Bind selected image URL in the "Image" field.
		$( '#aiovg-thumbnail-generator' ).on( 'change', 'input[type=radio]', function() {			
			var image = $( '#aiovg-thumbnail-generator input[type=radio]:checked' ).val();
			$( '#aiovg-image' ).val( image );
		});	
		
		// Video Form: Validate 'title' field.
		$( '#aiovg-title' ).on( 'keyup', function() {
			var value = $( this ).val();

			if ( value == '' ) {
				setFieldError( 'title', aiovg_form.i18n.required );				
			} else {
				clearFieldError( 'title' );
			}
		});	

		// Video Form: Validate 'mp4' field.
		$( '#aiovg-mp4' ).on( 'keyup', function() {
			var value = $( this ).val();

			if ( value == '' ) {		
				setFieldError( 'mp4', aiovg_form.i18n.required );					
			} else {
				clearFieldError( 'mp4' );
			}
		});

		// Video Form: Validate 'adaptive' field.
		$( '#aiovg-adaptive' ).on( 'keyup', function() {
			var value = $( this ).val();

			if ( value == '' ) {		
				setFieldError( 'adaptive', aiovg_form.i18n.required );					
			} else {
				clearFieldError( 'adaptive' );
			}
		}).on( 'blur', function() {
			var value = $( this ).val();
			var pattern = /mpd|m3u8/;

			if ( value && ! pattern.test( value.toLowerCase() ) ) {
				$( '#aiovg-adaptive' ).val( '' );
				setFieldError( 'adaptive', aiovg_form.i18n.invalid_file_format );
			}
		});

		// Video Form: Validate 'youtube' field.
		$( '#aiovg-youtube' ).on( 'keyup', function() {
			var value = $( this ).val();

			if ( value == '' ) {		
				setFieldError( 'youtube', aiovg_form.i18n.required );					
			} else {
				clearFieldError( 'youtube' );
			}
		}).on( 'blur', function() {
			var value = $( this ).val();
			var pattern = new RegExp( 'youtube.com|youtu.be' );

			if ( value && ! pattern.test( value.toLowerCase() ) ) {
				$( '#aiovg-youtube' ).val( '' );
				setFieldError( 'youtube', aiovg_form.i18n.invalid_video_url );
			}
		});

		// Video Form: Validate 'vimeo' field.
		$( '#aiovg-vimeo' ).on( 'keyup', function() {
			var value = $( this ).val();

			if ( value == '' ) {		
				setFieldError( 'vimeo', aiovg_form.i18n.required );					
			} else {
				clearFieldError( 'vimeo' );
			}
		}).on( 'blur', function() {
			var value = $( this ).val();
			var pattern = new RegExp( 'vimeo.com' );

			if ( value && ! pattern.test( value.toLowerCase() ) ) {
				$( '#aiovg-vimeo' ).val( '' );
				setFieldError( 'vimeo', aiovg_form.i18n.invalid_video_url );
			}
		});

		// Video Form: Validate 'dailymotion' field.
		$( '#aiovg-dailymotion' ).on( 'keyup', function() {
			var value = $( this ).val();

			if ( value == '' ) {		
				setFieldError( 'dailymotion', aiovg_form.i18n.required );					
			} else {
				clearFieldError( 'dailymotion' );
			}
		}).on( 'blur', function() {
			var value = $( this ).val();
			var pattern = new RegExp( 'dailymotion.com' );

			if ( value && ! pattern.test( value.toLowerCase() ) ) {
				$( '#aiovg-dailymotion' ).val( '' );
				setFieldError( 'dailymotion', aiovg_form.i18n.invalid_video_url );
			}
		});

		// Video Form: Validate 'rumble' field.
		$( '#aiovg-rumble' ).on( 'keyup', function() {
			var value = $( this ).val();

			if ( value == '' ) {		
				setFieldError( 'rumble', aiovg_form.i18n.required );					
			} else {
				clearFieldError( 'rumble' );
			}
		}).on( 'blur', function() {
			var value = $( this ).val();
			var pattern = new RegExp( 'rumble.com' );

			if ( value && ! pattern.test( value.toLowerCase() ) ) {
				$( '#aiovg-rumble' ).val( '' );
				setFieldError( 'rumble', aiovg_form.i18n.invalid_video_url );
			}
		});

		// Video Form: Validate 'facebook' field.
		$( '#aiovg-facebook' ).on( 'keyup', function() {
			var value = $( this ).val();

			if ( value == '' ) {		
				setFieldError( 'facebook', aiovg_form.i18n.required );					
			} else {
				clearFieldError( 'facebook' );
			}
		}).on( 'blur', function() {
			var value = $( this ).val();
			var pattern = new RegExp( 'facebook.com' );

			if ( value && ! pattern.test( value.toLowerCase() ) ) {
				$( '#aiovg-facebook' ).val( '' );
				setFieldError( 'facebook', aiovg_form.i18n.invalid_video_url );
			}
		});

		// Video Form: Validate 'tos' field.
		$( '#aiovg-tos' ).on( 'change', function() {
			if ( $( '#aiovg-tos' ).is( ':checked' ) ) {
				$( '#aiovg-field-tos' ).removeClass( 'aiovg-field-invalid' );
			} else {
				$( '#aiovg-field-tos' ).addClass( 'aiovg-field-invalid' );
			}
		});

		// Video Form: On Submit.
		var formSubmitted = false;

		$( '#aiovg-form-video' ).on( 'submit', function( event ) {
			if ( formSubmitted ) return false;
			formSubmitted = true;

			// Check for pending file uploads.
			if ( $( this ).find( '.aiovg-uploading' ).length > 0 ) {
				formSubmitted = false;
				
				alert( aiovg_form.i18n.pending_upload );
				return false;
			}

			var form   = event.target;
			var failed = false;			

			// Title
			if ( $( '#aiovg-title' ).val() == '' ) {
				setFieldError( 'title', aiovg_form.i18n.required );						
				failed = true;
			} else {
				clearFieldError( 'title' );
			}

			// Video
			var type = $( '#aiovg-field-type select' ).val();
			if ( type == 'default' ) type = 'mp4';

			if ( $( '#aiovg-' + type ).val() == '' ) {	
				setFieldError( type, aiovg_form.i18n.required );					
				failed = true;
			} else {
				clearFieldError( type );
			}

			// TOS
			if ( $( '#aiovg-tos' ).length > 0 ) {
				if ( $( '#aiovg-tos' ).is( ':checked' ) ) {
					$( '#aiovg-field-tos' ).removeClass( 'aiovg-field-invalid' );
				} else {
					$( '#aiovg-field-tos' ).addClass( 'aiovg-field-invalid' );
					failed = true;
				}
			}
		
			if ( ! form.checkValidity() ) {
				failed = true;	
			}

			// Form invalid.
			if ( failed == true ) {
				event.preventDefault();
				event.stopImmediatePropagation();

				formSubmitted = false;
				
				$( 'html, body' ).animate({
					scrollTop: $( ':invalid, .aiovg-field-invalid', '#aiovg-form-video' ).first().offset().top - 75
				}, 300);
			}
		});
		
	});

})( jQuery );

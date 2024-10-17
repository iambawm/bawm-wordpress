<?php

/**
 * The public-facing functionality of the plugin.
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
 * AIOVG_Premium_Public class.
 *
 * @since 1.5.7
 */
class AIOVG_Premium_Public {

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.5.7
	 */
	public function register_styles() {
		wp_register_style( 
			AIOVG_PLUGIN_SLUG . '-slick', 
			AIOVG_PLUGIN_URL . 'vendor/slick/slick.min.css', 
			array(), 
			'1.8.1', 
			'all' 
		);

		wp_register_style( 
			AIOVG_PLUGIN_SLUG . '-premium-public', 
			AIOVG_PLUGIN_URL . 'premium/public/assets/css/public.min.css', 
			array(), 
			AIOVG_PLUGIN_VERSION, 
			'all' 
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.5.7
	 */
	public function register_scripts() {
		$user_submission_settings = get_option( 'aiovg_user_submission_settings' );
		$thumbnail_generator_settings = get_option( 'aiovg_thumbnail_generator_settings' );	
		$antispam_settings = get_option( 'aiovg_antispam_settings' );
		$playlists_settings = get_option( 'aiovg_playlists_settings' );	

		$ajax_url   = admin_url( 'admin-ajax.php' );
		$ajax_nonce = wp_create_nonce( 'aiovg_ajax_nonce' );
		$user_id    = get_current_user_id();

		$scroll_to_top_offset = 20;
		if ( is_admin_bar_showing() ) $scroll_to_top_offset += 32;
		$scroll_to_top_offset = apply_filters( 'aiovg_scroll_to_top_offset', $scroll_to_top_offset );

		$playlists_limit = ! empty( $playlists_settings['limit'] ) ? (int) $playlists_settings['limit'] : 0;

		$supported_video_formats = array();
		$supported_image_formats = array();
		if ( ! empty( $user_submission_settings['allowed_file_formats'] ) ) {
			$allowed_file_formats = array_values( $user_submission_settings['allowed_file_formats'] );

			$supported_video_formats = array_intersect( $allowed_file_formats, array( 'mp4', 'webm', 'ogv', 'm4v', 'mov' ) );
			$supported_image_formats = array_intersect( $allowed_file_formats, array( 'jpg', 'png', 'gif' ) );
		}	

		$max_upload_size = ! empty( $user_submission_settings['max_upload_size'] ) ? (int) $user_submission_settings['max_upload_size'] : 0;
		$max_upload_size = apply_filters( 'aiovg_max_upload_size', $max_upload_size );

		$chunk_size = ( wp_max_upload_size() - 1024 ) . 'b';
		$chunk_size = apply_filters( 'aiovg_upload_chunk_size', $chunk_size );

		// Register the dependencies
		wp_register_script( 
			AIOVG_PLUGIN_SLUG . '-slick', 
			AIOVG_PLUGIN_URL . 'vendor/slick/slick.min.js', 
			array( 'jquery' ), 
			'1.8.1', 
			array( 'strategy' => 'defer' ) 
		);		
		
		wp_register_script( 
			AIOVG_PLUGIN_SLUG . '-playlists', 
			AIOVG_PLUGIN_URL . 'premium/public/assets/js/playlists.min.js', 
			array( 'jquery' ), 
			AIOVG_PLUGIN_VERSION,
			array( 'strategy' => 'defer' )
		);

		wp_localize_script( 
			AIOVG_PLUGIN_SLUG . '-playlists', 
			'aiovg_playlists', 
			array(
				'site_url'   => get_site_url(),
				'ajax_url'   => $ajax_url,
				'ajax_nonce' => $ajax_nonce,				
				'user_id'    => $user_id,
				'limit'      => $playlists_limit,
				'i18n'       => array(		
					'button_add_to_playlist'  => __( 'Add to Playlist', 'all-in-one-video-gallery' ),			
					'alert_login_required'    => __( 'Sorry, you must login to create a playlist.', 'all-in-one-video-gallery' ),
					'dropdown_header_text'    => __( 'Save video to...', 'all-in-one-video-gallery' ),					
					'playlists_not_found'     => __( 'No playlists found', 'all-in-one-video-gallery' ),
					'note_limit'              => sprintf( __( 'You can create up to %d playlists.', 'all-in-one-video-gallery' ), $playlists_limit ),
					'note_limit_reached'      => __( 'You have reached the maximium number of playlists allowed.', 'all-in-one-video-gallery' ),
					'title_field_placeholder' => __( 'Enter playlist title...', 'all-in-one-video-gallery' ),
					'button_add_playlist'     => __( 'Add Playlist', 'all-in-one-video-gallery' ),
					'button_update'           => __( 'Update', 'all-in-one-video-gallery' ),
					'status_added'            => __( 'Added...', 'all-in-one-video-gallery' ),
					'status_removed'          => __( 'Removed...', 'all-in-one-video-gallery' )
				)
			)
		);

		wp_register_script( 
			AIOVG_PLUGIN_SLUG . '-premium-player', 
			AIOVG_PLUGIN_URL . 'premium/public/assets/js/videojs.min.js', 
			array( 'jquery' ), 
			AIOVG_PLUGIN_VERSION, 
			array( 'strategy' => 'defer' ) 
		);
		
		wp_register_script( 
			AIOVG_PLUGIN_SLUG . '-premium-form', 
			AIOVG_PLUGIN_URL . 'premium/public/assets/js/form.min.js', 
			array( 'jquery' ), 
			AIOVG_PLUGIN_VERSION, 
			array( 'strategy' => 'defer' ) 
		);

		wp_localize_script( 
			AIOVG_PLUGIN_SLUG . '-premium-form', 
			'aiovg_form', 
			array(
				'site_url'                          => get_site_url(),
				'ajax_url'                          => $ajax_url,
				'ajax_nonce'                        => $ajax_nonce,		
				'supported_video_formats'		    => implode( ',', $supported_video_formats ),
				'supported_image_formats'           => implode( ',', $supported_image_formats ),
				'max_upload_size'                   => $max_upload_size,
				'chunk_size'                        => $chunk_size,
				'magic_field'                       => ( ! empty( $antispam_settings['honeypot'] ) || ! empty( $antispam_settings['timetrap'] ) ) ? true : false,
				'html5_thumbnail_generator_enabled' => empty( $thumbnail_generator_settings['enable_html5_thumbnail_generator'] ) ? false : true,
				'ffmpeg_enabled'                    => empty( $thumbnail_generator_settings['ffmpeg_path'] ) || empty( $thumbnail_generator_settings['ffmpeg_images_count'] ) ? false : true,
				'i18n'                              => array(
					'required'                               => __( 'This is a required field.', 'all-in-one-video-gallery' ),
					'allowed_files'                          => __( 'Allowed Files', 'all-in-one-video-gallery' ),
					'invalid_file_format'                    => __( 'Sorry, this file format is not allowed.', 'all-in-one-video-gallery' ),
					'invalid_file_size'                      => __( 'Sorry, this file size is not allowed.', 'all-in-one-video-gallery' ),
					'invalid_video_url'                      => __( 'Invalid video URL.', 'all-in-one-video-gallery' ),
					'upload_status'                          => __( '%d% uploaded.', 'all-in-one-video-gallery' ),
					'please_wait'                            => __( 'Please wait...', 'all-in-one-video-gallery' ),
					'pending_upload'                         => __( 'Please wait until the upload is complete.', 'all-in-one-video-gallery' ),
					'unknown_error'                          => __( 'Unknown error.', 'all-in-one-video-gallery' ),
					'ffmpeg_thumbnail_generation_failed'     => __( 'Sorry, the auto-thumbnail generation failed.', 'all-in-one-video-gallery' ),
					'thumbnail_generator_capture_image'      => __( 'Use the "Capture Image" button below to generate an image from your video.', 'all-in-one-video-gallery' ),
					'thumbnail_generator_select_image'       => __( 'Select an image from the options below.', 'all-in-one-video-gallery' ),
					'thumbnail_generator_processing'         => __( 'Generating images...', 'all-in-one-video-gallery' ),
					'thumbnail_generator_video_not_found'    => __( 'No video found. Add a video in the "MP4" video field to capture an image.', 'all-in-one-video-gallery' ),
					'thumbnail_generator_invalid_video_file' => __( 'Invalid video file.', 'all-in-one-video-gallery' ),
					'thumbnail_generator_cors_error'         => __( "Sorry, your video file server doesn't give us permission to generate an image from the video.", 'all-in-one-video-gallery' )
				)				
			)
		);

		wp_register_script( 
			AIOVG_PLUGIN_SLUG . '-template-compact', 
			AIOVG_PLUGIN_URL . 'premium/public/assets/js/template-compact.min.js', 
			array( 'jquery' ), 
			AIOVG_PLUGIN_VERSION, 
			array( 'strategy' => 'defer' ) 
		);	
		
		wp_localize_script( 
			AIOVG_PLUGIN_SLUG . '-template-compact', 
			'aiovg_template_compact', 
			array(				
				'scroll_to_top_offset' => $scroll_to_top_offset,
				'i18n'                 => array(
					'now_playing' => __( 'Now Playing', 'all-in-one-video-gallery' )
				)
			)
		);

		wp_register_script( 
			AIOVG_PLUGIN_SLUG . '-template-playlist', 
			AIOVG_PLUGIN_URL . 'premium/public/assets/js/template-playlist.min.js', 
			array( 'jquery' ), 
			AIOVG_PLUGIN_VERSION, 
			array( 'strategy' => 'defer' ) 
		);	
		
		wp_localize_script( 
			AIOVG_PLUGIN_SLUG . '-template-playlist', 
			'aiovg_template_playlist', 
			array(
				'i18n' => array(
					'now_playing' => __( 'Now Playing', 'all-in-one-video-gallery' )
				)
			)
		);

		wp_register_script( 
			AIOVG_PLUGIN_SLUG . '-template-popup', 
			AIOVG_PLUGIN_URL . 'premium/public/assets/js/template-popup.min.js', 
			array( 'jquery' ), 
			AIOVG_PLUGIN_VERSION, 
			array( 'strategy' => 'defer' ) 
		);	

		wp_register_script( 
			AIOVG_PLUGIN_SLUG . '-template-slider', 
			AIOVG_PLUGIN_URL . 'premium/public/assets/js/template-slider.min.js', 
			array( 'jquery' ), 
			AIOVG_PLUGIN_VERSION, 
			array( 'strategy' => 'defer' ) 
		);	
		
		wp_localize_script( 
			AIOVG_PLUGIN_SLUG . '-template-slider', 
			'aiovg_template_slider', 
			array(
				'scroll_to_top_offset' => $scroll_to_top_offset,
				'i18n'                 => array(
					'now_playing' => __( 'Now Playing', 'all-in-one-video-gallery' )
				)
			)
		);

		wp_register_script( 
			AIOVG_PLUGIN_SLUG . '-premium-public', 
			AIOVG_PLUGIN_URL . 'premium/public/assets/js/public.min.js', 
			array( 'jquery' ), 
			AIOVG_PLUGIN_VERSION, 
			array( 'strategy' => 'defer' ) 
		);	
		
		wp_localize_script( 
			AIOVG_PLUGIN_SLUG . '-premium-public', 
			'aiovg_premium', 
			array(
				'plugin_url'           => AIOVG_PLUGIN_URL,
				'plugin_version'       => AIOVG_PLUGIN_VERSION,
				'scroll_to_top_offset' => $scroll_to_top_offset,
				'i18n'                 => array(
					'now_playing' => __( 'Now Playing', 'all-in-one-video-gallery' )
				)
			)
		);
	}

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 *
	 * @since 1.6.1
	 */
	public function enqueue_block_editor_assets() {
		// Styles
		$this->register_styles();

		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-slick' );
		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );

		// Scripts
		$this->register_scripts();
		
		wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-slick' );
		wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-playlists' );
		wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-template-slider' );
	}

}

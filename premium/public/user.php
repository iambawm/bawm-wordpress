<?php

/**
 * User.
 *
 * @link       https://plugins360.com
 * @since      1.6.1
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Public_User class.
 *
 * @since 1.6.1
 */
class AIOVG_Premium_Public_User {

	/**
	 * Antispam settings.
	 *	 
	 * @since  2.5.6
	 * @access protected
	 * @var    array
	 */
	protected $antispam_settings;

	/**
	 * Get things started.
	 *
	 * @since 1.6.1
	 */
	public function __construct() {
		// Register shortcode(s)
		add_shortcode( 'aiovg_user_dashboard', array( $this, 'run_shortcode_user_dashboard' ) );
		add_shortcode( 'aiovg_video_form', array( $this, 'run_shortcode_video_form' ) );		
	}
	
	/**
	 * Add custom rewrite rules, save or delete videos.
	 *
	 * @since 1.6.1
	 */
	public function wp_loaded() {
		// Add rewrite rules
		$this->add_rewrites();

		// Save video
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['aiovg_video_nonce'] ) && wp_verify_nonce( $_POST['aiovg_video_nonce'], 'aiovg_save_video' ) ) {
			$check = $this->is_spam();
			
			if ( $check['maybe_spam'] == 1 ) {	
				$post_id = (int) $_POST['post_id'];	

				if ( $post_id > 0 )	{
					$redirect_url = add_query_arg( 'status', $check['error_code'], aiovg_premium_get_edit_video_page_url( $post_id ) );
				} else {
					$redirect_url = add_query_arg( 'status', $check['error_code'], aiovg_premium_get_video_form_page_url() );
				}

				// Redirect						
				wp_redirect( $redirect_url );
				exit();
			}
			
			$this->save_video();
		}
		
		// Delete video
		if ( isset( $_GET['aiovg_video'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'aiovg_delete_video_nonce' ) ) {
			$this->delete_video();
		}
	}

	/**
	 * Add rewrite rules.
	 *
	 * @since  1.6.1
	 * @access private
	 */
	private function add_rewrites() {
		$page_settings = get_option( 'aiovg_page_settings' );
		$url = home_url();
		
		if ( array_key_exists( 'video_form', $page_settings ) ) {
			$id = $page_settings['video_form'];
			
			if ( $id > 0 ) {
				$link = str_replace( $url, '', get_permalink( $id ) );			
				$link = trim( $link, '/' );		
				
				add_rewrite_rule( "$link/edit/([^/]+)/?$", 'index.php?page_id=' . $id . '&aiovg_action=edit&aiovg_video=$matches[1]', 'top' );
				add_rewrite_rule( "$link/delete/([^/]+)/?$", 'index.php?page_id=' . $id . '&aiovg_action=delete&aiovg_video=$matches[1]', 'top' );
			}
		}

		add_rewrite_tag( '%aiovg_action%', '([^/]+)' );
	}

	/**
	 * Run the shortcode [aiovg_user_dashboard].
	 *
	 * @since 1.6.1
	 * @param array $atts An associative array of attributes.
	 */
	public function run_shortcode_user_dashboard( $atts ) {	
		// Enqueue style dependencies
		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-public' );
		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );

		// Check if user logged in
		if ( ! is_user_logged_in() ) {		
			return aiovg_premium_login_form();			
		}	

		// Vars
		$videos_settings = get_option( 'aiovg_videos_settings' );
		$page_settings   = get_option( 'aiovg_page_settings' );

		$attributes = array(
			'page_id' => ! empty( $page_settings['user_dashboard'] ) ? $page_settings['user_dashboard'] : '',
			'vi'      => isset( $_GET['vi'] ) ? sanitize_text_field( $_GET['vi'] ) : '',
			'paged'   => aiovg_get_page_number()
		);

		// Define the query	
		$args = array(				
			'post_type'      => 'aiovg_videos',
			'post_status'    => 'any',
			'posts_per_page' => ! empty( $videos_settings['limit'] ) ? (int) $videos_settings['limit'] : -1,
			'paged'          => (int) $attributes['paged'],
			'author'         => get_current_user_id(),
			's'              => $attributes['vi']
	  	);
			
		$args = apply_filters( 'aiovg_query_args', $args );
		$aiovg_query = new WP_Query( $args );
			
		// Start the Loop
		global $post;

		// Process output
		ob_start();
		include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . 'premium/public/templates/user-dashboard.php' );
		return ob_get_clean();	
	}	

	/**
	 * Run the shortcode [aiovg_video_form].
	 *
	 * @since 1.6.1
	 * @param array $atts An associative array of attributes.
	 */
	public function run_shortcode_video_form( $atts ) {	
		// Enqueue style dependencies
		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-magnific-popup' );
		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-public' );
		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );

		wp_enqueue_script( 'plupload' );
		wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-magnific-popup' );		
		wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-premium-form' );

		// Verify if user logged in
		if ( ! is_user_logged_in() ) {		
			return aiovg_premium_login_form();			
		}	

		// Verify the nonce request
		$post_id = 'edit' == get_query_var( 'aiovg_action' ) ? (int) get_query_var( 'aiovg_video', 0 ) : 0;

		if ( $post_id > 0 ) {
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'aiovg_edit_video_nonce' ) ) {
				return __( 'You do not have sufficient permissions to access this page.', 'all-in-one-video-gallery' );
			}
		}

		// Verify if the user can add video		
		$has_permission = true;
		
		if ( $post_id > 0 ) {
			if ( ! aiovg_current_user_can( 'edit_aiovg_video', $post_id ) ) $has_permission = false;
		} elseif ( ! aiovg_current_user_can( 'edit_aiovg_videos' ) ) {
			$has_permission = false;
		}
		
		if ( ! $has_permission ) {
			return __( 'You do not have sufficient permissions to access this page.', 'all-in-one-video-gallery' );
		}		

		// Vars
		$user_submission_settings = get_option( 'aiovg_user_submission_settings' );

		$attributes = array(
			'assign_categories'       => $user_submission_settings['assign_categories'],
			'assign_tags'             => isset( $user_submission_settings['assign_tags'] ) ? $user_submission_settings['assign_tags'] : 0,
			'allowed_source_types'    => array(),
			'allow_file_uploads'      => $user_submission_settings['allow_file_uploads'],
			'supported_video_formats' => array(),
			'supported_image_formats' => array(),
			'terms_and_conditions'    => $user_submission_settings['terms_and_conditions'],
			'is_new'                  => 1,
			'post_id'                 => $post_id,
			'title'                   => '',
			'catids'                  => array(),
			'tagids'                  => array(),
			'type'                    => isset( $user_submission_settings['default_source_type'] ) ? $user_submission_settings['default_source_type'] : 'default',
			'mp4'                     => '',
			'has_webm'                => 0,
			'webm'                    => '',
			'has_ogv'                 => 0,
			'ogv'                     => '',
			'youtube'                 => '',
			'vimeo'                   => '',
			'dailymotion'             => '',
			'rumble'                  => '',
			'facebook'                => '',
			'adaptive'                => '',
			'image'                   => '',
			'description'             => ''			
		);	

		if ( $attributes['assign_tags'] ) {
			wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-autocomplete' );
		}

		$allowed_source_types = aiovg_get_video_source_types();
		if ( ! empty( $user_submission_settings['allowed_source_types'] ) ) {
			foreach ( $user_submission_settings['allowed_source_types'] as $index => $value ) {
				$attributes['allowed_source_types'][ $index ] = $allowed_source_types[ $index ];
			}
		}	

		if ( ! empty( $user_submission_settings['allowed_file_formats'] ) ) {
			$allowed_file_formats = array_values( $user_submission_settings['allowed_file_formats'] );

			$attributes['supported_video_formats'] = array_intersect( $allowed_file_formats, array( 'mp4', 'webm', 'ogv', 'm4v', 'mov' ) );
			$attributes['supported_image_formats'] = array_intersect( $allowed_file_formats, array( 'jpg', 'png', 'gif' ) );
		}
		
		$max_upload_size = wp_max_upload_size();
		if ( (int) $user_submission_settings['max_upload_size'] > 0 ) {
			$max_upload_size = min( (int) $user_submission_settings['max_upload_size'], $max_upload_size );
		}
		$attributes['max_upload_size'] = size_format( $max_upload_size, $decimals = 2 );
		
		if ( $post_id > 0 ) {
			$post = get_post( $post_id );
			$post_meta = get_post_meta( $post_id );

			if ( 'draft' != $post->post_status ) {
				$attributes['is_new'] = 0;
			}

			$attributes['title']       = $post->post_title;
			$attributes['catids']      = wp_get_object_terms( $post_id, 'aiovg_categories', array( 'fields' => 'ids' ) );
			$attributes['tagids']      = wp_get_object_terms( $post_id, 'aiovg_tags', array( 'fields' => 'ids' ) );
			$attributes['type']        = isset( $post_meta['type'] ) ? $post_meta['type'][0] : 'default';
			$attributes['mp4']         = isset( $post_meta['mp4'] ) ? $post_meta['mp4'][0] : '';
			$attributes['has_webm']    = isset( $post_meta['has_webm'] ) ? $post_meta['has_webm'][0] : 0;
			$attributes['webm']        = isset( $post_meta['webm'] ) ? $post_meta['webm'][0] : '';
			$attributes['has_ogv']     = isset( $post_meta['has_ogv'] ) ? $post_meta['has_ogv'][0] : 0;
			$attributes['ogv']         = isset( $post_meta['ogv'] ) ? $post_meta['ogv'][0] : '';
			$attributes['youtube']     = isset( $post_meta['youtube'] ) ? $post_meta['youtube'][0] : '';
			$attributes['vimeo']       = isset( $post_meta['vimeo'] ) ? $post_meta['vimeo'][0] : '';
			$attributes['dailymotion'] = isset( $post_meta['dailymotion'] ) ? $post_meta['dailymotion'][0] : '';
			$attributes['rumble']      = isset( $post_meta['rumble'] ) ? $post_meta['rumble'][0] : '';
			$attributes['facebook']    = isset( $post_meta['facebook'] ) ? $post_meta['facebook'][0] : '';
			$attributes['image']       = isset( $post_meta['image'] ) ? $post_meta['image'][0] : '';
			$attributes['description'] = $post->post_content;

			$attributes['adaptive'] = '';
			if ( ! empty( $post_meta['hls'][0] ) ) {
				$attributes['adaptive'] = $post_meta['hls'][0];
			} elseif ( ! empty( $post_meta['dash'][0] ) ) {
				$attributes['adaptive'] = $post_meta['dash'][0];
			}						
		}
				
		// Process output
		ob_start();
		include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . 'premium/public/templates/video-form.php' );
		return ob_get_clean();		
	}	

	/**
	 * Upload Files.
	 *
	 * @since 1.6.1
	 */
	public function ajax_callback_upload_media() {
		// Verify the ajax request
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );		

		// Vars
		$user_submission_settings = get_option( 'aiovg_user_submission_settings' );

		$post_id = 0;
		if ( isset( $_POST['post_id'] ) ) {
			$post_id = (int) $_POST['post_id'];
		}
		
		$field = '';
		if ( isset( $_POST['field'] ) ) {
			$field = sanitize_text_field( $_POST['field'] );
		}

		$allowed_mimes = array(
			'image' => array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
			),
			'mp4' => array(
				'mp4|m4v' => 'video/mp4',
				'mov|qt'  => 'video/quicktime',
				'webm'    => 'video/webm',
				'ogv'     => 'video/ogg'
			),
			'webm' => array(
				'webm' => 'video/webm'
			),
			'ogv' => array(
				'ogv' => 'video/ogg'
			)
		);

		$max_upload_size = (int) $user_submission_settings['max_upload_size'];

		$response = array(
			'id'  => 0,
			'url' => ''
		);

		@set_time_limit( 900 );

		// Verify if file uploads enabled for users
		if ( empty( $user_submission_settings['allow_file_uploads'] ) ) {
			wp_send_json_error( __( 'You do not have permission to upload files.', 'all-in-one-video-gallery' ) );
		}
		
		// Verify the current user can upload files
		$has_permission = true;
			
		if ( $post_id > 0 ) {
			if ( ! aiovg_current_user_can( 'edit_aiovg_video', $post_id ) ) $has_permission = false;
		} elseif ( ! aiovg_current_user_can( 'edit_aiovg_videos' ) ) {
			$has_permission = false;
		}
		
		if ( ! $has_permission ) {
			wp_send_json_error( __( 'You do not have permission to upload files.', 'all-in-one-video-gallery' ) );
		}

		// Verify the file type
		if ( empty( $field ) || ! array_key_exists( $field, $allowed_mimes ) ) {
			wp_send_json_error( __( 'Sorry, this file format is not allowed.', 'all-in-one-video-gallery' ) );
		}

		// Load the needed files
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		// ...
		$has_chunks = false;
		$chunks = 0;
		if ( isset( $_POST['chunks'] ) ) {
			$has_chunks = true;
			$chunks = intval( $_POST['chunks'] );
		}

		$has_chunk = false;
		$chunk = 0;
		if ( isset( $_POST['chunk'] ) ) {
			$has_chunk = true;
			$chunk = intval( $_POST['chunk'] );
		}

		$is_final_chunk = false;
		if ( $chunk == $chunks - 1 ) {
			$is_final_chunk = true;
		}

		if ( empty( $chunks ) ) {
			// Verify the file extension
			$file_info = wp_check_filetype( basename( $_FILES['async-upload']['name'] ), $allowed_mimes[ $field ] );		
			if ( empty( $file_info['type'] ) ) {
				wp_send_json_error( __( 'Sorry, this file format is not allowed.', 'all-in-one-video-gallery' ) );
			}

			// If Image?
			if ( 'image' == $field ) {
				if ( getimagesize( $_FILES['async-upload']['tmp_name'] ) === FALSE ) {
					wp_send_json_error( __( 'Sorry, this file type is not permitted for security reasons.', 'all-in-one-video-gallery' ) );
				} 

				$_FILES['async-upload'] = aiovg_premium_exif_rotate( $_FILES['async-upload'] );
			}

			// Check for the allowed file size			
			if ( $max_upload_size > 0 ) {
				if ( $_FILES['async-upload']['size'] > $max_upload_size ) {
					wp_send_json_error( __( 'Sorry, this file size is not allowed.', 'all-in-one-video-gallery' ) );
				}
			}	

			$attachment_id = media_handle_upload( 'async-upload', $post_id );
			if ( is_wp_error( $attachment_id ) ) {
				wp_send_json_error( $attachment_id->get_error_message() );
			} else {
				$response['id']  = $attachment_id;
				$response['url'] = wp_get_attachment_url( $response['id'] );
			}
		} else {
			// Verify the file extension	
			$file_info = wp_check_filetype( $_POST['name'], $allowed_mimes[ $field ] );		
			if ( empty( $file_info['type'] ) ) {
				wp_send_json_error( __( 'Sorry, this file format is not allowed.', 'all-in-one-video-gallery' ) );
			}

			$upload_dir = wp_upload_dir();

			$temp_dir = trailingslashit( $upload_dir['basedir'] ) . 'all-in-one-video-gallery';
			wp_mkdir_p( $temp_dir );

			if ( ! is_writable( $temp_dir ) ) {
				$message = sprintf( __( "The uploads directory (%s) is not writable (as verified by attempting to write to it). You need to fix this (asking your hosting company for help if necessary) to be able to upload any videos.", 'all-in-one-video-gallery' ), $temp_dir );
				wp_send_json_error( $message );
			}		

			// If this was the first chunk, then we can go removing the old temp files
			if ( $has_chunks && $has_chunk && $chunk === 0 ) {
				$max_file_age = 5 * 3600; // Temp file age in seconds 

				if ( $dir = opendir( $temp_dir ) ) { 
					while ( ( $__file = readdir( $dir ) ) !== false ) { 
						$tmp_file_path = $temp_dir . DIRECTORY_SEPARATOR . $__file; 
				
						// Remove temp file if it is older than the max age and is not the current file 
						if ( preg_match( '/\.part$/', $__file ) && ( filemtime( $tmp_file_path ) < time() - $max_file_age ) ) { 
							@unlink( $tmp_file_path ); 
						} 
					} 

					closedir( $dir );    
				}
			} 

			// Handle file upload
			$farray = array(
				'test_form' => true, 				
				'action'    => 'aiovg_public_upload_media',
				'test_type' => false,
				'ext'       => $file_info['ext'],
				'type'      => $file_info['type']
			);

			$status = wp_handle_upload( $_FILES['async-upload'], $farray );

			if ( isset( $status['error'] ) ) {
				wp_send_json_error( $status['error'] );
			}

			// Should be a no-op
			$name = basename( $_POST['name'] );

			// If this was the chunk, then we should instead be concatenating onto the final file
			if ( $has_chunks && $has_chunk && preg_match( '/^[0-9]+$/', $chunk ) ) {
				// A random element is added, because otherwise it is theoretically possible for another user to upload into a shared temporary directory in between the upload and install, and over-write
				$final_file = $name;

				rename( $status['file'], $temp_dir . DIRECTORY_SEPARATOR . $final_file . '.part' . $chunk );
				$status['file'] = $temp_dir . DIRECTORY_SEPARATOR . $final_file . '.part' . $chunk;

				// Final chunk? If so, then stich it all back together
				if ( $is_final_chunk ) {
					$unique_file_name = wp_unique_filename( $upload_dir['path'], $final_file ); // Generate unique name

					if ( $wh = fopen( trailingslashit( $upload_dir['path'] ) . $unique_file_name, 'wb' ) ) {
						for ( $i = 0; $i < $chunks; $i++ ) {
							$rf = $temp_dir . DIRECTORY_SEPARATOR . $final_file . '.part' . $i;
							if ( $rh = fopen( $rf, 'rb' ) ) {
								while ( $line = fread( $rh, 32768 ) ) fwrite( $wh, $line );
								fclose( $rh );
								@unlink( $rf );
							}
						}
						fclose( $wh );						
						$status['file'] = trailingslashit( $upload_dir['path'] ) . $unique_file_name;
					}
				}
			}

			if ( $has_chunk && $is_final_chunk ) {
				// Verify the file is a real video
				$file_info = wp_check_filetype_and_ext( $status['file'], $unique_file_name, $allowed_mimes[ $field ] );	
				if ( empty( $file_info['type'] ) ) {
					@unlink( $status['file'] );
					wp_send_json_error( __( 'Sorry, this file type is not permitted for security reasons.', 'all-in-one-video-gallery' ) );
				}

				// Prepare an array of post data for the attachment
				$attachment = array(
					'guid'           => trailingslashit( $upload_dir['url'] ) . $unique_file_name, 
					'post_mime_type' => $file_info['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', $unique_file_name ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				// Insert the attachment
				$attachment_id = wp_insert_attachment( $attachment, $status['file'], $post_id );
				if ( is_wp_error( $attachment_id ) ) {
					wp_send_json_error( $attachment_id->get_error_message() );
				}

				// Generate the metadata for the attachment, and update the database record
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $status['file'] );
				wp_update_attachment_metadata( $attachment_id, $attachment_data );

				// Get the attachment data
				$response['id'] = $attachment_id;
				$response['url'] = wp_get_attachment_url( $attachment_id );
			}
		}

		wp_send_json_success( $response );
	}

	/**
	 * Add a honeypot field from server side.
	 *
	 * @since 2.5.6
	 */
	public function add_honeypot_field() {
		$antispam_settings = $this->get_antispam_settings();

		if ( $antispam_settings['honeypot'] == 1 || $antispam_settings['timetrap'] == 1 ) {
			echo '<span class="aiovg-field-date"><input type="text" name="date" value="" tabindex="-1" autocomplete="off" /></span>';
		}
	}

	/**
	 * Get a honeypot field for javascript based integration.
	 *
	 * @since 2.5.6
	 */
	public function ajax_callback_get_honeypot_field() {
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );

		$antispam_settings = $this->get_antispam_settings();
		$html = '';

		if ( $antispam_settings['honeypot'] == 1 || $antispam_settings['timetrap'] == 1 ) {
			$html = sprintf(
				'<span class="aiovg-field-magic"><input type="text" name="%s" value="%s" tabindex="-1" autocomplete="off" /></span>',
				esc_attr( $antispam_settings['honeypot_field_name'] ),
				base64_encode( json_encode( time() * $antispam_settings['timetrap_salt'] ) )
			);
		}

		echo $html;
		wp_die();
	}

	/**
	 * Check Spam.
	 *
	 * @since  2.5.6
	 * @access private
	 * @return array   $response Filtered response array.
	 */
	private function is_spam() {
		$antispam_settings = $this->get_antispam_settings();
		$honeypot_field_name = $antispam_settings['honeypot_field_name'];

		$response = array(
			'maybe_spam' => 0,
			'error_code' => ''
		);

		if ( $antispam_settings['honeypot'] == 1 || $antispam_settings['timetrap'] == 1 ) {
			if ( isset( $_REQUEST['date'] ) ) {
				// Honeypot: Level 1
				if ( ! empty( $_REQUEST['date'] ) ) {
					// It's a bot
					$response['maybe_spam'] = 1;
					$response['error_code'] = 'maybe_spam1';
				}

				// Honeypot: Level 2
				if ( ! isset( $_REQUEST[ $honeypot_field_name ] ) ) {
					// It's a bot
					$response['maybe_spam'] = 1;
					$response['error_code'] = 'maybe_spam1';
				} else {
					// Timetrap
					if ( $antispam_settings['timetrap'] == 1 ) {
						// Decode the time value
						$timetrap = json_decode( base64_decode( $_REQUEST[ $honeypot_field_name ] ) );
			
						// Check if the time is above the minimum time needed to complete the form
						if ( is_numeric( $timetrap ) && time() - ( $timetrap / $antispam_settings['timetrap_salt'] ) > $antispam_settings['timetrap_minimum_time'] ) {
							// It's a human
						} else {
							// It's a bot
							$response['maybe_spam'] = 1;
							$response['error_code'] = 'maybe_spam2';
						}
					}
				}
			}
		}		

		return $response;
	}

	/**
	 * Get the antispam settings.
	 *
	 * @since  2.5.6
	 * @access private
	 * @return array   $settings Antispam settings.
	 */
	private function get_antispam_settings() {
		if ( ! empty( $this->antispam_settings ) ) {
			return $this->antispam_settings;
		}

		$antispam_settings = get_option( 'aiovg_antispam_settings' );

		$this->antispam_settings = array(
			'honeypot'              => ! empty( $antispam_settings['honeypot'] ) ? 1 : 0,
			'honeypot_field_name'   => ! empty( $antispam_settings['honeypot_field_name'] ) ? sanitize_text_field( $antispam_settings['honeypot_field_name'] ) : 'magic',
			'timetrap'              => ! empty( $antispam_settings['timetrap'] ) ? 1 : 0,
			'timetrap_salt'         => 973568,
			'timetrap_minimum_time' => ! empty( $antispam_settings['timetrap_minimum_time'] ) ? (int) $antispam_settings['timetrap_minimum_time'] : 3,
		);

		return $this->antispam_settings;
	}

	/**
	 * Save video.
	 *
	 * @since  1.6.1
	 * @access private
	 */
	private function save_video() {		
		ob_start(); // Output Buffer

		// Vars		
		$video_settings = get_option( 'aiovg_video_settings' );
		$featured_images_settings = get_option( 'aiovg_featured_images_settings' );
		$user_submission_settings = get_option( 'aiovg_user_submission_settings' );

		$post_id = (int) $_POST['post_id'];	
		$has_permission = true;

		$is_new  = 1;
		$is_edit = 0;
		
		// Verify if the user can add video	
		if ( $post_id > 0 ) {
			if ( ! aiovg_current_user_can( 'edit_aiovg_video', $post_id ) ) $has_permission = false;
		} elseif ( ! aiovg_current_user_can( 'edit_aiovg_videos' ) ) {
			$has_permission = false;
		}
		
		// Save data
		if ( $has_permission ) {
			$post_status = $user_submission_settings['new_video_status'];	

			if ( $post_id > 0 ) {
				$old_status = get_post_status( $post_id );

				if ( 'draft' != $old_status ) {
					$is_new  = 0;
					$is_edit = 1;

					$post_status = ( 'pending' == $user_submission_settings['edit_video_status'] ) ? 'pending' : $old_status;						
				}					
			}

			if ( isset( $_POST['action'] ) && __( 'Save Draft', 'all-in-one-video-gallery' ) == $_POST['action'] ) {
				$post_status = 'draft';
				$is_new = 0;
			}

			$post_array = array(
				'post_title'   => wp_strip_all_tags( $_POST['title'] ),
				'post_name'    => sanitize_title( $_POST['title'] ),
				'post_content' => isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '',
				'post_status'  => $post_status,
				'post_type'	   => 'aiovg_videos'
			);

			if ( $post_id > 0 ) {		
				// Update the existing post
				$post_array['ID'] = $post_id;
				wp_update_post( $post_array );					
			} else {					
				// Save as new post
				$post_array['comment_status'] = ( (int) $video_settings['has_comments'] > 0 ) ? 'open' : 'closed';
				$post_array['post_author'] = get_current_user_id();

				if ( isset( $_POST['tax_input'] ) ) {
					// Added for "Icegram Express" plugin
					$tax_input = array();

					if ( ! empty( $user_submission_settings['assign_categories'] ) ) {
						if ( isset( $_POST['tax_input']['aiovg_categories'] ) ) {
							$tax_input['aiovg_categories'] = array_map( 'intval', $_POST['tax_input']['aiovg_categories'] );						
						}
					}
					
					if ( ! empty( $user_submission_settings['assign_tags'] ) ) {
						if ( isset( $_POST['tax_input']['aiovg_tags'] ) ) {
							$tax_input['aiovg_tags'] = array_map( 'intval', $_POST['tax_input']['aiovg_tags'] );						
						}
					}
	
					if ( count( $tax_input ) > 0 ) {
						$post_array['tax_input'] = $tax_input;
					}	
				}
				
				$post_id = wp_insert_post( $post_array );					
			}
				
			if ( ! empty( $post_id ) ) {
				// Save meta data
				if ( ! empty( $user_submission_settings['assign_categories'] ) ) {
					wp_set_object_terms( $post_id, '', 'aiovg_categories' );

					if ( isset( $_POST['tax_input'] ) && isset( $_POST['tax_input']['aiovg_categories'] ) ) {
						$cat_ids = array_map( 'intval', $_POST['tax_input']['aiovg_categories'] );						
						wp_set_object_terms( $post_id, $cat_ids, 'aiovg_categories', true );
					}
				}
				
				if ( ! empty( $user_submission_settings['assign_tags'] ) ) {
					wp_set_object_terms( $post_id, '', 'aiovg_tags' );

					if ( isset( $_POST['tax_input'] ) && isset( $_POST['tax_input']['aiovg_tags'] ) ) {
						$tag_ids = array_map( 'intval', $_POST['tax_input']['aiovg_tags'] );						
						wp_set_object_terms( $post_id, $tag_ids, 'aiovg_tags', true );
					}
				}

				$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'default';
				update_post_meta( $post_id, 'type', $type );

				$mp4 = isset( $_POST['mp4'] ) ? aiovg_sanitize_url( $_POST['mp4'] ) : '';
				update_post_meta( $post_id, 'mp4', $mp4 );
				update_post_meta( $post_id, 'mp4_id', attachment_url_to_postid( $mp4, 'video' ) );

				$has_webm = isset( $_POST['has_webm'] ) ? 1 : 0;
				update_post_meta( $post_id, 'has_webm', $has_webm );

				$webm = isset( $_POST['webm'] ) ? aiovg_sanitize_url( $_POST['webm'] ) : '';
				update_post_meta( $post_id, 'webm', $webm );
				update_post_meta( $post_id, 'webm_id', attachment_url_to_postid( $webm, 'video' ) );

				$has_ogv = isset( $_POST['has_ogv'] ) ? 1 : 0;
				update_post_meta( $post_id, 'has_ogv', $has_ogv );

				$ogv = isset( $_POST['ogv'] ) ? aiovg_sanitize_url( $_POST['ogv'] ) : '';
				update_post_meta( $post_id, 'ogv', $ogv );
				update_post_meta( $post_id, 'ogv_id', attachment_url_to_postid( $ogv, 'video' ) );

				$youtube = isset( $_POST['youtube'] ) ? aiovg_sanitize_url( aiovg_resolve_youtube_url( $_POST['youtube'] ) ) : '';
				update_post_meta( $post_id, 'youtube', $youtube );

				$vimeo = isset( $_POST['vimeo'] ) ? aiovg_sanitize_url( $_POST['vimeo'] ) : '';
				update_post_meta( $post_id, 'vimeo', $vimeo );

				$dailymotion = isset( $_POST['dailymotion'] ) ? aiovg_sanitize_url( $_POST['dailymotion'] ) : '';
				update_post_meta( $post_id, 'dailymotion', $dailymotion );

				$rumble = isset( $_POST['rumble'] ) ? aiovg_sanitize_url( $_POST['rumble'] ) : '';
				update_post_meta( $post_id, 'rumble', $rumble );

				$facebook = isset( $_POST['facebook'] ) ? aiovg_sanitize_url( $_POST['facebook'] ) : '';
				update_post_meta( $post_id, 'facebook', $facebook );

				$adaptive = isset( $_POST['adaptive'] ) ? aiovg_sanitize_url( $_POST['adaptive'] ) : '';
				if ( ! empty( $adaptive ) ) {
					if ( strpos( $adaptive, '.mpd' ) !== false ) {
						update_post_meta( $post_id, 'dash', $adaptive );
					} elseif ( strpos( $adaptive, '.m3u8' ) !== false ) {
						update_post_meta( $post_id, 'hls', $adaptive );
					}
				}

				$image    = '';
				$image_id = 0;

				if ( ! empty( $_POST['image'] ) ) {
					$image    = aiovg_sanitize_url( $_POST['image'] );
					$image_id = attachment_url_to_postid( $image, 'image' );
				} else {
					if ( 'youtube' == $type && ! empty( $youtube ) ) {
						$image = aiovg_get_youtube_image_url( $youtube );
					} elseif ( 'vimeo' == $type && ! empty( $vimeo ) ) {
						$image = aiovg_get_vimeo_image_url( $vimeo );
					} elseif ( 'dailymotion' == $type && ! empty( $dailymotion ) ) {
						$image = aiovg_get_dailymotion_image_url( $dailymotion );
					} elseif ( 'rumble' == $type && ! empty( $rumble ) ) {
						$oembed = aiovg_get_rumble_oembed_data( $rumble );
						$image = $oembed['thumbnail_url'];
					}
				}

				if ( ! empty( $featured_images_settings['enabled'] ) ) { // Set featured image
					if ( ! metadata_exists( 'post', $post_id, 'set_featured_image' ) ) {
						$set_featured_image = 1;
						update_post_meta( $post_id, 'set_featured_image', 1 );
					} else {
						$set_featured_image = get_post_meta( $post_id, 'set_featured_image', true );
					}					

					if ( empty( $image ) ) {
						$set_featured_image = 0;
					} else {
						if ( isset( $_POST['images'] ) ) { // Has images from thumbnail generator?
							$images = array_map( 'aiovg_sanitize_url', $_POST['images'] );

							foreach ( $images as $__image ) {		
								if ( $__image == $image ) {
									$set_featured_image = 0;
									break;
								}
							}
						}
					}

					if ( ! empty( $set_featured_image ) ) {
						if ( empty( $image_id ) && ! empty( $featured_images_settings['download_external_images'] ) ) {
							$image_id = aiovg_create_attachment_from_external_image_url( $image, $post_id );
						}

						if ( ! empty( $image_id ) ) {
							set_post_thumbnail( $post_id, $image_id ); 
						}
					}					
				}
				
				update_post_meta( $post_id, 'image', $image );
				update_post_meta( $post_id, 'image_id', $image_id );

				if ( empty( $_POST['post_id'] ) ) {
					update_post_meta( $post_id, 'featured', 0 );
					update_post_meta( $post_id, 'views', 0 );						
				}
				
				// Send emails
				if ( $is_new ) {
					aiovg_premium_notify_admin_video_added( $post_id );

					if ( 'publish' == $post_status ) {							
						aiovg_premium_notify_user_video_published( $post_id );
					} else {
						aiovg_premium_notify_user_video_pending_review( $post_id );
					}
				} elseif ( $is_edit ) {
					if ( 'pending' == $post_status ) {
						aiovg_premium_notify_admin_video_edited( $post_id );
						aiovg_premium_notify_user_video_pending_review( $post_id );
					}
				}					
			}

			if ( 'draft' == $post_status ) {
				$redirect_url = add_query_arg( 'status', $post_status, aiovg_premium_get_edit_video_page_url( $post_id ) );
			} else {
				$redirect_url = add_query_arg( 'status', $post_status, aiovg_premium_get_user_dashboard_page_url() );
			}
		} else {
			$redirect_url = add_query_arg( 'permission_denied', 1, aiovg_premium_get_user_dashboard_page_url() );
		}	

		// Redirect			
		wp_redirect( $redirect_url );
		exit();
	}	
	
	/**
	 * Delete video.
	 *
	 * @since  1.6.1
	 * @access private
	 */
	private function delete_video() {		
		$post_id = (int) $_GET['aiovg_video'];		
		
		if ( $post_id > 0 ) {
			ob_start(); // Output Buffer

			// Verify if the user can delete the video		
			if ( aiovg_current_user_can( 'delete_aiovg_video', $post_id ) ) {
				wp_delete_post( $post_id, true );
				$redirect_url = add_query_arg( 'status', 'deleted', aiovg_premium_get_user_dashboard_page_url() );
			} else {
				$redirect_url = add_query_arg( 'permission_denied', 1, aiovg_premium_get_user_dashboard_page_url() );
			}

			// Redirect			
			wp_redirect( $redirect_url );
			exit();
		}
	}
	
}

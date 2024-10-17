<?php

/**
 * Thumbnail Generator.
 *
 * @link       https://plugins360.com
 * @since      1.6.6
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Public_Thumbnail_Generator class.
 *
 * @since 1.6.6
 */
class AIOVG_Premium_Public_Thumbnail_Generator {
	
	/**
	 * Upload base64 string as image.
	 *
	 * @since 1.6.6
	 */
	public function ajax_callback_upload_base64_image() {
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );			

		if ( isset( $_FILES ) ) {
			$video = sanitize_text_field( $_POST['video'] );

			$index = (int) $_POST['index'];

			$file_name = md5( $video ) . sprintf( '-%03d', ++$index ) . '.png';
			
			$allowed_mimes = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
			);

			$file_info = wp_check_filetype( basename( $_FILES['image_data']['name'] ), $allowed_mimes );		
			if ( empty( $file_info['type'] ) ) {
				wp_die( __( 'Invalid file format.', 'all-in-one-video-gallery' ) );
			}

			if ( getimagesize( $_FILES['image_data']['tmp_name'] ) === FALSE ) {
				wp_die( __( 'Sorry, this file type is not permitted for security reasons.', 'all-in-one-video-gallery' ) );
			} 

			$wp_upload_dir = wp_upload_dir();

			if ( move_uploaded_file( $_FILES['image_data']['tmp_name'], $wp_upload_dir['path'] . '/' . $file_name ) ) {
				if ( file_exists( $wp_upload_dir['path'] . '/' . $file_name ) ) {
					$attributes = array(
						'images' => array(
							$wp_upload_dir['url'] . '/' . $file_name
						)
					);
	
					// Process output
					ob_start();
					the_aiovg_premium_thumbnail_generator_images( $attributes ); 
					echo ob_get_clean();
				}
			}			
		}

		wp_die();
	}

	/**
	 * FFMPEG generate images.
	 *
	 * @since 1.6.6
	 */
	public function ajax_callback_ffmpeg_generate_images() {
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );			

		if ( ! aiovg_premium_is_exec_available() ) {
			wp_die();
		}

		if ( empty( $_POST['url'] ) ) {
			wp_die();
		}			

		// Vars
		$settings = get_option( 'aiovg_thumbnail_generator_settings' );

		if ( empty( $settings['ffmpeg_path'] ) || empty( $settings['ffmpeg_images_count'] ) ) {
			wp_die();
		}	

		$attributes = array(
			'images' => array()
		);
		
		$ffmpeg_path = sanitize_text_field( $settings['ffmpeg_path'] );

		$video_url = sanitize_text_field( $_POST['url'] );

		$input_file = str_replace( ' ', '%20', $video_url );

		$file_name = md5( $video_url );

		$wp_upload_dir = wp_upload_dir();	
		$output_file   = $wp_upload_dir['path'] . '/' . $file_name . '-%03d.jpg';

		// Get video info
		$command = $ffmpeg_path . ' -i ' . $input_file . ' 2>&1';
		exec( $command, $output );

		if ( ! empty( $output ) ) {
			$output = implode( "\n", $output );

			// Parse duration
			preg_match( "/Duration: (.{2}):(.{2}):(.{2})/", $output, $matches );

			if ( ! empty( $matches ) ) {
				$hours   = $matches[1];
				$minutes = $matches[2];
				$seconds = $matches[3];
				
				$duration = $seconds + ( $minutes * 60 ) + ( $hours * 60 * 60 );

				// Generate images		
				$no_of_images = max( 1, (int) $settings['ffmpeg_images_count'] );
				$no_of_images = min( 10, $duration, $no_of_images );

				$command = $ffmpeg_path . ' -i ' . $input_file . ' -vf fps=' . $no_of_images . '/' . $duration . ' ' . $output_file . ' 2>&1';
				exec( $command, $output );

				for ( $i = 0; $i <= $no_of_images; $i++ ) {
					$file = sprintf( basename( $output_file ), $i + 1 );
		
					if ( file_exists( $wp_upload_dir['path'] . '/' . $file ) ) {
						$attributes['images'][] = $wp_upload_dir['url'] . '/' . $file;
					}
				}
			}
		}	

		// Process output
		ob_start();
		the_aiovg_premium_thumbnail_generator_images( $attributes ); 
		echo ob_get_clean();

		wp_die();
	}

	/**
	 * Save image meta data.
	 *
	 * @since  1.6.6
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post    The post object.
	 * @return int     $post_id If the save was successful or not.
	 */
	public function save_image_meta( $post_id, $post ) {	
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return $post_id;
		}
		
		// Check the logged in user has permission to edit this post
    	if ( ! aiovg_current_user_can( 'edit_aiovg_video', $post_id ) ) {
        	return $post_id;
    	}
		
		if ( isset( $_POST['images'] ) ) {
			$featured_images_settings = get_option( 'aiovg_featured_images_settings' );

			$images = array_map( 'aiovg_sanitize_url', $_POST['images'] );
			$current_image = ! empty( $_POST['image'] ) ? aiovg_sanitize_url( $_POST['image'] ) : '';
			$image_id = 0;

			$wp_upload_dir = wp_upload_dir();

			foreach ( $images as $image ) {
				$file_name = basename( $image );
				$file = $wp_upload_dir['path'] . '/' . $file_name;

				if ( $image == $current_image ) {
					// Check the type of file
					$file_type = wp_check_filetype( $file_name, null );

					// Prepare an array of post data for the attachment
					$attachment = array(
						'guid'           => $wp_upload_dir['url'] . '/' . $file_name, 
						'post_mime_type' => $file_type['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);

					// Insert the attachment
					$image_id = wp_insert_attachment( $attachment, $file, $post_id );

					// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it
					require_once( ABSPATH . 'wp-admin/includes/image.php' );

					// Generate the metadata for the attachment, and update the database record
					$image_data = wp_generate_attachment_metadata( $image_id, $file );
					wp_update_attachment_metadata( $image_id, $image_data );
				} else {
					wp_delete_file( $file );
				}
			}

			if ( ! empty( $image_id ) ) {
				update_post_meta( $post_id, 'image_id', $image_id );

				if ( ! empty( $featured_images_settings['enabled'] ) ) { // Set featured image
					if ( is_admin() ) {
						$set_featured_image = isset( $_POST['set_featured_image'] ) ? (int) $_POST['set_featured_image'] : 0;
					} else {
						if ( ! metadata_exists( 'post', $post_id, 'set_featured_image' ) ) {
							$set_featured_image = 1;
						} else {
							$set_featured_image = get_post_meta( $post_id, 'set_featured_image', true );
						}
					}
					
					if ( ! empty( $set_featured_image ) ) {
						set_post_thumbnail( $post_id, $image_id ); 
					}
				}
			}			
		}
		
		return $post_id;	
	}

}

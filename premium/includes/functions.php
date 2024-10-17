<?php

/**
 * Helper Functions.
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
 * Rotate images to the correct orientation.
 *
 * @since  1.6.1
 * @param  array $file $_FILES array
 * @return array	   $_FILES array in the correct orientation
 */
function aiovg_premium_exif_rotate( $file ){
	if ( ! function_exists( 'exif_read_data' ) ) {
		return $file;
	}
	
	$exif = @exif_read_data( $file['tmp_name'] );
	$exif_orient = isset( $exif['Orientation'] ) ? $exif['Orientation'] : 0;
	$rotate_image = 0;

	if ( 6 == $exif_orient ) {
		$rotate_image = 90;
	} elseif ( 3 == $exif_orient ) {
		$rotate_image = 180;
	} elseif ( 8 == $exif_orient ) {
		$rotate_image = 270;
	}

	if ( $rotate_image ) {	
		if ( class_exists( 'Imagick' ) ) {		
			$imagick = new Imagick();
			$imagick_pixel = new ImagickPixel();
			$imagick->readImage( $file['tmp_name'] );
			$imagick->rotateImage( $imagick_pixel, $rotate_image );
			$imagick->setImageOrientation( 1 );
			$imagick->writeImage( $file['tmp_name'] );
			$imagick->clear();
			$imagick->destroy();		
		} else {		
			$rotate_image = -$rotate_image;
			
			switch ( $file['type'] ) {
				case 'image/jpeg' :
					if ( function_exists( 'imagecreatefromjpeg' ) ) {
						$source = imagecreatefromjpeg( $file['tmp_name'] );
						$rotate = imagerotate( $source, $rotate_image, 0 );
						imagejpeg( $rotate, $file['tmp_name'] );
					}
					break;
				case 'image/png' :
					if ( function_exists( 'imagecreatefrompng' ) ) {
						$source = imagecreatefrompng( $file['tmp_name'] );
						$rotate = imagerotate( $source, $rotate_image, 0 );
						imagepng( $rotate, $file['tmp_name'] );
					}
					break;
				case 'image/gif' :
					if ( function_exists( 'imagecreatefromgif' ) ) {
						$source = imagecreatefromgif( $file['tmp_name'] );
						$rotate = imagerotate( $source, $rotate_image, 0 );
						imagegif( $rotate, $file['tmp_name'] );
					}
					break;
			}
		}	
	}
	
	return $file;
}

/**
 * Get the automations source types.
 *
 * @since  1.6.2
 * @param  string $service Name of the import service.
 * @return array  $types   Array of source types.
 */
function aiovg_premium_get_automations_types( $service = 'youtube' ) {
	$types = array();

	switch ( $service ) {
		case 'youtube':
			$types = array(				
				'channel'  => __( 'Channel', 'all-in-one-video-gallery' ),
				'playlist' => __( 'Playlist', 'all-in-one-video-gallery' ),
				'search'   => __( 'Search', 'all-in-one-video-gallery' ),
				'username' => __( 'Username', 'all-in-one-video-gallery' ),				
				'videos'   => __( 'Videos', 'all-in-one-video-gallery' )
			);
			break;
		case 'vimeo':
			$types = array(
				'showcase'  => __( 'Showcase / Album', 'all-in-one-video-gallery' ),
				'category'  => __( 'Category', 'all-in-one-video-gallery' ),				
				'channel'   => __( 'Channel', 'all-in-one-video-gallery' ),
				'folder'    => __( 'Folder', 'all-in-one-video-gallery' ),
				'group'     => __( 'Group', 'all-in-one-video-gallery' ),
				'portfolio' => __( 'Portfolio', 'all-in-one-video-gallery' ),
				'search'    => __( 'Search', 'all-in-one-video-gallery' ),
				'username'  => __( 'User Uploads', 'all-in-one-video-gallery' ),
				'videos'    => __( 'Videos', 'all-in-one-video-gallery' )
			);
			break;
	}

	return $types;
}

/**
 * Get the automations video services.
 *
 * @since  1.6.2
 * @return array $services Array of video services.
 */
function aiovg_premium_get_automations_services() {
	$services = array(
		'youtube' => __( 'YouTube', 'all-in-one-video-gallery' ),
		'vimeo'   => __( 'Vimeo', 'all-in-one-video-gallery' )
	);

	return $services;
}

/**
 * Get the delete playlist page URL.
 *
 * @since  3.6.0
 * @param  int    $playlist_id Playlist ID.
 * @return string              Delete playlist page URL.
 */
function aiovg_premium_get_delete_playlist_page_url( $playlist_id ) {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';

	if ( $page_settings['playlist'] > 0 ) {	
		$url = get_permalink( $page_settings['playlist'] );	
		
		$query_args = array(
			'aiovg_action'   => 'delete',
			'aiovg_playlist' => (int) $playlist_id,
			'_wpnonce'       => wp_create_nonce( 'aiovg_delete_playlist_nonce' )
		);
		
		$url = add_query_arg( $query_args, $url );
	}
	
	return apply_filters( 'aiovg_delete_playlist_page_url', $url, $playlist_id );
}

/**
 * Get the delete video page URL.
 *
 * @since  1.6.1
 * @param  int    $post_id Video ID
 * @return string          Delete video page URL.
 */
function aiovg_premium_get_delete_video_page_url( $post_id ) {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';

	if ( $page_settings['video_form'] > 0 ) {	
		$url = get_permalink( $page_settings['video_form'] );	
		
		$query_args = array(
			'aiovg_action' => 'delete',
			'aiovg_video'  => (int) $post_id ,
			'_wpnonce'     => wp_create_nonce( 'aiovg_delete_video_nonce' )
		);
		
		$url = add_query_arg( $query_args, $url );
	}
	
	return apply_filters( 'aiovg_delete_video_page_url', $url, $post_id );	
}

/**
 * Get the edit video page URL.
 *
 * @since  1.6.1
 * @param  int    $post_id Video ID
 * @return string          Edit video page URL.
 */
function aiovg_premium_get_edit_video_page_url( $post_id ) {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';

	if ( $page_settings['video_form'] > 0 ) {	
		$url = get_permalink( $page_settings['video_form'] );	
		
		if ( '' != get_option( 'permalink_structure' ) ) {
    		$url = user_trailingslashit( trailingslashit( $url ) . 'edit/' . (int) $post_id );
  		} else {
    		$url = add_query_arg( array( 'aiovg_action' => 'edit', 'aiovg_video' => (int) $post_id ), $url );
		}		
		  
		$url = add_query_arg( '_wpnonce', wp_create_nonce( 'aiovg_edit_video_nonce' ), $url );
	}
	
	return apply_filters( 'aiovg_edit_video_page_url', $url, $post_id );	
}

/**
 * Get the playlist page URL.
 *
 * @since  3.6.0
 * @param  object $term The term object.
 * @return string       Playlist page URL.
 */
function aiovg_premium_get_playlist_page_url( $term ) {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';
	
	if ( $page_settings['playlist'] > 0 ) {
		$url = get_permalink( $page_settings['playlist'] );
	
		if ( '' != get_option( 'permalink_structure' ) ) {
    		$url = user_trailingslashit( trailingslashit( $url ) . $term->slug );
  		} else {
    		$url = add_query_arg( 'aiovg_playlist', $term->slug, $url );
  		}
	}
  
	return apply_filters( 'aiovg_playlist_page_url', $url, $term );
}

/**
 * Get the remove from playlist page URL.
 *
 * @since  3.6.0
 * @param  int    $playlist_id Playlist ID.
 * @param  int    $video_id    Video ID
 * @return string              Remove from playlist page URL.
 */
function aiovg_premium_get_remove_from_playlist_page_url( $playlist_id, $video_id ) {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';

	if ( $page_settings['playlist'] > 0 ) {	
		$url = get_permalink( $page_settings['playlist'] );	
		
		$query_args = array(
			'aiovg_action'   => 'remove',
			'aiovg_playlist' => (int) $playlist_id,
			'aiovg_video'    => (int) $video_id,
			'_wpnonce'       => wp_create_nonce( 'aiovg_remove_from_playlist_nonce' )
		);
		
		$url = add_query_arg( $query_args, $url );
	}
	
	return apply_filters( 'aiovg_remove_from_playlist_page_url', $url, $playlist_id, $video_id );	
}

/**
 * Get the user dashboard page URL.
 *
 * @since  1.6.1
 * @return string User dashboard page URL.
 */
function aiovg_premium_get_user_dashboard_page_url() {
	$page_settings = get_option( 'aiovg_page_settings' );

	$url = '/';
	
	if ( $page_settings['user_dashboard'] > 0 ) {
		$url = get_permalink( $page_settings['user_dashboard'] );
	}	
	
	return apply_filters( 'aiovg_user_dashboard_page_url', $url );	
}

/**
 * Get the video form page URL.
 *
 * @since  1.6.1
 * @return string Video form page URL.
 */
function aiovg_premium_get_video_form_page_url() {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';
	
	if ( $page_settings['video_form'] > 0 ) {
		$url = get_permalink( $page_settings['video_form'] );
	}
	
	return apply_filters( 'aiovg_video_form_page_url', $url );	
}

/**
 * Get translated import status text.
 * 
 * @since  1.6.2
 * @param  string $status            Import status.
 * @return string $translated_status Translated import status.
 */
function aiovg_premium_get_import_status_text( $status ) {
	$translated_status = '';

	if ( 'scheduled' == $status ) {
		$translated_status = __( 'Scheduled', 'all-in-one-video-gallery' );
	} elseif ( 'rescheduled' == $status ) {
		$translated_status = __( 'Rescheduled', 'all-in-one-video-gallery' );
	} elseif ( 'completed' == $status ) {
		$translated_status = __( 'Completed', 'all-in-one-video-gallery' );
	}

	return $translated_status;
}

/**
 * Check if "exec" is available.
 * 
 * @since  1.6.6
 * @return bool  $is_available True if available, false if not.
 */
function aiovg_premium_is_exec_available() {
	$is_available = true;

	if ( ini_get( 'safe_mode' ) ) {
		$is_available = false;
	} else {
		$d = ini_get( 'disable_functions' );
		$s = ini_get( 'suhosin.executor.func.blacklist' );

		if ( "$d$s" ) {
			$array = preg_split( '/,\s*/', "$d,$s" );

			if ( in_array( 'exec', $array ) ) {
				$is_available = false;
			}
		}
	}

	return $is_available;
}

/**
 * Provides a simple login form.
 *
 * @since  1.6.1
 * @return string Login form.
 */
function aiovg_premium_login_form() {
	$user_account_settings = get_option( 'aiovg_user_account_settings' );
	
	// Login Form
	$custom_login = $user_account_settings['custom_login'];
	
	if ( empty( $custom_login ) ) {
		$html = '<div class="aiovg aiovg-login-form">';

		// The WP default Login Form
		$html .= wp_login_form( array( 'echo' => false ) );

		// Forgot Password
		$lostpassword_url = empty( $user_account_settings['custom_forgot_password'] ) ? wp_lostpassword_url( get_permalink() ) : $user_account_settings['custom_forgot_password'];
		$html .= sprintf( '<p><a href="%s">%s</a></p>', esc_url( $lostpassword_url ), __( 'Forgot your password?', 'all-in-one-video-gallery' ) );
		
		// Registration		
		if ( get_option( 'users_can_register' ) ) {
			$registration_url = empty( $user_account_settings['custom_register'] ) ? wp_registration_url() : $user_account_settings['custom_register'];
			$html .= sprintf( '<p><a href="%s">%s</a></p>', esc_url( $registration_url ), __( 'Create an account', 'all-in-one-video-gallery' ) );
		}	

		$html .= '</div>';
	} else {
		if ( ! filter_var( $custom_login, FILTER_VALIDATE_URL ) === FALSE ) {
			// If URL redirect here
			echo '<script type="text/javascript">window.location.href="' . $custom_login . '";</script>';
			exit(); 
		} else {
			// If shortcode found
			$html = do_shortcode( $custom_login );
		}
	}		
	
	return $html;	
}

/**
 * Notify admin when a new video added.
 *
 * @since 1.6.1
 * @param int   $post_id Post ID.
 */
function aiovg_premium_notify_admin_video_added( $post_id ) {		
	$post_author_id = get_post_field( 'post_author', $post_id );
	$user           = get_userdata( $post_author_id );
	
	$placeholders = array(
		'{name}'         => $user->display_name,
		'{username}'     => $user->user_login,
		'{site_name}'    => get_bloginfo( 'name' ),
		'{video_id}'     => $post_id,
		'{video_title}'  => get_the_title( $post_id ),
		'{video_status}' => ( 'publish' == get_post_status( $post_id ) ) ? __( 'Active', 'all-in-one-video-gallery' ) : sprintf( '<a href="%s">%s</a>', admin_url( "post.php?post=$post_id&action=edit" ), __( 'Pending review', 'all-in-one-video-gallery' ) )
	);
		
	$to = get_bloginfo( 'admin_email' );
	
	$subject = __( '[{site_name}] New video received', 'all-in-one-video-gallery' );
	$subject = strtr( $subject, $placeholders );
	
	$message = __( "Dear Administrator,<br /><br />You have received a new video on the website {site_name}.<br />This e-mail contains the video details:<br /><br />Video ID:{video_id}<br />Video Title:{video_title}<br />Video Status:{video_status}<br /><br />Please do not respond to this message. It is automatically generated and is for information purposes only.", 'all-in-one-video-gallery' );
	$message = strtr( $message, $placeholders );

	aiovg_premium_send_mail( $to, $subject, $message );	
}

/**
 * Notify admin when a video is edited.
 *
 * @since 1.6.1
 * @param int   $post_id Post ID.
 */
function aiovg_premium_notify_admin_video_edited( $post_id ) {		
	$post_author_id = get_post_field( 'post_author', $post_id );
	$user           = get_userdata( $post_author_id );
	
	$placeholders = array(
		'{name}'         => $user->display_name,
		'{username}'     => $user->user_login,
		'{site_name}'    => get_bloginfo( 'name' ),
		'{video_id}'     => $post_id,
		'{video_title}'  => get_the_title( $post_id ),
		'{video_status}' => ( 'publish' == get_post_status( $post_id ) ) ? __( 'Active', 'all-in-one-video-gallery' ) : sprintf( '<a href="%s">%s</a>', admin_url( "post.php?post=$post_id&action=edit" ), __( 'Pending review', 'all-in-one-video-gallery' ) )
	);
		
	$to = get_bloginfo( 'admin_email' );
	
	$subject = __( '[{site_name}] Video "{video_title}" edited', 'all-in-one-video-gallery' );
	$subject = strtr( $subject, $placeholders );
	
	$message = __( "Dear Administrator,<br /><br />This notification was for the video on the website {site_name} \"{video_title}\" and is edited.<br />This e-mail contains the video details:<br /><br />Video ID:{video_id}<br />Video Title:{video_title}<br />Video Status:{video_status}<br /><br />Please do not respond to this message. It is automatically generated and is for information purposes only.", 'all-in-one-video-gallery' );
	$message = strtr( $message, $placeholders );

	aiovg_premium_send_mail( $to, $subject, $message );	
}

/**
 * Notify admin when videos auto imported and pending review.
 *
 * @since 1.6.2
 * @param int   $post_id    Automations Post ID.
 * @param array $statistics Import statistics.
 */
function aiovg_premium_notify_admin_videos_imported( $post_id, $statistics ) {	
	$videos_url = admin_url( 'edit.php?post_type=aiovg_videos&aiovg_filter=imported&import_id=' . $post_id . '&import_key=' . $statistics['key'] );
	$import_source_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

	$placeholders = array(
		'{site_name}'          => get_bloginfo( 'name' ),
		'{videos_count}'       => (int) $statistics['imported'],
		'{videos_link}'        => sprintf( '<a href="%1$s">%1$s</a>', esc_url( $videos_url ) ),
		'{import_source_link}' => sprintf( '<a href="%s">%s</a>', esc_url( $import_source_url ), get_the_title( $post_id ) )
	);
		
	$to = get_bloginfo( 'admin_email' );
	
	$subject = __( '[{site_name}] {videos_count} videos auto imported', 'all-in-one-video-gallery' );
	$subject = strtr( $subject, $placeholders );
	
	$message = __( "Dear Administrator,<br /><br />You have {videos_count} videos auto imported on the website {site_name} and waiting for your approval. You can find those videos using the link below,<br />{videos_link}<br />Import Source: {import_source_link}<br /><br />Please do not respond to this message. It is automatically generated and is for information purposes only.", 'all-in-one-video-gallery' );
	$message = strtr( $message, $placeholders );

	aiovg_premium_send_mail( $to, $subject, $message );		
}

/**
 * Notify user when his video is pending review.
 *
 * @since 1.6.1
 * @param int   $post_id Post ID.
 */
function aiovg_premium_notify_user_video_pending_review( $post_id ) {	
	$email_settings = get_option( 'aiovg_email_video_pending_review_settings' );
	
	$post_author_id = get_post_field( 'post_author', $post_id );
	$user           = get_userdata( $post_author_id );
	$site_name      = get_bloginfo( 'name' );
	$site_url       = get_bloginfo( 'url' );
	$video_title    = get_the_title( $post_id );
	$video_url      = get_permalink( $post_id );
	$date_format    = get_option( 'date_format' );
	$time_format    = get_option( 'time_format' );
	$current_time   = current_time( 'timestamp' );
	
	$placeholders = array(
		'{name}'          => $user->display_name,
		'{username}'      => $user->user_login,
		'{site_name}'     => $site_name,
		'{site_link}'     => sprintf( '<a href="%s">%s</a>', $site_url, $site_name ),
		'{site_url}'      => sprintf( '<a href="%s">%s</a>', $site_url, $site_url ),
		'{video_title}'   => $video_title,
		'{video_link}'    => sprintf( '<a href="%s">%s</a>', $video_url, $video_title ),
		'{video_url}'     => sprintf( '<a href="%s">%s</a>', $video_url, $video_url ),
		'{today}'         => date_i18n( $date_format, $current_time ),
		'{now}'           => date_i18n( $date_format . ' ' . $time_format, $current_time )
	);
		
	$to      = $user->user_email;		
	$subject = apply_filters( 'aiovg_translate_strings', strtr( $email_settings['subject'], $placeholders ), 'email_video_pending_review_settings_subject' );
	$message = strtr( $email_settings['body'], $placeholders );
	$message = apply_filters( 'aiovg_translate_strings', nl2br( $message ), 'email_video_pending_review_settings_body' );
	
	aiovg_premium_send_mail( $to, $subject, $message );	
}

/**
 * Notify user when his video published.
 *
 * @since 1.6.1
 * @param int   $post_id Post ID.
 */
function aiovg_premium_notify_user_video_published( $post_id ) {	
	$email_settings = get_option( 'aiovg_email_video_published_settings' );
	
	$post_author_id = get_post_field( 'post_author', $post_id );
	$user           = get_userdata( $post_author_id );
	$site_name      = get_bloginfo( 'name' );
	$site_url       = get_bloginfo( 'url' );
	$video_title    = get_the_title( $post_id );
	$video_url      = get_permalink( $post_id );
	$date_format    = get_option( 'date_format' );
	$time_format    = get_option( 'time_format' );
	$current_time   = current_time( 'timestamp' );
	
	$placeholders = array(
		'{name}'          => $user->display_name,
		'{username}'      => $user->user_login,
		'{site_name}'     => $site_name,
		'{site_link}'     => sprintf( '<a href="%s">%s</a>', $site_url, $site_name ),
		'{site_url}'      => sprintf( '<a href="%s">%s</a>', $site_url, $site_url ),
		'{video_title}'   => $video_title,
		'{video_link}'    => sprintf( '<a href="%s">%s</a>', $video_url, $video_title ),
		'{video_url}'     => sprintf( '<a href="%s">%s</a>', $video_url, $video_url ),
		'{today}'         => date_i18n( $date_format, $current_time ),
		'{now}'           => date_i18n( $date_format . ' ' . $time_format, $current_time )
	);
		
	$to      = $user->user_email;		
	$subject = apply_filters( 'aiovg_translate_strings', strtr( $email_settings['subject'], $placeholders ), 'email_video_published_settings_subject' );
	$message = strtr( $email_settings['body'], $placeholders );
	$message = apply_filters( 'aiovg_translate_strings', nl2br( $message ), 'email_video_published_settings_body' );
	
	aiovg_premium_send_mail( $to, $subject, $message );	
}

/**
 * Send mail, similar to PHP's mail.
 *
 * @since  1.6.1
 * @param  string|array $to          Array or comma-separated list of email addresses to send message.
 * @param  string       $subject     Email subject.
 * @param  string       $message     Message contents.
 * @param  string|array $headers     Additional headers.
 * @param  string|array $attachments Files to attach.
 * @return bool                      Whether the email contents were sent successfully.
 */
function aiovg_premium_send_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
	if ( empty( $to ) || empty( $subject ) || empty( $message ) ) {
		return false;
	}

	if ( empty( $headers ) ) {
		$name  = sanitize_text_field( get_option( 'blogname' ) );
		$email = sanitize_email( get_option( 'admin_email' ) );

		$headers .= "From: {$name} <{$email}>\r\n";
		$headers .= "Reply-To: {$email}\r\n";
	}

	add_filter( 'wp_mail_content_type', 'aiovg_premium_set_html_mail_content_type' );
	$success = wp_mail( $to, sanitize_text_field( html_entity_decode( $subject ) ), wp_kses_post( $message ), $headers );
	remove_filter( 'wp_mail_content_type', 'aiovg_premium_set_html_mail_content_type' );
		
	return $success;	
}

/**
 * Set the email content type.
 *
 * @since 1.6.1
 * @param string $content_type Default content type.
 */
function aiovg_premium_set_html_mail_content_type( $content_type ) {	
	return 'text/html';	
}

/**
 * Slick arrows HTML output.
 *
 * @since 3.3.1
 * @param array $atts Array of attributes.
 */
function the_aiovg_premium_slick_arrows( $attributes ) {
	include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . 'premium/public/templates/slick-arrows.php', $attributes );
}

/**
 * Thumbnail generator.
 *
 * @since 1.6.6
 */
function the_aiovg_premium_thumbnail_generator() {
	$attributes = get_option( 'aiovg_thumbnail_generator_settings' );

	if ( is_admin() || ! empty( $attributes['enable_html5_thumbnail_generator'] ) || ( ! empty( $attributes['ffmpeg_path'] ) && ! empty( $attributes['ffmpeg_images_count'] ) ) ) {
		include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . "premium/public/templates/thumbnail-generator.php", $attributes );
	}	
}

/**
 * Thumbnail generator images.
 *
 * @since 1.6.6
 * @param array $attributes Array of attributes.
 */
function the_aiovg_premium_thumbnail_generator_images( $attributes = array() ) {
	include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . "premium/public/templates/thumbnail-generator-images.php", $attributes );
}
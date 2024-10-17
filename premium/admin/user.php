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
 * AIOVG_Premium_Admin_User class.
 *
 * @since 1.6.1
 */
class AIOVG_Premium_Admin_User {

	/**
     * Insert user settings tab.
     *
	 * @since  1.6.1
	 * @param  array $tabs Core settings tabs array.
     * @return array $tabs Updated settings tabs array.
     */
    public function custom_settings_tab( $tabs ) {
		$new_tab = array(
			'user' => __( 'User / Front-end Submission', 'all-in-one-video-gallery' )
		);
		
		return aiovg_insert_array_after( 'general', $tabs, $new_tab );	
	}

	/**
     * Insert users settings sections.
     *
	 * @since  1.6.1
	 * @param  array $sections Core settings sections array.
     * @return array $sections Updated settings sections array.
     */
    public function custom_settings_sections( $sections ) {	
		$user_sections = array(
			array(
				'id'          => 'aiovg_playlists_settings',
				'title'       => __( 'User Playlists', 'all-in-one-video-gallery' ),
				'description' => '',
				'tab'         => 'user',
				'page'        => 'aiovg_playlists_settings'
			),
			array(
				'id'          => 'aiovg_user_submission_settings',
				'title'       => __( 'Front-end Video Submission', 'all-in-one-video-gallery' ),
				'description' => '',
				'tab'         => 'user',
				'page'        => 'aiovg_user_submission_settings'
			),
			array(
				'id'          => 'aiovg_antispam_settings',
				'title'       => __( 'Anti-Spam', 'all-in-one-video-gallery' ),	
				'description' => '',	
				'tab'         => 'user',
				'page'        => 'aiovg_antispam_settings'
			),
			array(
				'id'          => 'aiovg_user_account_settings',
				'title'       => __( 'User Account Settings', 'all-in-one-video-gallery' ),
				'description' => '',
				'tab'         => 'user',
				'page'        => 'aiovg_user_account_settings'
			),						
			array(
				'id'          => 'aiovg_email_video_pending_review_settings',
				'title'       => __( '[Email Template] Video Pending Review', 'all-in-one-video-gallery' ),
				'description' => '',
				'tab'         => 'user',
				'page'        => 'aiovg_email_video_pending_review_settings'
			),
			array(
				'id'          => 'aiovg_email_video_published_settings',
				'title'       => __( '[Email Template] Video Published', 'all-in-one-video-gallery' ),
				'description' => '',
				'tab'         => 'user',
				'page'        => 'aiovg_email_video_published_settings'
			)			
		);
		
		return array_merge( $sections, $user_sections );	
	}

	/**
     * Insert user settings fields.
     *
	 * @since  1.6.1
	 * @param  array $fields Core settings fields array.
     * @return array $fields Updated settings fields array.
     */
    public function custom_settings_fields( $fields ) {
		$fields['aiovg_playlists_settings'] = array(
			array(
				'name'              => 'enabled',
				'label'             => __( 'Playlists / Favorites', 'all-in-one-video-gallery' ),
				'description'       => __( 'Check this option to allow users to create playlists', 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'limit',
				'label'             => __( 'Playlists Limit', 'all-in-one-video-gallery' ),
				'description'       => __( 'Enter the maximum number of playlists a user can create. Leave this field empty for an unlimited count.', 'all-in-one-video-gallery' ),
				'type'              => 'text',
				'placeholder'       => '',
				'sanitize_callback' => 'aiovg_sanitize_int'
			),
			array(
				'name'              => 'admin_menu',
				'label'             => __( 'Admin Menu', 'all-in-one-video-gallery' ),
				'description'       => __( 'Check this option to enable the "Video Playlists" menu in the plugin back-end', 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
		);

		$fields['aiovg_user_submission_settings'] = array(			
			array(
				'name'              => 'allowed_source_types',
				'label'             => __( 'Allowed Source Types', 'all-in-one-video-gallery' ),
				'description'       => '',
				'type'              => 'multicheck',
				'options'           => aiovg_get_video_source_types(),
				'sanitize_callback' => 'aiovg_sanitize_array'
			),
			array(
				'name'              => 'default_source_type',
				'label'             => __( 'Default Source Type', 'all-in-one-video-gallery' ),
				'description'       => __( 'The default source type should be one of the "Allowed Source Types".', 'all-in-one-video-gallery' ),
				'type'              => 'select',
				'options'           => aiovg_get_video_source_types(),
				'sanitize_callback' => 'sanitize_key'
			),
			array(
				'name'              => 'assign_categories',
				'label'             => __( 'Assign Categories', 'all-in-one-video-gallery' ),
				'description'       => __( 'Check this box to allow users to select categories for their videos', 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'assign_tags',
				'label'             => __( 'Assign Tags', 'all-in-one-video-gallery' ),
				'description'       => __( 'Check this box to allow users to select tags for their videos', 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'allow_file_uploads',
				'label'             => __( 'Allow File Uploads', 'all-in-one-video-gallery' ),
				'description'       => __( 'Check this box to allow users to upload files to your website', 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'allowed_file_formats',
				'label'             => __( 'Allowed File Formats', 'all-in-one-video-gallery' ),
				'description'       => '',
				'type'              => 'multicheck',
				'options'           => array(
					'mp4'  => 'MP4',
					'webm' => 'WebM',
					'ogv'  => 'OGV',
					'm4v'  => 'M4V',
					'mov'  => 'MOV',
					'jpg'  => 'JPG',
					'png'  => 'PNG',
					'gif'  => 'GIF'
				),
				'sanitize_callback' => 'aiovg_sanitize_array'
			),
			array(
				'name'              => 'max_upload_size',
				'label'             => __( 'Maximum Upload Size', 'all-in-one-video-gallery' ),
				'description'       => __( 'In bytes. Enter the maximum file size the users can upload in your website. Leave this field empty to allow the maximium possible file size.', 'all-in-one-video-gallery' ),
				'type'              => 'text',
				'placeholder'       => '',
				'sanitize_callback' => 'aiovg_sanitize_int'
			),
			array(
				'name'              => 'new_video_status',
				'label'             => __( 'Default New Video Status', 'all-in-one-video-gallery' ),
				'description'       => '',
				'type'              => 'select',
				'options'           => array(
					'publish' => __( 'Publish', 'all-in-one-video-gallery' ),
					'pending' => __( 'Pending', 'all-in-one-video-gallery' )
				),
				'sanitize_callback' => 'sanitize_key'
			),
			array(
				'name'              => 'edit_video_status',
				'label'             => __( 'Edit Video Status', 'all-in-one-video-gallery' ),
				'description'       => '',
				'type'              => 'select',
				'options'           => array(
					'publish' => __( 'Publish', 'all-in-one-video-gallery' ),
					'pending' => __( 'Pending', 'all-in-one-video-gallery' )
				),
				'sanitize_callback' => 'sanitize_key'
			),
			array(
				'name'              => 'terms_and_conditions',
				'label'             => __( 'Terms and Conditions URL', 'all-in-one-video-gallery' ),
				'description'       => __( 'Optional. Enter your Terms and Conditions Page URL.', 'all-in-one-video-gallery' ),
				'type'              => 'text',
				'sanitize_callback' => 'aiovg_sanitize_url'
			),
		);
		
		$fields['aiovg_antispam_settings'] = array(
			array(
				'name'              => 'honeypot',
				'label'             => __( 'Honeypot Protection', 'all-in-one-video-gallery' ),
				'description'       => __( "Check this to block spam submissions using our honeypot anti-spam technique. No Captcha or extra verification field hassle to the users. Only lets spam bots suffer using our anti-spam filter.", 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'honeypot_field_name',
				'label'             => __( 'Honeypot Field Name', 'all-in-one-video-gallery' ),
				'description'       => __( 'Unique honeypot field name generated for each WordPress installation, so it is hard for spam bots to make one fit for all solutions to bypass the honeypot anti-spam test. Use this field to change the field named auto-generated by the plugin. Changing this field name often is a good idea. Should definitely do if you are getting spam.', 'all-in-one-video-gallery' ),
				'type'              => 'text',
				'sanitize_callback' => 'sanitize_text_field'
			),
			array(
				'name'              => 'timetrap',
				'label'             => __( 'Timetrap Protection', 'all-in-one-video-gallery' ),
				'description'       => __( 'Check this to enable a time-based spam blocker', 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'timetrap_minimum_time',
				'label'             => __( 'Timetrap Minimum Time in Seconds', 'all-in-one-video-gallery' ),
				'description'       => __( 'Minimum time needed to complete the form. Forms submitted in under 3 seconds of the page loading are typically spam. Use this field to have form timeouts, and prevent instant posting. ', 'all-in-one-video-gallery' ),
				'type'              => 'number',
				'sanitize_callback' => 'intval'
			)
		);
		
		$fields['aiovg_user_account_settings'] = array(
			array(
				'name'              => 'custom_login',
				'label'             => __( 'Custom Login URL', 'all-in-one-video-gallery' ),
				'description'       => __( 'Optional. Enter your custom Login Page URL. Leave this field empty to use the default WordPress Login form.', 'all-in-one-video-gallery' ),
				'type'              => 'text',
				'sanitize_callback' => 'aiovg_sanitize_url'
			),
			array(
				'name'              => 'custom_register',
				'label'             => __( 'Custom Registration URL', 'all-in-one-video-gallery' ),
				'description'       => __( 'Optional. Enter your custom Registration Page URL. Leave this field empty to use the default WordPress Registration URL.', 'all-in-one-video-gallery' ),
				'type'              => 'text',
				'sanitize_callback' => 'aiovg_sanitize_url'
			),
			array(
				'name'              => 'custom_forgot_password',
				'label'             => __( 'Custom Forgot Password URL', 'all-in-one-video-gallery' ),
				'description'       => __( 'Optional. Enter your custom Forgot Password Page URL. Leave this field empty to use the default WordPress Forgot Password URL.', 'all-in-one-video-gallery' ),
				'type'              => 'text',
				'sanitize_callback' => 'aiovg_sanitize_url'
			),
		);

		$fields['aiovg_email_video_pending_review_settings'] = array(
			array(
				'name'              => 'subject',
				'label'             => __( 'Email Subject', 'all-in-one-video-gallery' ),
				'description'       => '',
				'type'              => 'text',
				'sanitize_callback' => 'sanitize_text_field'
			),
			array(
				'name'              => 'body',
				'label'             => __( 'Email Body', 'all-in-one-video-gallery' ),
				'description'       => sprintf(
					'<strong>%s</strong><br>{name} - %s<br>{username} - %s<br>{site_name} - %s<br>{site_link} - %s<br>{site_url} - %s<br>{video_title} - %s<br>{video_link} - %s<br>{video_url} - %s<br>{today} - %s<br>{now} - %s',
					__( 'Supported Placeholders:', 'all-in-one-video-gallery' ),
					__( 'The video owner\'s display name on the site', 'all-in-one-video-gallery' ),
					__( 'The video owner\'s user name on the site', 'all-in-one-video-gallery' ),
					__( 'Your site name', 'all-in-one-video-gallery' ),
					__( 'Your site name with link', 'all-in-one-video-gallery' ),
					__( 'Your site url with link', 'all-in-one-video-gallery' ),
					__( 'Video\'s title', 'all-in-one-video-gallery' ),
					__( 'Video\'s title with link', 'all-in-one-video-gallery' ),
					__( 'Video\'s url with link', 'all-in-one-video-gallery' ),
					__( 'Current Date', 'all-in-one-video-gallery' ),
					__( 'Current Time', 'all-in-one-video-gallery' )
				),
				'type'              => 'wysiwyg',
				'sanitize_callback' => 'wp_kses_post'
			),
		);

		$fields['aiovg_email_video_published_settings'] = array(
			array(
				'name'              => 'subject',
				'label'             => __( 'Email Subject', 'all-in-one-video-gallery' ),
				'description'       => '',
				'type'              => 'text',
				'sanitize_callback' => 'sanitize_text_field'
			),
			array(
				'name'              => 'body',
				'label'             => __( 'Email Body', 'all-in-one-video-gallery' ),
				'description'       => sprintf(
					'<strong>%s</strong><br>{name} - %s<br>{username} - %s<br>{site_name} - %s<br>{site_link} - %s<br>{site_url} - %s<br>{video_title} - %s<br>{video_link} - %s<br>{video_url} - %s<br>{today} - %s<br>{now} - %s',
					__( 'Supported Placeholders:', 'all-in-one-video-gallery' ),
					__( 'The video owner\'s display name on the site', 'all-in-one-video-gallery' ),
					__( 'The video owner\'s user name on the site', 'all-in-one-video-gallery' ),
					__( 'Your site name', 'all-in-one-video-gallery' ),
					__( 'Your site name with link', 'all-in-one-video-gallery' ),
					__( 'Your site url with link', 'all-in-one-video-gallery' ),
					__( 'Video\'s title', 'all-in-one-video-gallery' ),
					__( 'Video\'s title with link', 'all-in-one-video-gallery' ),
					__( 'Video\'s url with link', 'all-in-one-video-gallery' ),
					__( 'Current Date', 'all-in-one-video-gallery' ),
					__( 'Current Time', 'all-in-one-video-gallery' )
				),
				'type'              => 'wysiwyg',
				'sanitize_callback' => 'wp_kses_post'
			),
		);		

		$fields['aiovg_page_settings'][] = array(
			'name'              => 'user_dashboard',
			'label'             => __( 'User Dashboard', 'all-in-one-video-gallery' ),
			'description'       => __( 'This is the page where the users can manage (add, edit or delete) their videos in front-end. The [aiovg_user_dashboard] short code must be on this page.', 'all-in-one-video-gallery' ),
			'type'              => 'pages',
			'sanitize_callback' => 'sanitize_key'
		);

		$fields['aiovg_page_settings'][] = array(
			'name'              => 'video_form',
			'label'             => __( 'Video Form', 'all-in-one-video-gallery' ),
			'description'       => __( 'This is the form page where the users can add their videos in front-end. The [aiovg_video_form] short code must be on this page.', 'all-in-one-video-gallery' ),
			'type'              => 'pages',
			'sanitize_callback' => 'sanitize_key'
		);

		$fields['aiovg_page_settings'][] = array(
			'name'              => 'playlist',
			'label'             => __( 'My Playlists', 'all-in-one-video-gallery' ),
			'description'       => __( 'This is the page where the users can manage their playlists in front-end. The [aiovg_playlist] short code must be on this page.', 'all-in-one-video-gallery' ),
			'type'              => 'pages',
			'sanitize_callback' => 'sanitize_key'
		);

		return $fields;
	}

	/**
	 * Add category form fields.
	 *
	 * @since 2.5.6
	 */
	public function add_category_form_fields() {	
		$form = 'add';		
		require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/category-fields.php';	
	}
	
	/**
	 * Edit category form fields.
	 *
	 * @since 2.5.6
	 * @param object $term Taxonomy term object.
	 */
	public function edit_category_form_fields( $term ) {	
		$form = 'edit';

		$exclude_video_form = get_term_meta( $term->term_id, 'exclude_video_form', true );
		
		require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/category-fields.php';	
	}
	
	/**
	 * Save the category form fields.
	 *
	 * @since 2.5.6
	 * @param int   $term_id Term ID.
	 */
	public function save_category_form_fields( $term_id ) {	
		// Check if "aiovg_category_fields_nonce" nonce is set
    	if ( isset( $_POST['aiovg_category_fields_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['aiovg_category_fields_nonce'], 'aiovg_save_category_fields' ) ) {			
				// OK to save meta data
				$exclude_video_form = isset( $_POST['exclude_video_form'] ) ? 1 : 0;
				update_term_meta( $term_id, 'exclude_video_form', $exclude_video_form );
			}		
		}   
	}
	
	/**
	 * Notify user when his video submission is approved/published.
	 *
	 * @since 1.6.1
	 * @param string  $new_status Transition to this post status.
	 * @param string  $old_status Previous post status.
	 * @param WP_Post $post       Post data.
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {	
		if ( 'aiovg_videos' !== $post->post_type ) {
			return;
		}
		
		// Check if we are transitioning from pending to publish
    	if ( 'pending' == $old_status && 'publish' == $new_status ) {					
			$is_auto_imported = get_post_meta( $post->ID, 'import_id', true );
			
			if ( empty( $is_auto_imported ) ) {
				aiovg_premium_notify_user_video_published( $post->ID );	
			}			
		}		
	}

}

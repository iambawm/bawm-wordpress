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
 * AIOVG_Premium_Admin_Ads class.
 *
 * @since 1.5.7
 */
class AIOVG_Premium_Admin_Ads {

	/**
     * Insert ads settings tab.
     *
	 * @since  1.5.7
	 * @param  array $tabs Core settings tabs array.
     * @return array $tabs Updated settings tabs array.
     */
    public function custom_settings_tab( $tabs ) {	
		$new_tab = array(
			'ads' => __( 'Monetize', 'all-in-one-video-gallery' )
		);
		
		return aiovg_insert_array_after( 'user', $tabs, $new_tab );	
	}

	/**
     * Insert ads settings section.
     *
	 * @since  1.5.7
	 * @param  array $sections Core settings sections array.
     * @return array $sections Updated settings sections array.
     */
    public function custom_settings_section( $sections ) {	
		$sections[] = array(
			'id'          => 'aiovg_ads_settings',
			'title'       => __( 'Video Ads', 'all-in-one-video-gallery' ),
			'description' => __( 'Monetize your videos using DoubleClick for Publishers (DFP), the Google AdSense network, or any VAST-compliant ad server.', 'all-in-one-video-gallery' ),
			'tab'         => 'ads',
			'page'        => 'aiovg_ads_settings'
		);
		
		return $sections;	
	}

	/**
     * Insert ads settings fields.
     *
	 * @since  1.5.7
	 * @param  array $fields Core settings fields array.
     * @return array $fields Updated settings fields array.
     */
    public function custom_settings_fields( $fields ) {	
		$fields['aiovg_ads_settings'] = array(
			array(
				'name'              => 'enable_ads',
				'label'             => __( 'Enable Ads', 'all-in-one-video-gallery' ),
				'description'       => __( 'Check this option to enable advertisements on your videos', 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'vast_url',
				'label'             => __( 'VAST URL', 'all-in-one-video-gallery' ),
				'description'       => sprintf( __( 'Click here for a list of <a href="%s" target="_blank">Ad Tag Variables</a> (Macros) supported by the plugin.', 'all-in-one-video-gallery' ), 'https://plugins360.com/all-in-one-video-gallery/ad-tag-variables-macros' ),
				'type'              => 'text',
				'sanitize_callback' => 'aiovg_sanitize_url'
				),
			array(
				'name'              => 'vpaid_mode',
				'label'             => __( 'VPAID Mode', 'all-in-one-video-gallery' ),
				'description'       => '',
				'type'              => 'radio',
				'options'           => array(
					'enabled'  => __( 'Enabled', 'all-in-one-video-gallery' ),
					'insecure' => __( 'Insecure', 'all-in-one-video-gallery' ),
					'disabled' => __( 'Disabled', 'all-in-one-video-gallery' )
				),
				'sanitize_callback' => 'sanitize_key'
			),
			array(
				'name'              => 'use_gpt',
				'label'             => __( 'Google Publisher Tag (GPT)', 'all-in-one-video-gallery' ),
				'description'       => sprintf( __( 'Optional. Check this option to display companion ads using <a href="%s" target="_blank">Google Publisher Tag</a>', 'all-in-one-video-gallery' ), 'https://developers.google.com/doubleclick-gpt/' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			)
		);
		
		return $fields;	
	}

	/**
	 * Register meta boxes.
	 *
	 * @since 1.5.7
	 */
	public function add_meta_boxes() {
		add_meta_box( 
			'aiovg-video-ads', 
			__( 'Video Ads', 'all-in-one-video-gallery' ), 
			array( $this, 'display_meta_box_ads' ), 
			'aiovg_videos', 
			'side', 
			'low' 
		);		
	}

	/**
	 * Display "Video Ads" meta box.
	 *
	 * @since 1.5.7
	 * @param WP_Post $post WordPress Post object.
	 */
	public function display_meta_box_ads( $post ) {		
		$disable_ads = get_post_meta( $post->ID, 'disable_ads', true );
		$override_vast_url = get_post_meta( $post->ID, 'override_vast_url', true );	
		$vast_url = get_post_meta( $post->ID, 'vast_url', true );

		require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/ads.php';
	}

	/**
	 * Save meta data.
	 *
	 * @since  1.5.7
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post    The post object.
	 * @return int     $post_id If the save was successful or not.
	 */
	public function save_meta_data( $post_id, $post ) {	
		if ( ! isset( $_POST['post_type'] ) ) {
        	return $post_id;
    	}
	
		// Check this is the "aiovg_videos" custom post type
    	if ( 'aiovg_videos' != $post->post_type ) {
        	return $post_id;
    	}
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return $post_id;
		}
		
		// Check the logged in user has permission to edit this post
    	if ( ! aiovg_current_user_can( 'edit_aiovg_video', $post_id ) ) {
        	return $post_id;
    	}
		
		// Check if "aiovg_video_ads_nonce" nonce is set
    	if ( isset( $_POST['aiovg_video_ads_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['aiovg_video_ads_nonce'], 'aiovg_video_save_ads' ) ) {			
				// OK to save meta data.
				$disable_ads = isset( $_POST['disable_ads'] ) ? 1 : 0;
				update_post_meta( $post_id, 'disable_ads', $disable_ads );
				
				$override_vast_url = isset( $_POST['override_vast_url'] ) ? 1 : 0;
				update_post_meta( $post_id, 'override_vast_url', $override_vast_url );
				
				$vast_url = isset( $_POST['vast_url'] ) ? aiovg_sanitize_url( $_POST['vast_url'] ) : '';
    			update_post_meta( $post_id, 'vast_url', $vast_url );
			}			
		}
		
		return $post_id;	
	}

}

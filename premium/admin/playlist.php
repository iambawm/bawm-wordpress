<?php

/**
 * Playlist.
 *
 * @link       https://plugins360.com
 * @since      3.3.1
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Admin_Playlist class.
 *
 * @since 3.3.1
 */
class AIOVG_Premium_Admin_Playlist {	

	/**
	 * Add "Playlist" settings fields.
	 *
	 * @since  3.3.1
	 * @param  array $fields Core settings fields array.
	 * @return array $fields Updated fields array.
	 */
	public function add_settings_fields( $fields ) {
		$fields['aiovg_videos_settings'][] = array(
			'name'              => 'playlist_position',
			'label'             => __( 'Playlist Position', 'all-in-one-video-gallery' ),
			'description'       => '',
			'type'              => 'select',
			'options'           => array(
				'right'  => __( 'Right', 'all-in-one-video-gallery' ),
				'bottom' => __( 'Bottom', 'all-in-one-video-gallery' )
			),
			'sanitize_callback' => 'sanitize_key'
		);

		$fields['aiovg_videos_settings'][] = array(
			'name'              => 'playlist_color',
			'label'             => __( 'Playlist Color', 'all-in-one-video-gallery' ),
			'description'       => '',
			'type'              => 'select',
			'options'           => array(
				'light' => __( 'Light', 'all-in-one-video-gallery' ),
				'dark'  => __( 'Dark', 'all-in-one-video-gallery' )
			),
			'sanitize_callback' => 'sanitize_key'
		);

		$fields['aiovg_videos_settings'][] = array(
			'name'              => 'playlist_width',
			'label'             => __( 'Playlist Width (in pixels)', 'all-in-one-video-gallery' ),
			'description'       => '',
			'type'              => 'number',
			'sanitize_callback' => 'intval'
		);

		$fields['aiovg_videos_settings'][] = array(
			'name'              => 'playlist_height',
			'label'             => __( 'Playlist Height (in pixels)', 'all-in-one-video-gallery' ),
			'description'       => '',
			'type'              => 'number',
			'sanitize_callback' => 'intval'
		);		

		$fields['aiovg_videos_settings'][] = array(
			'name'              => 'autoadvance',
			'label'             => __( 'Autoplay Next Video', 'all-in-one-video-gallery' ),
			'description'       => __( 'Check this to play next video in the list automatically', 'all-in-one-video-gallery' ),
			'type'              => 'checkbox',
			'sanitize_callback' => 'intval'
		);

		return $fields;	
	}	

}

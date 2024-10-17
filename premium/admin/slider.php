<?php

/**
 * Slider.
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
 * AIOVG_Premium_Admin_Slider class.
 *
 * @since 1.5.7
 */
class AIOVG_Premium_Admin_Slider {	

	/**
	 * Add "Slider" settings fields.
	 *
	 * @since  1.5.7
	 * @param  array $fields Core settings fields array.
	 * @return array $fields Updated fields array.
	 */
	public function add_settings_fields( $fields ) {
		$videos = array();

		foreach ( $fields['aiovg_videos_settings'] as $key => $field ) {
			$videos[] = $field;

			if ( 'template' == $field['name'] ) {
				$videos[] = array(
					'name'              => 'slider_layout',
					'label'             => __( 'Slider Layout', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'select',
					'options'           => array(						
						'thumbnails' => __( 'Classic (links to the single video page)', 'all-in-one-video-gallery' ),
						'popup'      => __( 'Popup (opens videos in a popup)', 'all-in-one-video-gallery' ),
						'both'       => __( 'Compact (player embedded at the top of the slider)', 'all-in-one-video-gallery' ),
						'player'     => __( 'Inline (players added as slides)', 'all-in-one-video-gallery' )
					),
					'sanitize_callback' => 'sanitize_key'
				);
			}

			if ( 'thumbnail_style' == $field['name'] ) {
				$videos[] = array(
					'name'              => 'display_player',
					'label'             => __( 'Show / Hide (Player)', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'title'           => __( 'Video Title', 'all-in-one-video-gallery' ),
						'description'     => __( 'Video Description', 'all-in-one-video-gallery' ),
						'like_button'     => __( 'Like / Dislike Button', 'all-in-one-video-gallery' ),
						'playlist_button' => __( 'Playlist Button', 'all-in-one-video-gallery' )
					),
					'sanitize_callback' => 'aiovg_sanitize_array'
				);				
			}

			if ( 'title_length' == $field['name'] ) {
				$videos[] = array(
					'name'              => 'link_title',
					'label'             => __( 'Link Video Titles', 'all-in-one-video-gallery' ),
					'description'       => __( 'Check this to link video titles to the single video page', 'all-in-one-video-gallery' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				);
			}
		}

		$fields['aiovg_videos_settings'] = array_merge(
			$videos,
			array(				
				array(
					'name'              => 'arrows',
					'label'             => __( 'Arrows', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
					'name'              => 'arrow_size',
					'label'             => __( 'Arrow Size (in pixels)', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'number',
					'min'               => 0,
					'max'               => 250,
					'step'              => 1,
					'sanitize_callback' => 'intval'
				),				
				array(
					'name'              => 'arrow_radius',
					'label'             => __( 'Arrow Radius (in pixels)', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'number',
					'min'               => 0,
					'max'               => 250,
					'step'              => 1,
					'sanitize_callback' => 'intval'
				),
				array(
					'name'              => 'arrow_top_offset',
					'label'             => __( 'Arrow Top Offset (in percentage)', 'all-in-one-video-gallery' ),
					'description'       => __( 'Enter "0" to show arrows at the top-right corner above the slider.', 'all-in-one-video-gallery' ),
					'type'              => 'number',
					'min'               => 0,
					'max'               => 100,
					'step'              => 1,
					'sanitize_callback' => 'intval'
				),
				array(
					'name'              => 'arrow_left_offset',
					'label'             => __( 'Arrow Left Offset (in pixels)', 'all-in-one-video-gallery' ),
					'description'       => __( 'This field has no effect when the "Arrow Top Offset" value is "0".', 'all-in-one-video-gallery' ),
					'type'              => 'number',
					'min'               => -500,
					'max'               => 500,
					'step'              => 1,
					'sanitize_callback' => 'intval'
				),
				array(
					'name'              => 'arrow_right_offset',
					'label'             => __( 'Arrow Right Offset (in pixels)', 'all-in-one-video-gallery' ),
					'description'       => __( 'This field has no effect when the "Arrow Top Offset" value is "0".', 'all-in-one-video-gallery' ),
					'type'              => 'number',
					'min'               => -500,
					'max'               => 500,
					'step'              => 1,
					'sanitize_callback' => 'intval'
				),
				array(
					'name'              => 'arrow_bg_color',
					'label'             => __( 'Arrow BG Color', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'color',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
					'name'              => 'arrow_icon_color',
					'label'             => __( 'Arrow Icon Color', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'color',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
					'name'              => 'dots',
					'label'             => __( 'Dots', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
					'name'              => 'dot_size',
					'label'             => __( 'Dot Size (in pixels)', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'number',
					'min'               => 0,
					'max'               => 250,
					'step'              => 1,
					'sanitize_callback' => 'intval'
				),
				array(
					'name'              => 'dot_color',
					'label'             => __( 'Dot Color', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'color',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
					'name'              => 'slider_autoplay',
					'label'             => __( 'Slider Autoplay', 'all-in-one-video-gallery' ),
					'description'       => __( 'Check this to auto-rotate the slider', 'all-in-one-video-gallery' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
					'name'              => 'autoplay_speed',
					'label'             => __( 'Autoplay Speed (in milliseconds)', 'all-in-one-video-gallery' ),
					'description'       => '',
					'type'              => 'number',
					'min'               => 0,
					'max'               => 300000,
					'step'              => 100,
					'sanitize_callback' => 'intval'
				)
			)
		);

		return $fields;	
	}	

}

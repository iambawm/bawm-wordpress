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
 * AIOVG_Premium_Public_Playlist class.
 *
 * @since 3.3.1
 */
class AIOVG_Premium_Public_Playlist {

	/**
	 * Register "Playlist" template.
	 *
	 * @since  3.3.1
	 * @param  array $templates Core templates array.
	 * @return array $templates Updated templates array.
	 */
	public function register_playlist_template( $templates ) {	
		$templates['playlist'] = __( 'Playlist', 'all-in-one-video-gallery' );
		return $templates;		
	}
	
	/**
	 * Register "Playlist" block fields.
	 *
	 * @since  3.3.1
	 * @param  array $fields Core fields array.
	 * @return array $fields Updated fields array.
	 */
	public function register_shortcode_fields( $fields ) {
		$videos_settings = get_option( 'aiovg_videos_settings' );

		if ( ! isset( $videos_settings['playlist_position'] ) ) {
			return $fields;
		}

		$fields['videos']['sections']['gallery']['fields'][] = array(
			'name'        => 'playlist_position',
			'label'       => __( 'Playlist Position', 'all-in-one-video-gallery' ),
			'description' => '',
			'type'        => 'select',
			'options'     => array(
				'right'  => __( 'Right', 'all-in-one-video-gallery' ),
				'bottom' => __( 'Bottom', 'all-in-one-video-gallery' )
			),
			'value'       => $videos_settings['playlist_position']
		);

		$fields['videos']['sections']['gallery']['fields'][] = array(
			'name'        => 'playlist_color',
			'label'       => __( 'Playlist Color', 'all-in-one-video-gallery' ),
			'description' => '',
			'type'        => 'select',
			'options'     => array(
				'light' => __( 'Light', 'all-in-one-video-gallery' ),
				'dark'  => __( 'Dark', 'all-in-one-video-gallery' )
			),
			'value'       => $videos_settings['playlist_color']
		);

		$fields['videos']['sections']['gallery']['fields'][] = array(
			'name'        => 'playlist_width',
			'label'       => __( 'Playlist Width (in pixels)', 'all-in-one-video-gallery' ),
			'description' => '',
			'type'        => 'number',
			'min'         => 0,
			'max'         => 500,
			'step'        => 1,
			'value'       => $videos_settings['playlist_width']
		);

		$fields['videos']['sections']['gallery']['fields'][] = array(
			'name'        => 'playlist_height',
			'label'       => __( 'Playlist Height (in pixels)', 'all-in-one-video-gallery' ),
			'description' => '',
			'type'        => 'number',
			'min'         => 0,
			'max'         => 500,
			'step'        => 1,
			'value'       => $videos_settings['playlist_height']
		);

		$fields['videos']['sections']['gallery']['fields'][] = array(
			'name'        => 'autoadvance',
			'label'       => __( 'Autoplay Next Video', 'all-in-one-video-gallery' ),
			'description' => __( 'Check this to play next video in the list automatically', 'all-in-one-video-gallery' ),
			'type'        => 'checkbox',
			'value'       => $videos_settings['autoadvance']
		);		
		
		return $fields;		
	}
	
	/**
	 * Get filtered php template file path.
	 *
	 * @since  3.3.1
	 * @param  array  $template   PHP file path.
	 * @param  array  $attributes An associative array of attributes.
	 * @return string             Filtered file path.
	 */
	public function load_template( $template, $attributes = array() ) {
		if ( 'videos-template-classic.php' == basename( $template ) ) {
			if ( 'playlist' == $attributes['template'] ) {
				// Enqueue dependencies
				wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );				
				wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-template-playlist' );
			
				$template = AIOVG_PLUGIN_DIR . 'premium/public/templates/videos-template-playlist.php';
			}
		}		

		return $template;
	}

}

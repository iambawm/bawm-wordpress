<?php

/**
 * Compact Template.
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
 * AIOVG_Premium_Public_Compact class.
 *
 * @since 3.3.1
 */
class AIOVG_Premium_Public_Compact {

	/**
	 * Register "Compact" template.
	 *
	 * @since  3.3.1
	 * @param  array $templates Core templates array.
	 * @return array $templates Updated templates array.
	 */
	public function register_compact_template( $templates ) {	
		$templates['compact'] = __( 'Compact', 'all-in-one-video-gallery' );
		return $templates;		
	}

	/**
	 * Register "Compact" block fields.
	 *
	 * @since  3.3.1
	 * @param  array $fields Core fields array.
	 * @return array $fields Updated fields array.
	 */
	public function register_shortcode_fields( $fields ) {
		$videos_settings = get_option( 'aiovg_videos_settings' );

		if ( 'compact' == $videos_settings['template'] ) {
			foreach ( $fields['videos']['sections']['gallery']['fields'] as $index => $field ) {
				if ( 'show_more' == $field['name'] ) {
					$fields['videos']['sections']['gallery']['fields'][ $index ]['value'] = 1;
				}
			}
		}

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
			if ( 'compact' == $attributes['template'] ) {
				$likes_settings = get_option( 'aiovg_likes_settings' );	
				$playlists_settings = get_option( 'aiovg_playlists_settings' );	

				$show_like_button = 0;
				if ( ! empty( $likes_settings['like_button'] ) || ! empty( $likes_settings['dislike_button'] ) ) {
					if ( isset( $attributes['show_player_like_button'] ) ) {
						$show_like_button = (int) $attributes['show_player_like_button'];
					}
				}

				$attributes['show_player_like_button'] = $show_like_button;

				$show_playlist_button = 0;
				if ( ! empty( $playlists_settings['enabled'] ) ) {
					if ( isset( $attributes['show_player_playlist_button'] ) ) {
						$show_playlist_button = (int) $attributes['show_player_playlist_button'];	
					}
				}

				$attributes['show_player_playlist_button'] = $show_playlist_button;

				// Enqueue style dependencies
				wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );	

				// Enqueue script dependencies
				if ( $show_like_button ) {
					wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-likes' );
				}

				if ( $show_playlist_button ) {
					wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-playlists' );
				}
				
				wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-template-compact' );
			
				$template = AIOVG_PLUGIN_DIR . 'premium/public/templates/videos-template-compact.php';
			}
		}		

		return $template;
	}	

}

<?php

/**
 * Popup.
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
 * AIOVG_Premium_Public_Popup class.
 *
 * @since 1.5.7
 */
class AIOVG_Premium_Public_Popup {

	/**
	 * Register "Popup" template.
	 *
	 * @since  1.5.7
	 * @param  array $templates Core templates array.
	 * @return array $templates Updated templates array.
	 */
	public function register_popup_template( $templates ) {	
		$templates['popup'] = __( 'Popup', 'all-in-one-video-gallery' );
		return $templates;		
	}
	
	/**
	 * Get filtered php template file path.
	 *
	 * @since  1.5.7
	 * @param  array  $template   PHP file path.
	 * @param  array  $attributes An associative array of attributes.
	 * @return string             Filtered file path.
	 */
	public function load_template( $template, $attributes = array() ) {
		if ( 'videos-template-classic.php' == basename( $template ) ) {
			if ( 'popup' == $attributes['template'] ) {
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
				wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-magnific-popup' );
				wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );
	
				// Enqueue script dependencies
				wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-magnific-popup' );				

				if ( $show_like_button ) {
					wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-likes' );
				}

				if ( $show_playlist_button ) {
					wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-playlists' );
				}
				
				wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-template-popup' );
			
				$template = AIOVG_PLUGIN_DIR . 'premium/public/templates/videos-template-popup.php';
			}
		}		

		return $template;
	}	

	/**
	 * Filter the single video shortcode output for the popup.
	 *
	 * @since  3.0.0
	 * @param  array  $attributes An associative array of shortcode attributes.
	 * @param  string $content    Enclosing content.
	 * @return array  $attributes Filtered array of shortcode attributes.
	 */
	public function shortcode_atts_aiovg_video( $attributes = array(), $content = null ) {
		if ( isset( $attributes['template'] ) && 'popup' == $attributes['template'] ) {
			$attributes['player'] = 'popup';
		}

		if ( ! empty( $content ) ) {
			$attributes['player']  = 'popup';
			$attributes['content'] = $content;
		}

		unset( $attributes['template'] );

		return $attributes;
	}

}

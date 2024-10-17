<?php

/**
 * Multilingual Compatibility.
 *
 * @link    https://plugins360.com
 * @since   3.0.0
 *
 * @package All_In_One_Video_Gallery
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Public_Multilingual class.
 *
 * @since 3.0.0
 */
class AIOVG_Premium_Public_Multilingual {

	/**
	 * [WPML] Set language for the imported video.
	 *
	 * @since  3.0.0
	 * @param  int $video_id  Video ID.
	 * @return int $import_id Import ID.
	 */
	public function set_video_language_for_wpml( $video_id, $import_id ) {
		$get_language_args = array(
			'element_id'   => $import_id, 
			'element_type' => 'aiovg_automations' 
		);	
			
        $original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
		
		if ( $original_post_language_info && ! empty( $original_post_language_info->language_code ) ) {
			$wpml_element_type = apply_filters( 'wpml_element_type', 'aiovg_videos' );

			$set_language_args = array(
				'element_id'           => $video_id,
				'element_type'         => $wpml_element_type,
				'trid'                 => FALSE,
				'language_code'        => $original_post_language_info->language_code,
				'source_language_code' => null
			);
	 
			do_action( 'wpml_set_element_language_details', $set_language_args );	
		}
	}	

	/**
	 * [Polylang] Set language for the imported video.
	 *
	 * @since  3.0.0
	 * @param  int $video_id  Video ID.
	 * @return int $import_id Import ID.
	 */
	public function set_video_language_for_polylang( $video_id, $import_id ) {
		if ( ! function_exists( 'pll_is_translated_post_type' ) ) {
			return false;
		}

		$is_translated = pll_is_translated_post_type( 'aiovg_automations' );
		
		if ( $is_translated ) {
			$language = pll_get_post_language( $import_id );
			pll_set_post_language( $video_id, $language );
		}
	}	
	
}
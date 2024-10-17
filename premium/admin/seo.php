<?php

/**
 * SEO.
 *
 * @link       https://plugins360.com
 * @since      2.4.0
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Admin_SEO class.
 *
 * @since 2.4.0
 */
class AIOVG_Premium_Admin_SEO {

	/**
     * Insert SEO settings section.
     *
	 * @since  2.4.0
	 * @param  array $sections Core settings sections array.
     * @return array           Updated settings sections array.
     */
    public function custom_settings_section( $sections ) {
		$new_sections = array();

		foreach ( $sections as $section ) {
			$new_sections[] = $section;

			if ( 'aiovg_related_videos_settings' == $section['id'] ) {
				$new_sections[] = array(
					'id'          => 'aiovg_seo_settings',
					'title'       => __( 'SEO Settings', 'all-in-one-video-gallery' ),
					'description' => '',
					'tab'         => 'seo',
					'page'        => 'aiovg_seo_settings'
				);
			}
		}		
		
		return $new_sections;	
	}

	/**
	 * Insert SEO settings fields.
	 *
	 * @since  2.4.0
	 * @param  array $fields Core settings fields array.
	 * @return array $fields Updated fields array.
	 */
	public function custom_settings_fields( $fields ) {
		$fields['aiovg_seo_settings'] = array(
			array(
				'name'              => 'schema_markup',
				'label'             => __( 'Schema Markup', 'all-in-one-video-gallery' ),
				'description'       => __( 'Check this option to enable Schema.org Markup (via JSON-LD) for the videos', 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			)
		);

		return $fields;
	}	

}

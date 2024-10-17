<?php

/**
 * Showcase Resource.
 *
 * @link       https://plugins360.com
 * @since      2.6.2
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Vimeo_API_Showcase_Resource class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Showcase_Resource extends AIOVG_Premium_Vimeo_API_Album_Resource {

	/**
	 * @param string $resource_id
	 * @param string $user_id
	 * @param array  $params
	 */
	public function __construct( $resource_id, $user_id = false, $params = array() ) {
		parent::__construct( $resource_id, $user_id, $params );
		parent::set_name( 'showcase', __( 'Showcase', 'all-in-one-video-gallery' ) );
	}

	/**
	 * Disable for importers.
	 *
	 * Keep implementation only for back-end.
	 *
	 * @return false
	 */
	public function enabled_for_importers() {
		return false;
	}

	/**
	 * Disable for automatic imports.
	 *
	 * Keep implementation only for back-end.
	 *
	 * @return false
	 */
	public function has_automatic_import() {
		return false;
	}
	
}
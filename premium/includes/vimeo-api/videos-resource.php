<?php

/**
 * Videos Resource.
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
 * AIOVG_Premium_Vimeo_API_Videos_Resource class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Videos_Resource extends AIOVG_Premium_Vimeo_API_Resource_Abstract implements AIOVG_Premium_Vimeo_API_Resource_Interface {

	/**
	 * AIOVG_Premium_Vimeo_API_Videos_Resource constructor.
	 *
	 * @param string $resource_id
	 */
	public function __construct( $resource_id ) {
		parent::__construct( $resource_id, false, false );

		parent::set_name( 'videos', __( 'Videos', 'all-in-one-video-gallery' ) );
	}

	/**
	 * @return bool
	 */
	public function is_single_entry() {
		return false;
	}

	/**
	 * No automatic import
	 *
	 * @return bool
	 */
	public function has_automatic_import() {
		return false;
	}

	/**
	 * Return resource relative API endpoint
	 *
	 * @return string
	 */
	public function get_api_endpoint() {
		return sprintf( 'videos?links=%s', $this->resource_id );
	}

	/**
	 * Searching within the returned results isn't allowed by API
	 *
	 * @return bool
	 */
	public function can_search_results() {
		return false;
	}
	
}
<?php

/**
 * Channel Resource.
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
 * AIOVG_Premium_Vimeo_API_Channel_Resource class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Channel_Resource extends AIOVG_Premium_Vimeo_API_Resource_Abstract implements AIOVG_Premium_Vimeo_API_Resource_Interface {

	/**
	 * AIOVG_Premium_Vimeo_API_Channel_Resource constructor.
	 *
	 * @param string $resource_id
	 * @param array  $params
	 */
	public function __construct( $resource_id, $params = array() ) {
		parent::__construct( $resource_id, false, $params );

		parent::set_default_params(
			array(
				'direction' => 'desc',
				'filter' => '',
				'filter_embeddable' => false,
				'page' => 1,
				'per_page' => 20,
				'query' => '',
				'sort' => 'added'
			)
		);

		parent::set_sort_options(
			array(
				'added',
				'alphabetical',
				'comments',
				'date',
				'default',
				'duration',
				'likes',
				'manual',
				'modified_time',
				'plays'
			)
		);

		parent::set_filtering_options( array( 'embeddable' ) );

		parent::set_name( 'channel', __( 'Channel', 'all-in-one-video-gallery' ) );
	}

	/**
	 * @param string $resource_id
	 */
	public function set_resource_id( $resource_id ) {
		parent::set_resource_id( $resource_id );
	}

	/**
	 * Can import newly added videos after importing the entire feed
	 *
	 * @return bool
	 */
	public function can_import_new_videos() {
		return true;
	}

	/**
	 * Allows import limiting by date
	 *
	 * @return bool
	 */
	public function has_date_limit() {
		return true;
	}

	/**
	 * @return string
	 */
	public function get_api_endpoint() {
		return sprintf( 'channels/%s/videos', $this->resource_id );
	}
	
}
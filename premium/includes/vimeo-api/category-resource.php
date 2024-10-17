<?php

/**
 * Category Resource.
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
 * AIOVG_Premium_Vimeo_API_Category_Resource class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Category_Resource extends AIOVG_Premium_Vimeo_API_Resource_Abstract implements AIOVG_Premium_Vimeo_API_Resource_Interface {

	/**
	 * AIOVG_Premium_Vimeo_API_Category_Resource constructor.
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
				'sort' => 'date'
			)
		);

		parent::set_sort_options(
			array(
				'alphabetical',
				'comments',
				'date',
				'duration',
				'featured',
				'likes',
				'plays',
				'relevant'
			)
		);

		parent::set_filtering_options(
			array(
				'conditional_featured',
				'embeddable'
			)
		);

		parent::set_name( 'category', __( 'Category', 'all-in-one-video-gallery' ) );
	}

	/**
	 * Feed can use date limit
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
		return sprintf( 'categories/%s/videos', $this->resource_id );
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
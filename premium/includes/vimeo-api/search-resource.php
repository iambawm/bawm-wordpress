<?php

/**
 * Search Resource.
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
 * AIOVG_Premium_Vimeo_API_Search_Resource class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Search_Resource extends AIOVG_Premium_Vimeo_API_Resource_Abstract implements AIOVG_Premium_Vimeo_API_Resource_Interface {

	/**
	 * AIOVG_Premium_Vimeo_API_Search_Resource constructor.
	 *
	 * @param string $resource_id
	 * @param array  $params
	 */
	public function __construct( $resource_id, $params = array() ) {
		// Search uses field "query" instead of resource id; set it
		$params['query'] = $resource_id;

		parent::__construct( false, false, $params );

		parent::set_default_params(
			array(
				'direction' => 'desc',
				'filter' => '',
				'links' => '',
				'page' => 1,
				'per_page' => 20,
				'query' => '',
				'sort' => 'date',
				'uris' => ''
			)
		);

		parent::set_sort_options(
			array(
				'alphabetical',
				'comments',
				'date',
				'duration',
				'likes',
				'plays',
				'relevant'
			)
		);

		parent::set_filtering_options(
			array(
				'CC',
				'CC-BY',
				'CC-BY-NC',
				'CC-BY-NC-ND',
				'CC-BY-NC-SA',
				'CC-BY-ND',
				'CC-BY-SA',
				'CC0',
				'categories',
				'duration',
				'in-progress',
				'minimum_likes',
				'trending',
				'upload_date'
			)
		);

		parent::set_name( 'search', __( 'Search', 'all-in-one-video-gallery' ) );
	}

	/**
	 * @param string $resource_id
	 */
	public function set_resource_id( $resource_id ) {
		$this->params['query'] = $resource_id;
		parent::set_resource_id( $resource_id );
	}

	/**
	 * @param array $params
	 */
	public function set_params( $params ) {
		if( isset( $this->params['query'] ) ){
			$params['query'] = $this->params['query'];
		}

		parent::set_params( $params );
	}

	/**
	 * Does not have automatic import
	 *
	 * @return boolean
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
		return 'videos';
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
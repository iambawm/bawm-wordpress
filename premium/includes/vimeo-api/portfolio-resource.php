<?php

/**
 * Portfolio Resource.
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
 * AIOVG_Premium_Vimeo_API_Portfolio_Resource class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Portfolio_Resource extends AIOVG_Premium_Vimeo_API_Resource_Abstract implements AIOVG_Premium_Vimeo_API_Resource_Interface {

	/**
	 * AIOVG_Premium_Vimeo_API_Portfolio_Resource constructor.
	 *
	 * @param string $resource_id
	 * @param string $user_id
	 * @param array  $params
	 */
	public function __construct( $resource_id, $user_id = '', $params = array() ) {
		parent::__construct( $resource_id, $user_id, $params );

		parent::set_default_params(
			array(
				'filter' => '',
				'filter_embeddable' => false,
				'page' => 1,
				'per_page' => 20,
				'sort' => 'date',
				'direction' => 'desc'
			)
		);

		parent::set_sort_options(
			array(
				'alphabetical',
				'comments',
				'date',
				'default', // The default sort set on the portfolio
				'likes',
				'manual',
				'plays'
			)
		);

		parent::set_filtering_options( array( 'embeddable' ) );

		parent::set_name( 'portfolio', __( 'Portfolio', 'all-in-one-video-gallery' ) );
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
	 * Return resource relative API endpoint
	 *
	 * @return string
	 */
	public function get_api_endpoint() {
		return sprintf(
			'users/%s/portfolios/%s/videos',
			$this->user_id,
			$this->resource_id
		);
	}

	/**
	 * @see AIOVG_Premium_Vimeo_API_Resource_Interface::requires_user_id()
	 *
	 * @return bool
	 */
	public function requires_user_id() {
		return true;
	}
	
}
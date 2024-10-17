<?php

/**
 * User Resource.
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
 * AIOVG_Premium_Vimeo_API_User_Resource class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_User_Resource extends AIOVG_Premium_Vimeo_API_Resource_Abstract implements AIOVG_Premium_Vimeo_API_Resource_Interface {

	/**
	 * AIOVG_Premium_Vimeo_API_User_Resource constructor.
	 *
	 * @param string $user_id
	 * @param array  $params
	 */
	public function __construct( $user_id = false, $params = array() ) {
		parent::__construct( false, $user_id, $params );

		parent::set_default_params(
			array(
				'direction' => 'desc',
				'filter' => '',
				'filter_embeddable' => false,
				'filter_playable' => false,
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
				'default',
				'duration',
				'last_user_action_event_date',
				'likes',
				'modified_time',
				'plays'
			)
		);

		parent::set_filtering_options(
			array(
				'app_only',
				'embeddable',
				'featured',
				'playable',
			)
		);

		parent::set_name( 'user', __( 'User Uploads', 'all-in-one-video-gallery' ) );
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
		return sprintf( 'users/%s/videos', $this->resource_id );
	}

}
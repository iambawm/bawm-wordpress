<?php

/**
 * Album Resource.
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
 * AIOVG_Premium_Vimeo_API_Album_Resource class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Album_Resource extends AIOVG_Premium_Vimeo_API_Resource_Abstract implements AIOVG_Premium_Vimeo_API_Resource_Interface {

	/**
	 * AIOVG_Premium_Vimeo_API_Album_Resource constructor.
	 *
	 * @param string $resource_id
	 * @param bool   $user_id
	 * @param array  $params
	 */
	public function __construct( $resource_id, $user_id = false, $params = array() ) {
		// Built without direction
		$default_params = array(
			'filter' => '',
			'filter_embeddable' => false,
			'page' => 1,
			'password' => '',
			'per_page' => 20,
			'query' => '',
			'sort' => 'date' // sorts by the date the video was added to album
		);

		// when sort is default, direction must be eliminated
		if ( isset( $params['sort'] ) && 'default' == $params['sort'] ) {
			unset( $params['direction'] );
		} else {
			$default_params['direction'] = 'desc';
		}

		parent::__construct( $resource_id, $user_id, $params );

		parent::set_default_params( $default_params );

		parent::set_sort_options(
			array(
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

		parent::set_name( 'album', __( 'Showcase / Album', 'all-in-one-video-gallery' ) );

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
		return sprintf(
			'users/%s/albums/%s/videos',
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
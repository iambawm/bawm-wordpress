<?php

/**
 * Vimeo API.
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
 * AIOVG_Premium_Vimeo_API class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API {

	/**
	 * The results array containing all videos
	 *
	 * @var array
	 */
	private $results;

	/**
	 * Total number of entries returned by API query
	 *
	 * @var int
	 */
	private $total_items;

	/**
	 * Current page in API query
	 *
	 * @var int
	 */
	private $page;

	/**
	 * Reached the end of the feed
	 *
	 * @var bool
	 */
	private $end = false;

	/**
	 * Errors
	 *
	 * @var array|string|WP_Error
	 */
	private $errors;

	/**
	 * @var AIOVG_Premium_Vimeo_Api_Query
	 */
	private $api;

	/**
	 * AIOVG_Premium_Vimeo_API constructor.
	 *
	 * @param string        $resource_type  The type of resource being queried (album, channel, search, etc.).
	 * @param string|array  $resource_id    The resource ID that should be retrieved from Vimeo API.
	 * @param bool|string   $user_id        The user ID (if required) that owns the resource or false in case the parameter is not needed.
	 * @param array $args   {
	 *      Additional request parameters.
	 *
	 *      @type   int     $page                   The page number to retrieve from Vimeo API.
	 *      @type   int     $per_page               Number of results per page.
	 *      @type   string  $query                  A search query to search within the set of results for further filtering.
	 *      @type   string  $filter                 Results filtering; has specific value based on the required feed type (ie. playable,
	 *                                              embeddable, featured, live, etc.).
	 *                                              See Vimeo API docs for the spcific resource imported to get the available
	 *                                              filtering options.
	 *      @type   bool    $filter_embeddable      Filter results by embeddable videos (true) or non-embeddable videos (false). Requires
	 *                                              parameter "filter" to be set to "embeddable".
	 *      @type   bool    $filter_playable        Whether to filter the results by playable videos (true) or non-playable videos (false).
	 *      @type   string  $links                  The page containing the video URI.
	 *      @type   string  $password               Password for password restricted resources (ie. showcases).
	 * }
	 */
	public function __construct( $resource_type, $resource_id, $user_id = false, $args = array() ) {
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/query.php';

		if ( 'videos' == $resource_type ) {
			$video_urls = str_replace( array( "\n", "\n\r", ' ' ), ',', $resource_id );
			$video_urls = explode( ',', $video_urls );
			$video_urls = array_filter( $video_urls );			

			if ( empty( $video_urls ) ) {
				return;
			}

			$total_videos = count( $video_urls );
			$total_pages  = ceil( $total_videos / $args['per_page'] );

			if ( $args['page'] > $total_pages ) {
				return;
			}

			$current_page = $args['page'];

			$offset = ( $current_page - 1 ) * $args['per_page'];
			if ( $offset < 0 ) {
				$offset = 0;
			}

			$video_urls = array_slice( $video_urls, $offset, $args['per_page'] );
			$resource_id = implode( ',', $video_urls );

			$this->api = new AIOVG_Premium_Vimeo_API_Query( $resource_type, $resource_id, $user_id, $args );
			$request = $this->api->request_feed();

			// Stop on error
			if ( is_wp_error( $request ) ) {
				$this->errors = $request;
				return;
			}

			$result = json_decode( $request['body'], true );

			$raw_entries = isset( $result['data'] ) ? $result['data'] : array();
			$entries = array();
			foreach ( $raw_entries as $entry ) {
				$_entry = $this->api->get_api_resource()->get_formatted_entry( $entry );

				if ( ! is_null( $_entry ) ) {
					$entries[] = $_entry;
				}
			}

			$this->results = $entries;
			$this->end = ( $current_page == $total_pages ) ? true : false;
			$this->total_items = $total_videos;
			$this->page = $current_page;
		} else {
			$this->api = new AIOVG_Premium_Vimeo_API_Query( $resource_type, $resource_id, $user_id, $args );
			$request = $this->api->request_feed();

			// Stop on error
			if ( is_wp_error( $request ) ) {
				$this->errors = $request;
				return;
			}
			
			$result = json_decode( $request['body'], true );
			
			// Single video entry
			if ( $this->api->get_api_resource()->is_single_entry() ) {
				$this->results = $this->api->get_api_resource()->get_formatted_entry( $result );
				return;
			}

			$raw_entries = isset( $result['data'] ) ? $result['data'] : array();
			$entries = array();
			foreach ( $raw_entries as $entry ) {
				$_entry = $this->api->get_api_resource()->get_formatted_entry( $entry );

				if ( ! is_null( $_entry ) ) {
					$entries[] = $_entry;
				}
			}		
			
			$this->results = $entries;
			$this->end = ( ! isset( $result['paging']['next'] ) || empty( $result['paging']['next'] ) );
			$this->total_items = isset( $result['total'] ) ? $result['total'] : 0;
			$this->page = isset( $result['page'] ) ? $result['page'] : 0;
		}
	}	

	/**
	 * @return array
	 */
	public function get_feed() {
		return $this->results;
	}

	/**
	 * @return int
	 */
	public function get_total_items() {
		return $this->total_items;
	}

	/**
	 * @return int
	 */
	public function get_page() {
		return $this->page;
	}

	/**
	 * @return bool
	 */
	public function has_ended() {
		return $this->end;
	}

	/**
	 * @return array|string|WP_Error
	 */
	public function get_errors() {
		return $this->errors;
	}

}
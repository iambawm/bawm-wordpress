<?php

/**
 * Vimeo API Query.
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
 * AIOVG_Premium_Vimeo_API_Query class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Query {

	/**
	 * Vimeo API endpoint
	 */
	const API_ENDPOINT = 'https://api.vimeo.com/';

	/**
	 * API version to be requested
	 * @see https://developer.vimeo.com/api/changelog
	 */
	const VERSION_STRING = 'application/vnd.vimeo.*+json; version=3.4';

	/**
	 * Store parameters.
	 * 
	 * @var array
	 */
	private $params;

	/**
	 * The type of resource that should be queried: album, channel, etc.
	 *
	 * @var string
	 */
	private $resource_type;

	/**
	 * The resource ID: album id, channel id, etc.
	 *
	 * @var bool|string
	 */
	private $resource_id;

	/**
	 * Vimeo user ID
	 *
	 * @var bool|string
	 */
	private $api_user_id;

	/**
	 * AIOVG_Premium_Vimeo_API_Query constructor.
	 *
	 * @param string        $resource_type  The type of resource that should be queried (ie. album, channel, etc).
	 * @param string|bool   $resource_id    The API resource ID (ie. channel ID, album ID, user ID, etc).
	 * @param string|bool   $api_user_id    The Vimeo user ID that should be used when making queries for albums or portfolios.
	 * @param array $args   {
	 *      Additional request parameters.
	 *
	 *      @type   int $page                   The page number to retrieve from Vimeo API.
	 *      @type   int $per_page               Number of results per page.
	 *      @type   string $query               The search query string or resource ID (showcase ID, folder ID, username, etc.).
	 *      @type   string $filter              Results filtering; has specific value based on the required feed type (ie. playable,
	 *                                          embeddable, featured, live, etc.).
	 *                                          See Vimeo API docs for the spcific resource imported to get the available
	 *                                          filtering options.
	 *      @type   bool $filter_embeddable     Filter results by embeddable videos (true) or non-embeddable videos (false). Requires
	 *                                          parameter "filter" to be set to "embeddable".
	 *      @type   bool $filter_playable       Whether to filter the results by playable videos (true) or non-playable videos (false).
	 *      @type   string $links               The page containing the video URI.
	 *      @type   string $password            Password for password restricted resources (ie. showcases).
	 * }
	 *
	 */
	public function __construct( $resource_type, $resource_id = false, $api_user_id = false, $args = array() ) {
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/resource-objects.php';

		$this->resource_type = $resource_type;
		$this->resource_id   = $resource_id;
		$this->api_user_id   = $api_user_id;

		/**
		 * Defaults must not include parameters "sort" and "direction". If not specified by
		 * the concrete implementation, the resource default will be used. This is useful when
		 * performing automatic imports which implement ordering by default and allows different
		 * order parameters to be used.
		 */
		$default = array(
			'page' => 1,
			'per_page' => 20,
			'query' => '',
			'filter' => '',
			'filter_embeddable' => false,
			'filter_playable' => false,
			'links' => '',
			'password' => ''
		);
		
		$this->params = wp_parse_args( $args, $default );		
	}
	
	/**
	 * Makes a request based on the params passed on constructor
	 */
	public function request_feed() {
		$endpoint = $this->get_endpoint();
		$api_resource = $this->get_api_resource();

		if ( is_wp_error( $endpoint ) ) {
			return $endpoint;
		}

		$request_args = array(
			'method' => $api_resource->get_request_method(),
			/**
			 * Vimeo API query request timeout filter.
			 *
			 * @param int $timeouot The request timeout.
			 */
			'timeout' => apply_filters( 'aiovg_vimeo_api_request_timeout', 30 ),
			'sslverify' => false,
			'headers' => array(
				'user-agent' => $this->request_user_agent(),
				'authorization' => 'bearer ' . $this->get_access_token(),
				'accept' => self::VERSION_STRING
			)
		);

		if ( in_array( $api_resource->get_request_method(), array( 'POST', 'PATCH', 'PUT', 'DELETE' ) ) ) {
			// Send only the variables set as defaults for the resource
			$request_args['body'] = array_intersect_key(
				$this->get_api_request_params(),
				$api_resource->get_default_params()
			);
		}

		$request = wp_remote_request( $endpoint, $request_args );
		
		// If Vimeo returned error, return the error
		if ( 200 != wp_remote_retrieve_response_code( $request ) ) {
			// Get request data
			$data = json_decode( wp_remote_retrieve_body( $request ), true );
			$data['response'] = $request['response'];

			return $this->api_error( $data );
		}	
		
		return $request;
	}	

	/**
	 * Returns reference for resource
	 * 
	 * @return AIOVG_Premium_Vimeo_API_Resource_Interface|AIOVG_Premium_Vimeo_API_Resource_Abstract
	 */
	public function get_api_resource() {
		return AIOVG_Premium_Vimeo_API_Resource_Objects::instance()->get_resource( $this->resource_type );
	}

	/**
	 * Returns request parameters
	 *
	 * @return array
	 */
	public function get_api_request_params() {
		if ( $this->get_api_resource()->is_single_entry() ) {
			return $this->params;
		}

		$sort_option = AIOVG_Premium_Vimeo_API_Resource_Objects::instance()->get_sort_option( false );

		if ( isset( $this->params['order'] ) ) {
			$sort_option = AIOVG_Premium_Vimeo_API_Resource_Objects::instance()->get_sort_option( $this->params['order'] );
		} else {
			$_options = $this->get_api_resource()->get_default_params();

			if ( isset( $_options['sort'] ) && isset( $_options['direction'] ) ) {
				$sort_option = array(
					'sort'      => $_options['sort'],
					'direction' => $_options['direction']
				);
			}
		}

		return array_merge( $this->params, $sort_option );
	}

	/**
	 * Returns endpoint URL complete with params for a given existing action.
	 *
	 * @return string|WP_Error
	 */
	private function get_endpoint() {
		$api_resource = $this->get_api_resource();
		if ( is_wp_error( $api_resource ) ) {
			return $api_resource;
		}

		$api_resource->set_resource_id( $this->resource_id );
		$api_resource->set_user_id( $this->api_user_id );
		$api_resource->set_params( $this->get_api_request_params() );
		$endpoint = $api_resource->get_endpoint();

		if ( is_wp_error( $endpoint ) ) {
			return $endpoint;
		}

		return self::API_ENDPOINT . $endpoint;
	}

	/**
	 * Returns the user agent for remote requests.
	 * Will return a WordPress user agent for the remote requests made by the plugin.
	 *
	 * @return string
	 */
	private function request_user_agent() {
		return 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' );
	}

	/**
	 * Return the access token for the Vimeo API.
	 *
	 * @return bool|string The access token or bool false if no token is set
	 */
	private function get_access_token() {
		$api_settings = get_option( 'aiovg_api_settings' );

		if ( isset( $api_settings['vimeo_access_token'] ) && ! empty( $api_settings['vimeo_access_token'] ) ) {
			return sanitize_text_field( $api_settings['vimeo_access_token'] );
		}

		return false;
	}

	/**
	 * Generates a WP_Error
	 *
	 * @param  string   $code
	 * @param  string   $message
	 * @param  bool     $data
	 * @return WP_Error
	 */
	private function error( $code, $message, $data = false ) {
		return new WP_Error( $code, $message, $data );
	}

	/**
	 * Process error responses from video into a WP_Error object
	 *
	 * @param  array    $data
	 * @return WP_Error
	 */
	private function api_error( $data ) {
		if ( isset( $data['developer_message'] ) ) {
			if ( 8003 == $data['error_code'] ) {
				$message = __( 'Vimeo access token not found.', 'all-in-one-video-gallery' ) . ' ' . sprintf( __( 'Follow <a href="%s" target="_blank">this guide</a> to get your own access token.', 'all-in-one-video-gallery' ), 'https://plugins360.com/all-in-one-video-gallery/how-to-get-vimeo-access-token/' );
			} else {
				$message = sprintf(
					__( '%s: %s (error code: %s)', 'all-in-one-video-gallery' ),
					__( 'Vimeo API error encountered', 'all-in-one-video-gallery' ),
					$data['developer_message'],
					$data['error_code']
				);
			}
		} elseif ( isset( $data['error'] ) ) {
			$message = sprintf(
				'%s: %s',
				__( 'Vimeo API error encountered', 'all-in-one-video-gallery' ),
				$data['error']
			);
		} else {
			$message = __( 'An unknown Vimeo API error has happened. Please try again.', 'all-in-one-video-gallery' );
		}

		// The error is Vimeo specific, flag it as such
		$data['vimeo_api_error'] = true;
		
		return $this->error( 'vimeo_api_error', $message, $data );
	}

}
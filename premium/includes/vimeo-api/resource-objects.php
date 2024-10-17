<?php

/**
 * Vimeo Resource Objects.
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
 * AIOVG_Premium_Vimeo_API_Resource_Objects class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Resource_Objects {

	/**
	 * @var AIOVG_Premium_Vimeo_API_Resource_Abstract[]
	 */
	private $resources = array();

	/**
	 * Sorting options
	 *
	 * @var array
	 */
	private $sort_options = array();

	/**
	 * Holds the plugin instance.
	 *
	 * @var AIOVG_Premium_Vimeo_API_Resource_Objects
	 */
	private static $instance = null;

	/**
	 * Clone.
	 *
	 * Disable class cloning and throw an error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object. Therefore, we don't want the object to be cloned.
	 *
	 * @access public
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'all-in-one-video-gallery' ), '2.6.2' );
	}

	/**
	 * Wakeup.
	 *
	 * Disable unserializing of the class.
	 *
	 * @access public
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'all-in-one-video-gallery' ), '2.6.2' );
	}

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @access public
	 * @static
	 *
	 * @return AIOVG_Premium_Vimeo_API_Resource_Objects
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Resources_Objects constructor.
	 */
	private function __construct() {
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/resource-interface.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/resource-abstract.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/entry-format.php';

		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/album-resource.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/showcase-resource.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/category-resource.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/channel-resource.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/folder-resource.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/group-resource.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/portfolio-resource.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/search-resource.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/user-resource.php';
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-api/videos-resource.php';

		// Set sorting options
		$this->set_sort_options();

		// Register resources
		$this->register_resource( new AIOVG_Premium_Vimeo_API_Album_Resource( false ) );		
		$this->register_resource( new AIOVG_Premium_Vimeo_API_Showcase_Resource( false ) ); // Duplicate for AIOVG_Premium_Vimeo_API_Album_Resource to add the "showcase" type (otherwise, implements the exact functionality of AIOVG_Premium_Vimeo_API_Album_Resource)
		$this->register_resource( new AIOVG_Premium_Vimeo_API_Category_Resource( false ) );
		$this->register_resource( new AIOVG_Premium_Vimeo_API_Channel_Resource( false ) );
		$this->register_resource( new AIOVG_Premium_Vimeo_API_Folder_Resource( false ) );
		$this->register_resource( new AIOVG_Premium_Vimeo_API_Group_Resource( false ) );
		$this->register_resource( new AIOVG_Premium_Vimeo_API_Portfolio_Resource( false ) );
		$this->register_resource( new AIOVG_Premium_Vimeo_API_Search_Resource( false ) );
		$this->register_resource( new AIOVG_Premium_Vimeo_API_User_Resource( false ) );
		$this->register_resource( new AIOVG_Premium_Vimeo_API_Videos_Resource( false ) );
	}

	/**
	 * Set sorting options
	 */
	private function set_sort_options() {
		$this->sort_options = array(
			'new' => array(
				'label'     => __( 'Newest', 'all-in-one-video-gallery' ),
				'sort'      => 'date',
				'direction' => 'desc',
				'resources' => array()
			),
			'alphabetical_asc' => array(
				'label'     => __( 'Alphabetical &#x1F81D;', 'all-in-one-video-gallery' ),
				'sort'      => 'alphabetical',
				'direction' => 'asc',
				'resources' => array()
			),
			'alphabetical_desc' => array(
				'label'     => __( 'Alphabetical &#x1F81F;', 'all-in-one-video-gallery' ),
				'sort'      => 'alphabetical',
				'direction' => 'desc',
				'resources' => array()
			),
			'duration' => array(
				'label'     => __( 'Duration', 'all-in-one-video-gallery' ),
				'sort'      => 'duration',
				'direction' => 'desc',
				'resources' => array()
			),
			'old' => array(
				'label'     => __( 'Oldest', 'all-in-one-video-gallery' ),
				'sort'      => 'date',
				'direction' => 'asc',
				'resources' => array()
			),
			'played' => array(
				'label'     => __( 'Plays', 'all-in-one-video-gallery' ),
				'sort'      => 'plays',
				'direction' => 'desc',
				'resources' => array()
			),
			'likes'	=> array(
				'label'     => __( 'Likes', 'all-in-one-video-gallery' ),
				'sort'      => 'likes',
				'direction' => 'desc',
				'resources' => array()
			),
			'comments' => array(
				'label'     => __( 'Comments', 'all-in-one-video-gallery' ),
				'sort'      => 'comments',
				'direction' => 'desc',
				'resources' => array()
			),
			'relevant' => array(
				'label'     => __( 'Relevancy', 'all-in-one-video-gallery' ),
				'sort'      => 'relevant',
				'direction' => 'desc',
				'resources' => array()
			),
			'default' => array(
				'label'     => __( 'Default order', 'all-in-one-video-gallery' ),
				'sort'      => 'default',
				'direction' => false,
				'resources' => array()
			)
		);
	}

	/**
	 * Registers a given resource sorting options
	 *
	 * @param AIOVG_Premium_Vimeo_API_Resource_Interface $resource
	 */
	private function register_sort_options( AIOVG_Premium_Vimeo_API_Resource_Interface $resource ) {
		$_sort_options = $resource->get_sort_options();

		foreach ( $this->sort_options as $k => $option ) {
			if ( in_array( $option['sort'], $_sort_options ) ) {
				$this->sort_options[ $k ]['resources'][ $resource->get_name() ] = $resource;
			}
		}
	}

	/**
	 * @return array
	 */
	public function get_sort_options() {
		return $this->sort_options;
	}

	/**
	 * Return a sort option from $this->sort_options
	 *
	 * @param  string $option
	 *
	 * @return array
	 */
	public function get_sort_option( $option ) {
		if ( isset( $this->sort_options[ $option ] ) ) {
			return $this->sort_options[ $option ];
		}

		return $this->sort_options['new'];
	}

	/**
	 * @param AIOVG_Premium_Vimeo_API_Resource_Interface $resource
	 */
	public function register_resource( AIOVG_Premium_Vimeo_API_Resource_Interface $resource ) {
		$this->resources[ $resource->get_name() ] = $resource;
		$this->register_sort_options( $resource );
	}

	/**
	 * @return AIOVG_Premium_Vimeo_API_Resource_Abstract[]
	 */
	public function get_resources() {
		return $this->resources;
	}

	/**
	 * @param  $name
	 *
	 * @return AIOVG_Premium_Vimeo_API_Resource_Interface|AIOVG_Premium_Vimeo_API_Resource_Abstract|WP_Error
	 */
	public function get_resource( $name ) {
		if ( ! isset( $this->resources[ $name ] ) ) {
			return new WP_Error(
				'aiovg-vimeo-api-query-resource-unknown',
				sprintf( __( 'Resource %s is not registered.', 'all-in-one-video-gallery' ), $name )
			);
		}

		return $this->resources[ $name ];
	}

}
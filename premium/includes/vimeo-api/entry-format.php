<?php

/**
 * Vimeo Entry Format.
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
 * AIOVG_Premium_Vimeo_API_Entry_Format class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_Vimeo_API_Entry_Format {

	/**
	 * The raw, unprocessed entry
	 *
	 * @var array
	 */
	private $_entry;

	/**
	 * The processed entry
	 *
	 * @var array
	 */
	private $formatted_entry;

	/**
	 * @var AIOVG_Premium_Vimeo_API_Resource_Interface
	 */
	private $api_resource;

	/**
	 * Entry_Format constructor.
	 *
	 * @param array                                      $entry
	 * @param AIOVG_Premium_Vimeo_API_Resource_Interface $api_resource
	 */
	public function __construct( $entry, AIOVG_Premium_Vimeo_API_Resource_Interface $api_resource ) {
		$this->_entry = $entry;
		$this->api_resource = $api_resource;
		$this->formatted_entry = $this->get_formatted_entry();
		$this->set_optional_fields();
	}

	/**
	 * @return object
	 */
	private function get_formatted_entry() {
		$entry = new stdClass();

		// Video ID
		$entry->id = $this->get_video_id();

		// Video URL
		$entry->url = $this->get_field( 'link' );

		// Video title
		$entry->title = (string) $this->get_field( 'name' );

		// Video description
		$entry->description = (string) $this->get_field( 'description' );
		$entry->description = nl2br( $entry->description );

		// Video image
		$entry->image = '';

		$images = [];
		$_thumbnails = $this->get_field( 'pictures' );

		if ( $_thumbnails ) {
			foreach ( $_thumbnails['sizes'] as $thumbnail ) {
				$entry->image = $thumbnail['link'];
				$images[ $thumbnail['width'] ] = $thumbnail['link'];
			}
			
			if ( isset( $images[1280] ) ) {
				$entry->image = $images[1280];
			} elseif ( isset( $images[960] ) ) {
				$entry->image = $images[960];
			} elseif ( isset( $images[640] ) ) {
				$entry->image = $images[640];
			}

			$entry->image = add_query_arg( 'isnew', 1, $entry->image );
		}

		// Video duration
		$entry->duration = $this->human_time( (int) $this->get_field( 'duration' ) );

		// Video publish date
		$entry->date = date( 'Y-m-d H:i:s', strtotime( $this->get_publish_date() ) );	

		// Additional
		$entry->stats = $this->get_stats();
		$entry->privacy = $this->get_privacy();
		$entry->view_privacy = $this->get_field( 'privacy' ) ? $this->_entry['privacy']['view'] : false;
		$entry->embed_privacy = $this->get_field( 'privacy' ) ? $this->_entry['privacy']['embed'] : false;
		$entry->type = $this->get_field( 'type' );

		return $entry;
	}

	/**
	 * Process the optional fields set by third party
	 */
	private function set_optional_fields() {
		$fields = (array) $this->api_resource->get_optional_fields();

		if ( $fields ) {
			foreach ( $fields as $field ) {
				$_field = $this->get_field( $field );
				if ( ! is_null( $_field ) ) {
					$this->formatted_entry[ $field ] = $_field;
				}
			}
		}
	}

	/**
	 * @return mixed|null
	 */
	private function get_video_id() {
		/**
		 * Videos with private link have uri like: /videos/174336869:444d9d089b
		 * To match the ID, look for :
		 */
		preg_match( '#/videos/([^:]+)(.*)$#', $this->get_field( 'uri' ), $matches );
		return isset( $matches[1] ) ? $matches[1] : NULL;
	}

	/**
	 * @return bool|mixed
	 */
	private function get_publish_date() {
		$publish_date = false;

		if ( $this->get_field( 'created_time' ) ){
			$publish_date = $this->_entry['created_time'];
		} elseif ( $this->get_field( 'release_time' ) ) {
			$publish_date = $this->_entry['release_time'];
		}

		return $publish_date;
	}

	/**
	 * Create a HH:MM:SS from a timestamp.
	 * Given a number of seconds, the function returns a readable duration formatted as HH:MM:SS
	 *
	 * @param int $seconds  Number of seconds.
	 * @return string       The formatted time.
	 */
	private function human_time( $seconds ) {
		$seconds = absint( $seconds );

		if ( $seconds < 0 ) {
			return;
		}

		$h = floor( $seconds / 3600 );
		$m = floor( $seconds % 3600 / 60 );
		$s = floor( $seconds %3600 % 60 );

		return ( ( $h > 0 ? $h . ":" : "" ) . ( ( $m < 10 ? "0" : "" ) . $m . ":" ) . ( $s < 10 ? "0" : "" ) . $s );
	}

	/**
	 * @return array
	 */
	private function get_stats() {
		$stats = array(
			'views' => 0
		);

		if ( isset( $this->_entry['stats']['plays'] ) ) {
			$stats['views'] = $this->_entry['stats']['plays'];
		}

		return $stats;
	}

	/**
	 * @return bool|string
	 */
	private function get_privacy() {
		/**
		 * View privacy on Vimeo can have the following values:
		 *
		 * - anybody - video is visible for everyone
		 * - nobody - video can be viewed only by the owner
		 * - contacts - video can be viewed only by the uploader's followers
		 * - users - video can be viewed only by specified users
		 * - password - video is password protected
		 * - unlisted - video can be viewed using a special, private link
		 * - disable - video won't be displayed on vimeo.com
		 */
		$privacy = false;

		if ( $this->get_field( 'privacy' ) ) {
			if ( in_array( $this->_entry['privacy']['view'], array( 'anybody', 'unlisted', 'disable' ) ) ) {
				$privacy = 'public';
			} else {
				$privacy = 'private';
			}
		}

		return $privacy;
	}

	/**
	 * @param  string     $name
	 *
	 * @return mixed|null
	 */
	private function get_field( $name ) {
		return isset( $this->_entry[ $name ] ) ? $this->_entry[ $name ] : null;
	}

	/**
	 * @return array
	 */
	public function get_entry() {
		return $this->formatted_entry;
	}

}
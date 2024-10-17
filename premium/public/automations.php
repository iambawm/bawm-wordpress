<?php

/**
 * Automations.
 *
 * @link       https://plugins360.com
 * @since      1.6.2
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Public_Automations class.
 *
 * @since 1.6.2
 */
class AIOVG_Premium_Public_Automations {

	/**
	 * The class instance.
	 *
	 * @since  1.6.2
	 * @access private
	 * @var    object|AIOVG_Premium_Public_Automations
	 */
	private static $instance;

	/**
	 * Get an instance of this class.
	 *
	 * @since  1.6.2
	 * @return object|AIOVG_Premium_Public_Automations
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new AIOVG_Premium_Public_Automations();
		}

		return self::$instance;
	}

	/**
	 * Add a custom cron schedule for every 5 minutes.
	 *
	 * @since  2.4.1
	 * @param  array $schedules An array of non-default cron schedules.
	 * @return array $schedules Filtered array of non-default cron schedules.
	 */
	public function cron_schedules( $schedules ) {
		$schedules[ 'every-5-minutes' ] = array( 
			'interval' => 5 * MINUTE_IN_SECONDS, 
			'display' => __( 'Every 5 minutes', 'all-in-one-video-gallery' ) 
		);

		return $schedules;
	}

	/**
	 * Schedule an action if it's not already scheduled.
	 *
	 * @since 1.6.2
	 */
	public function schedule_events() {
		if ( wp_next_scheduled( 'aiovg_hourly_scheduled_events' ) ) {
			wp_clear_scheduled_hook( 'aiovg_hourly_scheduled_events' );
		}

		if ( ! wp_next_scheduled( 'aiovg_schedule_every_five_minutes' ) ) {
			wp_schedule_event( time(), 'every-5-minutes', 'aiovg_schedule_every_five_minutes' );
		}
	}

	/**
	 * Called every 5 minutes. Run the auto import script.
	 *
	 * @since 2.4.1
	 */
	public function cron_event() {
		// Define the query
		$args = array(				
			'post_type' => 'aiovg_automations',			
			'post_status' => 'publish',
			'posts_per_page' => 1,			
			'meta_query' => array(
				array(
					'key' => 'import_next_schedule',
					'value'	=> date( 'Y-m-d H:i:s' ),
					'compare' => '<',
					'type' => 'DATETIME'
				)
			),
			'meta_key' => 'import_next_schedule',			
			'meta_type' => 'DATETIME',
			'orderby' => 'meta_value',
			'order' => 'ASC',
			'fields' => 'ids',
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		);

		$aiovg_query = new WP_Query( $args );

		if ( $aiovg_query->have_posts() ) {
			$posts = $aiovg_query->posts;

			foreach ( $posts as $post_id ) {
				delete_post_meta( $post_id, 'import_next_schedule' );	
				$this->import( $post_id );
			}
		}
	}

	/**
	 * Test import videos.
	 * 
	 * @since  1.6.2
   	 * @param  array $params Array of query params.
     * @return mixed
     */
	public function test( $params ) {	
		$importer = $this->get_importer( $params['service'] );
		if ( $importer ) {
			$importer->set_params( $params );
			$response = $importer->get_videos();
			return $response;
		}		

		return false;
	}

	/**
	 * Import videos.
	 * 
	 * @since  1.6.2
   	 * @param  int   $import_id Automations Post ID.
     */
	public function import( $import_id ) {
		$service = get_post_meta( $import_id, 'service', true );

		$importer = $this->get_importer( $service );
		if ( $importer ) {
			$importer->import_videos( $import_id );
		}
	}	

	/**
	 * Update the "Exclude URLs" list.
	 *
	 * @since 2.4.1
	 * @param int   $video_id Video Post ID.
	 */
	public function before_delete_post( $video_id ) {	
		if ( defined( 'AIOVG_UNINSTALL_PLUGIN' ) ) {
			return;	
		}

		if ( 'aiovg_videos' != get_post_type( $video_id ) ) {
			return;
		}

		$import_id = (int) get_post_meta( $video_id, 'import_id', true );
		if ( empty( $import_id ) ) {
			return;
		}

		$service = get_post_meta( $video_id, 'type', true );

		$importer = $this->get_importer( $service );
		if ( $importer ) {
			$importer->before_delete_post( $video_id, $import_id );
		}
	}

	/**
	 * Get the import class.
	 *
	 * @since  2.6.2
	 * @access private
	 * @param  string  $service Name of the import service.
	 * @return mixed
	 */
	private function get_importer( $service ) {
		switch ( $service ) {
			case 'youtube':
				require_once AIOVG_PLUGIN_DIR . 'premium/includes/youtube-import.php';
				return new AIOVG_Premium_YouTube_Import();		
				break;
			case 'vimeo':
				require_once AIOVG_PLUGIN_DIR . 'premium/includes/vimeo-import.php';
				return new AIOVG_Premium_Vimeo_Import();		
				break;
		}

		return false;
	}

}

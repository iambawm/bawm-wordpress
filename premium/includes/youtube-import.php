<?php

/**
 * YouTube Import.
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
 * AIOVG_Premium_YouTube_Import class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_YouTube_Import {

	/**
	 * Import options.
	 * 
	 * @since  2.6.2
	 * @access private
	 * @param  array   $params Array of import options.
     */
	private $params = array();

	/**
	 * Get videos.
	 * 
	 * @since  2.6.2
     * @return mixed
     */
	public function get_videos() {
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/youtube-api/youtube-api.php';

		// Vars
		$params = $this->get_params();

		$args = array(
			'type'           => sanitize_text_field( $params['type'] ),
			'src'            => sanitize_text_field( $params['src'] ),
			'playlistId'     => isset( $params['import_api_params']['playlistId'] ) ? sanitize_text_field( $params['import_api_params']['playlistId'] ) : '',
			'pageToken'      => isset( $params['import_api_params']['pageToken'] ) ? sanitize_text_field( $params['import_api_params']['pageToken'] ) : '',
			'publishedAfter' => isset( $params['import_api_params']['publishedAfter'] ) ? sanitize_text_field( $params['import_api_params']['publishedAfter'] ) : '',
			'maxResults'     => min( (int) $params['limit'], 50 )
		);

		if ( 'search' == $params['type'] ) {
			$args['order'] = sanitize_text_field( $params['order'] );
		}

		$no_of_iterations = ceil( (int) $params['limit'] / 50 );

		$is_doing_reimport = in_array( $params['import_status'], array( 'completed', 'rescheduled' ) ) ? true : false;

		$insert_keys = array();
		foreach ( $params['import_statistics']['data'] as $data ) {
			$insert_keys[] = $data['key'];
		}

		// Request API
		$response = new stdClass();
		$response->success = 0;
		$response->videos = array();
		$response->status = $params['import_status'];
		$response->params = $params['import_api_params'];
		$response->statistics = array(
			'key'        => '',
			'date'       => date( 'Y-m-d H:i:s' ),
			'imported'   => 0,
			'excluded'   => 0,
			'duplicates' => 0
		);	
		$response->last_error = '';

		for ( $i = 0; $i < $no_of_iterations; $i++ ) {
			if ( $i == $no_of_iterations - 1 ) { // Last iteration
				$args['maxResults'] = (int) $params['limit'] - ( $i * $args['maxResults'] );
			}

			$api = new AIOVG_Premium_YouTube_API();		
			$data = $api->query( $args );

			if ( isset( $data->error ) ) {
				$response->last_error = $data->error_message;
				break;
			}

			$response->success = 1;			

			$args = array_merge( $args, array_map( 'sanitize_text_field', wp_unslash( $data->params ) ) );
			$response->params = $data->params;

			if ( empty( $response->status ) ) {
				$response->status = 'scheduled';
			}

			$bypass_further_api_requests = false;
			if ( empty( $args['pageToken'] ) ) {
				$bypass_further_api_requests = true; // Bypass next iteration
			}

			// Set Videos
			$videos = array();

			foreach ( $data->videos as $video ) {
				if ( in_array( $video->id, $insert_keys ) ) { // Imported and most likely a reimport
					if ( 'playlist' == $params['type'] ) {
						continue;
					} else {
						$bypass_further_api_requests = true;
						break;
					}
				}

				// Set the first video id as key
				if ( empty( $response->statistics['key'] ) ) {
					$response->statistics['key'] = $video->id;
				}

				// Check in the excluded list
				if ( in_array( $video->url, $params['exclude'] ) ) {
					if ( ! $is_doing_reimport ) {
						++$response->statistics['excluded'];
					}

					continue;
				}

				// Check if the video post already exists
				$video_post_id = $this->is_video_exists( $video->id, $params );

				if ( false != $video_post_id ) {
					$import_id = get_post_meta( $video_post_id, 'import_id', true );

					if ( ! empty( $import_id ) && $import_id == $params['import_id'] ) { // Imported by our script and most likely a reimport
						if ( 'playlist' == $params['type'] ) {
							continue;
						} else {
							$bypass_further_api_requests = true;						
							break;
						}
					}

					$can_import_video = apply_filters( 'can_import_video', false, $video_post_id, $video->id, $params ); // Hook for developers to allow duplicate imports

					if ( ! $can_import_video ) {
						if ( ! $is_doing_reimport ) {
							++$response->statistics['duplicates'];
						}

						continue;
					}
				}

				// OK to import
				$videos[] = $video;

				++$response->statistics['imported'];
			}				

			if ( ! empty( $videos ) ) {
				$response->videos = array_merge( $response->videos, $videos );					
			}			

			// Bypass next iteration
			if ( $bypass_further_api_requests ) {
				$response->params['pageToken'] = '';
				$response->status = ! empty( $params['reschedule'] ) ? 'rescheduled' : 'completed';
				break;
			}
		}

		return $response;
	}	

	/**
	 * Import videos.
	 * 
	 * @since  2.6.2
   	 * @param  int   $import_id Automations Post ID.
     */
	public function import_videos( $import_id ) {
		set_time_limit( 1200 );

		$this->set_params( $import_id );
		$params = $this->get_params();

		$response = $this->get_videos();

		if ( $response->success ) {
			// Pre import
			wp_defer_term_counting( true );
			wp_defer_comment_counting( true );

			if ( ! empty( $params['is_fast_mode'] ) ) {
				$actions = array( 'transition_post_status', 'save_post', 'pre_post_update', 'add_attachment', 'edit_attachment', 'edit_post', 'post_updated', 'wp_insert_post', 'save_post_aiovg_videos' );
				
				foreach ( $actions as $action ) {
					remove_all_actions( $action );
				}
			}

			// Insert video posts
			if ( $response->statistics['imported'] > 0 ) {
				$videos_inserted = 0;
				
				foreach ( $response->videos as $video ) {
					$title = str_replace( '%%title%%', $video->title, $params['video_title_format'] );
					$title = str_replace( '%%id%%', $video->id, $title );

					$args = array(
						'post_type'      => 'aiovg_videos',
						'post_title'     => wp_strip_all_tags( $title ),
						'post_author'    => $params['video_author'],
						'post_status'    => $params['video_status'],
						'ping_status'    => 'closed',
						'comment_status' => $params['comment_status']						
					);

					if ( ! empty( $params['video_description'] ) ) {
						$description = str_replace( '%%description%%', $video->description, $params['video_description_format'] );
						$args['post_content'] = $description;	
					}

					if ( 'original' == $params['video_date'] ) {
						$args['post_date'] = $video->date;	
					}

					$video_id = wp_insert_post( $args );
	
					// Insert post meta
					if ( ! is_wp_error( $video_id ) ) {
						if ( ! empty( $params['video_categories'] ) ) {
							wp_set_object_terms( $video_id, array_map( 'intval', $params['video_categories'] ), 'aiovg_categories' );
						}	

						if ( ! empty( $params['video_tags'] ) ) {
							wp_set_object_terms( $video_id, array_map( 'intval', $params['video_tags'] ), 'aiovg_tags' );
						}

						$image_id = 0;
						if ( ! empty( $params['video_featured_image'] ) ) {
							$image_id = aiovg_create_attachment_from_external_image_url( $video->image, $video_id );

							if ( ! empty( $image_id ) ) {
								set_post_thumbnail( $video_id, $image_id ); 
							}
						}
	
						$meta = array(
							'type'       => 'youtube',
							'youtube'    => aiovg_sanitize_url( $video->url ),
							'image'      => aiovg_sanitize_url( $video->image ),
							'image_id'   => $image_id,
							'duration'   => sanitize_text_field( $video->duration ),
							'featured'   => 0,
							'views'      => 0,
							'import_id'  => (int) $import_id,
							'import_key' => sanitize_text_field( $response->statistics['key'] )
						);

						$this->add_post_meta_bulk( $video_id, $meta );

						// Hook for developers
						do_action( 'aiovg_video_imported', $video_id, $import_id );						

						++$videos_inserted;
					}
				}
				
				// If not all videos are inserted
				if ( $videos_inserted != $response->statistics['imported'] ) {
					$response->statistics['imported'] = $videos_inserted;
				}
			}

			// Import status
			update_post_meta( $import_id, 'import_status', sanitize_text_field( $response->status ) );

			// API Params
			update_post_meta( $import_id, 'import_api_params', array_map( 'sanitize_text_field', wp_unslash( $response->params ) ) );

			// Staistics
			$params['import_statistics']['last_error'] = $response->last_error;
			$params['import_statistics']['data'][] = $response->statistics;	

			update_post_meta( $import_id, 'import_statistics', $this->sanitize_statistics( $params['import_statistics'] ) );

			// Schedule
			if ( ! empty( $params['schedule'] ) ) {
				if ( 'completed' != $response->status ) {
					if ( 'rescheduled' == $response->status && ! empty( $response->params['pageToken'] ) ) {
						$params['schedule'] = 5 * 60;
					}

					$next_schedule = date( 'Y-m-d H:i:s', strtotime( '+' . (int) $params['schedule'] . ' seconds' ) );
					update_post_meta( $import_id, 'import_next_schedule', $next_schedule );
				}
			}			
			
			// Notify Admin
			if ( $response->statistics['imported'] > 0 ) {
				if ( 'draft' == $params['video_status'] || 'pending' == $params['video_status'] ) {
					aiovg_premium_notify_admin_videos_imported( $import_id, $response->statistics );
				}
			}

			// Post Import
			wp_defer_term_counting( false );
			wp_defer_comment_counting( false );
		} else {
			// Staistics
			$params['import_statistics']['last_error'] = $response->last_error;
			update_post_meta( $import_id, 'import_statistics', $this->sanitize_statistics( $params['import_statistics'] ) );
		}
	}	

	/**
	 * Set import options.
	 * 
	 * @since 2.6.2
	 * @param int|array $input Automations Post ID or array of import options.
     */
	public function set_params( $input ) {
		$video_settings = get_option( 'aiovg_video_settings' );
		$featured_images_settings = get_option( 'aiovg_featured_images_settings' );
		$automations_settings = get_option( 'aiovg_automations_settings' );		

		// Params
		$params = array(
			'import_id'                => is_array( $input ) ? 0 : (int) $input,
			'is_fast_mode'             => $automations_settings['is_fast_mode'],
			'service'                  => 'youtube',
			'type'                     => 'channel',			
			'src'                      => '',
			'exclude'                  => array(),
			'filter_duplicates'        => 1,
			'order'                    => 'relevance',
			'limit'                    => 50,
			'schedule'                 => 0,
			'reschedule'               => 0,
			'video_title_format'       => '%%title%%',
			'video_description'        => 0,
			'video_description_format' => '%%description%%',
			'video_featured_image'     => 0,
			'video_categories'         => array(),
			'video_tags'               => array(),			
			'video_date'               => 'original',
			'video_author'             => get_current_user_id(),
			'video_status'             => 'publish',
			'comment_status'           => ( (int) $video_settings['has_comments'] > 0 ) ? 'open' : 'closed',
			'import_status'            => '',
			'import_api_params'        => array(),			
			'import_statistics'        => array( 
				'last_error' => '',
				'data'       => array()
			)
		);
		
		if ( is_array( $input ) ) { // Test Run
			foreach ( $params as $key => $value ) {
				if ( isset( $input[ $key ] ) && ! empty( $input[ $key ] ) ) {
					$params[ $key ] = $input[ $key ];
				}
			}

			$params['limit'] = 10;
			$params['filter_duplicates'] = 0;
		} else {
			$post_meta = get_post_meta( $params['import_id'] );

			foreach ( $params as $key => $value ) {
				if ( isset( $post_meta[ $key ] ) ) {
					if ( ! empty( $post_meta[ $key ][0] ) ) {
						$params[ $key ] = maybe_unserialize( $post_meta[ $key ][0] );
					}
				} else {
					if ( 'video_featured_image' == $key ) {
						$params['video_featured_image'] = 1;
					}

					if ( 'video_description' == $key ) {
						$params['video_description'] = 1;
					}
				}
			}

			$type = $params['type'];
			$params['src'] = isset( $post_meta[ $type ] ) ? $post_meta[ $type ][0] : '';
		}

		// Exclude
		if ( ! empty( $params['exclude'] ) ) {
			$exclude = str_replace( array( "\n", "\n\r", ' ' ), ',', $params['exclude'] );
			$exclude = explode( ',', $exclude );
			$params['exclude'] = array_filter( $exclude );
		}

		// Reschedule
		if ( empty( $params['schedule'] ) ) {
			$params['reschedule'] = 0;
		}

		if ( 'videos' == $params['type'] ) {
			$params['reschedule'] = 0;
		}

		if ( 'search' == $params['type'] && 'date' != $params['order'] ) {
			$params['reschedule'] = 0;
		}

		// Limit
		if ( 'playlist' == $params['type'] && 'rescheduled' == $params['import_status'] ) {
			$params['limit'] = max( 250, (int) $params['limit'] );
		}

		// Featured Image
		if ( is_array( $featured_images_settings ) ) {
			if ( empty( $featured_images_settings['enabled'] ) || empty( $featured_images_settings['download_external_images'] ) ) {
				$params['video_featured_image'] = 0;
			}
		}

		$this->params = $params;
	}

	/**
	 * Get import options.
	 * 
	 * @since  2.6.2
	 * @access private
     * @return array   Array of import options.
     */
	private function get_params() {
		return $this->params;
	}

	/**
	 * Check if the video already exists.
	 * 
	 * @since  2.6.2
	 * @access private
	 * @param  string  $video_id Video ID.
	 * @param  array   $params   Array of query params.
     * @return mixed             Video post ID if exists, false if not.
     */
	private function is_video_exists( $video_id, $params ) {
		if ( 0 == $params['filter_duplicates'] ) {
			return false;
		}

		$service = sanitize_text_field( $params['service'] );

		$args = array(				
			'post_type' => 'aiovg_videos',			
			'post_status' => 'any',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'type',
					'value'	=> $service,
					'compare' => '='
				),
				array(
					'key' => $service,
					'value'	=> sanitize_text_field( $video_id ),
					'compare' => 'LIKE'
				)
			)
		);

		$aiovg_query = new WP_Query( $args );

		if ( $aiovg_query->have_posts() ) {
			$posts = $aiovg_query->posts;

			foreach ( $posts as $post_id ) {
				return $post_id;
			}
		}

		return false;
	}	

	/**
	 * Insert multiple post meta at once.
	 * 
	 * @since  2.6.2
	 * @access private
	 * @param  int     $post_id Post ID.
	 * @param  array   $data    Post meta keys and values.
     */
	private function add_post_meta_bulk( $post_id, $data ) {
		global $wpdb;

		$meta_table = _get_meta_table( 'post' );
		$values = array();
		
		foreach ( $data as $key => $value ) {					
			$values[] = '(' . $post_id . ',"' . $key . '",\'' . maybe_serialize( $value ) . '\')';						
		}
		
		$wpdb->query( "INSERT INTO $meta_table (`post_id`, `meta_key`, `meta_value`) VALUES " . implode( ',', $values ) );
	}

	/**
	 * Sanitize statistics array.
	 * 
	 * @since  2.6.2
	 * @access private
	 * @param  array   $statistics Raw statistics data.
     * @return array   $statistics Cleaned statistics data.
     */
	private function sanitize_statistics( $statistics ) {
		if ( ! empty( $statistics ) ) {
			$statistics['last_error'] = wp_kses_post( $statistics['last_error'] );

			if ( ! empty( $statistics['data'] ) ) {				
				$count = count( $statistics['data'] );
				$array = array();

				foreach ( $statistics['data'] as $index => $data ) {
					if ( empty( $data['key'] ) ) {
						if ( $index < ( $count - 1 ) ) { // Not the last index?
							continue;
						}
					}

					$array[] = array(
						'key'        => sanitize_text_field( $data['key'] ),
						'date'       => sanitize_text_field( $data['date'] ),
						'imported'   => (int) $data['imported'],
						'excluded'   => (int) $data['excluded'],
						'duplicates' => (int) $data['duplicates']
					); 
				}

				$statistics['data'] = $array;
			}
		}
		
		return $statistics;
	}

	/**
	 * Update the "Exclude URLs" list.
	 *
	 * @since 2.6.2
	 * @param int   $video_id  Video Post ID.
	 * @param int   $import_id Automations Post ID.
	 */
	public function before_delete_post( $video_id, $import_id ) {	
		$excluded_video = get_post_meta( $video_id, 'youtube', true );
		$excluded_urls  = get_post_meta( $import_id, 'exclude', true );				

		if ( ! empty( $excluded_urls ) ) {
			$excluded_urls = str_replace( array( "\n", "\n\r", ' ' ), ',', $excluded_urls );
			$excluded_urls = explode( ',', $excluded_urls );

			$excluded_urls[] = $excluded_video;

			$excluded_urls = array_filter( $excluded_urls );
			$excluded_urls = array_unique( $excluded_urls );
			$excluded_urls = array_map( 'aiovg_sanitize_url', $excluded_urls );

			$excluded_urls = implode( "\n", $excluded_urls );
		} else {
			$excluded_urls = aiovg_sanitize_url( $excluded_video );
		}

		update_post_meta( $import_id, 'exclude', $excluded_urls );
	}

}
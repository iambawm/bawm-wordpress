<?php

/**
 * A wrapper class for the Youtube Data API v3.
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
 * AIOVG_Premium_YouTube_API class.
 *
 * @since 2.6.2
 */
class AIOVG_Premium_YouTube_API {

	/**
     * The YouTube API Key.
	 * 
	 * @since  2.6.2
	 * @access protected
     * @var    string
     */
	protected $api_key;

	/**
	 * The YouTube API URLs.
	 * 
	 * @since  2.6.2
	 * @access protected
     * @var    array
     */
    protected $api_urls = array(       
		'playlistItems.list' => 'https://www.googleapis.com/youtube/v3/playlistItems',
		'channels.list'      => 'https://www.googleapis.com/youtube/v3/channels',
		'search.list'        => 'https://www.googleapis.com/youtube/v3/search',
		'videos.list'        => 'https://www.googleapis.com/youtube/v3/videos'
	);

	/**
	 * Get videos.
	 * 
	 * @since  2.6.2
     * @param  array $params Array of query params.
     * @return mixed
     */
    public function query( $params = array() ) {
		$api_settings = get_option( 'aiovg_api_settings' );
		$this->api_key = ! empty( $api_settings['youtube_api_key'] ) ? sanitize_text_field( $api_settings['youtube_api_key'] ) : '';

		if ( empty( $this->api_key ) ) {
			return $this->get_error( __( 'YouTube API key not found.', 'all-in-one-video-gallery' ) . ' ' . sprintf( __( 'Follow <a href="%s" target="_blank">this guide</a> to get your own API key.', 'all-in-one-video-gallery' ), 'https://plugins360.com/all-in-one-video-gallery/how-to-get-youtube-api-key/' ) );
		}

		if ( empty( $params['src'] ) ) {
			return $this->get_error( __( 'Import source not found.', 'all-in-one-video-gallery' ) );
		}		

		$response = new stdClass();
		$response->params = $this->safe_merge_params(
			array(
				'playlistId'     => '',				
				'pageToken'      => '',
				'publishedAfter' => ''
			),
			$params
		);

		// Search
		if ( 'search' == $params['type'] ) {	
			// Get video ids, page token using the search API	
			$params['q'] = $params['src'];
			$data = $this->request_api_search( $params );

			if ( ! isset( $data->error ) ) {
				$response->params['pageToken'] = $data->pageToken;

				// Get videos using the videos API
				if ( ! empty( $data->videos ) ) {
					$params['id'] = $data->videos;
					$data = $this->request_api_videos( $params );

					if ( ! isset( $data->error ) ) {
						$response->videos = $data->videos;

						if ( 'date' == $params['order'] && empty( $params['pageToken'] ) ) {
							$dates = array();

							foreach ( $data->videos as $video ) {
								$dates[] = $video->date;
							}

							rsort( $dates );

							$response->params['publishedAfter'] = $dates[0];
						}
					} else {
						$response = $data;
					}
				} else {
					$response->videos = $data->videos;
				}
			} else {
				$response = $data;
			}

			return $response;
		}

		// Playlist
		if ( 'playlist' == $params['type'] || ! empty( $params['playlistId'] ) ) {
			// Get video ids, page token using the playlistItems API	
			if ( 'playlist' == $params['type'] ) {
				$params['playlistId'] = $this->parse_youtube_id_from_url( $params['src'], 'playlist' );
			}			
			$data = $this->request_api_playlist_items( $params );

			if ( ! isset( $data->error ) ) {
				$response->params['pageToken'] = $data->pageToken;

				// Get videos using the videos API
				$params['id'] = $data->videos;
				$data = $this->request_api_videos( $params );

				if ( ! isset( $data->error ) ) {
					$response->videos = $data->videos;
				} else {
					$response = $data;
				}
			} else {
				$response = $data;
			}

			return $response;
		}

		// Channel / Username
		if ( 'channel' == $params['type'] || 'username' == $params['type'] ) {			
			if ( 'channel' == $params['type'] ) {
				$params['id'] = $this->get_channel_id( $params );
			} else {
				$params['forUsername'] = $this->parse_youtube_id_from_url( $params['src'], 'username' );
			}

			// Find playlistId using the channels API
			$data = $this->request_api_channels( $params );

			// Get videos using the playlistItems API
			if ( ! isset( $data->error ) ) {
				$params['playlistId'] = $data->playlistId;
				$response->params['playlistId'] = $data->playlistId;

				$data = $this->request_api_playlist_items( $params );

				if ( ! isset( $data->error ) ) {
					$response->params['pageToken'] = $data->pageToken;
	
					// Get videos using the videos API
					$params['id'] = $data->videos;
					$data = $this->request_api_videos( $params );
	
					if ( ! isset( $data->error ) ) {
						$response->videos = $data->videos;
					} else {
						$response = $data;
					}
				} else {
					$response = $data;
				}
			} else {
				$response = $data;
			}

			return $response;
		}

		// Videos
		if ( 'videos' == $params['type'] ) {
			$video_urls = str_replace( array( "\n", "\n\r", ' ' ), ',', $params['src'] );
			$video_urls = explode( ',', $video_urls );
			$video_urls = array_filter( $video_urls );

			$video_ids = array();
			foreach ( $video_urls as $video_url ) {
				$video_ids[] = $this->parse_youtube_id_from_url( $video_url, 'video' );
			}

			$total_videos = count( $video_ids );
			$total_pages  = ceil( $total_videos / $params['maxResults'] );

			$current_page = isset( $params['pageToken'] ) ? (int) $params['pageToken'] : 1;
			$current_page = max( $current_page, 1 );
			$current_page = min( $current_page, $total_pages );

			$offset = ( $current_page - 1 ) * $params['maxResults'];
			if ( $offset < 0 ) {
				$offset = 0;
			}

			$current_ids  = array_slice( $video_ids, $offset, $params['maxResults'] );
			$params['id'] = implode( ',', $current_ids );

			$data = $this->request_api_videos( $params );

			if ( ! isset( $data->error ) ) {
				$response->videos = $data->videos;

				if ( $current_page < $total_pages ) {
					$response->params['pageToken'] = $current_page + 1;
				} else {
					$response->params['pageToken'] = '';
				}
			} else {
				$response = $data;
			}

			return $response;
		}

		return $response;
	}

	/**
	 * Grab the playlist, channel or video ID using the YouTube URL given.
	 * 
	 * @since  2.6.2
	 * @access private
     * @param  string  $url  YouTube URL.
	 * @param  string  $type Type of the URL (playlist|channel|video).
     * @return mixed
     */
    private function parse_youtube_id_from_url( $url, $type = 'video' ) {
		$url = trim( $url );
		$id = $url;

		switch ( $type ) {
			case 'playlist':
				if ( preg_match( '/list=(.*)&?\/?/', $url, $matches ) ) {
					$id = $matches[1];
				}
				break;

			case 'channel':
				if ( wp_http_validate_url( $id ) ) {
					$id = '';
				}
				
				$url = parse_url( rtrim( $url, '/' ) );

				if ( isset( $url['path'] ) && preg_match( '/^\/channel\/(([^\/])+?)$/', $url['path'], $matches ) ) {
					$id = $matches[1];
				}
				break;

			case 'username':
				$url = parse_url( rtrim( $url, '/' ) );

				if ( isset( $url['path'] ) && preg_match( '/^\/user\/(([^\/])+?)$/', $url['path'], $matches ) ) {
					$id = $matches[1];
				}
				break;
			
			default: // video
				$url = parse_url( $url );
			
				if ( array_key_exists( 'host', $url ) ) {				
					if ( 0 === strcasecmp( $url['host'], 'youtu.be' ) ) {
						$id = substr( $url['path'], 1 );
					} elseif ( 0 === strcasecmp( $url['host'], 'www.youtube.com' ) ) {
						if ( isset( $url['query'] ) ) {
							parse_str( $url['query'], $url['query'] );

							if ( isset( $url['query']['v'] ) ) {
								$id = $url['query']['v'];
							}
						}
							
						if ( empty( $id ) ) {
							$url['path'] = explode( '/', substr( $url['path'], 1 ) );

							if ( in_array( $url['path'][0], array( 'e', 'embed', 'v' ) ) ) {
								$id = $url['path'][1];
							}
						}
					}
				}
		}

		return $id;
	}

	/**
	 * Get the channel ID.
	 * 
	 * @since  2.6.3
	 * @access private
     * @param  array   $params Array of query params.
     * @return string
     */
    private function get_channel_id( $params = array() ) {
		$id = $this->parse_youtube_id_from_url( $params['src'], 'channel' );

		if ( empty( $id ) ) {
			$api_url = $this->get_api_url( 'videos.list' );
		
			$params['id'] = $this->parse_youtube_id_from_url( $params['src'], 'video' );
			
			$api_params = $this->safe_merge_params(
				array(
					'id'    => '',
					'part'  => 'id,snippet,contentDetails,status',
					'cache' => 0
				), 
				$params
			);

			$data = $this->request_api( $api_url, $api_params );
			if ( ! isset( $data->error ) && count( $data->items ) > 0 ) {
				$id = $data->items[0]->snippet->channelId;
			}
		}

		return $id;		
	}

	/**
	 * Get videos using search API.
	 * 
	 * @since  2.6.2
	 * @access private
     * @param  array    $params Array of query params.
     * @return stdClass
     */
    private function request_api_search( $params = array() ) {
		$api_url = $this->get_api_url( 'search.list' );				

		$params['q'] = str_replace( '|', '%7C', $params['q'] );
		$params['type'] = 'video'; // Overrides user defined type value 'search'

		$api_params = $this->safe_merge_params(
			array(
				'q'          => '',
				'type'       => 'video',
				'part'       => 'id',
				'order'      => 'date',
				'maxResults' => 50,
				'pageToken'  => ''
			),
			$params
		);
		
		if ( 'date' == $api_params['order'] && empty( $api_params['pageToken'] ) && ! empty( $params['publishedAfter'] ) ) {
			$api_params['publishedAfter'] = $params['publishedAfter'];
		}
		
		$data = $this->request_api( $api_url, $api_params );
		if ( isset( $data->error ) ) {
			return $data;
		}

		// Process result
		$response = new stdClass();	
		$response->videos = array();
		$response->pageToken = '';	

		if ( count( $data->items ) > 0 ) {
			$videos = array();
			foreach ( $data->items as $item ) {
				if ( isset( $item->id ) && isset( $item->id->videoId ) ) {
					$videos[] = $item->id->videoId;
				}
			}
			$response->videos = implode( ',', $videos );
			
			if ( ! isset( $api_params['publishedAfter'] ) && isset( $data->nextPageToken ) ) {
				$response->pageToken = $data->nextPageToken;
			}
		}

		return $response;		
	}

	/**
	 * Get videos using playlistItems API.
	 * 
	 * @since  2.6.2
	 * @access private
     * @param  array    $params Array of query params.
     * @return stdClass
     */
    private function request_api_playlist_items( $params = array() ) {
		$api_url = $this->get_api_url( 'playlistItems.list' );		

    	$api_params = $this->safe_merge_params(
			array(
				'playlistId' => '',
				'part'       => 'contentDetails',
				'maxResults' => 50,
				'pageToken'  => ''
			),
			$params
		);
		
		$data = $this->request_api( $api_url, $api_params );
		if ( isset( $data->error ) ) {
			return $data;
		}

		if ( 0 == count( $data->items ) ) {
			return $this->get_error( __( 'No videos found matching your query.', 'all-in-one-video-gallery' ) );
		}

		// Process result
		$response = new stdClass();	

		$videos = array();
		foreach ( $data->items as $item ) {
			if ( isset( $item->contentDetails ) && isset( $item->contentDetails->videoId ) ) {
				$videos[] = $item->contentDetails->videoId;
			}
		}
		$response->videos = implode( ',', $videos );

		$response->pageToken = '';
		if ( isset( $data->nextPageToken ) ) {
			$response->pageToken = $data->nextPageToken;
		}

		return $response;		
	}

	/**
	 * Find playlistId using channels API.
	 * 
	 * @since  2.6.2
	 * @access private
     * @param  array   $params Array of query params.
     * @return mixed
     */
  	private function request_api_channels( $params = array() ) {
		$key = isset( $params['id'] ) ? $params['id'] : $params['forUsername'];

		$api_url = $this->get_api_url( 'channels.list' );

		$api_params = $this->safe_merge_params(
			array(
				'id'          => '',
				'forUsername' => '',
				'part'        => 'contentDetails'
			),
			$params
		);

		$data = $this->request_api( $api_url, $api_params );
		if ( isset( $data->error ) ) {
			return $data;
		}

		if ( 0 == count( $data->items ) ) {
			return $this->get_error( __( 'No videos found matching your query.', 'all-in-one-video-gallery' ) );
		}

		$playlist_id = $data->items[0]->contentDetails->relatedPlaylists->uploads;

		// Process result
		$response = new stdClass();
		$response->playlistId = $playlist_id;		

		return $response;
	}	

	/**
	 * Get details of the given video IDs.
	 * 
	 * @since  2.6.2
	 * @access private
     * @param  array    $params Array of query params.
     * @return stdClass
     */
  	private function request_api_videos( $params = array() ) {
		$api_url = $this->get_api_url( 'videos.list' );	

		$api_params = $this->safe_merge_params(
			array(
        		'id'   => '',
				'part' => 'id,snippet,contentDetails,status'
			), 
			$params
		);

		$data = $this->request_api( $api_url, $api_params );
		if ( isset( $data->error ) ) {
			return $data;
		}

		// Process result
		$response = new stdClass();

		$videos = array();

		if ( count( $data->items ) > 0 ) {
			foreach ( $data->items as $item ) {
				// Is video private?
				if ( ! isset( $item->snippet->thumbnails ) || ( isset( $item->snippet->status ) && 'private' == $item->snippet->status->privacyStatus )	) {
					continue;
				}

				$video = new stdClass();

				// Video ID
				$video->id = $item->id;

				// Video URL
				$video->url = 'https://www.youtube.com/watch?v=' . $video->id;

				// Video title
				$video->title = $item->snippet->title;

				// Video description
				$video->description = $item->snippet->description;

				// Video image
				$video->image = '';	

				if ( isset( $item->snippet->thumbnails->medium->url ) ) {
					$video->image = $item->snippet->thumbnails->medium->url;
				} elseif ( isset( $item->snippet->thumbnails->standard->url ) ) {
					$video->image = $item->snippet->thumbnails->standard->url;
				}
				
				// Video duration
				$video->duration = '';

				if ( isset( $item->contentDetails->duration ) ) {
					$time = new DateTime( '@0' ); // Unix epoch
					$time->add( new DateInterval( $item->contentDetails->duration ) );

					$video->duration = $time->format( 'H:i:s' );
				}

				// Video publish date
				$datetime = new DateTime( $item->snippet->publishedAt );
			 	$video->date = date_format( $datetime, 'Y-m-d H:i:s' );		

				// Push resulting object to the main array
				$videos[] = $video;
			}
		}

		$response->videos = $videos;

		return $response;		
	}
	
	/**
     * Get API URL by request.
	 *
	 * @since  2.6.2
	 * @access private
     * @param  array   $name
     * @return string
     */
  	private function get_api_url( $name ) {
    	return $this->api_urls[ $name ];
	}	

	/**
     * Request data from the API server.
     *
	 * @since  2.6.2
	 * @access private
     * @param  string  $url    YouTube API URL.
     * @param  array   $params Array of query params.
     * @return mixed     
     */
  	private function request_api( $url, $params ) {
		$params['key'] = $this->api_key;	

		// Request data from API server		
		$url = $url . '?' . http_build_query( $params );

		$request = wp_remote_get( $url, array(
			'headers' => [ 'referer' => home_url() ],
		) );

		if ( is_wp_error( $request ) ) {
			return $this->get_error( $request->get_error_message() );
		}

		$body = wp_remote_retrieve_body( $request );

		$data = json_decode( $body );

		if ( isset( $data->error ) ) {
			$message = "Error " . $data->error->code . " " . $data->error->message;
			
			if ( isset( $data->error->errors[0] ) ) {
				$message .= " : " . $data->error->errors[0]->reason;
			}
			
			return $this->get_error( $message );			
		}

		// Finally return the data
		return $data;
	}

	/**
	 * Combine user params with known params and fill in defaults when needed.
	 *
	 * @since  2.6.2
	 * @access private
	 * @param  array   $pairs  Entire list of supported params and their defaults.
	 * @param  array   $params User defined params.
	 * @return array   $out    Combined and filtered params array.
	*/
	private function safe_merge_params( $pairs, $params ) {
		$params = (array) $params;
		$out = array();
		
		foreach ( $pairs as $name => $default ) {
			if ( array_key_exists( $name, $params ) ) {
				$out[ $name ] = $params[ $name ];
			} else {
				$out[ $name ] = $default;
			}

			if ( empty( $out[ $name ] ) ) {
				unset( $out[ $name ] );
			}
		}
		
		return $out;
	}

	/**
	 * Build error object.
	 *
	 * @since  2.6.2
	 * @access private
	 * @param  string  $message Error message.
	 * @return object           Error object.
	*/
	private function get_error( $message ) {
		$obj = new stdClass();
		$obj->error = 1;
		$obj->error_message = $message;

		return $obj;
	}
	
}
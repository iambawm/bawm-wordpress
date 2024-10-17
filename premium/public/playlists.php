<?php

/**
 * Playlists.
 *
 * @link       https://plugins360.com
 * @since      3.6.0
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Public_Playlists class.
 *
 * @since 3.6.0
 */
class AIOVG_Premium_Public_Playlists {
	
	/**
	 * Get things started.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Register shortcode(s)
		add_shortcode( 'aiovg_playlist_button', array( $this, 'run_shortcode_playlist_button' ) );
		add_shortcode( 'aiovg_playlists', array( $this, 'run_shortcode_playlists' ) );
		add_shortcode( 'aiovg_playlist', array( $this, 'run_shortcode_playlist' ) );
	}

	/**
	 * Add custom rewrite rules, remove video from playist or delete playlists.
	 *
	 * @since 3.6.0
	 */
	public function wp_loaded() {
		// Add rewrite rules
		$this->add_rewrites();
	
		// User actions
		if ( isset( $_GET['_wpnonce'] ) ) {
			// Remove video from playlist
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'aiovg_remove_from_playlist_nonce' ) ) {				
				$playlist_id = isset( $_GET['aiovg_playlist'] ) ? (int) $_GET['aiovg_playlist'] : 0;
				$post_id     = isset( $_GET['aiovg_video'] ) ? (int) $_GET['aiovg_video'] : 0;			
			
				if ( $playlist_id > 0 && $post_id > 0 ) {
					ob_start(); // Output Buffer			

					if ( $playlist = get_term_by( 'id', $playlist_id, 'aiovg_playlists' ) ) {
						$user_id = get_current_user_id();
						$playlist_author = (int) get_term_meta( $playlist_id, 'playlist_author', true );

						if ( $user_id == $playlist_author ) {
							wp_remove_object_terms( $post_id, $playlist_id, 'aiovg_playlists' );
							$redirect_url = add_query_arg( 'status', 'removed', aiovg_premium_get_playlist_page_url( $playlist ) );
						} else {
							$redirect_url = add_query_arg( 'permission_denied', 1, aiovg_premium_get_playlist_page_url( $playlist ) );
						}
						
						// Redirect			
						wp_redirect( $redirect_url );
						exit();
					}					
				}
			}

			// Delete playlist
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'aiovg_delete_playlist_nonce' ) ) {
				$playlist_id = (int) $_GET['aiovg_playlist'];		
			
				if ( $playlist_id > 0 ) {
					ob_start(); // Output Buffer				

					$page_settings = get_option( 'aiovg_page_settings' );

					$user_id = get_current_user_id();
					$playlist_author = (int) get_term_meta( $playlist_id, 'playlist_author', true );

					if ( $user_id == $playlist_author ) {
						wp_delete_term( $playlist_id, 'aiovg_playlists' );						
						$redirect_url = add_query_arg( 'status', 'deleted', get_permalink( $page_settings['playlist'] ) );
					} else {
						$redirect_url = add_query_arg( 'permission_denied', 1, get_permalink( $page_settings['playlist'] ) );
					}

					// Redirect			
					wp_redirect( $redirect_url );
					exit();
				}
			}
		}
	}

	/**
	 * Add rewrite rules.
	 *
	 * @since  3.6.0
	 * @access private
	 */
	private function add_rewrites() {
		$page_settings = get_option( 'aiovg_page_settings' );
		$url = home_url();
		
		if ( array_key_exists( 'playlist', $page_settings ) ) {
			$id = (int) $page_settings['playlist'];
			
			if ( $id > 0 ) {
				$link = str_replace( $url, '', get_permalink( $id ) );			
				$link = trim( $link, '/' );	
				$link = urldecode( $link );		
				
				add_rewrite_rule( "$link/([^/]+)/?$", 'index.php?page_id=' . $id . '&aiovg_playlist=$matches[1]', 'top' );
			}
		}

		// Rewrite tags
		add_rewrite_tag( '%aiovg_playlist%', '([^/]+)' );
	}

	/**
	 * Change the current page title if applicable.
	 *
	 * @since  3.6.0
	 * @param  string $title   Current page title.
	 * @param  int    $post_id The post ID.
	 * @return string $title   Filtered page title.
	 */
	public function the_title( $title, $id = 0 ) {
		if ( ! in_the_loop() || ! is_main_query() ) {
			return $title;
		}

		global $post;

		if ( ! empty( $id ) ) {
			if ( $id != $post->ID ) {
				return $title;
			}
		}
		
		$page_settings = get_option( 'aiovg_page_settings' );

		// Change playlist page title
		if ( $post->ID == $page_settings['playlist'] ) {		
			if ( $slug = get_query_var( 'aiovg_playlist' ) ) {
				if ( $term = get_term_by( 'slug', $slug, 'aiovg_playlists' ) ) {
					$user_id = get_current_user_id();
					$title = str_replace( '[' . $user_id . '] ', '', $term->name );
				}			
			}			
		}	
		
		return $title;	
	}

	/**
	 * Run the shortcode [aiovg_playlist_button].
	 *
	 * @since  3.6.1
	 * @param  array  $attributes An associative array of attributes.
	 * @return string             Shortcode output.
	 */
	public function run_shortcode_playlist_button( $attributes ) {
		if ( ! is_array( $attributes ) ) {
			$attributes = array();
		}

		global $post;

		$post_id = 0;
		$content = '';
		
		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}

		if ( isset( $attributes['id'] ) && ! empty( $attributes['id'] ) ) {
			$post_id = $attributes['id'];
		}

		if ( ! empty( $post_id ) ) {
			$post_id   = (int) $post_id;
			$post_type = get_post_type( $post_id );

			if ( 'aiovg_videos' == $post_type ) {
				// Enqueue dependencies
				wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );				
				wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-playlists' );	
	
				// Output
				$content = sprintf( '<aiovg-playlist-button post_id="%d"></aiovg-playlist-button>', $post_id );
			} 
		}

		return $content;		
	}

	/**
	 * Run the shortcode [aiovg_playlists].
	 *
	 * @since 3.6.0
	 * @param array $atts An associative array of attributes.
	 */
	public function run_shortcode_playlists( $atts ) {	
		// Enqueue dependencies
		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );
		wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-playlists' );	

		// Verify if user logged in
		if ( ! is_user_logged_in() ) {		
			return aiovg_premium_login_form();			
		}	

		// Vars
		$defaults = array(
			'uid'        => aiovg_get_uniqid(),
			'orderby'    => 'name',
			'order'      => 'asc',
			'hide_empty' => 0
		);

		$attributes = shortcode_atts( $defaults, $atts, 'aiovg_playlists' );
		$attributes['shortcode'] = 'aiovg_playlists';	
		$attributes['user_id'] = get_current_user_id();		
		
		// Process output
		$args = array(			
			'taxonomy'     => 'aiovg_playlists',
			'orderby'      => sanitize_text_field( $attributes['orderby'] ), 
			'order'        => sanitize_text_field( $attributes['order'] ),
			'hide_empty'   => (int) $attributes['hide_empty'],
			'hierarchical' => false,
			'meta_key'     => 'playlist_author',
        	'meta_value'   => (int) $attributes['user_id']
		);

		$args = apply_filters( 'aiovg_playlists_args', $args, $attributes );
		$terms = get_terms( $args );			
		
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $key => $term ) {
				$terms[ $key ]->name = str_replace( '[' . $attributes['user_id'] . '] ', '', $term->name );
			}

			ob_start();
			include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . 'premium/public/templates/user-playlists.php' );
			return ob_get_clean();		
		} else {
			return sprintf(
				'<div class="aiovg-shortcode-playlists aiovg-no-items-found">%s</div>',
				esc_html__( 'No playlists found', 'all-in-one-video-gallery' )
			);
		}			
	}	

	/**
	 * Run the shortcode [aiovg_playlist].
	 *
	 * @since 3.6.0
	 * @param array $atts An associative array of attributes.
	 */
	public function run_shortcode_playlist( $atts ) {	
		// Enqueue dependencies
		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-public' );
		wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );

		wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-playlists' );	
		
		// Verify if user logged in
		if ( ! is_user_logged_in() ) {		
			return aiovg_premium_login_form();			
		}	

		$term_slug = get_query_var( 'aiovg_playlist' );
		
		if ( ! empty( $term_slug ) ) {
			$term = get_term_by( 'slug', sanitize_title( $term_slug ), 'aiovg_playlists' );
		} elseif ( ! empty( $atts['id'] ) ) {			
			$term = get_term_by( 'id', (int) $atts['id'], 'aiovg_playlists' );
		}
		
		if ( isset( $term ) && ! empty( $term ) ) {
			$defaults = array();

			$fields = aiovg_get_shortcode_fields();			

			foreach ( $fields['videos']['sections'] as $section ) {
				foreach ( $section['fields'] as $field ) {
					$defaults[ $field['name'] ] = $field['value'];
				}
			}

			foreach ( $fields['categories']['sections']['general']['fields'] as $field ) {
				if ( 'orderby' == $field['name'] || 'order' == $field['name'] ) {
					$defaults[ 'categories_' . $field['name'] ] = $field['value'];
				}
			}	
			
			$defaults['uid'] = aiovg_get_uniqid();
			$defaults['source'] = 'videos';
			$defaults['template'] = 'compact';
			$defaults['thumbnail_style'] = 'standard';	
			$defaults['count'] = 0;
			$defaults['paged'] = 1;	
			$defaults['pagination_ajax'] = 0;					

			$attributes = shortcode_atts( $defaults, $atts, 'aiovg_playlist' );
			$attributes['shortcode'] = 'aiovg_playlist';			
			$attributes['ratio'] = ! empty( $attributes['ratio'] ) ? (float) $attributes['ratio'] . '%' : '56.25%';
			
			$orderby = sanitize_text_field( $attributes['orderby'] );
			$order   = sanitize_text_field( $attributes['order'] );

			// Define the query
			$args = array(				
				'post_type'      => 'aiovg_videos',
				'posts_per_page' => -1,
				'post_status'    => array( 'publish' )
			);

			// Taxonomy Parameters
			$tax_queries = array();

			$tax_queries[] = array(
				'taxonomy' => 'aiovg_playlists',
				'field'    => 'term_id',
				'terms'    => $term->term_id
			);

			$count_tax_queries = count( $tax_queries );
			$args['tax_query'] = ( $count_tax_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $tax_queries ) : $tax_queries;
						
			// Custom Field (post meta) Parameters
			$meta_queries = array();		

			if ( 'likes' == $orderby ) { // Likes			
				$meta_queries['likes'] = array(
					'relation' => 'OR',
					array(
						'key'     => 'likes',
						'compare' => 'NOT EXISTS'
					),
					array(
						'key'     => 'likes',
						'type'    => 'NUMERIC',
						'compare' => 'EXISTS'
					)
				);				
			}

			if ( 'dislikes' == $orderby ) { // Dislikes			
				$meta_queries['dislikes'] = array(
					'relation' => 'OR',
					array(
						'key'     => 'dislikes',
						'compare' => 'NOT EXISTS'
					),
					array(
						'key'     => 'dislikes',
						'type'    => 'NUMERIC',
						'compare' => 'EXISTS'
					)
				);				
			}

			$count_meta_queries = count( $meta_queries );
			if ( $count_meta_queries ) {
				$args['meta_query'] = ( $count_meta_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : $meta_queries;
			}

			// Order & Orderby Parameters
			switch ( $orderby ) {
				case 'likes':
				case 'dislikes':
					$args['orderby'] = array(
						$orderby => $order,
						'date'   => 'DESC'
					);
					break;

				case 'views':
					$args['meta_key'] = $orderby;
					$args['orderby']  = 'meta_value_num';
				
					$args['order']    = $order;
					break;

				case 'rand':
					$args['orderby']  = 'RAND()';
					break;

				default:
					$args['orderby']  = $orderby;
					$args['order']    = $order;
			}
		
			$args = apply_filters( 'aiovg_query_args', $args, $attributes );
			$aiovg_query = new WP_Query( $args );
			
			// Start the loop
			global $post;
			
			// Process output
			ob_start();
			include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . 'premium/public/templates/single-playlist.php', $attributes );				
			$content = ob_get_clean();			
		
			return $content;
		}
		
		return do_shortcode( '[aiovg_playlists]' );	
	}
	
	/**
	 * Get playlists info.
	 *
	 * @since 3.6.1
	 */
	public function ajax_callback_get_playlists_info() {
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );

		// Proceed safe
		$response = array( 'status' => 'success', 'message' => '', 'html' => '' );

		$user_id = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : 0;
		$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;

		$selected = array();

		if ( ! empty( $post_id ) ) {
			$playlist_ids = wp_get_object_terms( $post_id, 'aiovg_playlists', array( 'fields' => 'ids' ) );

			if ( ! empty( $playlist_ids ) && ! is_wp_error( $playlist_ids ) ) {
				$selected = $playlist_ids;
			}
		}

		if ( $user_id > 0 ) {
			$playlists = get_terms(array(
				'taxonomy'   => 'aiovg_playlists',		
				'orderby'    => 'name',   
				'order'      => 'ASC',  
				'hide_empty' => false,
				'meta_key'   => 'playlist_author',
				'meta_value' => (int) $user_id
			));	

			if ( ! empty( $playlists ) && ! is_wp_error( $playlists ) ) { 
				$html = '';

				foreach ( $playlists as $playlist ) { 
					$html .= '<div class="aiovg-item">';
					$html .= '<label class="aiovg-item-label">';

					$html .= sprintf( 
						'<input type="checkbox" name="aiovg_playlist[]" class="aiovg-form-control" value="%d" %s/>', 
						$playlist->term_id,
						( in_array( $playlist->term_id, $selected ) ? 'checked' : '' )
					);			

					$playlist_name = str_replace( '[' . $user_id . '] ', '', $playlist->name );

					$html .= sprintf( '<span class="aiovg-item-name">%s</span>', esc_html( $playlist_name ) );
					$html .= '</label>';
					$html .= '</div>';
				}
				
				$response['html'] = $html;
			}
		}		
		
		// Output
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * "Add to Playlist" button.
	 *
	 * @since 3.6.1
	 * @param string  $content    HTML string.
	 * @param int     $post_id    Video post ID.
     * @param array   $attributes Array of attributes.
	 * @return string             Filtered HTML string.
	 */
	public function add_to_playlist_button( $content, $post_id = 0, $attributes = array() ) {
		if ( $post_id > 0 ) {
			$show_playlist_button = isset( $attributes['show_player_playlist_button'] ) ? (int) $attributes['show_player_playlist_button'] : 1;	

			if ( $show_playlist_button ) {
				// Enqueue dependencies
				wp_enqueue_style( AIOVG_PLUGIN_SLUG . '-premium-public' );
				wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-playlists' );	

				// Output
				$unfiltered_content = $content;

				$content  = sprintf( '<aiovg-playlist-button post_id="%d"></aiovg-playlist-button>', $post_id );
				$content .= $unfiltered_content;
			}
		}

		return $content;
	}

	/**
	 * "Remove from Playlist" button.
	 *
	 * @since  3.6.1
	 * @param  string $content    HTML string.
	 * @param  array  $attributes An associative array of attributes.
	 * @return string             Filtered HTML string.
	 */
	public function remove_from_playlist_button( $content, $attributes = array() ) {
		if ( isset( $attributes['shortcode'] ) && 'aiovg_playlist' == $attributes['shortcode'] ) {	
			if ( $playlist_slug = get_query_var( 'aiovg_playlist' ) ) {
				if ( $playlist = get_term_by( 'slug', sanitize_title( $playlist_slug ), 'aiovg_playlists' ) ) {
					// Vars
					$post_id   = get_the_ID();
					$permalink = aiovg_premium_get_remove_from_playlist_page_url( $playlist->term_id, $post_id );

					// Output				
					$content .= sprintf( '<a href="%s" class="aiovg-link-remove-from-playlist aiovg-flex aiovg-gap-1 aiovg-items-center aiovg-text-error aiovg-text-small">', esc_url( $permalink ) );
					$content .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">
						<path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
					</svg>';
					$content .= esc_html__( 'Remove from Playlist', 'all-in-one-video-gallery' );
					$content .= '</a>';
				}
			}
		}

		return $content;
	}

	/**
	 * Create playlist.
	 *
	 * @since 3.6.0
	 */
	public function ajax_callback_create_playlist() {
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );

		// Proceed safe
		$response = array( 'status' => 'success', 'message' => '', 'html' => '' );

		if ( aiovg_current_user_can( 'edit_aiovg_videos' ) ) {
			$playlists_settings = get_option( 'aiovg_playlists_settings' );	

			$user_id = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : 0;
			$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;		
			$playlist_title = isset( $_REQUEST['playlist_title'] ) ? sanitize_text_field( $_REQUEST['playlist_title'] ) : '';
				
			if ( $user_id > 0 && $post_id > 0 && ! empty( $playlist_title ) ) {
				$playlists = get_terms(array(
					'taxonomy'   => 'aiovg_playlists',		
					'orderby'    => 'name',   
					'order'      => 'ASC',  
					'hide_empty' => false,
					'meta_key'   => 'playlist_author',
					'meta_value' => (int) $user_id
				));	

				$playlists_count = 0;
				if ( ! empty( $playlists ) && ! is_wp_error( $playlists ) ) { 
					$playlists_count = count( $playlists );
				}

				$limit = ! empty( $playlists_settings['limit'] ) ? (int) $playlists_settings['limit'] : 0;
				$limit_reached = ( $limit > 0 && $playlists_count >= $limit ) ? true : false;

				if ( ! $limit_reached ) {
					$playlist = wp_insert_term( '[' . $user_id . '] ' . $playlist_title, 'aiovg_playlists' );

					if ( ! is_wp_error( $playlist ) ) {
						$playlist_id = $playlist['term_id'];

						update_term_meta( $playlist_id, 'playlist_author', $user_id );
						wp_set_object_terms( $post_id, $playlist_id, 'aiovg_playlists', true );

						// Output
						$html  = '<div class="aiovg-item">';
						$html .= '<label class="aiovg-item-label">';

						$html .= sprintf( 
							'<input type="checkbox" name="aiovg_playlist[]" class="aiovg-form-control" value="%d" checked/>', 
							$playlist_id
						);			

						$html .= sprintf( '<span class="aiovg-item-name">%s</span>', $playlist_title );
						$html .= '</label>';
						$html .= '</div>';

						$response['html'] = $html;
					} else {
						$response['status']  = 'error';
						$response['message'] = $playlist->get_error_message();
					}
				} else {
					$response['status']  = 'error';
					$response['message'] = esc_html__( 'You have reached the maximium number of playlists allowed.', 'all-in-one-video-gallery' );
				}
			}
		} else {
			$response['status']  = 'error';
			$response['message'] = esc_html__( 'You do not have sufficient permissions to do this action.', 'all-in-one-video-gallery' );
		}
		
		// Output
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Update playlist.
	 *
	 * @since 3.6.0
	 */
	public function ajax_callback_update_playlist() {
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );

		// Proceed safe
		$response = array( 'status' => 'success', 'message' => '', 'html' => '' );

		$user_id        = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : 0;
		$playlist_id    = isset( $_REQUEST['playlist_id'] ) ? (int) $_REQUEST['playlist_id'] : 0;
		$playlist_title = isset( $_REQUEST['playlist_title'] ) ? sanitize_text_field( $_REQUEST['playlist_title'] ) : '';
			
		if ( $user_id > 0 && $playlist_id > 0 && ! empty( $playlist_title )) {
			$playlist_author = (int) get_term_meta( $playlist_id, 'playlist_author', true );

			if ( $user_id == $playlist_author ) {
				$args = array(
					'name' => '[' . $user_id . '] ' . $playlist_title
				);

				wp_update_term( $playlist_id, 'aiovg_playlists', $args );
			} else {
				$response['status']  = 'error';
				$response['message'] = esc_html__( 'You do not have sufficient permissions to do this action.', 'all-in-one-video-gallery' );
			}
		}
		
		// Output
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Add to playlist.
	 *
	 * @since 3.6.0
	 */
	public function ajax_callback_add_to_playlist() {
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );

		// Proceed safe
		$response = array( 'status' => 'success', 'message' => '', 'html' => '' );

		$user_id = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : 0;
		$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;		
		$playlist_id = isset( $_REQUEST['playlist_id'] ) ? (int) $_REQUEST['playlist_id'] : 0;
			
		if ( $user_id > 0 && $post_id > 0 && $playlist_id > 0 ) {
			$playlist_author = (int) get_term_meta( $playlist_id, 'playlist_author', true );

			if ( $user_id == $playlist_author ) {
				wp_set_object_terms( $post_id, $playlist_id, 'aiovg_playlists', true );
			} else {
				$response['status']  = 'error';
				$response['message'] = esc_html__( 'You do not have sufficient permissions to do this action.', 'all-in-one-video-gallery' );
			}
		}
		
		// Output
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Remove from playlist.
	 *
	 * @since 3.6.0
	 */
	public function ajax_callback_remove_from_playlist() {
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );

		// Proceed safe
		$response = array( 'status' => 'success', 'message' => '', 'html' => '' );

		$user_id = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : 0;
		$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;		
		$playlist_id = isset( $_REQUEST['playlist_id'] ) ? (int) $_REQUEST['playlist_id'] : 0;
			
		if ( $user_id > 0 && $post_id > 0 && $playlist_id > 0 ) {
			$playlist_author = (int) get_term_meta( $playlist_id, 'playlist_author', true );

			if ( $user_id == $playlist_author ) {
				wp_remove_object_terms( $post_id, $playlist_id, 'aiovg_playlists' );
			} else {
				$response['status']  = 'error';
				$response['message'] = esc_html__( 'You do not have sufficient permissions to do this action.', 'all-in-one-video-gallery' );
			}
		}
		
		// Output
		echo wp_json_encode( $response );
		wp_die();
	}	

}

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
 * AIOVG_Premium_Admin_Automations class.
 *
 * @since 1.6.2
 */
class AIOVG_Premium_Admin_Automations {

	/**
	 * Add "Automations" menu.
	 *
	 * @since 1.6.5
	 */
	public function admin_menu() {	
		add_submenu_page(
			'all-in-one-video-gallery',
			__( 'All-in-One Video Gallery - Automations', 'all-in-one-video-gallery' ),
			__( 'Automations', 'all-in-one-video-gallery' ),
			'manage_aiovg_options',
			'edit.php?post_type=aiovg_automations'
		);	
	}

	/**
	 * Move "Automations" submenu under our plugin's main menu.
	 *
	 * @since  1.6.5
	 * @param  string $parent_file The parent file.
	 * @return string $parent_file The parent file.
	 */
	public function parent_file( $parent_file ) {	
		global $submenu_file, $current_screen;

		if ( 'aiovg_automations' == $current_screen->post_type ) {
			$submenu_file = 'edit.php?post_type=aiovg_automations';
			$parent_file  = 'all-in-one-video-gallery';
		}

		return $parent_file;
	}

	/**
     * Insert automations settings section.
     *
	 * @since  1.6.2
	 * @param  array $sections Core settings sections array.
     * @return array $sections Updated settings sections array.
     */
    public function custom_settings_section( $sections ) {	
		$sections[] = array(
			'id'          => 'aiovg_automations_settings',
			'title'       => __( 'Automations Settings', 'all-in-one-video-gallery' ),
			'description' => sprintf( 
				__( 'This feature also requires API keys from the services you import. Please configure your keys <a href="%s">here</a>.', 'all-in-one-video-gallery' ), 
				esc_url( admin_url( 'admin.php?page=aiovg_settings&tab=advanced&section=aiovg_api_settings' ) ) 
			),
			'tab'         => 'advanced',
			'page'        => 'aiovg_automations_settings'
		);
		
		return $sections;	
	}

	/**
     * Insert automations settings fields.
     *
	 * @since  1.6.2
	 * @param  array $fields Core settings fields array.
     * @return array $fields Updated settings fields array.
     */
    public function custom_settings_fields( $fields ) {	
		$fields['aiovg_automations_settings'] = array(
			array(
				'name'              => 'is_fast_mode',
				'label'             => __( 'Enable Fast Mode', 'all-in-one-video-gallery' ),
				'description'       => __( 'Increase speed by disabling do_action calls in wp_insert_post during import', 'all-in-one-video-gallery' ) . '<br><br>' .
					'<span class="description">' . __( "NOTE: This option is for advanced users with knowledge of WordPress development. Your theme or plugins may require these calls when posts are created. Next action will be disabled: 'transition_post_status', 'save_post', 'pre_post_update', 'add_attachment', 'edit_attachment', 'edit_post', 'post_updated', 'wp_insert_post'. Verify your created posts work properly if you check this box.", 'all-in-one-video-gallery' ) . '</span>',
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			)
		);
		
		return $fields;	
	}

	/**
	 * Register the custom post type "aiovg_automations".
	 *
	 * @since 1.6.2
	 */
	public function register_post_type() {		
		$labels = array(
			'name'                => _x( 'Automations', 'Post Type General Name', 'all-in-one-video-gallery' ),
			'singular_name'       => _x( 'Automation', 'Post Type Singular Name', 'all-in-one-video-gallery' ),
			'menu_name'           => __( 'Automations', 'all-in-one-video-gallery' ),
			'name_admin_bar'      => __( 'Automation', 'all-in-one-video-gallery' ),
			'all_items'           => __( 'Automations', 'all-in-one-video-gallery' ),
			'add_new_item'        => __( 'Add New Import', 'all-in-one-video-gallery' ),
			'add_new'             => __( 'Add New', 'all-in-one-video-gallery' ),
			'new_item'            => __( 'New Import', 'all-in-one-video-gallery' ),
			'edit_item'           => __( 'Edit Import', 'all-in-one-video-gallery' ),
			'update_item'         => __( 'Update Import', 'all-in-one-video-gallery' ),
			'view_item'           => __( 'View Import', 'all-in-one-video-gallery' ),
			'search_items'        => __( 'Search Imports', 'all-in-one-video-gallery' ),
			'not_found'           => __( 'No imports found', 'all-in-one-video-gallery' ),
			'not_found_in_trash'  => __( 'No imports found in Trash', 'all-in-one-video-gallery' ),
		);	
		
		$args = array(
			'label'                => __( 'Automations', 'all-in-one-video-gallery' ),
			'description'          => __( 'Automations Description', 'all-in-one-video-gallery' ),
			'labels'               => $labels,
			'supports'             => array( 'title' ),
			'taxonomies'           => array(),
			'hierarchical'         => false,
			'public'               => true,
			'show_ui'              => true,
			'show_in_menu'         => false,
			'show_in_admin_bar'    => true,
			'show_in_nav_menus'    => false,
			'show_in_rest'         => false,
			'can_export'           => true,
			'has_archive'          => false,
			'rewrite'              => false, 		
			'exclude_from_search'  => true,
			'publicly_queryable'   => true,
			'capability_type'      => 'aiovg_video',
			'map_meta_cap'          => true
		);
		
		register_post_type( 'aiovg_automations', $args );	
	}

	/**
	 * Replace the default "Enter title here" placeholder text in the title input box.
	 *
	 * @since  1.6.2
	 * @param  string $text Placeholder text. Default 'Enter title here'.
	 * @return string $text Custom placeholder text.
	 */	
	public function change_default_title( $text ) {	
    	$screen = get_current_screen();
	
    	if ( 'aiovg_automations' == $screen->post_type ) {
        	$text = __( 'Enter your import title', 'all-in-one-video-gallery' );
    	}
	
    	return $text;	
	}	

	/**
	 * Regiser custom videos filter.
	 *
	 * @since  1.6.2
	 * @param  array $filters Array of custom filters.
	 * @return array $filters Filtered array of custom filters.
	 */
	public function register_videos_custom_filter( $filters ) {
		$filters['imported'] = __( 'Imported Only', 'all-in-one-video-gallery' );
		return $filters;
	}

	/**
	 * Add custom filter options.
	 *
	 * @since 1.6.2
	 */
	public function restrict_manage_posts() {	
		global $typenow;
		
		// Custom Post Type "aiovg_automations"
		if ( 'aiovg_automations' == $typenow ) {			
			// Restrict by category
        	wp_dropdown_categories(array(
            	'show_option_none'  =>  __( 'All Categories', 'all-in-one-video-gallery' ),
				'option_none_value' => 0,
            	'taxonomy'          => 'aiovg_categories',
            	'name'              => 'catid',
            	'orderby'           => 'name',
            	'selected'          => isset( $_GET['catid'] ) ? (int) $_GET['catid'] : 0,
            	'hierarchical'      => true,
            	'depth'             => 3,
            	'show_count'        => false,
            	'hide_empty'        => false,
        	));		
		}
		
		// Custom Post Type "aiovg_videos"
		if ( 'aiovg_videos' == $typenow ) {			
			if ( isset( $_GET['aiovg_filter'] ) && 'imported' == $_GET['aiovg_filter'] ) {
				// Set import id
				if ( isset( $_GET['import_id'] ) ) {
					printf( '<input type="hidden" name="import_id" value="%d" />', (int) $_GET['import_id'] );
				}

				// Set import key
				if ( isset( $_GET['import_key'] ) ) {
					printf( '<input type="hidden" name="import_key" value="%s" />', sanitize_text_field( $_GET['import_key'] ) );
				}				
			}	
    	}	
	}

	/**
	 * Filter imports by categories.
	 *
	 * @since 1.6.2
	 * @param WP_Query $query WordPress Query object
	 */
	public function parse_query( $query ) {	
		global $pagenow, $post_type;
		
    	if ( 'edit.php' == $pagenow ) {	
			// Custom Post Type "aiovg_automations"
			if ( 'aiovg_automations' == $post_type ) {
				$catid = isset( $_GET['catid'] ) ? (int) $_GET['catid'] : 0;

				if ( $catid > 0 ) {		
					$query->query_vars['meta_key']     = 'video_categories';
					$query->query_vars['meta_value']   = serialize( strval( $catid ) );
					$query->query_vars['meta_compare'] = 'LIKE';			
				}
			}
			
			// Custom Post Type "aiovg_videos"
			if ( 'aiovg_videos' == $post_type ) {
				if ( isset( $_GET['aiovg_filter'] ) && 'imported' == $_GET['aiovg_filter'] ) {
					// Set import id	
					if ( isset( $_GET['import_id'] ) ) {
						$query->query_vars['meta_query']['import_id'] = array(
							'key'   => 'import_id',
							'value' => (int) $_GET['import_id']
						);		
					} else {
						$query->query_vars['meta_query']['import_id'] = array(
							'key'     => 'import_id',
							'compare' => 'EXISTS'
						);
					}
					
					// Set import key
					if ( isset( $_GET['import_key'] ) ) {
						$query->query_vars['meta_query']['import_key'] = array(
							'key'   => 'import_key',
							'value' => sanitize_text_field( $_GET['import_key'] )
						);
					}
				}
			}
    	}	
	}

	/**
	 * Retrieve the "aiovg_automations" custom post type table columns.
	 *
	 * @since  1.6.2
	 * @param  array $columns Array of default table columns.
	 * @return array $columns Updated list of table columns.
	 */
	public function get_columns( $columns ) {		
		$new_columns = array(
			'source'           => __( 'Source', 'all-in-one-video-gallery' ),
			'video_categories' => __( 'Video Categories', 'all-in-one-video-gallery' ),
			'video_tags'       => __( 'Video Tags', 'all-in-one-video-gallery' ),
			'statistics'       => __( 'Stats', 'all-in-one-video-gallery' )
		);		
		$columns = aiovg_insert_array_after( 'title', $columns, $new_columns );
		
		return $columns;		
	}

	/**
	 * Renders the "aiovg_automations" custom post type table columns.
	 *
	 * @since 1.6.2
	 * @param string $column  The name of the column.
	 * @param string $post_id Post ID.
	 */
	public function custom_column_content( $column, $post_id ) {	
		switch ( $column ) {
			case 'source':
				$services = aiovg_premium_get_automations_services();
				$service = get_post_meta( $post_id, 'service', true );

				$types = aiovg_premium_get_automations_types( $service );				
				$type = get_post_meta( $post_id, 'type', true );

				if ( ! empty( $service ) ) {
					echo $services[ $service ] . ' / ' . $types[ $type ];
				} else {
					echo '—';
				}				
				break;
			case 'video_categories':
				$video_categories = get_post_meta( $post_id, 'video_categories', true );

				if ( ! empty( $video_categories ) ) {
					$links = array();

					foreach ( $video_categories as $catid ) {
						$term = get_term_by( 'id', (int) $catid, 'aiovg_categories' );
						if ( $term ) {
							$url = admin_url( 'edit.php?post_type=aiovg_videos&aiovg_categories=' . $term->term_id . '&aiovg_filter=imported&import_id=' . $post_id );
							$links[] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), $term->name );
						}						
					}

					echo implode( ", ", $links );
				} else {
					echo '—';
				}			
				break;
			case 'video_tags':
				$video_tags = get_post_meta( $post_id, 'video_tags', true );

				if ( ! empty( $video_tags ) ) {
					$links = array();

					foreach ( $video_tags as $tagid ) {
						$term = get_term_by( 'id', (int) $tagid, 'aiovg_tags' );
						if ( $term ) {
							$url = admin_url( 'edit.php?post_type=aiovg_videos&aiovg_tags=' . $term->term_id . '&aiovg_filter=imported&import_id=' . $post_id );
							$links[] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), $term->name );
						}						
					}

					echo implode( ", ", $links );
				} else {
					echo '—';
				}			
				break;
			case 'statistics':
				$import_status = get_post_meta( $post_id, 'import_status', true );

				if ( ! empty( $import_status ) ) {
					$schedule             = get_post_meta( $post_id, 'schedule', true );
					$import_statistics    = get_post_meta( $post_id, 'import_statistics', true );
					$import_next_schedule = get_post_meta( $post_id, 'import_next_schedule', true );

					$scheduled_status = '';
					if ( (int) $schedule > 0 ) {
						if ( 'publish' == get_post_status( $post_id ) || 'completed' == $import_status ) {
							$scheduled_status = aiovg_premium_get_import_status_text( $import_status );
						} else {
							$scheduled_status = __( 'Paused', 'all-in-one-video-gallery' );
						}
					}					

					$videos_imported = 0;
					foreach ( $import_statistics['data'] as $data ) {
						$videos_imported += (int) $data['imported'];
					}

					if ( ! empty( $import_next_schedule ) ) {
						$import_next_schedule = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $import_next_schedule ) );
					} else {
						$import_next_schedule = '—';
					}

					// Status
					printf( 
						'<strong>%s</strong>: %s<br />- <a href="%s">%d %s</a><br /><strong>%s</strong>:<br />%s',
						__( 'Import status', 'all-in-one-video-gallery' ),
						$scheduled_status,
						admin_url( 'edit.php?post_type=aiovg_videos&aiovg_filter=imported&import_id=' . $post_id ),
						$videos_imported,
						__( 'videos imported', 'all-in-one-video-gallery' ),
						__( 'Next scheduled update', 'all-in-one-video-gallery' ),
						$import_next_schedule
					);
				} else {
					echo '—';
				}				
				break;		
		}		
	}

	/**
	 * Renders the "aiovg_videos" custom post type table columns.
	 *
	 * @since 1.6.2
	 * @param string $column  The name of the column.
	 * @param string $post_id Post ID.
	 */
	public function custom_column_content_videos( $column, $post_id ) {	
		switch ( $column ) {
			case 'misc':
				$import_id = get_post_meta( $post_id, 'import_id', true );
				$value = ! empty( $import_id ) ? 1 : 0;

				printf( 
					'<br /><span class="aiovg-import-meta">%s: %s</span>', 
					esc_html__( 'Imported', 'all-in-one-video-gallery' ),
					( 1 == $value ? '&#x2713;' : '&#x2717;' )
				);
				break;
		}		
	}

	/**
	 * Filters the array of row action links on the automations list table.
	 *
	 * @since  1.6.2
	 * @param  array   $actions An array of row action links.
	 * @param  WP_Post $post    WordPress Post object.
	 * @return array   $actions Filtered row action links.
	 */
	public function remove_row_actions( $actions, $post ) {
		if ( 'aiovg_automations' == $post->post_type ) {
			unset( $actions['view'] );
			unset( $actions['inline hide-if-no-js'] );
		}        
		
    	return $actions;
	}

	/**
	 * Add status messages in the "Publish" meta box.
	 *
	 * @since 1.6.2
	 */
	public function post_submitbox_misc_actions() {	
		global $post_type;
		
		if ( 'aiovg_automations' == $post_type ) {
			require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/automations-submitbox.php';
		}		
	}

	/**
	 * Register meta boxes.
	 *
	 * @since 1.6.2
	 */
	public function add_meta_boxes() {
		$automations_settings = get_option( 'aiovg_automations_settings' );

		add_meta_box( 
			'aiovg-automations-sources', 
			__( 'Configure Import Sources', 'all-in-one-video-gallery' ), 
			array( $this, 'display_meta_box_sources' ), 
			'aiovg_automations', 
			'normal', 
			'high' 
		);
		
		add_meta_box( 
			'aiovg-automations-video-settings', 
			__( 'Video Post Settings', 'all-in-one-video-gallery' ), 
			array( $this, 'display_meta_box_video_settings' ), 
			'aiovg_automations', 
			'normal', 
			'high' 
		);

		add_meta_box( 
			'aiovg-automations-statistics', 
			__( 'Import Stats', 'all-in-one-video-gallery' ), 
			array( $this, 'display_meta_box_statistics' ), 
			'aiovg_automations', 
			'side', 
			'low' 
		);
	}

	/**
	 * Display "Configure Import Sources" meta box.
	 *
	 * @since 1.6.2
	 * @param WP_Post $post WordPress Post object.
	 */
	public function display_meta_box_sources( $post ) {
		$_post_meta = get_post_meta( $post->ID );

		$post_meta = array(
			'service'            => isset( $_post_meta['service'] ) ? $_post_meta['service'][0] : 'youtube',
			'type'               => isset( $_post_meta['type'] ) ? $_post_meta['type'][0] : 'channel',	
			'category'           => isset( $_post_meta['category'] ) ? $_post_meta['category'][0] : '',		
			'channel'            => isset( $_post_meta['channel'] ) ? $_post_meta['channel'][0] : '',
			'folder'             => isset( $_post_meta['folder'] ) ? $_post_meta['folder'][0] : '',
			'group'              => isset( $_post_meta['group'] ) ? $_post_meta['group'][0] : '',
			'playlist'           => isset( $_post_meta['playlist'] ) ? $_post_meta['playlist'][0] : '',
			'portfolio'          => isset( $_post_meta['portfolio'] ) ? $_post_meta['portfolio'][0] : '',
			'search'             => isset( $_post_meta['search'] ) ? $_post_meta['search'][0] : '',
			'showcase'           => isset( $_post_meta['showcase'] ) ? $_post_meta['showcase'][0] : '',
			'username'           => isset( $_post_meta['username'] ) ? $_post_meta['username'][0] : '',
			'videos'             => isset( $_post_meta['videos'] ) ? $_post_meta['videos'][0] : '',
			'filter_tag'         => isset( $_post_meta['filter_tag'] ) ? $_post_meta['filter_tag'][0] : '',
			'filter_tag_exclude' => isset( $_post_meta['filter_tag_exclude'] ) ? $_post_meta['filter_tag_exclude'][0] : '',
			'include_subfolders' => isset( $_post_meta['include_subfolders'] ) ? $_post_meta['include_subfolders'][0] : 0,
			'exclude'            => isset( $_post_meta['exclude'] ) ? $_post_meta['exclude'][0] : '',
			'order'              => isset( $_post_meta['order'] ) ? $_post_meta['order'][0] : 'relevance',
			'limit'              => isset( $_post_meta['limit'] ) ? $_post_meta['limit'][0] : 50,
			'schedule'           => isset( $_post_meta['schedule'] ) ? $_post_meta['schedule'][0] : 0,
			'reschedule'         => isset( $_post_meta['reschedule'] ) ? $_post_meta['reschedule'][0] : 0,
			'import_status'      => isset( $_post_meta['import_status'] ) ? $_post_meta['import_status'][0] : ''
		);

		require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/automations-sources.php';
	}

	/**
	 * Display "Video Post Settings" meta box.
	 *
	 * @since 1.6.2
	 * @param WP_Post $post WordPress Post object.
	 */
	public function display_meta_box_video_settings( $post ) {
		$featured_images_settings = get_option( 'aiovg_featured_images_settings' );

		$_post_meta = get_post_meta( $post->ID );

		$post_meta = array(
			'video_title_format'       => isset( $_post_meta['video_title_format'] ) ? $_post_meta['video_title_format'][0] : '%%title%%',
			'video_description_format' => isset( $_post_meta['video_description_format'] ) ? $_post_meta['video_description_format'][0] : '%%description%%',
			'video_featured_image'     => isset( $_post_meta['video_featured_image'] ) ? $_post_meta['video_featured_image'][0] : 1,
			'video_categories'         => isset( $_post_meta['video_categories'] ) ? unserialize( $_post_meta['video_categories'][0] ) : array(),
			'video_tags'               => isset( $_post_meta['video_tags'] ) ? unserialize( $_post_meta['video_tags'][0] ) : array(),
			'video_date'               => isset( $_post_meta['video_date'] ) ? $_post_meta['video_date'][0] : 'original',
			'video_author'             => isset( $_post_meta['video_author'] ) ? $_post_meta['video_author'][0] : get_current_user_id(),
			'video_status'             => isset( $_post_meta['video_status'] ) ? $_post_meta['video_status'][0] : 'publish',
		);

		if ( isset( $_post_meta['video_description'] ) ) {
			$post_meta['video_description'] = $_post_meta['video_description'][0];
		} else {
			if ( isset( $_post_meta['video_status'] ) ) { // If the "video_status" exists, we are upgrading from versions less than 2.5.4
				$post_meta['video_description'] = 1;
			} else {
				$post_meta['video_description'] = 0;
			}
		}

		require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/automations-video-settings.php';
	}

	/**
	 * Display "Import Statistics" meta box.
	 *
	 * @since 1.6.2
	 * @param WP_Post $post WordPress Post object.
	 */
	public function display_meta_box_statistics( $post ) {
		$_post_meta = get_post_meta( $post->ID );

		$post_meta = array(	
			'post_id'              => $post->ID,	
			'schedule'             => isset( $_post_meta['schedule'] ) ? (int) $_post_meta['schedule'][0] : 0,
			'import_status'        => isset( $_post_meta['import_status'] ) ? $_post_meta['import_status'][0] : '',
			'scheduled_status'     => '',
			'import_next_schedule' => isset( $_post_meta['import_next_schedule'] ) ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $_post_meta['import_next_schedule'][0] ) ) : '—',
			'import_statistics'    => isset( $_post_meta['import_statistics'] ) ? unserialize( $_post_meta['import_statistics'][0] ) : array(),
			'imported'             => 0,
			'excluded'             => 0,
			'duplicates'           => 0,
		);

		if ( ! empty( $post_meta['import_status'] ) ) {
			if ( $post_meta['schedule'] > 0 ) {
				if ( 'publish' == $post->post_status || 'completed' == $post_meta['import_status'] ) {
					$post_meta['scheduled_status'] = aiovg_premium_get_import_status_text( $post_meta['import_status'] );
				} else {
					$post_meta['scheduled_status'] = __( 'Paused', 'all-in-one-video-gallery' );
				}
			}

			$post_meta['import_statistics']['data'] = array_reverse( $post_meta['import_statistics']['data'] );

			foreach ( $post_meta['import_statistics']['data'] as $data ) {
				$post_meta['imported']   += (int) $data['imported'];
				$post_meta['excluded']   += (int) $data['excluded']; 
				$post_meta['duplicates'] += (int) $data['duplicates']; 
			}
		}

		require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/automations-statistics.php';
	}

	/**
	 * Save meta data.
	 *
	 * @since  1.6.2
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post    The post object.
	 * @return int     $post_id If the save was successful or not.
	 */
	public function save_meta_data( $post_id, $post ) {	
		if ( ! isset( $_POST['post_type'] ) ) {
        	return $post_id;
    	}
	
		// Check this is the "aiovg_automations" custom post type
    	if ( 'aiovg_automations' != $post->post_type ) {
        	return $post_id;
    	}
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return $post_id;
		}
		
		// Check the logged in user has permission to edit this post
    	if ( ! aiovg_current_user_can( 'edit_aiovg_video', $post_id ) ) {
        	return $post_id;
    	}

		// Check if "aiovg_automations_sources_nonce" nonce is set
    	if ( isset( $_POST['aiovg_automations_sources_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['aiovg_automations_sources_nonce'], 'aiovg_save_automations_sources' ) ) {			
				// OK to save meta data
				$service = isset( $_POST['service'] ) ? sanitize_text_field( $_POST['service'] ) : 'youtube';
				update_post_meta( $post_id, 'service', $service );

				$post_arr = isset( $_POST[ $service ] ) ? $_POST[ $service ] : array();

				$type = isset( $post_arr['type'] ) ? sanitize_text_field( $post_arr['type'] ) : '';
				update_post_meta( $post_id, 'type', $type );

				$category = isset( $post_arr['category'] ) ? sanitize_text_field( $post_arr['category'] ) : '';
				update_post_meta( $post_id, 'category', $category );

				$channel = isset( $post_arr['channel'] ) ? sanitize_text_field( $post_arr['channel'] ) : '';
				update_post_meta( $post_id, 'channel', $channel );

				$folder = isset( $post_arr['folder'] ) ? sanitize_text_field( $post_arr['folder'] ) : '';
				update_post_meta( $post_id, 'folder', $folder );

				$group = isset( $post_arr['group'] ) ? sanitize_text_field( $post_arr['group'] ) : '';
				update_post_meta( $post_id, 'group', $group );

				$playlist = isset( $post_arr['playlist'] ) ? aiovg_sanitize_url( $post_arr['playlist'] ) : '';
				update_post_meta( $post_id, 'playlist', $playlist );

				$portfolio = isset( $post_arr['portfolio'] ) ? sanitize_text_field( $post_arr['portfolio'] ) : '';
				update_post_meta( $post_id, 'portfolio', $portfolio );

				$search = isset( $post_arr['search'] ) ? sanitize_text_field( $post_arr['search'] ) : '';
				update_post_meta( $post_id, 'search', $search );				

				$showcase = isset( $post_arr['showcase'] ) ? sanitize_text_field( $post_arr['showcase'] ) : '';
				update_post_meta( $post_id, 'showcase', $showcase );

				$username = isset( $post_arr['username'] ) ? sanitize_text_field( $post_arr['username'] ) : '';
				update_post_meta( $post_id, 'username', $username );

				$videos = isset( $post_arr['videos'] ) ? sanitize_textarea_field( $post_arr['videos'] ) : '';
				update_post_meta( $post_id, 'videos', $videos );				

				$filter_tag = isset( $post_arr['filter_tag'] ) ? sanitize_text_field( $post_arr['filter_tag'] ) : '';
				update_post_meta( $post_id, 'filter_tag', $filter_tag );

				$filter_tag_exclude = isset( $post_arr['filter_tag_exclude'] ) ? sanitize_text_field( $post_arr['filter_tag_exclude'] ) : '';
				update_post_meta( $post_id, 'filter_tag_exclude', $filter_tag_exclude );

				$include_subfolders = isset( $post_arr['include_subfolders'] ) ? (int) $post_arr['include_subfolders'] : 0;
				update_post_meta( $post_id, 'include_subfolders', $include_subfolders );
				
				$exclude = isset( $post_arr['exclude'] ) ? sanitize_textarea_field( $post_arr['exclude'] ) : '';
				update_post_meta( $post_id, 'exclude', $exclude );	
				
				$order = isset( $post_arr['order'] ) ? sanitize_text_field( $post_arr['order'] ) : 'relevance';
				update_post_meta( $post_id, 'order', $order );

				$limit = isset( $_POST['limit'] ) ? (int) $_POST['limit'] : 50;
				update_post_meta( $post_id, 'limit', $limit );

				$schedule = isset( $_POST['schedule'] ) ? (int) $_POST['schedule'] : 0;
				update_post_meta( $post_id, 'schedule', $schedule );

				$reschedule = isset( $_POST['reschedule'] ) ? (int) $_POST['reschedule'] : 0;
				update_post_meta( $post_id, 'reschedule', $reschedule );
			}			
		}
		
		// Check if "aiovg_automations_video_settings_nonce" nonce is set
    	if ( isset( $_POST['aiovg_automations_video_settings_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['aiovg_automations_video_settings_nonce'], 'aiovg_save_automations_video_settings' ) ) {			
				// OK to save meta data
				$video_title_format = isset( $_POST['video_title_format'] ) ? sanitize_text_field( $_POST['video_title_format'] ) : '';
				update_post_meta( $post_id, 'video_title_format', $video_title_format );

				$video_description = isset( $_POST['video_description'] ) ? (int) $_POST['video_description'] : 0;
				update_post_meta( $post_id, 'video_description', $video_description );

				$video_description_format = isset( $_POST['video_description_format'] ) ? wp_kses_post( $_POST['video_description_format'] ) : '';
				update_post_meta( $post_id, 'video_description_format', $video_description_format );
				
				$video_featured_image = isset( $_POST['video_featured_image'] ) ? (int) $_POST['video_featured_image'] : 0;
				update_post_meta( $post_id, 'video_featured_image', $video_featured_image );

				$video_categories = ! empty( $_POST['tax_input']['aiovg_categories'] ) ? array_map( 'intval', $_POST['tax_input']['aiovg_categories'] ) : array();
				update_post_meta( $post_id, 'video_categories', $video_categories );

				$video_tags = ! empty( $_POST['tags'] ) ? array_map( 'intval', $_POST['tags'] ) : array();
				update_post_meta( $post_id, 'video_tags', $video_tags );				
				
				$video_date = isset( $_POST['video_date'] ) ? sanitize_text_field( $_POST['video_date'] ) : 'original';
				update_post_meta( $post_id, 'video_date', $video_date );

				$video_author = isset( $_POST['video_author'] ) ? (int) $_POST['video_author'] : get_current_user_id();
				update_post_meta( $post_id, 'video_author', $video_author );

				$video_status = isset( $_POST['video_status'] ) ? sanitize_text_field( $_POST['video_status'] ) : 'pending';
				update_post_meta( $post_id, 'video_status', $video_status );

				// Ok to import videos
				if ( 'publish' == $post->post_status ) {
					$api = AIOVG_Premium_Public_Automations::get_instance();		
					$api->import( $post_id );	
				}
			}			
		}					
		
		return $post_id;	
	}

	/**
	 * Print footer scripts.
	 *
	 * @since 1.6.2
	 */
	public function print_footer_scripts() {
		global $pagenow, $typenow, $post;		
		
		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) || 'aiovg_automations' != $typenow ) {
			return;
		}

		if ( ! isset( $post ) ) {
			return;
		}

		$import_status = get_post_meta( (int) $post->ID, 'import_status', true );

		$i18n = array(
			'preview' => __( 'Test Run', 'all-in-one-video-gallery' ),
			'publish' => empty( $import_status ) ? __( 'Publish & Import', 'all-in-one-video-gallery' ) : __( 'Import Next Batch', 'all-in-one-video-gallery' )
		);

		// Ensure jQuery library loads
		wp_enqueue_script( 'jquery' );
		?>
		<script type="text/javascript">
			(function( $ ) {
				'use strict';
				
				$(function() {
					var aiovgL10n = <?php echo json_encode( $i18n ); ?>;
					
					$( '#post-preview' )
						.attr( 'href', '#aiovg-automations-preview-modal' )
						.addClass( 'aiovg-automations-preview' )
						.html( aiovgL10n.preview )
						.off( 'click' )
						.magnificPopup({
							type: 'inline'
						});
					
					$( '#publish' )
						.val( aiovgL10n.publish )
						.on( 'click', function() {
							$( '#aiovg-automations-misc-pub-notice' ).slideDown( 'fast' ).show();
						});
					
					$( '#submitdiv' ).show();
				});
			})( jQuery );
		</script>
		<?php
	}

	/**
	 * Perform a test import.
	 *
	 * @since 1.6.2
	 */
	public function ajax_callback_test_run() {	
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );

		$api = AIOVG_Premium_Public_Automations::get_instance();		
		$response = $api->test( $_POST );

		ob_start();
		require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/automations-test-screen.php';
		echo ob_get_clean();

		wp_die();	
	}	

}

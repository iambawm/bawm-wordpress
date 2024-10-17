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
 * AIOVG_Premium_Admin_Playlists class.
 *
 * @since 3.6.0
 */
class AIOVG_Premium_Admin_Playlists {	

	/**
	 * Add "Playlists" menu.
	 *
	 * @since 3.6.0
	 */
	public function admin_menu() {	
		add_submenu_page(
			'all-in-one-video-gallery',
			__( 'All-in-One Video Gallery - Playlists', 'all-in-one-video-gallery' ),
			__( 'Video Playlists', 'all-in-one-video-gallery' ),
			'manage_aiovg_options',
			'edit-tags.php?taxonomy=aiovg_playlists&post_type=aiovg_videos'
		);	
	}

	/**
	 * Move "Video Playlists" submenu under our plugin's main menu.
	 *
	 * @since  3.6.0
	 * @param  string $parent_file The parent file.
	 * @return string $parent_file The parent file.
	 */
	public function parent_file( $parent_file ) {	
		global $submenu_file, $current_screen;

		if ( 'aiovg_playlists' == $current_screen->taxonomy ) {
			$submenu_file = 'edit-tags.php?taxonomy=aiovg_playlists&post_type=aiovg_videos';
			$parent_file  = 'all-in-one-video-gallery';
		}

		return $parent_file;
	}

	/**
	 * Register the custom taxonomy "aiovg_playlists".
	 *
	 * @since 3.6.0
	 */
	public function register_taxonomy() {	
		$labels = array(
			'name'                       => _x( 'Video Playlists', 'Taxonomy General Name', 'all-in-one-video-gallery' ),
			'singular_name'              => _x( 'Playlist', 'Taxonomy Singular Name', 'all-in-one-video-gallery' ),
			'menu_name'                  => __( 'Video Playlists', 'all-in-one-video-gallery' ),
			'all_items'                  => __( 'All Playlists', 'all-in-one-video-gallery' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'new_item_name'              => __( 'New Playlist Name', 'all-in-one-video-gallery' ),
			'add_new_item'               => __( 'Add New Playlist', 'all-in-one-video-gallery' ),
			'edit_item'                  => __( 'Edit Playlist', 'all-in-one-video-gallery' ),
			'update_item'                => __( 'Update Playlist', 'all-in-one-video-gallery' ),
			'view_item'                  => __( 'View Playlist', 'all-in-one-video-gallery' ),
			'separate_items_with_commas' => __( 'Separate playlists with commas', 'all-in-one-video-gallery' ),
			'add_or_remove_items'        => __( 'Add or remove playlists', 'all-in-one-video-gallery' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'all-in-one-video-gallery' ),
			'popular_items'              => __( 'Popular Playlists', 'all-in-one-video-gallery' ),
			'search_items'               => __( 'Search Playlists', 'all-in-one-video-gallery' ),
			'not_found'                  => __( 'No playlists found', 'all-in-one-video-gallery' ),
			'no_terms'                   => __( 'No playlists', 'all-in-one-video-gallery' ),
			'back_to_items'              => __( '← Go to Playlists', 'all-in-one-video-gallery' ),
			'items_list'                 => __( 'Playlists list', 'all-in-one-video-gallery' ),
			'items_list_navigation'      => __( 'Playlists list navigation', 'all-in-one-video-gallery' ),
		);
		
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_in_quick_edit'         => false,
			'meta_box_cb'                => false,
			'show_admin_column'          => false,
			'show_in_nav_menus'          => false,
			'show_in_rest'               => false,
			'show_tagcloud'              => false,			
			'capabilities'               => array(
				'manage_terms' => 'manage_aiovg_options',
				'edit_terms'   => 'manage_aiovg_options',				
				'delete_terms' => 'manage_aiovg_options',
				'assign_terms' => 'edit_aiovg_videos'
			)
		);
		
		register_taxonomy( 'aiovg_playlists', array( 'aiovg_videos' ), $args );	
	}

	/**
	 * Display admin notices 
	 *
	 * @since 3.6.1
	 */
	public function admin_notices() {
		$screen = get_current_screen();

		if ( $screen->id !== 'edit-aiovg_playlists' ) {
			return false;
		}

		echo '<div class="notice notice-error">';
		echo '<p>' . __( 'Generally, playlists are created by users from the site\'s front end. So, kindly use this page only for informational purposes.', 'all-in-one-video-gallery' ) . '</p>';
		echo '<p>' . __( 'To avoid conflict with the playlist name created by another user, we prepend the user ID with the user-entered playlist name "<strong>[User ID] Playlist Name"</strong>. This allows the users to have a playlist name of their wish. However, the user will only see the actual playlist name he entered in the front end.', 'all-in-one-video-gallery' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Add custom form fields.
	 *
	 * @since 3.6.0
	 */
	public function add_form_fields() {	
		$form = 'add';
		require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/playlist-fields.php';		
	}

	/**
	 * Edit custom form fields.
	 *
	 * @since 3.6.0
	 * @param object $term Taxonomy term object.
	 */
	public function edit_form_fields( $term ) {		
		$form = 'edit';
		require_once AIOVG_PLUGIN_DIR . 'premium/admin/partials/playlist-fields.php';
	}

	/**
	 * Save custom form fields.
	 *
	 * @since 3.6.0
	 * @param int   $term_id Term ID.
	 */
	public function save_form_fields( $term_id ) {	
		// Check if "aiovg_playlist_fields_nonce" nonce is set
    	if ( isset( $_POST['aiovg_playlist_fields_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['aiovg_playlist_fields_nonce'], 'aiovg_save_playlist_fields' ) ) {			
				// OK to save meta data
				$playlist_author = isset( $_POST['playlist_author'] ) ? (int) $_POST['playlist_author'] : 0;
				update_term_meta( $term_id, 'playlist_author', $playlist_author );
			}
		}   
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since  3.6.0
	 * @param  array $columns Array of default table columns.
	 * @return array $columns Updated list of table columns.
	 */
	public function get_columns( $columns ) {	
		$columns['playlist_author'] = __( 'Author', 'all-in-one-video-gallery' );
    	return $columns;		
	}
	
	/**
	 * This function renders the custom columns in the list table.
	 *
	 * @since 3.6.0
	 * @param string $content Content of the column.
	 * @param string $column  Name of the column.
	 * @param string $term_id Term ID.
	 */
	public function custom_column_content( $content, $column, $term_id ) {		
		if ( 'playlist_author' == $column ) {
			$playlist_author = (int) get_term_meta( $term_id, 'playlist_author', true );
			$content = 'N/A';

			if ( ! empty( $playlist_author ) ) {
				if ( $user_info = get_userdata( $playlist_author ) ) {
					$content = $user_info->display_name;
				}
			}
    	}
		
		return $content;	
	}

}

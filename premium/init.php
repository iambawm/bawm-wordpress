<?php

/**
 * Initialize Premium Features.
 *
 * @link       https://plugins360.com
 * @since      1.5.7
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Init - The premium plugin class.
 *
 * @since 1.5.7
 */
class AIOVG_Premium_Init {

	/**
	 * Array of active plugins.
	 *
	 * @since  1.5.7
	 * @access protected
	 * @var    array
	 */
	protected $active_plugins;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.5.7
	 * @access protected
	 * @var    AIOVG_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Get things started.
	 *
	 * @since 1.5.7
	 */
	public function __construct() {
		$this->active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

		// The file that holds the general helper functions
		require_once AIOVG_PLUGIN_DIR . 'premium/includes/functions.php';		

		// Loader
		$this->loader = new AIOVG_Loader();
		
		// Admin
		if ( is_admin() ) {
			require_once AIOVG_PLUGIN_DIR . 'premium/admin/admin.php';

			$admin = new AIOVG_Premium_Admin();			

			$this->loader->add_action( 'admin_init', $admin, 'insert_missing_options', 2 );
			$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
			
			$this->loader->add_filter( 'aiovg_custom_pages_list', $admin, 'get_custom_pages_list' );
		}

		// Public
		require_once AIOVG_PLUGIN_DIR . 'premium/public/public.php';

		$public = new AIOVG_Premium_Public();

		$this->loader->add_action( 'init', $public, 'register_styles' );
		$this->loader->add_action( 'init', $public, 'register_scripts' );
		$this->loader->add_action( 'aiovg_enqueue_block_editor_assets', $public, 'enqueue_block_editor_assets', 99 );	

		// Modules
		$this->init_thumbnail_generator();
		$this->init_automations();				
		$this->init_popup_template();
		$this->init_slider_template();		
		$this->init_playlist_template();
		$this->init_compact_template();
		$this->init_user();
		$this->init_playlists();
		$this->init_seo();
		$this->init_multilingual();
		
		if ( aiovg_fs()->is_plan_or_trial( 'business' ) ) {
			$this->init_ads();			
		}
	}	
	
	/**
	 * Initialize thumbnail generator.
	 *
	 * @since  1.6.6
	 * @access private
	 */
	private function init_thumbnail_generator() {
		// Admin
		if ( is_admin() ) {
			require_once AIOVG_PLUGIN_DIR . 'premium/admin/thumbnail-generator.php';

			$admin_thumbnail_generator = new AIOVG_Premium_Admin_Thumbnail_Generator();

			$this->loader->add_action( 'aiovg_admin_after_image_field', $admin_thumbnail_generator, 'init_thumbnail_generator' );
			$this->loader->add_action( 'wp_ajax_aiovg_ffmpeg_status', $admin_thumbnail_generator, 'ajax_callback_ffmpeg_status' );		

			$this->loader->add_filter( 'aiovg_settings_sections', $admin_thumbnail_generator, 'custom_settings_section' );
			$this->loader->add_filter( 'aiovg_settings_fields', $admin_thumbnail_generator, 'custom_settings_fields' );
		}

		// Public
		require_once AIOVG_PLUGIN_DIR . 'premium/public/thumbnail-generator.php';

		$public_thumbnail_generator = new AIOVG_Premium_Public_Thumbnail_Generator();
		
		$this->loader->add_action( 'wp_ajax_aiovg_upload_base64_image', $public_thumbnail_generator, 'ajax_callback_upload_base64_image' );
		$this->loader->add_action( 'wp_ajax_nopriv_aiovg_upload_base64_image', $public_thumbnail_generator, 'ajax_callback_upload_base64_image' );
		$this->loader->add_action( 'wp_ajax_aiovg_ffmpeg_generate_images', $public_thumbnail_generator, 'ajax_callback_ffmpeg_generate_images' );
		$this->loader->add_action( 'wp_ajax_nopriv_aiovg_ffmpeg_generate_images', $public_thumbnail_generator, 'ajax_callback_ffmpeg_generate_images' );
		$this->loader->add_action( 'save_post_aiovg_videos', $public_thumbnail_generator, 'save_image_meta', 11, 2 );
	}
	
	/**
	 * Initialize automations.
	 *
	 * @since  1.6.2
	 * @access private
	 */
	private function init_automations() {
		// Admin
		if ( is_admin() ) {
			require_once AIOVG_PLUGIN_DIR . 'premium/admin/automations.php';

			$admin_automations = new AIOVG_Premium_Admin_Automations();

			$this->loader->add_action( 'admin_menu', $admin_automations, 'admin_menu' );
			$this->loader->add_action( 'init', $admin_automations, 'register_post_type' );
			$this->loader->add_action( 'post_submitbox_misc_actions', $admin_automations, 'post_submitbox_misc_actions' );
			$this->loader->add_action( 'add_meta_boxes', $admin_automations, 'add_meta_boxes' );
			$this->loader->add_action( 'save_post', $admin_automations, 'save_meta_data', 10, 2 );
			$this->loader->add_action( 'restrict_manage_posts', $admin_automations, 'restrict_manage_posts' );
			$this->loader->add_action( 'manage_aiovg_automations_posts_custom_column', $admin_automations, 'custom_column_content', 10, 2 );
			$this->loader->add_action( 'manage_aiovg_videos_posts_custom_column', $admin_automations, 'custom_column_content_videos', 10, 2 );
			$this->loader->add_action( 'admin_print_footer_scripts', $admin_automations, 'print_footer_scripts', 99 );
			$this->loader->add_action( 'wp_ajax_aiovg_automations_test_run', $admin_automations, 'ajax_callback_test_run' );			
			
			$this->loader->add_filter( 'parent_file', $admin_automations, 'parent_file' );
			$this->loader->add_filter( 'aiovg_settings_sections', $admin_automations, 'custom_settings_section' );
			$this->loader->add_filter( 'aiovg_settings_fields', $admin_automations, 'custom_settings_fields' );
			$this->loader->add_filter( 'enter_title_here', $admin_automations, 'change_default_title' );
			$this->loader->add_filter( 'aiovg_admin_videos_custom_filters', $admin_automations, 'register_videos_custom_filter' );
			$this->loader->add_filter( 'parse_query', $admin_automations, 'parse_query', 11 );
			$this->loader->add_filter( 'manage_edit-aiovg_automations_columns', $admin_automations, 'get_columns' );
			$this->loader->add_filter( 'post_row_actions', $admin_automations, 'remove_row_actions', 10, 2 );
		}

		// Public
		require_once AIOVG_PLUGIN_DIR . 'premium/public/automations.php';

		$public_automations = AIOVG_Premium_Public_Automations::get_instance();

		$this->loader->add_action( 'wp', $public_automations, 'schedule_events' );
		$this->loader->add_action( 'aiovg_schedule_every_five_minutes', $public_automations, 'cron_event' );
		$this->loader->add_action( 'wp_trash_post', $public_automations, 'before_delete_post' );
		$this->loader->add_action( 'before_delete_post', $public_automations, 'before_delete_post' );

		$this->loader->add_filter( 'cron_schedules', $public_automations, 'cron_schedules' );
	}		

	/**
	 * Initialize the popup template.
	 *
	 * @since  1.5.7
	 * @access private
	 */
	private function init_popup_template() {
		if ( ! in_array( 'aiovg-popup/aiovg-popup.php', $this->active_plugins ) ) {
			// Public
			require_once AIOVG_PLUGIN_DIR . 'premium/public/popup.php';

			$public_popup = new AIOVG_Premium_Public_Popup();

			$this->loader->add_filter( 'aiovg_video_templates', $public_popup, 'register_popup_template' );
			$this->loader->add_filter( 'aiovg_load_template', $public_popup, 'load_template', 10, 2 );
			$this->loader->add_filter( 'shortcode_atts_aiovg_video', $public_popup, 'shortcode_atts_aiovg_video', 10, 3 );
		}
	}

	/**
	 * Initialize the slider template.
	 *
	 * @since  1.5.7
	 * @access private
	 */
	private function init_slider_template() {
		if ( ! in_array( 'aiovg-slider/aiovg-slider.php', $this->active_plugins ) ) {
			// Admin
			if ( is_admin() ) {
				require_once AIOVG_PLUGIN_DIR . 'premium/admin/slider.php';

				$admin_slider = new AIOVG_Premium_Admin_Slider();				
				$this->loader->add_filter( 'aiovg_settings_fields', $admin_slider, 'add_settings_fields' );				
			}

			// Public
			require_once AIOVG_PLUGIN_DIR . 'premium/public/slider.php';

			$public_slider = new AIOVG_Premium_Public_Slider();

			$this->loader->add_filter( 'aiovg_video_templates', $public_slider, 'register_slider_template' );
			$this->loader->add_filter( 'aiovg_shortcode_fields', $public_slider, 'register_shortcode_fields' );
			$this->loader->add_filter( 'aiovg_load_template', $public_slider, 'load_template', 10, 2 );
		}
	}

	/**
	 * Initialize the playlist template.
	 *
	 * @since  3.3.1
	 * @access private
	 */
	private function init_playlist_template() {
		// Admin
		if ( is_admin() ) {
			require_once AIOVG_PLUGIN_DIR . 'premium/admin/playlist.php';

			$admin_playlist = new AIOVG_Premium_Admin_Playlist();				
			$this->loader->add_filter( 'aiovg_settings_fields', $admin_playlist, 'add_settings_fields' );				
		}

		// Public
		require_once AIOVG_PLUGIN_DIR . 'premium/public/playlist.php';

		$public_playlist = new AIOVG_Premium_Public_Playlist();

		$this->loader->add_filter( 'aiovg_video_templates', $public_playlist, 'register_playlist_template' );
		$this->loader->add_filter( 'aiovg_shortcode_fields', $public_playlist, 'register_shortcode_fields' );
		$this->loader->add_filter( 'aiovg_load_template', $public_playlist, 'load_template', 10, 2 );
	}

	/**
	 * Initialize the compact template.
	 *
	 * @since  3.3.1
	 * @access private
	 */
	private function init_compact_template() {
		// Public
		require_once AIOVG_PLUGIN_DIR . 'premium/public/compact.php';

		$public_compact = new AIOVG_Premium_Public_Compact();

		$this->loader->add_filter( 'aiovg_video_templates', $public_compact, 'register_compact_template' );
		$this->loader->add_filter( 'aiovg_shortcode_fields', $public_compact, 'register_shortcode_fields' );
		$this->loader->add_filter( 'aiovg_load_template', $public_compact, 'load_template', 10, 2 );
	}

	/**
	 * Initialize user functionalities (Front-end video submission).
	 *
	 * @since  1.6.1
	 * @access private
	 */
	private function init_user() {
		// Admin
		if ( is_admin() ) {
			require_once AIOVG_PLUGIN_DIR . 'premium/admin/user.php';

			$admin_user = new AIOVG_Premium_Admin_User();

			$this->loader->add_action( 'transition_post_status', $admin_user, 'transition_post_status', 10, 3 );

			$this->loader->add_filter( 'aiovg_settings_tabs', $admin_user, 'custom_settings_tab' );
			$this->loader->add_filter( 'aiovg_settings_sections', $admin_user, 'custom_settings_sections' );
			$this->loader->add_filter( 'aiovg_settings_fields', $admin_user, 'custom_settings_fields' );
			$this->loader->add_action( 'aiovg_categories_add_form_fields', $admin_user, 'add_category_form_fields' );		
			$this->loader->add_action( 'aiovg_categories_edit_form_fields', $admin_user, 'edit_category_form_fields' );
			$this->loader->add_action( 'created_aiovg_categories', $admin_user, 'save_category_form_fields' );
			$this->loader->add_action( 'edited_aiovg_categories', $admin_user, 'save_category_form_fields' );
		}		
		
		// Public
		require_once AIOVG_PLUGIN_DIR . 'premium/public/user.php';

		$public_user = new AIOVG_Premium_Public_User();
		
		$this->loader->add_action( 'wp_loaded', $public_user, 'wp_loaded' );
		$this->loader->add_action( 'aiovg_video_form_fields', $public_user, 'add_honeypot_field' );
		$this->loader->add_action( 'wp_ajax_aiovg_public_upload_media', $public_user, 'ajax_callback_upload_media', 10, 2 );
		$this->loader->add_action( 'wp_ajax_nopriv_aiovg_public_upload_media', $public_user, 'ajax_callback_upload_media', 10, 2 );
		$this->loader->add_action( 'wp_ajax_aiovg_public_get_magic_field', $public_user, 'ajax_callback_get_honeypot_field' );
		$this->loader->add_action( 'wp_ajax_nopriv_aiovg_public_get_magic_field', $public_user, 'ajax_callback_get_honeypot_field' );
	}

	/**
	 * Initialize user playlists / favorites.
	 *
	 * @since  3.6.0
	 * @access private
	 */
	private function init_playlists() {
		$playlists_settings = get_option( 'aiovg_playlists_settings', array() );

		// Admin
		require_once AIOVG_PLUGIN_DIR . 'premium/admin/playlists.php';

		$admin_playlists = new AIOVG_Premium_Admin_Playlists();	
		
		if ( ! empty( $playlists_settings ) && ( isset( $playlists_settings['admin_menu'] ) && 1 == $playlists_settings['admin_menu'] ) ) {
			$this->loader->add_action( 'admin_menu', $admin_playlists, 'admin_menu' );
			$this->loader->add_action( 'admin_notices', $admin_playlists, 'admin_notices' );
		}

		$this->loader->add_action( 'init', $admin_playlists, 'register_taxonomy' );		
		$this->loader->add_action( 'aiovg_playlists_add_form_fields', $admin_playlists, 'add_form_fields' );
		$this->loader->add_action( 'aiovg_playlists_edit_form_fields', $admin_playlists, 'edit_form_fields' );
		$this->loader->add_action( 'created_aiovg_playlists', $admin_playlists, 'save_form_fields' );
		$this->loader->add_action( 'edited_aiovg_playlists', $admin_playlists, 'save_form_fields' );	

		$this->loader->add_filter( 'parent_file', $admin_playlists, 'parent_file' );		
		$this->loader->add_filter( 'manage_edit-aiovg_playlists_columns', $admin_playlists, 'get_columns' );
		$this->loader->add_filter( 'manage_edit-aiovg_playlists_sortable_columns', $admin_playlists, 'get_columns' );
		$this->loader->add_filter( 'manage_aiovg_playlists_custom_column', $admin_playlists, 'custom_column_content', 10, 3 );

		// Public
		if ( ! empty( $playlists_settings ) && 1 == $playlists_settings['enabled'] ) {
			require_once AIOVG_PLUGIN_DIR . 'premium/public/playlists.php';

			$public_playlists = new AIOVG_Premium_Public_Playlists();			
			
			$this->loader->add_action( 'wp_loaded', $public_playlists, 'wp_loaded' );			
			$this->loader->add_action( 'wp_ajax_aiovg_get_playlists_info', $public_playlists, 'ajax_callback_get_playlists_info' );
			$this->loader->add_action( 'wp_ajax_nopriv_aiovg_get_playlists_info', $public_playlists, 'ajax_callback_get_playlists_info' );
			$this->loader->add_action( 'wp_ajax_aiovg_create_playlist', $public_playlists, 'ajax_callback_create_playlist' );
			$this->loader->add_action( 'wp_ajax_nopriv_aiovg_create_playlist', $public_playlists, 'ajax_callback_create_playlist' );
			$this->loader->add_action( 'wp_ajax_aiovg_update_playlist', $public_playlists, 'ajax_callback_update_playlist' );
			$this->loader->add_action( 'wp_ajax_nopriv_aiovg_update_playlist', $public_playlists, 'ajax_callback_update_playlist' );
			$this->loader->add_action( 'wp_ajax_aiovg_add_to_playlist', $public_playlists, 'ajax_callback_add_to_playlist' );
			$this->loader->add_action( 'wp_ajax_nopriv_aiovg_add_to_playlist', $public_playlists, 'ajax_callback_add_to_playlist' );
			$this->loader->add_action( 'wp_ajax_aiovg_remove_from_playlist', $public_playlists, 'ajax_callback_remove_from_playlist' );
			$this->loader->add_action( 'wp_ajax_nopriv_aiovg_remove_from_playlist', $public_playlists, 'ajax_callback_remove_from_playlist' );
		
			$this->loader->add_filter( 'the_title', $public_playlists, 'the_title', 999, 2 );
			$this->loader->add_filter( 'single_post_title', $public_playlists, 'the_title', 99 );
			$this->loader->add_filter( 'aiovg_content_after_player', $public_playlists, 'add_to_playlist_button', 10, 3 );
			$this->loader->add_filter( 'aiovg_content_after_thumbnail', $public_playlists, 'remove_from_playlist_button', 10, 2 );	
		}
	}

	/**
	 * Initialize premium SEO options.
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function init_seo() {
		// Admin
		if ( is_admin() ) {
			require_once AIOVG_PLUGIN_DIR . 'premium/admin/seo.php';

			$admin_seo = new AIOVG_Premium_Admin_SEO();

			$this->loader->add_filter( 'aiovg_settings_sections', $admin_seo, 'custom_settings_section' );
			$this->loader->add_filter( 'aiovg_settings_fields', $admin_seo, 'custom_settings_fields' );				
		}

		// Public
		require_once AIOVG_PLUGIN_DIR . 'premium/public/seo.php';

		$public_seo = new AIOVG_Premium_Public_SEO();

		$this->loader->add_filter( 'aiovg_player_html', $public_seo, 'player_html', 10, 2 );
	}

	/**
	 * Initialize hooks that make the plugin multilingual.
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function init_multilingual() {
		require_once AIOVG_PLUGIN_DIR . 'premium/public/multilingual.php';

		$public_multilingual = new AIOVG_Premium_Public_Multilingual();

		$this->loader->add_filter( 'aiovg_video_imported', $public_multilingual, 'set_video_language_for_wpml', 10, 2 );
		$this->loader->add_filter( 'aiovg_video_imported', $public_multilingual, 'set_video_language_for_polylang', 10, 2 );
	}

	/**
	 * Initialize Ads.
	 *
	 * @since  1.5.7
	 * @access private
	 */
	private function init_ads() {
		if ( ! in_array( 'wp-video-monetize/wp-video-monetize.php', $this->active_plugins ) ) {
			$ads_settings = get_option( 'aiovg_ads_settings', array() );

			// Admin
			if ( is_admin() ) {
				require_once AIOVG_PLUGIN_DIR . 'premium/admin/ads.php';

				$admin_ads = new AIOVG_Premium_Admin_Ads();

				if ( ! empty( $ads_settings ) && 1 == $ads_settings['enable_ads'] ) {
					$this->loader->add_action( 'add_meta_boxes', $admin_ads, 'add_meta_boxes' );
					$this->loader->add_action( 'save_post', $admin_ads, 'save_meta_data', 10, 2 );
				}

				$this->loader->add_filter( 'aiovg_settings_tabs', $admin_ads, 'custom_settings_tab' );
				$this->loader->add_filter( 'aiovg_settings_sections', $admin_ads, 'custom_settings_section' );
				$this->loader->add_filter( 'aiovg_settings_fields', $admin_ads, 'custom_settings_fields' );
			}

			// Public
			if ( ! empty( $ads_settings ) && 1 == $ads_settings['enable_ads'] ) {				
				require_once AIOVG_PLUGIN_DIR . 'premium/public/ads.php';
				require_once AIOVG_PLUGIN_DIR . 'premium/widgets/companion.php';

				$public_ads = new AIOVG_Premium_Public_Ads();

				$this->loader->add_action( 'aiovg_videojs_player_scripts', $public_ads, 'videojs_player_scripts' );
				$this->loader->add_action( 'aiovg_iframe_videojs_player_head', $public_ads, 'iframe_videojs_player_styles' );
				$this->loader->add_action( 'aiovg_iframe_videojs_player_footer', $public_ads, 'iframe_videojs_player_scripts' );
				$this->loader->add_action( 'aiovg_iframe_vidstack_player_footer', $public_ads, 'iframe_vidstack_player_scripts' );
				$this->loader->add_action( 'wp_print_scripts', $public_ads, 'wp_print_scripts' );		
				$this->loader->add_action( 'wp_print_footer_scripts', $public_ads, 'wp_print_footer_scripts', 99 );		
				
				$this->loader->add_filter( 'aiovg_videojs_player_settings', $public_ads, 'videojs_player_settings', 10, 2 );
				$this->loader->add_filter( 'aiovg_vidstack_player_settings', $public_ads, 'vidstack_player_settings', 10, 2 );
				$this->loader->add_filter( 'aiovg_iframe_videojs_player_settings', $public_ads, 'iframe_videojs_player_settings' );
				$this->loader->add_filter( 'aiovg_iframe_vidstack_player_settings', $public_ads, 'iframe_vidstack_player_settings' );
				$this->loader->add_filter( 'aiovg_player_page_url', $public_ads, 'player_page_url', 10, 3 );

				// Widgets
				$this->loader->add_action( 'widgets_init', $this, 'register_widgets' );
			}
		}
	}
	
	/**
	 * Register widgets.
	 *
	 * @since 1.5.7
	 */
	public function register_widgets() {
		register_widget( 'AIOVG_Premium_Widget_Companion' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.5.7
	 */
	public function run() {
		$this->loader->run();
	}

}

<?php

/**
 * Thumbnail Generator.
 *
 * @link       https://plugins360.com
 * @since      1.6.6
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Admin_Thumbnail_Generator class.
 *
 * @since 1.6.6
 */
class AIOVG_Premium_Admin_Thumbnail_Generator {

	/**
     * Insert thumbnail generator settings section.
     *
	 * @since  1.6.6
	 * @param  array $sections Core settings sections array.
     * @return array $sections Updated settings sections array.
     */
    public function custom_settings_section( $sections ) {	
		$sections[] = array(
			'id'          => 'aiovg_thumbnail_generator_settings',
			'title'       => __( 'Auto Thumbnail Generator', 'all-in-one-video-gallery' ),
			'description' => '',
			'tab'         => 'advanced',
			'page'        => 'aiovg_thumbnail_generator_settings'
		);
		
		return $sections;	
	}

	/**
     * Insert thumbnail generator settings fields.
     *
	 * @since  1.6.6
	 * @param  array $fields Core settings fields array.
     * @return array $fields Updated settings fields array.
     */
    public function custom_settings_fields( $fields ) {	
		$fields['aiovg_thumbnail_generator_settings'] = array(
			array(
				'name'              => 'enable_html5_thumbnail_generator',
				'label'             => __( '"Capture Image" button', 'all-in-one-video-gallery' ),
				'description'       => __( 'Check this option to enable HTML5 browser-based thumbnail generation feature in the front-end video form', 'all-in-one-video-gallery' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'ffmpeg_path',
				'label'             => __( 'FFMPEG Path', 'all-in-one-video-gallery' ),
				'description'       => sprintf( 
					__( '<a href="%s" target="_blank">FFMPEG</a> is a free and open-source library/tool that is required to auto-generate images from your videos. Your server may already have FFMPEG installed. Simply contact your hosting provider and ask them the application path and add to this field.', 'all-in-one-video-gallery' ), 
					'https://ffmpeg.org/download.html' 
				) . '<br><br>' . __( 'If your server does not support FFMPEG and you want to install it into the server, you need root access. If you are in a dedicated/VPS server you can install it using the root. In the case of shared hosting, you need to contact your hosting provider for installing FFMPEG.', 'all-in-one-video-gallery' ),
				'type'              => 'text',
				'callback'          => array( $this, 'callback_ffmpeg_path' ),
				'sanitize_callback' => 'sanitize_text_field'
			),
			array(
				'name'              => 'ffmpeg_images_count',
				'label'             => __( 'Number of images to auto-generate', 'all-in-one-video-gallery' ),
				'description'       => __( 'Accepted values are [0 - 10]. Leave empty or add "0" to not generate any image.', 'all-in-one-video-gallery' ),
				'type'              => 'number',
				'placeholder'       => '10',
				'sanitize_callback' => 'intval'
			)
		);
		
		return $fields;	
	}

	/**
     * Displays the ffmpeg path field.
     *
	 * @since 1.6.6
     * @param array $args Settings field args.
     */
    public function callback_ffmpeg_path( $args ) {
		$options = get_option( $args['section'] );
		$option  = $args['id'];

		$value = '';
		if ( ! empty( $options[ $option ] ) ) {
            $value = $options[ $option ];
        }

        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $type        = isset( $args['type'] ) ? $args['type'] : 'text';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
		$label       = __( 'Test FFMPEG', 'all-in-one-video-gallery' );
		
		$html        = '<div class="aiovg-flex aiovg-flex-wrap aiovg-gap-1 aiovg-items-center">';
		$html       .= '<div class="aiovg-flex aiovg-gap-1 aiovg-items-center">';
        $html       .= sprintf( '<input type="%1$s" class="%2$s-text" id="aiovg-ffmpeg-path" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder );
		$html       .= '<input type="button" id="aiovg-test-ffmpeg-button" class="aiovg-no-margin button" value="' . $label . '" />';
		$html       .= '</div>';
		$html       .= '<span id="aiovg-ffmpeg-status" class="aiovg-ajax-status"></span>';
		$html       .= '</div>';
		$html       .= sprintf( '<p class="description">%s</p>', $args['description'] );
		
        echo $html;		
	}
	
	/**
	 * Init Thumbnail Generator.
	 *
	 * @since 1.6.6
	 */
	public function init_thumbnail_generator() {
		the_aiovg_premium_thumbnail_generator(); 
	}
	
	/**
	 * Check FFMPEG status.
	 *
	 * @since 1.6.6
	 */
	public function ajax_callback_ffmpeg_status() {
		check_ajax_referer( 'aiovg_ajax_nonce', 'security' );

		$data = array(
			'status'  => '',
			'message' => ''
		);

		if ( aiovg_premium_is_exec_available() ) {
			if ( ! empty( $_POST['ffmpeg_path'] ) ) {
				$ffmpeg_path = stripslashes( $_POST['ffmpeg_path'] );
				$command = sanitize_text_field( $ffmpeg_path ) . ' -version';			
				exec( $command, $output );
	
				if ( ! empty( $output ) ) {
					$data['status']  = 'success';
					$data['message'] = __( 'Congrats, FFMPEG is active in your server.', 'all-in-one-video-gallery' );
				} else {
					$data['status']  = 'error';
					$data['message'] = __( 'FFMPEG not found in the given path. Please contact your hosting provider.', 'all-in-one-video-gallery' );
				}
			} else {
				$data['status']  = 'error';
				$data['message'] = __( 'FFMPEG path is empty.', 'all-in-one-video-gallery' );
			}
		} else {
			$data['status']  = 'error';
			$data['message'] = __( 'exec() is disabled in PHP settings. Please contact your hosting provider.', 'all-in-one-video-gallery' );
		}		

		echo wp_json_encode( $data );
		wp_die();
	}	

}

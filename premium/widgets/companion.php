<?php

/**
 * Companion Ads widget.
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
 * AIOVG_Premium_Widget_Companion class.
 *
 * @since 1.5.7
 */
class AIOVG_Premium_Widget_Companion extends WP_Widget {
	
	/**
     * Unique identifier for the widget.
     *
     * @since  1.5.7
	 * @access protected
     * @var    string
     */
    protected $widget_slug;
	
	/**
	 * Get things started.
	 *
	 * @since 1.5.7
	 */
	public function __construct() {		
		$this->widget_slug = 'aiovg-premium-widget-companion';
		
		parent::__construct(
			$this->widget_slug,
			__( 'AIOVG - Companion Ads', 'all-in-one-video-gallery' ),
			array(
				'classname'   => $this->widget_slug,
				'description' => __( 'Display a companion ad.', 'all-in-one-video-gallery' )
			)
		);	
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @since 1.5.7
	 * @param array $args	  The array of form elements.
	 * @param array $instance The current instance of the widget.
	 */
	public function widget( $args, $instance ) {	
		if ( ! empty( $instance['width'] ) && ! empty( $instance['height'] ) ) {
			$attributes = array();			
			$attributes[] = 'width="' . $instance['width'] . '"';			
			$attributes[] = 'height="' . $instance['height'] . '"';
			
			if ( ! empty( $instance['ad_unit_path'] ) ) {
				$attributes[] = 'ad_unit_path="' . $instance['ad_unit_path'] . '"';
			}
					
			echo $args['before_widget'];
			echo do_shortcode( '[companion ' . implode( ' ', $attributes ) . ']' );	
			echo $args['after_widget'];			
		}
	}
	
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @since 1.5.7
	 * @param array $new_instance The new instance of values to be generated via the update.
	 * @param array $old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['width']        = ! empty( $new_instance['width'] ) ? (int) $new_instance['width'] : '';
		$instance['height']       = ! empty( $new_instance['height'] ) ? (int) $new_instance['height'] : '';
		$instance['ad_unit_path'] = ! empty( $new_instance['ad_unit_path'] ) ? sanitize_text_field( $new_instance['ad_unit_path'] ) : '';
		
		return $instance;
	}
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @since 1.5.7
	 * @param array $instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		// Define the array of defaults
		$defaults = array(
			'width'        => '',
			'height'       => '',
			'ad_unit_path' => ''
		);
		
		// Parse incoming $instance into an array and merge it with $defaults
		$instance = wp_parse_args(
			(array) $instance,
			$defaults
		);		
			
		// Display the admin form
		include AIOVG_PLUGIN_DIR . 'premium/widgets/forms/companion.php';
	}
	
}
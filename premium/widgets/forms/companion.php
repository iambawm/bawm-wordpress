<?php

/**
 * Admin form: Companion ads widget.
 *
 * @link       https://plugins360.com
 * @since      1.5.7
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
?>

<div class="aiovg aiovg-widget-form aiovg-widget-form-companion">
	<div class="aiovg-widget-section">
		<div class="aiovg-widget-field aiovg-widget-field-width">
			<label class="aiovg-widget-label" for="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>"><?php esc_html_e( 'Width', 'all-in-one-video-gallery' ); ?></label> 
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'width' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>" class="widefat aiovg-widget-input-width" value="<?php echo esc_attr( $instance['width'] ); ?>" />
		</div>

		<div class="aiovg-widget-field aiovg-widget-field-height">
			<label class="aiovg-widget-label" for="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>"><?php esc_html_e( 'Height', 'all-in-one-video-gallery' ); ?></label> 
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'height' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>" class="widefat aiovg-widget-input-height" value="<?php echo esc_attr( $instance['height'] ); ?>" />
		</div>

		<div class="aiovg-widget-field aiovg-widget-field-ad_unit_path">
			<label class="aiovg-widget-label" for="<?php echo esc_attr( $this->get_field_id( 'ad_unit_path' ) ); ?>"><?php esc_html_e( 'Ad Unit Path', 'all-in-one-video-gallery' ); ?></label> 
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'ad_unit_path' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'ad_unit_path' ) ); ?>" class="widefat aiovg-widget-input-ad_unit_path" value="<?php echo esc_attr( $instance['ad_unit_path'] ); ?>" />
		</div>

		<p class="description"><?php printf( __( 'Optional. Full path of the ad unit with the network code and unit code. Required only when you enable the <a href="%s" target="_blank">Google Publisher Tag (GPT)</a> in the <a href="%s">plugin settings</a>.', 'all-in-one-video-gallery' ), 'https://developers.google.com/doubleclick-gpt/', admin_url( 'admin.php?page=aiovg_settings&tab=ads&section=aiovg_ads_settings' ) ); ?></p>
	</div>
</div>
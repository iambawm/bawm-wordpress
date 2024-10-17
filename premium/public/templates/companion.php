<?php

/**
 * Companion Ads.
 *
 * @link       https://plugins360.com
 * @since      1.5.7
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
?>

<div id="aiovg-companion-<?php echo esc_attr( $attributes['id'] ); ?>" class="aiovg-companion" style="width:<?php echo (int) $attributes['width']; ?>px; height:<?php echo (int) $attributes['height']; ?>px;" data-width="<?php echo (int) $attributes['width']; ?>" data-height="<?php echo (int) $attributes['height']; ?>">    
	<?php if ( 1 == $ads_settings['use_gpt'] && ! empty( $attributes['ad_unit_path'] ) ) : ?>
    	<script type="text/javascript">
        	googletag.cmd.push(function() {
           		googletag.defineSlot( '<?php echo esc_attr( $attributes['ad_unit_path'] ); ?>', [<?php echo (int) $attributes['width']; ?>, <?php echo (int) $attributes['height']; ?>], 'aiovg-companion-<?php echo esc_attr( $attributes['id'] ); ?>' )
               		.addService( googletag.companionAds() )
               		.addService( googletag.pubads() );
           		googletag.companionAds().setRefreshUnfilledSlots( true );
           		googletag.pubads().enableVideoAds();
           		googletag.enableServices();
           		googletag.display( 'aiovg-companion-<?php echo esc_attr( $attributes['id'] ); ?>' );
         	});
       </script>
	<?php endif; ?>
</div>
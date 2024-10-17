<?php

/**
 * Template: Slider (Classic).
 *
 * @link       https://plugins360.com
 * @since      3.3.1
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */

$uid = sanitize_text_field( $attributes['uid'] );

$data_params = array(
    'uid'       => $uid,
    'dot_size'  => (int) $attributes['dot_size'] . 'px',
    'dot_color' => sanitize_text_field( $attributes['dot_color'] )
);

$data_slick = array(
    'rtl'            => is_rtl(),
    'slidesToShow'   => (int) $attributes['columns'],
    'slidesToScroll' => 1,
    'adaptiveHeight' => true,
    'autoplay'       => ! empty( $attributes['slider_autoplay'] ) ? true : false,
    'autoplaySpeed'  => (int) $attributes['autoplay_speed'],
    'arrows'         => ! empty( $attributes['arrows'] ) ? true : false,
    'dots'           => ! empty( $attributes['dots'] ) ? true : false,
    'responsive'     => array()
);

if ( (int) $attributes['columns'] > 3 ) {
    $data_slick['responsive'][] = array(
        'breakpoint' => 768,
        'settings'   => array(
            'slidesToShow' => 3
        )
    );
}

if ( (int) $attributes['columns'] > 2 ) {
    $data_slick['responsive'][] = array(
        'breakpoint' => 600,
        'settings'   => array(
            'slidesToShow' => 2
        )
    );
}

if ( (int) $attributes['columns'] > 1 ) {
    $data_slick['responsive'][] = array(
        'breakpoint' => 480,
        'settings'   => array(
            'slidesToShow' => 1,
            'centerMode'   => true
        )
    );
}

$data_slick = apply_filters( 'aiovg_template_slider_slick_options', $data_slick, $attributes );
?>

<div id="aiovg-<?php echo esc_attr( $uid ); ?>" class="aiovg aiovg-videos aiovg-videos-template-slider aiovg-slider-layout-classic" data-params='<?php echo wp_json_encode( $data_params ); ?>'>
    <?php                    
    // Title
    if ( ! empty( $attributes['title'] ) ) : ?>
        <h2 class="aiovg-header">
            <?php echo esc_html( $attributes['title'] ); ?>
        </h2>
    <?php endif; 
    
    // Videos count
    if ( ! empty( $attributes['show_count'] ) ) : ?>
        <div class="aiovg-count">
            <?php 
            $videos_count = (int) $attributes['count'];
            printf( _n( '%s video found', '%s videos found', $videos_count, 'all-in-one-video-gallery' ), number_format_i18n( $videos_count ) );
            ?>
        </div>
    <?php endif; ?>    
    
    <!-- Videos -->
    <div class="aiovg-section-videos">
        <?php 
        // Arrows
        if ( ! empty( $attributes['arrows'] ) ) {
            the_aiovg_premium_slick_arrows( $attributes ); 
        }
        ?> 

        <div class="aiovg-slick aiovg-grid aiovg-row" data-slick='<?php echo wp_json_encode( $data_slick ); ?>'>
            <?php
            // The loop            
            while ( $aiovg_query->have_posts() ) :        
                $aiovg_query->the_post(); 

                $classes = array();
                $classes[] = 'aiovg-item-video';
                $classes[] = 'aiovg-item-video-' . $post->ID;
                ?>            
                <div class="<?php echo implode( ' ', $classes ); ?>" data-id="<?php echo esc_attr( $post->ID ); ?>">
                    <?php the_aiovg_video_thumbnail( $post, $attributes ); ?>
                </div>            
                <?php           
            endwhile;
                
            // Use reset postdata to restore orginal query
            wp_reset_postdata();
            ?>
        </div>
    </div>
</div>
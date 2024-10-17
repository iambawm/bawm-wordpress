<?php

/**
 * Template: Slider (Compact).
 *
 * @link       https://plugins360.com
 * @since      3.3.1
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */

$player_settings = get_option( 'aiovg_player_settings' );

$uid = sanitize_text_field( $attributes['uid'] );
$autoadvance = ! empty( $attributes['autoadvance'] ) ? 1 : 0;
$loop = ! empty( $player_settings['loop'] ) ? 1 : 0;

$player_args = array(
    'player' => 'iframe', 
    'width'  => ''
);

if ( $aiovg_query->have_posts() ) {
    if ( $autoadvance ) {        
        if ( count( $aiovg_query->posts ) > 1 ) {
            $player_args['uid'] = $uid;
            $player_args['autoadvance'] = $autoadvance;

            if ( $loop ) {
                $player_args['loop'] = 0;
            }
        }
    }
}

$data_params = array(
    'uid'                  => $uid,
    'autoadvance'          => $autoadvance,
    'loop'                 => $loop,
    'show_title'           => (int) $attributes['show_player_title'],
    'link_title'           => (int) $attributes['link_title'],
    'show_description'     => (int) $attributes['show_player_description'],
    'show_like_button'     => (int) $attributes['show_player_like_button'],
    'show_playlist_button' => (int) $attributes['show_player_playlist_button'],
    'dot_size'             => (int) $attributes['dot_size'] . 'px',
    'dot_color'            => sanitize_text_field( $attributes['dot_color'] )
);

$data_slick = array(
    'rtl'            => is_rtl(),
    'slidesToShow'   => (int) $attributes['columns'],
    'slidesToScroll' => 1,
    'adaptiveHeight' => true,
    'focusOnSelect'  => true,
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

<div id="aiovg-<?php echo esc_attr( $uid ); ?>" class="aiovg aiovg-videos aiovg-videos-template-slider aiovg-slider-layout-compact" data-params='<?php echo wp_json_encode( $data_params ); ?>'>
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
    
    <!-- Player -->
    <div class="aiovg-section-player">
        <?php if ( $aiovg_query->have_posts() ) :
            $featured = $aiovg_query->posts[0];

            // Player
            the_aiovg_player( $featured->ID, $player_args );

            // Title
            if ( ! empty( $attributes['show_player_title'] ) ) {
                echo '<h2 class="aiovg-player-title">'; 

                if ( ! empty( $attributes['link_title'] ) ) {
                    echo '<a href="' . esc_url( get_permalink( $featured->ID ) ) . '" class="aiovg-link-title">';
                    echo esc_html( get_the_title( $featured->ID ) );
                    echo '</a>';
                } else {
                    echo esc_html( get_the_title( $featured->ID ) );
                } 

                echo '</h2>';
            }

            // After Player
            the_aiovg_content_after_player( $featured->ID, $attributes );

            // Description
            if ( ! empty( $attributes['show_player_description'] ) ) {
                $filtered_content = do_shortcode( wpautop( $featured->post_content ) );

                echo '<div class="aiovg-player-description aiovg-hide-if-empty">';
                echo wp_kses_post( $filtered_content );
                echo '</div>';
            }
            ?>  
        <?php endif; ?>
    </div>

    <!-- Videos -->
    <div class="aiovg-section-videos<?php if ( empty( $attributes['link_title'] ) ) echo ' aiovg-disable-link-title'; ?>"> 
        <?php 
        // Arrows
        if ( ! empty( $attributes['arrows'] ) ) {
            the_aiovg_premium_slick_arrows( $attributes ); 
        }
        ?>     
        
        <!-- Slider -->
        <div class="aiovg-slick aiovg-grid aiovg-row" data-slick='<?php echo wp_json_encode( $data_slick ); ?>'>
            <?php
            // The loop
            $i = 0;

            while ( $aiovg_query->have_posts() ) :        
                $aiovg_query->the_post(); 

                $classes = array();
                $classes[] = 'aiovg-item-video';
                $classes[] = 'aiovg-item-video-' . $post->ID;
                if ( 0 == $i ) $classes[] = 'aiovg-active';

                $player_args['autoplay'] = 1;
                $player_url = aiovg_get_player_page_url( $post->ID, $player_args );
                ?>            
                <div class="<?php echo implode( ' ', $classes ); ?>" data-id="<?php echo esc_attr( $post->ID ); ?>" data-src="<?php echo esc_url( $player_url ); ?>">
                    <?php 
                    the_aiovg_video_thumbnail( $post, $attributes ); 
                    
                    // Title (hidden)
                    if ( ! empty( $attributes['show_player_title'] ) ) {
                        echo '<div class="aiovg-hidden-title" style="display: none;">';

                        if ( ! empty( $attributes['link_title'] ) ) {
                            echo '<a href="' . esc_url( get_permalink() ) . '" class="aiovg-link-title">';
                            echo esc_html( get_the_title() );
                            echo '</a>';
                        } else {
                            echo esc_html( get_the_title() );
                        }           

                        echo '</div>';
                    }

                    // Description (hidden)
                    if ( ! empty( $attributes['show_player_description'] ) ) {
                        $filtered_content = do_shortcode( wpautop( $post->post_content ) );

                        echo '<div class="aiovg-hidden-description" style="display: none;">';
                        echo wp_kses_post( $filtered_content );
                        echo '</div>';
                    }
                    ?>
                </div>            
                <?php 
                ++$i;
            endwhile;
                
            // Use reset postdata to restore orginal query
            wp_reset_postdata();
            ?>
        </div>
    </div>
</div>
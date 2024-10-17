<?php

/**
 * Template: Slider (Inline).
 *
 * @link       https://plugins360.com
 * @since      3.3.1
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */

$player_settings = get_option( 'aiovg_player_settings' );

$uid = sanitize_text_field( $attributes['uid'] );
$player_ratio = ! empty( $player_settings['ratio'] ) ? (float) $player_settings['ratio'] . '%' : '56.25%';
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
    'adaptiveHeight' => true,
    'arrows'         => ! empty( $attributes['arrows'] ) ? true : false,
    'dots'           => ! empty( $attributes['dots'] ) ? true : false
);

$data_slick = apply_filters( 'aiovg_template_slider_slick_options', $data_slick, $attributes );
?>

<div id="aiovg-<?php echo esc_attr( $uid ); ?>" class="aiovg aiovg-videos aiovg-videos-template-slider aiovg-slider-layout-inline" data-params='<?php echo wp_json_encode( $data_params ); ?>'>
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
    <div class="aiovg-section-videos aiovg-player-position-<?php echo ( 'image-left' == $attributes['thumbnail_style'] ) ? 'left' : 'top'; ?>">
        <?php 
        // Arrows
        if ( ! empty( $attributes['arrows'] ) ) {
            the_aiovg_premium_slick_arrows( $attributes ); 
        }
        ?> 

        <div class="aiovg-slick" data-slick='<?php echo wp_json_encode( $data_slick ); ?>'>
            <?php
            // The loop    
            $i = 0;

            while ( $aiovg_query->have_posts() ) :        
                $aiovg_query->the_post(); 

                $classes = array();
                $classes[] = 'aiovg-item-video';
                $classes[] = 'aiovg-item-video-' . $post->ID;

                $player_url = aiovg_get_player_page_url( $post->ID, $player_args );
                ?>            
                <div class="<?php echo implode( ' ', $classes ); ?>" data-id="<?php echo esc_attr( $post->ID ); ?>" data-src="<?php echo esc_url( $player_url ); ?>">                    
                    <?php 
                    // Player
                    if ( 0 == $i ) {
                        $player = aiovg_get_player_html( $post->ID, $player_args );
                    } else {
                        $player  = '<div class="aiovg-player-container">';
                        $player .= sprintf( '<div class="aiovg-player" style="padding-bottom: %s"></div>', $player_ratio );
                        $player .= '</div>';
                    }                

                    // Title
                    $title_html = '';
                    
                    if ( ! empty( $attributes['show_player_title'] ) ) {
                        $title_html = '<h2 class="aiovg-player-title">'; 

                        if ( ! empty( $attributes['link_title'] ) ) {
                            $title_html .= sprintf( '<a href="%s" class="aiovg-link-title">', esc_url( get_permalink() ) );
                            $title_html .= esc_html( get_the_title() );
                            $title_html .= '</a>';
                        } else {
                            $title_html .= esc_html( get_the_title() );
                        } 

                        $title_html .= '</h2>';
                    }                    
 
                    // Description
                    $desc_html = '';

                    if ( ! empty( $attributes['show_player_description'] ) ) {
                        $filtered_content = do_shortcode( wpautop( $post->post_content ) );

                        $desc_html  = '<div class="aiovg-player-description">';
                        $desc_html .= wp_kses_post( $filtered_content );
                        $desc_html .= '</div>';
                    }

                    // ...
                    if ( ! empty( $title_html ) || ! empty( $desc_html ) ) {
                        if ( 'image-left' == $attributes['thumbnail_style'] ) {
                            echo '<div class="aiovg-row">';

                            echo '<div class="aiovg-col aiovg-col-p-60">';
                            echo $player;
                            echo '</div>';

                            echo '<div class="aiovg-col aiovg-col-p-40">';    
                            echo $title_html;                        
                            the_aiovg_content_after_player( $post->ID, $attributes ); // After Player  
                            echo $desc_html;                          
                            echo '</div>';

                            echo '</div>';
                        } else {
                            echo $player;      
                            echo $title_html;                       
                            the_aiovg_content_after_player( $post->ID, $attributes ); // After Player
                            echo $desc_html;
                        }
                    } else {
                        echo $player;
                        the_aiovg_content_after_player( $post->ID, $attributes ); // After Player
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
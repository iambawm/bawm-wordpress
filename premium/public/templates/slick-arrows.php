<?php

/**
 * Slick Arrows.
 *
 * @link       https://plugins360.com
 * @since      3.3.1
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */

$type = empty( $attributes['arrow_top_offset'] ) ? 'fixed' : 'floating';

$styles = array();
$styles['display'] = 'display: none;';
$styles['width'] = 'width: ' . (int) $attributes['arrow_size'] . 'px';
$styles['height'] = 'height: ' . (int) $attributes['arrow_size'] . 'px';
$styles['background'] = 'background: ' . sanitize_text_field( $attributes['arrow_bg_color'] );
$styles['border-radius'] = 'border-radius: ' . (int) $attributes['arrow_radius'] . 'px';
$styles['font-size'] = 'font-size: ' . ( (int) $attributes['arrow_size'] - 5 ) . 'px';
$styles['color'] = 'color: ' . sanitize_text_field( $attributes['arrow_icon_color'] );
$styles['line-height'] = 'line-height: ' . (int) $attributes['arrow_size'] . 'px';

if ( 'floating' == $type ) {
    $styles['top'] = 'top: ' . (int) $attributes['arrow_top_offset'] . '%';
    
    echo '<div class="aiovg-slick-arrows aiovg-slick-floating-arrows">';

    // Previous Arrow
    if ( is_rtl() ) {
        $styles['right'] = 'right: ' . (int) $attributes['arrow_left_offset'] . 'px';
    } else {
        $styles['left'] = 'left: ' . (int) $attributes['arrow_left_offset'] . 'px';
    }

    echo '<div class="aiovg-slick-prev" style="' . implode( '; ', $styles ) . '" role="button">&#10094;</div>';

    // Next Arrow
    if ( is_rtl() ) {
        unset( $styles['right'] );
        $styles['left'] = 'left: ' . (int) $attributes['arrow_right_offset'] . 'px';
    } else {
        unset( $styles['left'] );
        $styles['right'] = 'right: ' . (int) $attributes['arrow_right_offset'] . 'px';
    }
    
    echo '<div class="aiovg-slick-next" style="' . implode( '; ', $styles ) . '" role="button">&#10095;</div>';

    echo '</div>';
} else {
    echo '<div class="aiovg-slick-arrows aiovg-slick-fixed-arrows" style="text-align: ' . ( is_rtl() ? 'left' : 'right' ) . ';">';
    
    // Previous Arrow
    echo '<div class="aiovg-slick-prev" style="' . implode( '; ', $styles ) . '" role="button">&#10094;</div>';

    // Next Arrow
    echo '<div class="aiovg-slick-next" style="' . implode( '; ', $styles ) . '" role="button">&#10095;</div>';

    echo '</div>';
}    
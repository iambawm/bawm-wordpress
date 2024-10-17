<?php

/**
 * Single Playlist Layout.
 *
 * @link    https://plugins360.com
 * @since   3.6.0
 *
 * @package All_In_One_Video_Gallery
 */

if ( isset( $_GET['permission_denied'] ) && 1 == $_GET['permission_denied'] ) {
    echo '<div class="aiovg">';
    echo '<p class="aiovg-notice aiovg-notice-error">';
    echo esc_html__( 'You do not have sufficient permissions to do this action.', 'all-in-one-video-gallery' );
    echo '</p>';
    echo '</div>';
}	

if ( isset( $_GET['status'] ) && 'removed' == $_GET['status'] ) {
    echo '<div class="aiovg">';
    echo '<p class="aiovg-notice aiovg-notice-success">';
    echo esc_html__( 'Video removed successfully from this playlist.', 'all-in-one-video-gallery' );
    echo '</p>';
    echo '</div>';
}

if ( $aiovg_query->have_posts() ) {		
    $attributes['count'] = $aiovg_query->post_count;
    include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . 'public/templates/videos-template-classic.php', $attributes );
} else {
    echo '<div class="aiovg-shortcode-playlist aiovg-no-items-found">';
    echo esc_html( aiovg_get_message( 'videos_empty' ) );
    echo '</div>';
}
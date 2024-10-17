<?php

/**
 * User Playlists Layout.
 *
 * @link    https://plugins360.com
 * @since   3.6.0
 *
 * @package All_In_One_Video_Gallery
 */
?>

<div id="aiovg-playlists" class="aiovg aiovg-playlists">  
    <?php if ( isset( $_GET['permission_denied'] ) && 1 == $_GET['permission_denied'] ) : ?>
        <p class="aiovg-notice aiovg-notice-error">
            <?php esc_html_e( 'You do not have sufficient permissions to do this action.', 'all-in-one-video-gallery' ); ?>
        </p>
    <?php endif; ?> 

    <?php if ( isset( $_GET['status'] ) && 'deleted' == $_GET['status'] ) : ?>
        <p class="aiovg-notice aiovg-notice-success">
            <?php esc_html_e( 'The playlist has been deleted successfully.', 'all-in-one-video-gallery' ); ?>
        </p>
    <?php endif; ?> 

    <div class="aiovg-section-playlists aiovg-grid aiovg-row">
        <?php
        // The loop 
        $columns = 2;

        foreach ( $terms as $key => $term ) :       
            $classes = array();
            $classes[] = 'aiovg-item-playlist';
            $classes[] = 'aiovg-item-playlist-' . $term->term_id;
            $classes[] = 'aiovg-col';
            $classes[] = 'aiovg-col-' . $columns;              
            ?>            
            <div class="<?php echo implode( ' ', $classes ); ?>" data-playlist_id="<?php echo esc_attr( $term->term_id ); ?>">		
                <div class="aiovg-thumbnail">                  
                    <div class="aiovg-caption aiovg-card">
                        <div class="aiovg-title aiovg-flex aiovg-gap-1 aiovg-items-center">
                            <div class="aiovg-flex-grow-1"><?php echo esc_html( $term->name ); ?></div>
                            <button type="button" class="aiovg-button-edit-playlist aiovg-no-margin aiovg-leading-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                        </div> 
        
                        <div class="aiovg-count aiovg-flex aiovg-gap-1 aiovg-items-center aiovg-text-muted aiovg-text-small">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
                            </svg>
                            <?php 
                            if ( $term->count > 1 ) {
                                printf( esc_html__( '%d videos', 'all-in-one-video-gallery' ), $term->count ); 
                            } else {
                                printf( esc_html__( '%d video', 'all-in-one-video-gallery' ), $term->count ); 
                            }
                            ?>
                        </div>

                        <?php
                        $meta = array();

                        // Play all
                        $meta[] = sprintf(
                            '<a href="%s">%s</a>',
                            esc_url( aiovg_premium_get_playlist_page_url( $term ) ),
                            esc_html__( 'Play all', 'all-in-one-video-gallery' )
                        );

                        // Delete this Playlist
                        $meta[] = sprintf(
                            '<a onclick="return confirm( \'%s\' )" href="%s" class="aiovg-link-delete-playlist">%s</a>',
                            esc_html__( 'Are you SURE you want to delete this playlist?', 'all-in-one-video-gallery' ),
                            esc_url( aiovg_premium_get_delete_playlist_page_url( $term->term_id ) ),
                            esc_html__( 'Delete this Playlist', 'all-in-one-video-gallery' )
                        );

                        echo '<div class="aiovg-flex aiovg-gap-1 aiovg-items-center aiovg-text-small">';
                        echo implode( '<span class="aiovg-text-muted">/</span>',  $meta );
                        echo '</div>';
                        ?>
                    </div>            			
                </div>		
            </div> 
        <?php endforeach; ?>
    </div>
</div>
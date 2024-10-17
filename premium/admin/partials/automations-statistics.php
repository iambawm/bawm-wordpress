<?php

/**
 * Automations: "Import Statistics" meta box.
 *
 * @link       https://plugins360.com
 * @since      1.6.2
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
?>

<div class="aiovg">
    <!-- Error -->
    <?php if ( ! empty( $post_meta['import_statistics']['last_error'] ) ) : ?>
        <p class="aiovg-notice aiovg-notice-error">
            <?php echo wp_kses_post( $post_meta['import_statistics']['last_error'] ); ?>
        </p>
    <?php endif; ?>
        
    <?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
        <!-- Status -->
        <p>
            <strong><?php esc_html_e( 'Import status', 'all-in-one-video-gallery' ); ?></strong>:
            <?php if ( ! empty( $post_meta['scheduled_status'] ) ) : ?>
                <span class="aiovg-text-success"><?php echo esc_html( $post_meta['scheduled_status'] ); ?></span>
            <?php endif; ?>
        </p>
        
        <ul>
            <li>
                <?php
                printf(
                    '- <a href="%s">%d %s</a>',
                    esc_url( admin_url( 'edit.php?post_type=aiovg_videos&aiovg_filter=imported&import_id=' . $post_meta['post_id'] ) ),
                    $post_meta['imported'],                
                    esc_html__( 'videos imported', 'all-in-one-video-gallery' )
                );
                ?>
            </li>
            <li>
                <?php
                printf(
                    '- %d %s',
                    $post_meta['excluded'],                
                    esc_html__( 'videos excluded', 'all-in-one-video-gallery' )
                );
                ?>
            </li>
            <li>
                <?php
                printf(
                    '- %d %s',
                    $post_meta['duplicates'],                
                    esc_html__( 'duplicate videos ignored', 'all-in-one-video-gallery' )
                );
                ?>
            </li>    
        </ul>

        <!-- Next scheduled update -->
        <p>
            <strong><?php esc_html_e( 'Next scheduled update', 'all-in-one-video-gallery' ); ?></strong>:
            <span class="aiovg-text-success"><?php echo esc_html( $post_meta['import_next_schedule'] ); ?></span>
        </p>        

        <!-- History -->
        <p><strong><?php esc_html_e( 'History', 'all-in-one-video-gallery' ); ?></strong>:</p>
        <?php foreach ( $post_meta['import_statistics']['data'] as $data ) : 
            $url = admin_url( 'edit.php?post_type=aiovg_videos&aiovg_filter=imported&import_id=' . $post_meta['post_id'] . '&import_key=' . $data['key'] );
            ?>
            <a href="<?php echo esc_url( $url ); ?>" class="aiovg-block">
                <?php 
                printf( 
                    esc_html__( 'Imported %d videos on %s', 'all-in-one-video-gallery' ), 
                    $data['imported'], 
                    date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $data['date'] ) ) 
                ); 
                ?>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="aiovg-margin-top description"><?php esc_html_e( 'You have not started importing videos.', 'all-in-one-video-gallery' ); ?></p>
    <?php endif; ?>

    <!-- Modal -->
    <div id="aiovg-automations-preview-modal" class="aiovg aiovg-modal mfp-hide">
        <div class="aiovg-modal-body"></div>
    </div>
</div>
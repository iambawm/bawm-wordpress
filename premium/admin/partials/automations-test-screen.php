<?php

/**
 * Automations: Test Screen.
 *
 * @link       https://plugins360.com
 * @since      1.6.2
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
?>

<?php if ( ! empty( $response->last_error ) ) : ?>
    <div class="aiovg-notice aiovg-notice-error">
        <?php echo wp_kses_post( $response->last_error ); ?>
    </div>
<?php endif; ?>

<?php if ( count( $response->videos ) > 0 ) : ?>
    <p class="aiovg-notice aiovg-notice-success">
        <?php esc_html_e( 'Congrats! Your configuration is working. You can publish the form and start importing videos.', 'all-in-one-video-gallery' ); ?>
    </p>

    <table class="striped widefat">
        <?php foreach ( $response->videos as $index => $video ) : 
            if ( 'imported' == $_POST['video_date'] ) {
                $video->date = current_time( 'mysql' );
            }
            ?>
            <tr>
                <td class="aiovg-text-center">
                    <strong><?php echo $index + 1; ?></strong>
                </td>
                <td>
                    <img src="<?php echo esc_url( $video->image ); ?>" alt="" />
                </td>
                <td>
                    <a href="<?php echo esc_url( $video->url ); ?>" class="row-title" target="_blank"><?php echo esc_html( $video->title ); ?></a>
                    <br />
                    <?php printf( esc_html__( 'Published on %s', 'all-in-one-video-gallery' ), esc_html( $video->date ) ); ?>
                </td>
                <td>
                    <?php echo esc_html( $video->duration ); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif;

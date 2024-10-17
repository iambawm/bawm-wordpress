<?php

/**
 * Categories: Custom Fields.
 *
 * @link       https://plugins360.com
 * @since      2.5.6
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
?>

<?php if ( 'add' == $form ) : ?>
    <div class="aiovg form-field term-exclude_video_form-group">
        <label>
			<input type="checkbox" name="exclude_video_form" id="aiovg-categories-exclude_video_form" value="1" />
            <?php esc_html_e( 'Exclude in Video Form', 'all-in-one-video-gallery' ); ?>			
		</label>

        <p class="description">
            <?php esc_html_e( 'Exclude this category in the front-end video form.', 'all-in-one-video-gallery' ); ?>
        </p>
    </div>
<?php elseif ( 'edit' == $form ) : ?>
    <tr class="aiovg form-field term-exclude_video_form-wrap">
        <th scope="row">
            <label for="aiovg-categories-exclude_video_form"><?php esc_html_e( 'Exclude in Video Form', 'all-in-one-video-gallery' ); ?></label>
        </th>
        <td>
            <label>
                <input type="checkbox" name="exclude_video_form" id="aiovg-categories-exclude_video_form" value="1" <?php checked( $exclude_video_form, 1 ); ?> />
                <?php esc_html_e( 'Exclude this category in the front-end video form.', 'all-in-one-video-gallery' ); ?>		
            </label>
        </td>
    </tr>
<?php endif;
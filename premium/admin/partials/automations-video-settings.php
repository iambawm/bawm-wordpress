<?php

/**
 * Automations: "Video Post Settings" meta box.
 *
 * @link       https://plugins360.com
 * @since      1.6.2
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
?>

<div class="aiovg">
	<table class="aiovg-table form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="aiovg-video_title_format"><?php esc_html_e( 'Video Title', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<input type="text" name="video_title_format" id="aiovg-video_title_format" class="widefat" placeholder="%%title%%" value="<?php echo esc_attr( $post_meta['video_title_format'] ); ?>" />
					<p class="description"><?php _e( '<code>%%title%%</code> will be replaced by the imported YouTube / Vimeo title.', 'all-in-one-video-gallery' ); ?></p>
					<p class="description"><?php _e( '<code>%%id%%</code> will be replaced by the imported YouTube / Vimeo ID.', 'all-in-one-video-gallery' ); ?></p>
					<p class="description"><?php _e( 'Use this field to add custom text/shortcode before or after the imported video title. Leaving this field empty will import the video title equivalent to <code>%%title%%</code>.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="aiovg-video_description"><?php esc_html_e( 'Video Description', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<label class="aiovg-block">
						<input type="checkbox" name="video_description" id="aiovg-video_description" value="1" <?php checked( $post_meta['video_description'], 1 ); ?> />
						<?php esc_html_e( 'Check this option to import the video description.', 'all-in-one-video-gallery' ); ?>
					</label>

					<textarea name="video_description_format" class="aiovg-margin-top widefat" rows="8" placeholder="%%description%%"><?php echo esc_textarea( $post_meta['video_description_format'] ); ?></textarea>
					<p class="description"><?php _e( '<code>%%description%%</code> will be replaced by the actual video description.', 'all-in-one-video-gallery' ); ?></p>
					<p class="description"><?php _e( 'Use this field to add custom text/shortcode before or after the imported video description. Leaving this field empty will import the video description equivalent to <code>%%description%%</code>.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr>

			<?php if ( ! empty( $featured_images_settings['enabled'] ) && ! empty( $featured_images_settings['download_external_images'] ) ) : ?>
				<tr>
					<th scope="row">
						<label for="aiovg-video_featured_image"><?php esc_html_e( 'Featured Image', 'all-in-one-video-gallery' ); ?></label>
					</th>
					<td>        
						<label>
							<input type="checkbox" name="video_featured_image" id="aiovg-video_featured_image" value="1" <?php checked( $post_meta['video_featured_image'], 1 ); ?> />
							<?php esc_html_e( 'Check this option to set a featured image.', 'all-in-one-video-gallery' ); ?>
						</label>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th scope="row">
					<label for="aiovg-categories"><?php esc_html_e( 'Video Categories', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<ul class="aiovg-checklist widefat">
						<?php
						$args = array(
							'taxonomy'      => 'aiovg_categories',
							'walker'        => null,
							'checked_ontop' => false,
							'selected_cats' => array_map( 'intval', $post_meta['video_categories'] )
						); 
					
						wp_terms_checklist( 0, $args );
						?>
					</ul>
					<p class="description"><?php esc_html_e( 'Assign categories to the imported videos.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="aiovg-tags"><?php esc_html_e( 'Video Tags', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<ul class="aiovg-checklist widefat">
						<?php
						$args = array(
							'taxonomy'      => 'aiovg_tags',
							'walker'        => null,
							'checked_ontop' => false,
							'selected_cats' => array_map( 'intval', $post_meta['video_tags'] ),
							'echo'          => false
						); 
					
						$tags_checklist = wp_terms_checklist( 0, $args );
						$tags_checklist = str_replace( 'tax_input[aiovg_tags]', 'tags', $tags_checklist );

						echo $tags_checklist;
						?>
					</ul>
					<p class="description"><?php esc_html_e( 'Assign tags to the imported videos.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr>	 

			<tr>
				<th scope="row">
					<label for="aiovg-video_date"><?php esc_html_e( 'Video Date', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<select name="video_date" id="aiovg-video_date" class="widefat">
						<?php 
						$options = array(
							'original' => __( 'Original date on the video service', 'all-in-one-video-gallery' ),
							'imported' => __( 'Date when the video is imported', 'all-in-one-video-gallery' )
						);

						foreach ( $options as $key => $label ) {
							printf( 
								'<option value="%s"%s>%s</option>', 
								$key, 
								selected( $key, $post_meta['video_date'], false ), 
								esc_html( $label )
							);
						}
						?>
					</select>
					<p class="description"><?php esc_html_e( 'Select whether to use the original posting date on the video service, or the date when the video is imported.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="aiovg-video_author"><?php esc_html_e( 'Video Author', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<?php
					$args = array(
						'name'     => 'video_author',
						'class'    => 'widefat',
 						'selected' => (int) $post_meta['video_author']
					); 
				
					wp_dropdown_users( $args );
					?>
					<p class="description"><?php esc_html_e( 'Select the author to whom the video should be assigned.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr> 

			<tr>
				<th scope="row">
					<label for="aiovg-video_status"><?php esc_html_e( 'Video Status', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<select name="video_status" id="aiovg-video_status" class="widefat">
						<?php 
						$options = array(
							'draft'   => __( 'Draft', 'all-in-one-video-gallery' ),
							'pending' => __( 'Pending', 'all-in-one-video-gallery' ),
							'publish' => __( 'Publish', 'all-in-one-video-gallery' )
						);

						foreach ( $options as $key => $label ) {
							printf( 
								'<option value="%s"%s>%s</option>', 
								$key, 
								selected( $key, $post_meta['video_status'], false ), 
								esc_html( $label )
							);
						}
						?>
					</select>
					<p class="description"><?php esc_html_e( 'Select the default status of the imported videos. Site admin will be notified through email when an import occurs with the "Pending" status.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr>   
		</tbody>
	</table>

	<?php wp_nonce_field( 'aiovg_save_automations_video_settings', 'aiovg_automations_video_settings_nonce' ); // Nonce ?>
</div>
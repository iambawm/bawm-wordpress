<?php

/**
 * Automations: "Configure Import Sources" meta box.
 *
 * @link       https://plugins360.com
 * @since      1.6.2
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
?>

<div class="aiovg">
	<table class="aiovg-table form-table aiovg-automations-service-<?php echo esc_attr( $post_meta['service'] ); ?> aiovg-automations-type-<?php echo esc_attr( $post_meta['type'] ); ?>">
		<tbody>
			<tr class="aiovg-automations-field-service">
				<th scope="row">
					<label for="aiovg-service"><?php esc_html_e( 'Video Service', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<?php
					$options = aiovg_premium_get_automations_services();

					if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $options[ $post_meta['service'] ] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-flex aiovg-flex-wrap aiovg-gap-3"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<?php
						foreach ( $options as $key => $label ) {
							printf( 
								'<label><input type="radio" name="service" class="aiovg-automations-field" value="%s" %s/>%s</label>', 
								esc_attr( $key ), 
								checked( $key, $post_meta['service'], false ), 
								esc_html( $label )
							);
						}
						?>
					</div>
				</td>
			</tr>
		</tbody>

		<tbody class="aiovg-automations-fields-youtube">
			<tr class="aiovg-automations-field-type">
				<th scope="row">
					<label for="aiovg-youtube-type"><?php esc_html_e( 'Source Type', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<?php
					$options = aiovg_premium_get_automations_types( 'youtube' );

					if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description">
							<?php 
							$type = $post_meta['type'];
							echo isset( $options[ $type ] ) ? esc_html( $options[ $type ] ) : 'N/A'; 
							?>
						</p>
					<?php endif; ?>

					<select name="youtube[type]" id="aiovg-youtube-type" class="aiovg-automations-field widefat"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<?php 
						foreach ( $options as $key => $label ) {
							printf( 
								'<option value="%s"%s>%s</option>', 
								esc_attr( $key ), 
								selected( $key, $post_meta['type'], false ), 
								esc_html( $label )
							);
						}
						?>
					</select>
				</td>
			</tr>

			<tr class="aiovg-automations-field-channel">
				<th scope="row">
					<label for="aiovg-youtube-channel">
						<?php esc_html_e( 'Channel URL', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_url( $post_meta['channel'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="youtube[channel]" id="aiovg-youtube-channel" class="aiovg-automations-field widefat" placeholder="<?php printf( '%s: https://www.youtube.com/channel/XXXXXXXXXX', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>" value="<?php echo esc_url( $post_meta['channel'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Enter the YouTube Channel ID (or) a YouTube Video URL from the Channel.', 'all-in-one-video-gallery' ); ?></p>
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-playlist">
				<th scope="row">
					<label for="aiovg-youtube-playlist">
						<?php esc_html_e( 'Playlist URL', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_url( $post_meta['playlist'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="youtube[playlist]" id="aiovg-youtube-playlist" class="aiovg-automations-field widefat" placeholder="<?php printf( '%s: https://www.youtube.com/playlist?list=XXXXXXXXXX', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>" value="<?php echo esc_url( $post_meta['playlist'] ); ?>" />
					</div>
				</td>
			</tr> 

			<tr class="aiovg-automations-field-search">
				<th scope="row">
					<label for="aiovg-youtube-search">
						<?php esc_html_e( 'Search Keyword', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['search'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="youtube[search]" id="aiovg-youtube-search" class="aiovg-automations-field widefat" placeholder="<?php printf( '%s: Cartoon', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>" value="<?php echo esc_attr( $post_meta['search'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Enter search terms (space:AND, -:NOT, |:OR)', 'all-in-one-video-gallery' ); ?></p>
					</div>
				</td>
			</tr>               

			<tr class="aiovg-automations-field-username">
				<th scope="row">
					<label for="aiovg-youtube-username">
						<?php esc_html_e( 'Username', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['username'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="youtube[username]" id="aiovg-youtube-username" class="aiovg-automations-field widefat" placeholder="<?php printf( '%s: SanRosh', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>" value="<?php echo esc_attr( $post_meta['username'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-videos">
				<th scope="row">
					<label for="aiovg-youtube-videos">
						<?php esc_html_e( 'Video URLs', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="aiovg-checklist description"><?php echo wp_kses_post( nl2br( $post_meta['videos'] ) ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<textarea name="youtube[videos]" id="aiovg-youtube-videos" class="aiovg-automations-field widefat" rows="8" placeholder="<?php printf( '%s: https://www.youtube.com/watch?v=XXXXXXXXXX', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>"><?php echo esc_textarea( $post_meta['videos'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Enter one video per line.', 'all-in-one-video-gallery' ); ?></p>
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-exclude">
				<th scope="row">
					<label for="aiovg-youtube-exclude"><?php esc_html_e( 'Exclude URLs', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>
					<textarea name="youtube[exclude]" id="aiovg-youtube-exclude" class="aiovg-automations-field widefat" rows="8" placeholder="<?php printf( '%s: https://www.youtube.com/watch?v=XXXXXXXXXX', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>"><?php echo esc_textarea( $post_meta['exclude'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Enter the list of video URLs those should be excluded during the import. Enter one video per line.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr>

			<tr class="aiovg-automations-field-order">
				<th scope="row">
					<label for="aiovg-youtube-order"><?php esc_html_e( 'Order By', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['order'] ); ?></p>
					<?php endif; ?>

					<select name="youtube[order]" id="aiovg-youtube-order" class="aiovg-automations-field widefat"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<?php 
						$options = array(
							'date'      => __( 'Date', 'all-in-one-video-gallery' ),
							'rating'    => __( 'Rating', 'all-in-one-video-gallery' ),
							'relevance' => __( 'Relevance', 'all-in-one-video-gallery' ),
							'title'     => __( 'Title', 'all-in-one-video-gallery' ),
							'viewCount' => __( 'Views Count', 'all-in-one-video-gallery' )
						);

						foreach ( $options as $key => $label ) {
							printf( 
								'<option value="%s"%s>%s</option>', 
								$key, 
								selected( $key, $post_meta['order'], false ), 
								esc_html( $label )
							);
						}
						?>
					</select>
				</td>
			</tr>  
		</tbody> 

		<tbody class="aiovg-automations-fields-vimeo">
			<tr class="aiovg-automations-field-type">
				<th scope="row">
					<label for="aiovg-vimeo-type"><?php esc_html_e( 'Source Type', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<?php
					$options = aiovg_premium_get_automations_types( 'vimeo' );

					if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description">
							<?php 
							$type = $post_meta['type'];
							echo isset( $options[ $type ] ) ? esc_html( $options[ $type ] ) : 'N/A'; 
							?>
						</p>
					<?php endif; ?>

					<select name="vimeo[type]" id="aiovg-vimeo-type" class="aiovg-automations-field widefat"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<?php 
						foreach ( $options as $key => $label ) {
							printf( 
								'<option value="%s"%s>%s</option>', 
								esc_attr( $key ), 
								selected( $key, $post_meta['type'], false ), 
								esc_html( $label )
							);
						}
						?>
					</select>
				</td>
			</tr>

			<tr class="aiovg-automations-field-username">
				<th scope="row">
					<label for="aiovg-vimeo-username">
						<?php esc_html_e( 'User ID', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['username'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[username]" id="aiovg-vimeo-username" class="aiovg-automations-field widefat" placeholder="<?php esc_attr_e( 'Enter your Vimeo User ID', 'all-in-one-video-gallery' ); ?>" value="<?php echo esc_attr( $post_meta['username'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-showcase">
				<th scope="row">
					<label for="aiovg-vimeo-showcase">
						<?php esc_html_e( 'Showcase ID', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['showcase'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[showcase]" id="aiovg-vimeo-showcase" class="aiovg-automations-field widefat" placeholder="<?php esc_attr_e( 'Enter your Vimeo Showcase ID', 'all-in-one-video-gallery' ); ?>" value="<?php echo esc_attr( $post_meta['showcase'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-category">
				<th scope="row">
					<label for="aiovg-vimeo-category">
						<?php esc_html_e( 'Category Name', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['category'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[category]" id="aiovg-vimeo-category" class="aiovg-automations-field widefat" placeholder="<?php printf( '%s: Animation', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>" value="<?php echo esc_attr( $post_meta['category'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-channel">
				<th scope="row">
					<label for="aiovg-vimeo-channel">
						<?php esc_html_e( 'Channel ID', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['channel'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[channel]" id="aiovg-vimeo-channel" class="aiovg-automations-field widefat" placeholder="<?php esc_attr_e( 'Enter your Vimeo Channel ID', 'all-in-one-video-gallery' ); ?>" value="<?php echo esc_attr( $post_meta['channel'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-folder">
				<th scope="row">
					<label for="aiovg-vimeo-folder">
						<?php esc_html_e( 'Folder ID', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['folder'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[folder]" id="aiovg-vimeo-folder" class="aiovg-automations-field widefat" placeholder="<?php esc_attr_e( 'Enter your Vimeo Folder ID', 'all-in-one-video-gallery' ); ?>" value="<?php echo esc_attr( $post_meta['folder'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-group">
				<th scope="row">
					<label for="aiovg-vimeo-group">
						<?php esc_html_e( 'Group ID', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['group'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[group]" id="aiovg-vimeo-group" class="aiovg-automations-field widefat" placeholder="<?php esc_attr_e( 'Enter your Vimeo Group ID', 'all-in-one-video-gallery' ); ?>" value="<?php echo esc_attr( $post_meta['group'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-portfolio">
				<th scope="row">
					<label for="aiovg-vimeo-portfolio">
						<?php esc_html_e( 'Portfolio ID', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['portfolio'] ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[portfolio]" id="aiovg-vimeo-portfolio" class="aiovg-automations-field widefat" placeholder="<?php esc_attr_e( 'Enter your Vimeo Portfolio ID', 'all-in-one-video-gallery' ); ?>" value="<?php echo esc_attr( $post_meta['portfolio'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-search">
				<th scope="row">
					<label for="aiovg-vimeo-search">
						<?php esc_html_e( 'Search Keyword', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo esc_html( $post_meta['search'] ); ?></p>				
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[search]" id="aiovg-vimeo-search" class="aiovg-automations-field widefat" placeholder="<?php printf( '%s: Cartoon', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>" value="<?php echo esc_attr( $post_meta['search'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-videos">
				<th scope="row">
					<label for="aiovg-vimeo-videos">
						<?php esc_html_e( 'Video URLs', 'all-in-one-video-gallery' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="aiovg-checklist description"><?php echo wp_kses_post( nl2br( $post_meta['videos'] ) ); ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<textarea name="vimeo[videos]" id="aiovg-vimeo-videos" class="aiovg-automations-field widefat" rows="8" placeholder="<?php printf( '%s: https://vimeo.com/108018156', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>"><?php echo esc_textarea( $post_meta['videos'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Enter one video per line.', 'all-in-one-video-gallery' ); ?></p>
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-filter_tag">
				<th scope="row">
					<label for="aiovg-vimeo-filter_tag"><?php esc_html_e( 'Filter by Tags', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo ! empty( $post_meta['filter_tag'] ) ? esc_html( $post_meta['filter_tag'] ) : 'N/A'; ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[filter_tag]" id="aiovg-vimeo-filter_tag" class="aiovg-automations-field widefat" placeholder="<?php esc_attr_e( 'Enter a comma-separated list of tags to filter on', 'all-in-one-video-gallery' ); ?>" value="<?php echo esc_attr( $post_meta['filter_tag'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-filter_tag_exclude">
				<th scope="row">
					<label for="aiovg-vimeo-filter_tag_exclude"><?php esc_html_e( 'Exclude Tags', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo ! empty( $post_meta['filter_tag_exclude'] ) ? esc_html( $post_meta['filter_tag_exclude'] ) : 'N/A'; ?></p>
					<?php endif; ?>

					<div class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="text" name="vimeo[filter_tag_exclude]" id="aiovg-vimeo-filter_tag_exclude" class="aiovg-automations-field widefat" placeholder="<?php esc_attr_e( 'Enter a comma-separated list of tags to exclude', 'all-in-one-video-gallery' ); ?>" value="<?php echo esc_attr( $post_meta['filter_tag_exclude'] ); ?>" />
					</div>
				</td>
			</tr>

			<tr class="aiovg-automations-field-include_subfolders">
				<th scope="row">
					<label for="aiovg-vimeo-include_subfolders"><?php esc_html_e( 'Include Subfolders', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>
					<?php if ( ! empty( $post_meta['import_status'] ) ) : ?>
						<p class="description"><?php echo ! empty( $post_meta['include_subfolders'] ) ? __( 'Yes', 'all-in-one-video-gallery' ) : __( 'No', 'all-in-one-video-gallery' ); ?></p>
					<?php endif; ?>
					
					<label class="aiovg-input-wrap"<?php if ( ! empty( $post_meta['import_status'] ) ) echo ' style="display: none;"'; ?>>
						<input type="checkbox" name="vimeo[include_subfolders]" id="aiovg-vimeo-include_subfolders" class="aiovg-automations-field" value="1" <?php checked( $post_meta['include_subfolders'], 1 ); ?>/>
						<?php esc_html_e( 'Check this option to import videos from the subfolders too.', 'all-in-one-video-gallery' ); ?>
					</label>
				</td>
			</tr>
			
			<tr class="aiovg-automations-field-exclude">
				<th scope="row">
					<label for="aiovg-vimeo-exclude"><?php esc_html_e( 'Exclude URLs', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>
					<textarea name="vimeo[exclude]" id="aiovg-vimeo-exclude" class="aiovg-automations-field widefat" rows="8" placeholder="<?php printf( '%s: https://vimeo.com/108018156', esc_attr__( 'Example', 'all-in-one-video-gallery' ) ); ?>"><?php echo esc_textarea( $post_meta['exclude'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Enter the list of video URLs those should be excluded during the import. Enter one video per line.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr> 
		</tbody> 

		<tbody>
			<tr class="aiovg-automations-field-limit">
				<th scope="row">
					<label for="aiovg-limit"><?php esc_html_e( 'Batch Limit', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>
					<input type="text" name="limit" id="aiovg-limit" class="aiovg-automations-field widefat" value="<?php echo esc_attr( $post_meta['limit'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Enter the maximum amount of videos to be imported per batch. We recommend keeping this value less than 500.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr> 

			<tr class="aiovg-automations-field-schedule">
				<th scope="row">
					<label for="aiovg-schedule"><?php esc_html_e( 'Schedule', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<select name="schedule" id="aiovg-schedule" class="aiovg-automations-field widefat">
						<?php 
						$options = array(
							'0'       => __( 'Only Once', 'all-in-one-video-gallery' ),
							'3600'    => __( 'Every 1 Hour', 'all-in-one-video-gallery' ),
							'86400'   => __( 'Every 1 Day', 'all-in-one-video-gallery' ),
							'604800'  => __( 'Every 1 Week', 'all-in-one-video-gallery' ),
							'2419200' => __( 'Every 1 Month', 'all-in-one-video-gallery' )
						);

						foreach ( $options as $key => $label ) {
							printf( 
								'<option value="%s"%s>%s</option>', 
								$key, 
								selected( $key, $post_meta['schedule'], false ), 
								esc_html( $label )
							);
						}
						?>
					</select>
					<p class="description"><?php esc_html_e( 'Configure how frequent the plugin the plugin should import videos.', 'all-in-one-video-gallery' ); ?></p>
				</td>
			</tr>

			<tr class="aiovg-automations-field-reschedule">
				<th scope="row">
					<label for="aiovg-reschedule"><?php esc_html_e( 'Reschedule', 'all-in-one-video-gallery' ); ?></label>
				</th>
				<td>        
					<label>
						<input type="checkbox" name="reschedule" id="aiovg-reschedule" class="aiovg-automations-field" value="1" <?php checked( $post_meta['reschedule'], 1 ); ?> />
						<?php esc_html_e( 'Check this option if the plugin should check for new videos after the import has been completed.', 'all-in-one-video-gallery' ); ?>
					</label>
				</td>
			</tr>   
		</tbody>
	</table>

	<?php wp_nonce_field( 'aiovg_save_automations_sources', 'aiovg_automations_sources_nonce' ); // Nonce ?>
</div>
<?php

/**
 * User Videos Manager.
 *
 * @link       https://plugins360.com
 * @since      1.6.1
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
?>

<div class="aiovg aiovg-user-dashboard">
	<?php if ( isset( $_GET['permission_denied'] ) && 1 == $_GET['permission_denied'] ) : ?>
		<p class="aiovg-notice aiovg-notice-error">
			<?php printf( __( 'You do not have sufficient permissions to do this action. <a href="%s">Go back</a>', 'all-in-one-video-gallery' ), aiovg_premium_get_user_dashboard_page_url() ); ?>
		</p>
	<?php else : ?>
		<?php if ( isset( $_GET['status'] ) && 'draft' == $_GET['status'] ) : ?>
			<p class="aiovg-notice aiovg-notice-success">
				<?php esc_html_e( 'Congrats, the video has been saved as a draft.', 'all-in-one-video-gallery' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( isset( $_GET['status'] ) && 'pending' == $_GET['status'] ) : ?>
			<p class="aiovg-notice aiovg-notice-success">
				<?php esc_html_e( 'Congrats, the video has been submitted, and it is pending review. This review process could take up to 48 hours. Please be patient.', 'all-in-one-video-gallery' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( isset( $_GET['status'] ) && 'private' == $_GET['status'] ) : ?>
			<p class="aiovg-notice aiovg-notice-success">
				<?php esc_html_e( 'Congrats, the video has been updated.', 'all-in-one-video-gallery' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( isset( $_GET['status'] ) && 'publish' == $_GET['status'] ) : ?>
			<p class="aiovg-notice aiovg-notice-success">
				<?php esc_html_e( 'Congrats, the video has been published.', 'all-in-one-video-gallery' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( isset( $_GET['status'] ) && 'deleted' == $_GET['status'] ) : ?>
			<p class="aiovg-notice aiovg-notice-success">
				<?php esc_html_e( 'The video has been deleted successfully.', 'all-in-one-video-gallery' ); ?>
			</p>
		<?php endif; ?>

		<form method="get" action="<?php echo aiovg_premium_get_user_dashboard_page_url(); ?>">
			<div class="aiovg-flex aiovg-gap-4 aiovg-items-center">
				<div class="aiovg-search-form aiovg-search-form-template-compact aiovg-flex-grow-1">		
					<?php if ( ! get_option('permalink_structure') ) : ?>
						<input type="hidden" name="page_id" value="<?php echo esc_attr( $attributes['page_id'] ); ?>" />
					<?php endif; ?>        
						
					<div class="aiovg-form-group aiovg-field-keyword">
						<input type="text" name="vi" class="aiovg-form-control" placeholder="<?php esc_attr_e( 'Search videos by title', 'all-in-one-video-gallery' ); ?>" value="<?php echo esc_attr( $attributes['vi'] ); ?>" />
					</div>
							
					<div class="aiovg-form-group aiovg-field-submit">
						<button type="submit" class="aiovg-button"> 
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">
								<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
							</svg>
						</button>
					</div>
				</div>			

				<button type="button" class="aiovg-button aiovg-button-add-video" onclick="location.href='<?php echo aiovg_premium_get_video_form_page_url(); ?>'">
					<?php esc_html_e( 'Add New Video', 'all-in-one-video-gallery' ); ?>
				</button>
			</div>
		</form>

		<br />

		<table id="aiovg-user-videos-list">
			<tr>
				<th class="aiovg-col-image"></th>
				<th class="aiovg-col-caption"><?php esc_html_e( 'Videos', 'all-in-one-video-gallery' ); ?></th>
				<th class="aiovg-col-action"><?php esc_html_e( 'Actions', 'all-in-one-video-gallery' ); ?></th>
			</tr>
			<?php if ( ! $aiovg_query->have_posts() ) : ?>
				<tr class="aiovg-no-items-found">
					<td class="aiovg-text-center" colspan="3">
						<?php echo esc_html( aiovg_get_message( 'videos_empty' ) ); ?>
					</td>
				</tr>
			<?php else : ?>
				<?php
				// Start the loop
				while ( $aiovg_query->have_posts() ) :        
					$aiovg_query->the_post();  
					
					$post_meta = get_post_meta( get_the_ID() );

					$image_data = aiovg_get_image( get_the_ID(), 'thumbnail', 'post', true );
					$image = $image_data['src'];
					?>
					<tr>         
						<td class="aiovg-col-image">
							<div class="aiovg-relative">
								<a href="<?php the_permalink(); ?>" class="aiovg-link-image">
									<img src="<?php echo esc_url( $image ); ?>" alt="<?php the_title_attribute(); ?>" />

									<?php if ( ! empty( $post_meta['duration'][0] ) ) : ?>
										<div class="aiovg-duration">
											<?php echo esc_html( $post_meta['duration'][0] ); ?>
										</div>
									<?php endif; ?>
									
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="32" height="32" viewBox="0 0 32 32" class="aiovg-svg-icon-play aiovg-flex-shrink-0">
										<path d="M16 0c-8.837 0-16 7.163-16 16s7.163 16 16 16 16-7.163 16-16-7.163-16-16-16zM16 29c-7.18 0-13-5.82-13-13s5.82-13 13-13 13 5.82 13 13-5.82 13-13 13zM12 9l12 7-12 7z"></path>
									</svg>
								</a> 
							</div>                      
						</td>
						<td class="aiovg-col-caption">
							<div class="aiovg-title">
								<a href="<?php the_permalink(); ?>" class="aiovg-link-title">
									<?php echo esc_html( get_the_title() ); ?>
								</a>
							</div>

							<?php
							$meta = array();

							// Date
							$icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">
								<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
							</svg>';
				
							$meta[] = sprintf(
								'<div class="aiovg-date aiovg-flex aiovg-gap-1 aiovg-items-center">%s<time>%s</time></div>',
								$icon,
								esc_html( aiovg_get_the_date() )
							);

							// Views
							$icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">
								<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
								<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
							</svg>';
				
							$meta[] = sprintf(
								'<div class="aiovg-views aiovg-flex aiovg-gap-1 aiovg-items-center">%s<span class="aiovg-views-count">%s</span></div>',
								$icon,
								sprintf( 
									esc_html__( '%s views', 'all-in-one-video-gallery' ), 
									isset( $post_meta['views'] ) ? number_format_i18n( $post_meta['views'][0] ) : 0
								)
							);

							// ...
							if ( count( $meta ) ) {
								echo '<div class="aiovg-meta aiovg-flex aiovg-flex-wrap aiovg-gap-1 aiovg-items-center aiovg-text-small">';
								echo implode( '<span class="aiovg-text-separator">/</span>', $meta );
								echo '</div>';
							}
							?>
						
							<?php
							// Categories
							if ( $categories = get_the_terms( get_the_ID(), 'aiovg_categories' ) ) {
								$meta = array();

								foreach ( $categories as $category ) {
									$category_url = aiovg_get_category_page_url( $category );

									$meta[] = sprintf( 
										'<a href="%s" class="aiovg-link-category">%s</a>',
										esc_url( $category_url ), 
										esc_html( $category->name ) 
									);
								}

								echo '<div class="aiovg-category aiovg-flex aiovg-flex-wrap aiovg-gap-1 aiovg-items-center aiovg-text-small">';
								echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">
									<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
								</svg>';
								echo '<div class="aiovg-item-category">' . implode( '<span class="aiovg-separator">,</span></div><div class="aiovg-item-category">', $meta ) . '</div>';
								echo '</div>';
							}
							?>

							<?php
							// Tags
							if ( $tags = get_the_terms( get_the_ID(), 'aiovg_tags' ) ) {
								$meta = array();

								foreach ( $tags as $tag ) {
									$tag_url = aiovg_get_tag_page_url( $tag );

									$meta[] = sprintf( 
										'<a href="%s" class="aiovg-link-tag">%s</a>', 
										esc_url( $tag_url ), 
										esc_html( $tag->name ) 
									);
								}

								echo '<div class="aiovg-tag aiovg-flex aiovg-flex-wrap aiovg-gap-1 aiovg-items-center aiovg-text-small">';
								echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">
									<path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" />
								</svg>';
								echo '<div class="aiovg-item-tag">' . implode( '<span class="aiovg-separator">,</span></div><div class="aiovg-item-tag">', $meta ) . '</div>';
								echo '</div>';
							}
							?>

							<div class="aiovg-description aiovg-text-small">
								<strong><?php esc_html_e( 'Status', 'all-in-one-video-gallery' ); ?>:</strong> 
								<?php
								$obj = get_post_status_object( $post->post_status );
								echo esc_html( $obj->label );
								?>
							</div>
						</td>		
						<td class="aiovg-col-action">
							<a href="<?php echo aiovg_premium_get_edit_video_page_url( get_the_ID() ); ?>" class="aiovg-link-edit-video">
								<?php esc_html_e( 'Edit', 'all-in-one-video-gallery' ); ?>
							</a>

							<span class="aiovg-link-separator">/</span>

							<a onclick="return confirm( '<?php esc_html_e( 'Are you SURE you want to delete this video?', 'all-in-one-video-gallery' ); ?>' )" href="<?php echo aiovg_premium_get_delete_video_page_url( get_the_ID() ); ?>" class="aiovg-link-delete-video">
								<?php esc_html_e( 'Delete', 'all-in-one-video-gallery' ); ?>
							</a>
						</td>
					</tr>         
				<?php endwhile; // End of the loop ?>   

				<?php wp_reset_postdata(); // Use reset postdata to restore orginal query ?>
			<?php endif; ?>
		</table>

		<?php the_aiovg_pagination( $aiovg_query->max_num_pages, '', $attributes['paged'] ); // Pagination ?>
	<?php endif; ?>
</div>
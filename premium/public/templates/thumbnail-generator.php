<?php

/**
 * Thumbnail Generator.
 *
 * @link       https://plugins360.com
 * @since      1.6.6
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */

$html5_thumbnail_generator_enabled = ( is_admin() || ! empty( $attributes['enable_html5_thumbnail_generator'] ) ) ? 1 : 0;
?>

<div id="aiovg-thumbnail-generator" class="aiovg-toggle-fields aiovg-type-default"<?php if ( ! $html5_thumbnail_generator_enabled ) echo ' style="display: none;"'; ?>>
	<!-- Header -->
	<div class="aiovg-header">
		<?php 
		if ( $html5_thumbnail_generator_enabled ) {
			esc_html_e( '(OR) use the "Capture Image" button below to generate an image from your video.', 'all-in-one-video-gallery' ); 
		}
		?>
	</div>

	<!-- Body -->
	<div class="aiovg-body">		
		<?php if ( $html5_thumbnail_generator_enabled ) : ?>
			<!-- Capture Image -->			
			<div id="aiovg-html5-thumbnail-generator" class="aiovg-item">
				<button type="button" class="button button-secondary">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">
						<path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
						<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
					</svg>
					<?php esc_html_e( 'Capture Image', 'all-in-one-video-gallery' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>

	<!-- Footer -->
	<div class="aiovg-footer aiovg-notice aiovg-notice-info" style="display: none;">
		<?php esc_html_e( 'When you submit the form, only the image you\'ve selected will be stored in the server to keep the site clean. The other images will be deleted automatically. Still, you can recreate these images using the "Capture Image" button.', 'all-in-one-video-gallery' ); ?>
	</div>	
</div>

<?php if ( $html5_thumbnail_generator_enabled ) : ?>
	<!-- Modal -->
	<div id="aiovg-thumbnail-generator-modal" class="aiovg aiovg-modal mfp-hide">
		<div id="aiovg-thumbnail-generator-modal-body" class="aiovg-modal-body">
			<div class="aiovg-modal-title">
				<?php esc_html_e( 'Play and capture the scene you wish as a video preview image.', 'all-in-one-video-gallery' ); ?>
			</div>

			<video id="aiovg-thumbnail-generator-player" controls crossorigin="anonymous"></video>

			<canvas id="aiovg-thumbnail-generator-canvas"></canvas>

			<div class="aiovg-modal-actions">
				<div class="aiovg-pull-left">
					<?php printf( __( 'Seek to %s seconds', 'all-in-one-video-gallery' ), '<select id="aiovg-thumbnail-generator-seekto" disabled></select>' );	?>
				</div>

				<div class="aiovg-pull-right">
					<button type="button" id="aiovg-thumbnail-generator-button" class="button button-secondary" disabled>
						<?php esc_html_e( 'Capture This Scene', 'all-in-one-video-gallery' ); ?>
					</button>
				</div>

				<div class="aiovg-clearfix"></div>
			</div>
		</div>
	</div>
<?php endif;

<?php

/**
 * SEO.
 *
 * @link       https://plugins360.com
 * @since      2.4.0
 *
 * @package    All_In_One_Video_Gallery
 * @subpackage All_In_One_Video_Gallery/premium
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AIOVG_Premium_Public_SEO class.
 *
 * @since 2.4.0
 */
class AIOVG_Premium_Public_SEO {

	/**
	 * Filters the player html output.
	 * 
	 * @since  2.4.0
	 * @param  string $html   Player HTML code.
	 * @param  array  $params Player params.
	 * @return string $html   Filtered player HTML code.
	 */
	public function player_html( $html, $params ) {
		$seo_settings = get_option( 'aiovg_seo_settings' );

		if ( ! empty( $seo_settings['schema_markup'] ) ) {
			if ( 'aiovg_videos' == $params['post_type'] ) {
				$post_id = (int) $params['post_id'];
				$video_title = get_the_title( $post_id );
				$video_description = aiovg_get_excerpt( $post_id, 160, '', false );
				$video_date = get_the_date( 'c', $post_id );
				$video_duration = get_post_meta( $post_id, 'duration', true );
				$video_url = ! empty( $params['embed_url'] ) ? $params['embed_url'] : aiovg_get_player_page_url( $post_id );
				$video_views = get_post_meta( $post_id, 'views', true );

				$image_data = aiovg_get_image( $post_id, 'large' );
				$image_url = $image_data['src'];

				if ( ! empty( $video_description ) && ! empty( $image_url ) ) {
					$schema_markup = array();

					$schema_markup['@context'] = 'https://schema.org';				
					$schema_markup['@type'] = 'VideoObject';
					$schema_markup['@id'] = aiovg_sanitize_url( home_url() ) . '#/schema/video/' . $post_id;
					$schema_markup['name'] = sanitize_text_field( $video_title );
					$schema_markup['description'] = sanitize_text_field( $video_description );
					$schema_markup['thumbnailUrl'] = aiovg_sanitize_url( $image_url );
					$schema_markup['uploadDate'] = $video_date;	

					$schema_markup['embedUrl'] = aiovg_sanitize_url( $video_url );

					if ( ! empty( $video_duration ) ) {
						$schema_markup['duration'] = $this->iso_8601_duration( $video_duration );
					}

					if ( ! empty( $video_views ) ) {
						$schema_markup['interactionStatistic'] = array(
							'@type' => 'InteractionCounter',
							'interactionType' => array(
								'@type' => 'http://schema.org/WatchAction'
							),
							'userInteractionCount' => (int) $video_views
						);
					}

					$html .= sprintf(
						'<script type="application/ld+json">%s</script>',
						json_encode( $schema_markup )
					);
				}
			}
		}

		return $html;
	}

	/**
	 * Convert string duration to ISO 8601 duration format.
	 * 
	 * @since  2.4.0
	 * @access private
	 * @param  string  $duration     Video duration.
	 * @return string  $iso_duration Formatted video duration.
	 */
	private function iso_8601_duration( $duration ) {
		if ( ! empty( $duration ) ) {
			$parts = explode( ':', $duration );
			$parts = array_map( 'intval', $parts );

			$hours   = 0;
			$minutes = 0;
			$seconds = 0;

			if ( 1 == count( $parts ) ) {
				$seconds = $parts[0];
			}

			if ( 2 == count( $parts ) ) {
				$minutes = $parts[0];
				$seconds = $parts[1];
			}

			if ( count( $parts ) > 2 ) {
				$hours   = $parts[0];
				$minutes = $parts[1];
				$seconds = $parts[2];
			}

			$duration = sprintf( 
				'PT%s%s%s',
                $hours > 0 ? ( $hours < 10 ? '0' . $hours : $hours ) . 'H' : '',
                $minutes > 0 ? ( $minutes < 10 ? '0' . $minutes : $minutes ) . 'M' : '',
                ( $seconds < 10 ? '0' . $seconds : $seconds ) . 'S'
            );
		}

		return $duration; 
	}

}

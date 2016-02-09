<?php

class WP_CLI_Unsplash_Command extends WP_CLI_Command {
	/**
	 * Import images from Unsplash into your Media Library.
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : How many media items to generate. Default: 10
	 *
	 * [--media_author=<login>]
	 * : The author of the generated media. Default: none
	 *
	 * [--media_date=<yyyy-mm-dd|random>]
	 * : The date of the generated media. Default: current date
	 *
	 * [--media_dimensions=<dimensions>]
	 * : The dimensions of the generated media. Default: none
	 *
	 * ## EXAMPLES
	 *
	 *     wp unsplash --count=10
	 *     wp unsplash --media_date=random
	 *     wp unsplash --media_dimensions=1080x720
	 */
	public function __invoke( $args, $assoc_args = array() ) {
		$defaults = array(
			'count'            => 10,
			'media_author'     => false,
			'media_date'       => current_time( 'mysql' ),
			'media_dimensions' => false,
		);
		extract( array_merge( $defaults, $assoc_args ), EXTR_SKIP );

		if ( $media_author ) {
			$user_fetcher = new \WP_CLI\Fetchers\User;
			$media_author = $user_fetcher->get_check( $media_author )->ID;
		}

		$url = 'https://source.unsplash.com/random/';

		if ( $media_dimensions ) {
			$url .= $media_dimensions;
		}

		$notify = \WP_CLI\Utils\make_progress_bar( 'Generating media', $count );

		for ( $i = 0; $i < $count; $i++ ) {
			$tmp_file = download_url( $url );

			if ( ! is_wp_error( $tmp_file ) ) {
				$this->_process_downloaded_image( $tmp_file, $media_author, $media_date );
			} else {
				WP_CLI::warning( 'Could not download image from Unsplash API.' );
			}

			if ( file_exists( $tmp_file ) ) {
				unlink( $tmp_file );
			}

			$notify->tick();
		}

		$notify->finish();
	}

	/**
	 * Process downloaded image
	 *
	 * @param string   $tmp_file
	 * @param int|bool $media_author
	 * @param string   $media_date
	 *
	 * @return bool
	 */
	private function _process_downloaded_image( $tmp_file, $media_author, $media_date ) {
		if ( 'image/jpeg' !== ( $mime = mime_content_type( $tmp_file ) ) ) {
			WP_CLI::warning( 'Invalid image type.' );

			return false;
		}

		$info       = pathinfo( $tmp_file );
		$name       = ( isset( $info['filename'] ) ? $info['filename'] : 'unsplash' );
		$file_array = array(
			'name'     => $name . '.jpeg',
			'type'     => $mime,
			'tmp_name' => $tmp_file,
			'error'    => 0,
			'size'     => filesize( $tmp_file ),
		);

		if ( 'random' === $media_date ) {
			$timestamp  = current_time( 'timestamp' ) - mt_rand( 0, 315576000 ); // In last 10 years
			$media_date = gmdate( 'Y-m-d H:i:s', $timestamp );
		}

		$file = wp_handle_sideload( $file_array, array( 'test_form' => false ), $media_date );

		if ( isset( $file['error'] ) ) {
			WP_CLI::warning( 'Error uploading file.' );

			return false;
		}

		$attachment = array(
			'post_mime_type' => $file['type'],
			'guid'           => $file['url'],
			'post_title'     => $name,
			'post_author'    => $media_author,
			'post_date'      => $media_date,
		);

		// Save the attachment metadata
		$id = wp_insert_attachment( $attachment, $file['file'] );

		if ( is_wp_error( $id ) ) {
			WP_CLI::warning( 'Error creating attachment.' );

			return false;
		}

		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file['file'] ) );
	}
}

WP_CLI::add_command( 'unsplash', 'WP_CLI_Unsplash_Command' );

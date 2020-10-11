<?php
/*
Plugin Name: Add Attachment Id
Author: webfood
Plugin URI: http://webfood.info/
Description: Add Attachment Id.
Version: 0.1
Author URI: http://webfood.info/
Text Domain: Add Attachment Id
Domain Path: /languages

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2019 (email : webfood.info@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function add_img_size($content){
  $pattern = '/<img [^>]*?src="([^"]+?)"[^>]*?>/iu';
  preg_match_all($pattern, $content, $imgs);
  foreach ( $imgs[0] as $i => $img ) {
    if ( false !== strpos( $img, 'wp-image-' )) {
      continue;
    }
    $img_url = $imgs[1][$i];

		if(strpos($img_url,home_url()) === false ){
			$img_url = home_url().$img_url;
		}

    $id = get_attachment_id( $img_url );

    if ( false === $img_size ) {
      continue;
    }

    if(strpos($imgs[0][$i], 'class="') !== false ){
      $replaced_img = str_replace( 'class="', 'class="wp-image-'.$id.' ', $imgs[0][$i] );
    }else{
      $replaced_img = str_replace( '<img', '<img class="wp-image-'.$id.'" ', $imgs[0][$i] );
    }

    $content = str_replace( $img, $replaced_img, $content );
  }
  return $content;
}
add_filter('the_content','add_img_size', -9999);

function get_attachment_id( $url ) {

	$attachment_id = 0;

	$dir = wp_upload_dir();

	if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?
		$file = basename( $url );

		$query_args = array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'fields'      => 'ids',
			'meta_query'  => array(
				array(
					'value'   => $file,
					'compare' => 'LIKE',
					'key'     => '_wp_attachment_metadata',
				),
			)
		);

		$query = new WP_Query( $query_args );

		if ( $query->have_posts() ) {

			foreach ( $query->posts as $post_id ) {

				$meta = wp_get_attachment_metadata( $post_id );

				$original_file       = basename( $meta['file'] );
				$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );

				if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
					$attachment_id = $post_id;
					break;
				}

			}

		}

	}

	return $attachment_id;
}

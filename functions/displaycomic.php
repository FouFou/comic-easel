<?php

if (!defined('CEO_FEATURE_DISABLE_MOTION_ARTIST') && ceo_pluginfo('enable_motion_artist_support')) 
	add_action('wp_head', 'ceo_add_motion_artist_header_info');

function ceo_add_motion_artist_header_info() {
	global $post;
	if (!is_admin()) {
		if ((is_home() || is_front_page()) && !is_paged() && !ceo_pluginfo('disable_comic_on_home_page')) {
			$order = (ceo_pluginfo('display_first_comic_on_home_page')) ?  'ASC' : 'DESC';
			$args = array(
					'showposts' => 1,
					'posts_per_page' => 1,
					'order' => $order,
					'post_type' => 'comic'
					);
			$posts = get_posts($args);
			foreach ($posts as $post) {
				setup_postdata($post);
			}
		}
		if (!empty($post) && ($post->post_type == 'comic')) {
			$motion_artist_comic = get_post_meta( $post->ID, 'ma-directory', true );
			$motion_artist_id = get_post_meta( $post->ID, 'ma-id', true );
			if (!empty($motion_artist_comic)) {
				echo '<base href="'.get_stylesheet_directory_uri().'/motion-artist/'.$motion_artist_comic.'/" />';
				echo '<script src="http://motionartist.smithmicro.com/public/motionartist_1.0.js"></script>'."\r\n";
				echo '<script src="'.get_stylesheet_directory_uri().'/motion-artist/'.$motion_artist_comic.'/scripts/'.$motion_artist_id.'.js"></script>';
			}
		}
	}
}

function ceo_display_motion_artist_comic($motion_artist_comic = '') {
	global $post;
	$output = '';
	if (!empty($motion_artist_comic)){
		$motion_artist_id = get_post_meta( $post->ID, 'ma-id', true );
		$motion_artist_height = get_post_meta( $post->ID, 'ma-height', true);
		$motion_artist_width = get_post_meta( $post->ID, 'ma-width', true);
		$output .= '<center>'."\r\n";
		$output .= '<div class="MADoc">'."\r\n";
		$output .= '    <canvas id="'.$motion_artist_id.'_canvas" width="'.$motion_artist_width.'px" height="'.$motion_artist_height.'px"></canvas>'."\r\n";
		$output .= '</div>'."\r\n";
		$output .= '<div class="MAButtons">'."\r\n";
		$output .= '    <ul class="MAButtonSet">'."\r\n";
		$output .= '        <li><button class="MAButton" id="'.$motion_artist_id.'_pauseButton">Play</button></li>'."\r\n";
		$output .= '    </ul>'."\r\n";
		$output .= '</div>'."\r\n";
		$output .= '</center>'."\r\n";
	}
	return apply_filters('ceo_display_motion_artist_comic', $output);
}

function ceo_display_featured_image_comic($size = 'full') {
	global $post;
	$output = '';
	$post_image_id = get_post_thumbnail_id($post->ID);
	if ($post_image_id) { // If there's a featured image.
		$hovertext = ceo_the_hovertext();
		$thumbnail = wp_get_attachment_image_src( $post_image_id, $size, false);
		if (is_array($thumbnail)) {
			$thumbnail = reset($thumbnail);
			
			$comic_lightbox = get_post_meta( $post->ID, 'comic-open-lightbox', true );
			if (is_wp_error($comic_lightbox)) $comic_lightbox = false;
			
			if (ceo_pluginfo('navigate_only_chapters')) {
				$next_comic = ceo_get_next_comic_in_chapter_permalink();
			} else {
				$next_comic = ceo_get_next_comic_permalink();
			}
			
			if ($comic_lightbox) {
				$output .= '<a href="'.$thumbnail.'" title="'.$hovertext.'" rel="lightbox">';
			}
			
			if (ceo_pluginfo('click_comic_next') && !empty($next_comic) && !$comic_lightbox) {
				$output .= '<a href="'.$next_comic.'" title="'.$hovertext.'">';
			}
						
			$output .= '<img src="'.$thumbnail.'" alt="'.$hovertext.'" title="'.$hovertext.'" />';
			if ((ceo_pluginfo('click_comic_next') && !empty($next_comic)) || $comic_lightbox) {
				$output .= '</a>';
			}
			if ($comic_lightbox) $output .= '<div class="comic-lightbox-text">'.__('Click comic to view larger version.','comiceasel').'</div>';
		}
	}
	return apply_filters('ceo_display_featured_image_comic', $output);
}

function ceo_display_comic_gallery($size = 'full') {
	global $post;
	$output = '';
	if (ceo_pluginfo('click_comic_next')) {
		if (ceo_pluginfo('navigate_only_chapters')) {
			$next_comic = ceo_get_next_comic_in_chapter_permalink();
		} else {
			$next_comic = ceo_get_next_comic_permalink();
		}
	}
	$hovertext = ceo_the_hovertext();
	$comic_galleries_full = get_post_meta( $post->ID, 'comic-gallery-full', true );
	if ($comic_galleries_full) {
		$comic_lightbox = get_post_meta( $post->ID, 'comic-open-lightbox', true );
		$comic_galleries_jquery = get_post_meta( $post->ID, 'comic-gallery-jquery', true );
		if ($images = get_posts(array(
						'post_parent'    => $post->ID,
						'post_type'      => 'attachment',
						'numberposts'    => -1, // show all
						'post_status'    => null,
						'post_mime_type' => 'image',
						'orderby'        => 'menu_order',
						'order'           => 'ASC'
						))) {
			$count = 0;
			if ($comic_galleries_jquery) wp_enqueue_script('multicomic', ceo_pluginfo('plugin_url') . '/js/multicomic.js', null, null, true);
			foreach($images as $image) {
				if ($comic_galleries_jquery) $output .= '<div id="comic-'.$count.'" class="comicpane">';
				$thumbnail   = wp_get_attachment_image_src($image->ID, 'full');
				$thumbnail = reset($thumbnail);

//				$thumbnail = apply_filters('jetpack_photon_url', $thumbnail);

				if ($comic_lightbox) {
					$output .= '<a href="'.$thumbnail.'" title="'.$hovertext.'" rel="lightbox">';
				}
				if (ceo_pluginfo('click_comic_next') && !empty($next_comic) && !$comic_lightbox) {
					$output .= '<a href="'.$next_comic.'" title="'.$hovertext.'">';
				}
				$output .= '<img src="'.$thumbnail.'" alt="'.$hovertext.'" title="'.$hovertext.'" />';
				if ((ceo_pluginfo('click_comic_next') && !empty($next_comic)) || $comic_lightbox) {
					$output .= '</a>';
				}

				if ($comic_galleries_jquery) $output .= "</div>\r\n";
				$count += 1;
			}
			if ($comic_galleries_jquery) $output .= "<button id=\"show-".$count."\" type=\"button\" style=\"display:none;\">".$count."</button>\r\n";
			if ($comic_lightbox) $output .= '<div class="comic-lightbox-text">'.__('Click comic to view larger version.','comiceasel').'</div>';
		}			
	} else {
		$columns = get_post_meta( $post->ID, 'comic-gallery-columns', true );
		if (empty($columns)) $columns = 5;
		$args = array(
				'id'         => $post->ID,
				'columns'    => $columns,
				'exclude'    => $post_image_id
				);
		$output .= gallery_shortcode($args);
	}	
	return apply_filters('ceo_display_comic_gallery', $output);
}

function ceo_display_comic($size = 'full') {
	global $post;
    if ( post_password_required() ) { 
		return __('This comic is password protected.','comiceasel');
    }
	$output = '';
	if (ceo_the_above_html()) $output .= html_entity_decode(ceo_the_above_html())."\r\n";
	
	$motion_artist_comic = get_post_meta( $post->ID, 'ma-directory', true );
	if ($motion_artist_comic && !defined('CEO_FEATURE_DISABLE_MOTION_ARTIST')) {
		$output .= ceo_display_motion_artist_comic($motion_artist_comic);
	} else {
		$comic_galleries = get_post_meta( $post->ID, 'comic-gallery', true );
		if ($comic_galleries) {
			$output .= ceo_display_comic_gallery($size);
		} else {
			$output .= ceo_display_featured_image_comic($size);
		}
	}
	
	if (ceo_the_below_html()) $output .= html_entity_decode(ceo_the_below_html())."\r\n";
	if ($output) { 
		return apply_filters('ceo_comics_display_comic', $output);
	} else
		return apply_filters('ceo_comics_display_comic', __('<!-- No HTML, Gallery, Motion Artist Comic or Featured Image Found. //-->', 'comiceasel'));
}

add_filter('ceo_comics_display_comic', 'ceo_filter_comic_output',10,1);

function ceo_filter_comic_output($output = '') {
	global $post;
	return $output;
}

function ceo_the_hovertext($override_post = null) {
	global $post;
	$post_to_use = !is_null($override_post) ? $override_post : $post;
	$hovertext = esc_attr( get_post_meta( $post_to_use->ID, 'comic-hovertext', true ) );
	if (empty($hovertext)) $hovertext = esc_attr( get_post_meta($post_to_use->ID, 'hovertext', true) ); // check if using old hovertext
//	return (empty($hovertext)) ? get_the_title($post_to_use->ID) : $hovertext;
	return (empty($hovertext)) ? '' : $hovertext;
}

function ceo_the_above_html($override_post = null) {
	global $post;
	$post_to_use = !is_null($override_post) ? $override_post : $post;
	$html_to_use = get_post_meta( $post_to_use->ID, 'comic-html-above', true);
	return $html_to_use;
}

function ceo_the_below_html($override_post = null) {
	global $post;
	$post_to_use = !is_null($override_post) ? $override_post : $post;
	$html_to_use = get_post_meta( $post_to_use->ID, 'comic-html-below', true);
	return $html_to_use;
}

// We use this type of query so that $post is set, it's already set with is_single - but needs to be set on the home page
function ceo_display_comic_area() {
	global $wp_query, $post;
	if (is_single()) {
		ceo_display_comic_wrapper();
	} else {
		if ((is_home() || is_front_page()) && !is_paged() && !ceo_pluginfo('disable_comic_on_home_page'))  {
			ceo_protect();
			$order = (ceo_pluginfo('display_first_comic_on_home_page')) ?  'asc' : 'desc';
			$comic_args = array(
					'showposts' => 1,
					'posts_per_page' => 1,
					'post_type' => 'comic',
					'order' => $order
					);
			$wp_query->in_the_loop = true; $comicFrontpage = new WP_Query(); $comicFrontpage->query($comic_args);
			while ($comicFrontpage->have_posts()) : $comicFrontpage->the_post();
				ceo_display_comic_wrapper();
			endwhile;
			ceo_unprotect();
		}
	}
}

// Do the thumbnail display functions here.
function ceo_display_comic_thumbnail($thumbnail_size = 'thumbnail', $override_post = null) {
	global $post;
	$thumbnail = $output = '';
	$post_to_use = !empty($override_post) ? $override_post : $post;
	if (class_exists('MultiPostThumbnails') && ($thumbnail_size == 'secondary-image') && is_null($override_post)) {
		$thumbnail = MultiPostThumbnails::get_the_post_thumbnail(get_post_type(), 'secondary-image');
	} else {
		$thumbnail = get_the_post_thumbnail($post_to_use->ID, $thumbnail_size);
	}
	if ( has_post_thumbnail($post_to_use->ID) ) {
		$output =  '<a href="'.get_permalink($post_to_use->ID).'" rel="bookmark" title="'.get_the_title().'">'.$thumbnail.'</a>'."\r\n";
	} else {
//			$output = "No Thumbnail Found.";
	}
	return apply_filters('easel_display_comic_thumbnail', $output);
}

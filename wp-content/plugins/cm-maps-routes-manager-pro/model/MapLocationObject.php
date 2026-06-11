<?php
namespace com\cminds\mapsroutesmanager\model;

use com\cminds\mapsroutesmanager\controller\TaxonomyController;

class MapLocationObject extends PostType {
	
	const POST_TYPE = 'cmloc_object';
	
	const META_RATE = '_cmmrm_route_rate';
	const META_RATE_USER_ID = '_cmmrm_route_rate_user_id';
	const META_RATE_TIME = '_cmmrm_route_rate_time';
	const META_VIEWS = '_cmmrm_views';
	const META_ICON = '_cmloc_icon';
	const META_ICON_SIZE = '_cmloc_icon_size';
	
	const ICON_SIZE_SMALL = 'small';
	const ICON_SIZE_NORMAL = 'normal';
	const ICON_SIZE_LARGE = 'large';
	
	static function registerPostType() {
		// don't
	}

	static function getIndexMapJSLocations() {
		global $wpdb;
		
		if((is_plugin_active('cm-map-locations/cm-map-locations-pro.php') || is_plugin_active('cm-map-locations-pro/cm-map-locations-pro.php')) && Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_INTEGRATION) == '1' && Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_MERGE_CATEGORIES) == '1') {
			
			$join = '';
			if(isset($_GET['cmmrm_category']) && $_GET['cmmrm_category'] != '') {
				$join .= " INNER JOIN `$wpdb->term_relationships` wtr ON (r.`ID` = wtr.`object_id`) ";
				$join .= " INNER JOIN `$wpdb->term_taxonomy` wtt ON (wtr.`term_taxonomy_id` = wtt.`term_taxonomy_id`) ";
				$join .= " INNER JOIN `$wpdb->terms` wt ON (wt.`term_id` = wtt.`term_id`) AND wt.slug = '".$_GET['cmmrm_category']."' ";
			}

			$customTaxonomies = Settings::getOption(TaxonomyController::OPTION_CUSTOM_TAXONOMIES);
			if($customTaxonomies) {
				$customTaxonomies_counter = 1;
				foreach ($customTaxonomies as $tax) {
					if($tax['show_index_filter'] == '1') {
						if(isset($_GET[$tax['taxonomy']]) && $_GET[$tax['taxonomy']] != '') {
							$join .= " INNER JOIN `$wpdb->term_relationships` wtr".$customTaxonomies_counter." ON (r.`ID` = wtr".$customTaxonomies_counter.".`object_id`) ";
							$join .= " INNER JOIN `$wpdb->term_taxonomy` wtt".$customTaxonomies_counter." ON (wtr".$customTaxonomies_counter.".`term_taxonomy_id` = wtt".$customTaxonomies_counter.".`term_taxonomy_id`) ";
							$join .= " INNER JOIN `$wpdb->terms` wt".$customTaxonomies_counter." ON (wt".$customTaxonomies_counter.".`term_id` = wtt".$customTaxonomies_counter.".`term_id`) AND wt".$customTaxonomies_counter.".slug = '".$_GET[$tax['taxonomy']]."' ";
							$customTaxonomies_counter++;
						}
					}
				}
			}

			$sql = $wpdb->prepare("SELECT
					r.ID,
					r.post_title AS name,
					lm_lat.meta_value AS lat,
					lm_lon.meta_value AS `lng`,
					rm_is.meta_value AS `iconSize`,
					lm_lat.post_id As `parent_id`
					FROM $wpdb->posts r
					".$join."
					JOIN $wpdb->posts l ON l.post_parent = r.ID AND l.post_type = %s AND l.post_status IN ('publish', 'inherit')
					JOIN $wpdb->postmeta lm_lat ON lm_lat.post_id = l.ID AND lm_lat.meta_key = %s
					JOIN $wpdb->postmeta lm_lon ON lm_lon.post_id = l.ID AND lm_lon.meta_key = %s
					LEFT JOIN $wpdb->postmeta rm_is ON rm_is.post_id = r.ID AND rm_is.meta_key = %s
					WHERE r.post_type = %s AND r.post_status = 'publish'
				",
				MapLocationPlacemark::POST_TYPE,
				MapLocationPlacemark::META_LAT,
				MapLocationPlacemark::META_LONG,
				MapLocationObject::META_ICON_SIZE,
				MapLocationObject::POST_TYPE
			);
		} else {
			$sql = $wpdb->prepare("SELECT
					r.ID,
					r.post_title AS name,
					lm_lat.meta_value AS lat,
					lm_lon.meta_value AS `lng`,
					rm_is.meta_value AS `iconSize`,
					lm_lat.post_id As `parent_id`
					FROM $wpdb->posts r
					JOIN $wpdb->posts l ON l.post_parent = r.ID AND l.post_type = %s AND l.post_status IN ('publish', 'inherit')
					JOIN $wpdb->postmeta lm_lat ON lm_lat.post_id = l.ID AND lm_lat.meta_key = %s
					JOIN $wpdb->postmeta lm_lon ON lm_lon.post_id = l.ID AND lm_lon.meta_key = %s
					LEFT JOIN $wpdb->postmeta rm_is ON rm_is.post_id = r.ID AND rm_is.meta_key = %s
					WHERE r.post_type = %s AND r.post_status = 'publish'
				",
				MapLocationPlacemark::POST_TYPE,
				MapLocationPlacemark::META_LAT,
				MapLocationPlacemark::META_LONG,
				MapLocationObject::META_ICON_SIZE,
				MapLocationObject::POST_TYPE
			);
		}
		
		$objects = $wpdb->get_results($sql, ARRAY_A);
		
		if(is_plugin_active('polylang/polylang.php')) {
			foreach($objects as $i => $row) {
				if (pll_current_language() != pll_get_post_language($row['ID'])) {
					unset($objects[$i]);
				}
			}
			$objects = array_values($objects);
		}

		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
			foreach($objects as $i => $row) {
				$current_language = apply_filters('wpml_current_language', null);
				$current_post = apply_filters('wpml_post_language_details', NULL, $row['ID']);
				$current_post_lang = $current_post['language_code'];
				if($current_language != $current_post_lang) {
					unset($objects[$i]);
				}
			}
			$objects = array_values($objects);
		}

		foreach ($objects as $i => $row) {
			$className = 'com\cminds\maplocations\model\Route';
			if (class_exists($className)) {
				if ($object = call_user_func(array($className, 'getInstance'), $row['ID'])) {					
					$objects[$i]['permalink'] = $object->getPermalink();
					//$objects[$i]['type'] = Location::TYPE_LOCATION;
					$objects[$i]['icon'] = $object->getIconUrl();

					$content = wpautop(get_option('cmloc_map_location_info_window_template', ''));
					$loc_data = get_post($row['ID']);
					
					$attachment_post_id = '';
					$attachment_posts = get_posts(array(
						'posts_per_page' => -1,
						'post_type' => 'attachment',
						'post_status' => 'any',
						'post_parent' => $row['ID'],
						'orderby' => 'menu_order',
						'order' => 'asc',
					));
					if(count($attachment_posts) > 0) {
						foreach($attachment_posts as $attach) {
							if(strpos($attach->post_mime_type, 'image') !== false) {
								$attachment_post_id = $attach->ID;
								break;
							}
						}
					}

					$content = str_replace('[title]', $loc_data->post_title, $content);
					$content = str_replace('[description]', $loc_data->post_content, $content);
					$content = str_replace('[address]', get_post_meta($row['parent_id'], '_cmloc_address', true), $content);
					$content = str_replace('[city]', get_post_meta($row['parent_id'], '_cmloc_city', true), $content);
					$content = str_replace('[latitude]', get_post_meta($row['parent_id'], '_cmloc_latitude', true), $content);
					$content = str_replace('[longitude]', get_post_meta($row['parent_id'], '_cmloc_longitude', true), $content);
					$content = str_replace('[postalcode]', get_post_meta($row['parent_id'], '_cmloc_postal_code', true), $content);
					$content = str_replace('[phone]', get_post_meta($row['parent_id'], '_cmloc_phone_number', true), $content);
					$content = str_replace('[website]', get_post_meta($row['parent_id'], '_cmloc_website', true), $content);
					$content = str_replace('[email]', get_post_meta($row['parent_id'], '_cmloc_email', true), $content);
					$content = str_replace('[url]', get_post_meta($row['parent_id'], '_cmloc_url', true), $content);
					$content = str_replace('[permalink]', get_permalink($loc_data->ID), $content);
					$content = str_replace('[imagesrc]', ($attachment_post_id != '')?wp_get_attachment_url($attachment_post_id):'', $content);
					$content = str_replace('[image]', ($attachment_post_id != '')?'<img src="'.wp_get_attachment_url($attachment_post_id).'" class="cmloc-location-image-'.$attachment_post_id.'" />':'', $content);

					$createddate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_time('U', true, $loc_data->ID));
					$content = str_replace('[createddate]', $createddate, $content);

					$updatedate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_modified_time('U', true, $loc_data->ID));
					$content = str_replace('[updatedate]', $updatedate, $content);

					$editlink = '';
					if(is_user_logged_in() && current_user_can('administrator')) {
						$editlink = '<a href="'.get_edit_post_link($loc_data->ID).'" target="_blank">'.Labels::getLocalized('dashboard_edit').'</a>';
					}
					$content = str_replace('[editlink]', $editlink, $content);
					
					$deletelink = '';
					/*
					if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
						
						$href = RouteController::getDashboardUrl('delete', array('id' => $loc_data->ID, 'nonce' => wp_create_nonce(DashboardController::DELETE_NONCE),));

						$deletelink = '<a href="'.$href.'" onClick="return confirm(\''.Labels::getLocalized('confirm_delete_msg').'\')">' . Labels::getLocalized( 'dashboard_delete' ) . '</a>';
					}
					*/
					$content = str_replace('[deletelink]', $deletelink, $content);

					$content = str_replace('[coordinates]', '', $content);

					$closelink = '<a href="javascript:void(0);" class="rinfowindow_closelink">'.Labels::getLocalized('close').'</a>';
					$content = str_replace( '[closelink]', $closelink, $content );
					
					$ctabutton = '';
					$content = str_replace( '[ctabutton]', $ctabutton, $content );

					$objects[$i]['infoContent'] = $content;
					
					$shape_fill_color = get_post_meta($row['parent_id'], '_cmloc_shape_fill_color', true);
					if($shape_fill_color == '') {
						$shape_fill_color = get_option('cmloc_location_shape_fill_opacity', '#000000');
					}
					$objects[$i]['shape_fill_color'] = $shape_fill_color;

					$shape_fill_opacity = get_post_meta($row['parent_id'], '_cmloc_shape_fill_opacity', true);
					if($shape_fill_opacity == '') {
						$shape_fill_opacity = get_option('cmloc_location_shape_fill_opacity', '0.2');
					}
					$objects[$i]['shape_fill_opacity'] = $shape_fill_opacity;
					
					$shape_stroke_color = get_post_meta($row['parent_id'], '_cmloc_shape_stroke_color', true);
					if($shape_stroke_color == '') {
						$shape_stroke_color = get_option('cmloc_location_shape_stroke_color', '#000000');
					}
					$objects[$i]['shape_stroke_color'] = $shape_stroke_color;
					
					$shape_stroke_opacity = get_post_meta($row['parent_id'], '_cmloc_shape_stroke_opacity', true);
					if($shape_stroke_opacity == '') {
						$shape_stroke_opacity = get_option('cmloc_location_shape_stroke_opacity', '1');
					}
					$objects[$i]['shape_stroke_opacity'] = $shape_stroke_opacity;

					$shape_stroke_weight = get_post_meta($row['parent_id'], '_cmloc_shape_stroke_weight', true);
					if($shape_stroke_weight == '') {
						$shape_stroke_weight = get_option('cmloc_location_shape_stroke_weight', '2');
					}
					$objects[$i]['shape_stroke_weight'] = $shape_stroke_weight;

					$objects[$i]['shape_type'] = get_post_meta($row['parent_id'], '_cmloc_shape_type', true);
					$objects[$i]['shape_polygon_coords'] = get_post_meta($row['parent_id'], '_cmloc_shape_polygon_coords', true);
					$objects[$i]['shape_circle_center'] = get_post_meta($row['parent_id'], '_cmloc_shape_circle_center', true);
					$objects[$i]['shape_circle_radius'] = get_post_meta($row['parent_id'], '_cmloc_shape_circle_radius', true);
					$objects[$i]['shape_rectangle_bounds'] = get_post_meta($row['parent_id'], '_cmloc_shape_rectangle_bounds', true);
					$objects[$i]['user_track'] = '';
					$objects[$i]['user_track_all'] = '';

					call_user_func(array($className, 'clearInstances'));
				}
			}
		}
		
		return $objects;
		
	}
		
}
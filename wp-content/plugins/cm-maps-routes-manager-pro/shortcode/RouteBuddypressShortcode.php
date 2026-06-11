<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\model;
use com\cminds\mapsroutesmanager\controller;

class RouteBuddypressShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cm-route-bp-index';

	static function shortcode($atts) {
		
		global $bp;

		if (isset($atts['routetype'])) {
			$atts['cmmrm_route_type'] = $atts['routetype'];
		}

		if (isset($atts['tag'])) {
			$tag = $atts['tag'];
		} else {
			$tag = '';
		}

		$atts = shortcode_atts(apply_filters('cmmrm_routes_shortcode_atts_defaults', array(
			'category' => null,
			'author' => null,
			'search' => null,
			'width' => '',
			'mapwidth' => '',
			'mapheight' => '',
			'showmap' => model\Settings::getOption(model\Settings::OPTION_INDEX_MAP_SHOW),
			'showlist' => 1,
			'showfilters' => 0,
			'ajax' => 0,
			'listlayout' => model\Settings::getOption(model\Settings::OPTION_INDEX_LAYOUT),
			'featuredimage' => model\Settings::getOption(model\Settings::OPTION_ROUTE_INDEX_FEATURED_IMAGE),
			'fancy' => model\Settings::getOption(model\Settings::OPTION_FANCY_STYLE_ENABLE),
			'limit' => model\Settings::getOption(model\Settings::OPTION_PAGINATION_LIMIT),
			'cmlocations' => model\Settings::getOption(model\Settings::OPTION_INDEX_MAP_LOCATIONS_INTEGRATION),
			'page' => 1,
			'query' => null,
		), $atts, static::SHORTCODE_NAME), $atts);
		
		$atts['mapId'] = mt_rand();
		
		if (isset($atts['query']) AND $atts['query'] instanceof \WP_Query) {
			$query = $atts['query'];
		} else {
			
			$current_group_slug = bp_get_current_group_slug();
			$current_group_ids = array(bp_get_current_group_id());
			
			if($current_group_slug == '') {
				
				//$current_username_from_url = bp_get_displayed_user_username();
				//$current_user_data = get_user_by('login', $current_username_from_url);
				//$current_user_id = $current_user_data->ID;
				
				$segments = array_values(array_filter(explode('/', $_SERVER['REQUEST_URI']), 'strlen'));
				if($segments[count($segments)-2] == 'maps' && $segments[count($segments)-1] != '') {
					$current_group_slug = $segments[count($segments)-1];
					$current_group_ids = array(groups_get_id($current_group_slug));
				} else {
					$group_ids = groups_get_user_groups(bp_displayed_user_id());
					$current_group_ids = $group_ids['groups'];
				}

			}

			$post_ids = array();

			$args = array(
				'posts_per_page'   => -1,
				'post_type'        => model\Route::POST_TYPE,
				'post_status'      => 'publish',
			);
			$posts_array = get_posts($args);
			if(count($posts_array) > 0) {
				foreach($posts_array as $post){
					if($post->ID){
						$bp_groups = get_post_meta($post->ID, '_cmmrm_bp_groups', true);
						if($bp_groups && is_array($bp_groups)) {
							if(count($bp_groups) > 0) {
								if(count($current_group_ids) > 0) {
									foreach($current_group_ids as $cgroup){
										if(in_array($cgroup, $bp_groups)) {
											$post_ids[] = $post->ID;
										}
									}
								}
							}
						}
					}
				}
			}

			$query = new \WP_Query(apply_filters('cmmrm_routes_shortcode_query', array(
				'post_type' => model\Route::POST_TYPE,
				'post_status' => 'publish',
				'post__in' => $post_ids,
				'author' => $atts['author'],
				'posts_per_page' => $atts['limit'],
				'page' => $atts['page'],
				'tag' => $tag,
				model\Category::TAXONOMY => $atts['category'],
			), $atts, static::SHORTCODE_NAME));
		}
		
		if (!is_null($atts['search'])) {
			$query->set('s', $atts['search']);
		}
		
		$routes = array_map(function($post) { return model\Route::getInstance($post); }, $query->get_posts());
		$totalRoutesNumber = $query->found_posts;
		
		controller\FrontendController::enqueueStyle();
		wp_enqueue_script('cmmrm-widget-index-map');
		
		do_action('cmmrm_load_single_page_scripts');
		wp_enqueue_script('cmmrm-index-ajax');
		
		$displayParams = model\Settings::getOption(model\Settings::OPTION_INDEX_ROUTE_PARAMS);
		
		global $wp_query, $wp_the_query;
		
		// Temporarily replace $wp_query
		$temp_wp_query = $wp_query;
		$wp_query = $query;
		
		// Temporarily replace controller's query
		$tempControllerQuery = controller\FrontendController::$query;
		controller\FrontendController::$query = $query;
		
		$cmlocations = '';

		$out = controller\RouteController::loadFrontendView('index',
				compact('displayParams', 'atts', 'routes', 'totalRoutesNumber', 'cmlocations'));
		
		// Restore original query
		$wp_query = $temp_wp_query;
		controller\FrontendController::$query = $tempControllerQuery;
		
		if(count($post_ids) == 0) {
			$out = model\Labels::getLocalized('route_not_found');
		}
		
		return $out;
	}

}
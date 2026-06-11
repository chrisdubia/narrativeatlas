<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\model;
use com\cminds\mapsroutesmanager\controller;

class MapShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cm-routes-map';

	static function shortcode($atts) {

		// For backwards compatibility:
		if (isset($atts['difficulty'])) {
			$atts['cmmrm_route_difficulty'] = $atts['difficulty'];
		}
		if (isset($atts['type'])) {
			$atts['cmmrm_route_type'] = $atts['type'];
		}

		$atts = shortcode_atts(apply_filters('cmmrm_routes_shortcode_atts_defaults', array(
			'category' => null,
			'author' => null,
			'params' => 1,
			'width' => '',
			'mapwidth' => '',
			'theme' => false,
			'mapheight' => '',
			'mapId' => mt_rand(),
			//'showlist' => 0,
			//'limit' => model\Settings::getOption(model\Settings::OPTION_PAGINATION_LIMIT),
			//'page' => 1,
		), $atts, static::SHORTCODE_NAME), $atts);

		$query = new \WP_Query(apply_filters('cmmrm_routes_shortcode_query', array(
			'post_type' => model\Route::POST_TYPE,
			'post_status' => 'publish',
			'author' => $atts['author'],
			model\Category::TAXONOMY => $atts['category'],
		), $atts, static::SHORTCODE_NAME));

		$routes = model\Route::getIndexMapJSLocations($query);

		//echo "<pre>"; print_r($routes); echo "</pre>";

		if (!empty($routes)) {
			
			controller\FrontendController::enqueueStyle();
			wp_enqueue_script('cmmrm-widget-index-map');
			
			do_action('cmmrm_load_single_page_scripts');
			$displayParams = model\Settings::getOption(model\Settings::OPTION_INDEX_ROUTE_PARAMS);
			
			/*
 			if ($atts['showlist']) {
 				$query->set('posts_per_page', $atts['limit']);
 				$query->set('page', $atts['page']);
 				$routesList = $query->get_posts();
 			}
			*/
			$mapStyle = controller\RouteController::getStyle($atts['theme']);
			return controller\RouteController::loadFrontendView('index-map-shortcode',
					compact('mapStyle','displayParams', 'atts', 'routes'));
			
		} else {
			
			if(is_plugin_active('cm-maps-routes-peepso-addon/plugin.php')) {
				return '<div class="cm_routes_map_no_records">'.model\Labels::getLocalized('index_no_routes').'</div>';
			}

		}
		
	}

}
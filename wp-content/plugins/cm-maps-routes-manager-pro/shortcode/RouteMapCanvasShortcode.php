<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\controller;
use com\cminds\mapsroutesmanager\model;

class RouteMapCanvasShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-map-canvas';
	
	static function shortcodeContent(model\Route $route, $atts, $content) {
		
		$id = null;
		if($route) {
			if($route->getId() != '') {
				$id = $route->getId();
			}
		}

		$atts = shortcode_atts(array(
			'id' => $id,
			'route' => null,
			'graph' => 1,
			'params' => 1,
			'map' => 1,
			'topinfo' => 0,
			'showdate' => 1,
			'showtitle' => 1,
			'width' => '',
			'mapwidth' => '',
			'mapheight' => '',
			'toolbar' => 1,
			'mapId' => mt_rand(),
			'zoom' => null,
			'theme' => false,
			'showtravelmode' => 0,
			'cmlocations' => model\Settings::getOption(model\Settings::OPTION_ROUTE_MAP_LOCATIONS_INTEGRATION),
		), $atts);

		controller\FrontendController::enqueueStyle();
		do_action('cmmrm_load_single_page_scripts');
		
		if (!empty($atts['cmlocations'])) {
			$atts['cmlocations'] = model\MapLocationObject::getIndexMapJSLocations();
			$atts['cmlocations_marker_click'] = get_option('cmloc_index_map_marker_click', 'redirect');
		}
		$mapStyle = controller\RouteController::getStyle($atts['theme']);
		return controller\RouteController::loadFrontendView('route-map-standalone', compact('mapStyle', 'route', 'atts'));
		
	}

}
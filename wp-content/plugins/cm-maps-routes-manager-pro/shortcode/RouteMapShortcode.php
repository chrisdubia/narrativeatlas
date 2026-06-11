<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Route;

class RouteMapShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'route-map';
	
	static function shortcode($atts) {
		global $route;
		$atts = shortcode_atts(array(
			'id' => null,
			'route' => null,
			'graph' => 1,
			'params' => 1,
			'map' => 1,
			'topinfo' => 0,
			'showdate' => 1,
			'showtitle' => 1,
			'showtravelmode' => '',
			'zoom' => '',
			'width' => '',
			'theme' => false,
			'mapwidth' => '',
			'mapheight' => '',
			'toolbar' => 1,
		), $atts);
		
		if (!empty($atts['id'])) {
			$route = Route::getInstance($atts['id']);
		}
		else if (!empty($atts['route'])) {
			$route = $atts['route'];
		}
		$mapStyle = RouteController::getStyle($atts['theme']);
		
		if (!empty($route) AND $route instanceof Route AND $route->canView()) {
			FrontendController::enqueueStyle();
			do_action('cmmrm_load_single_page_scripts');
			$displayParams = Settings::getOption(Settings::OPTION_SINGLE_ROUTE_PARAMS);
			return RouteController::loadFrontendView('shortcode-map', compact('mapStyle','route', 'atts', 'displayParams'));
		}

	}
	
}
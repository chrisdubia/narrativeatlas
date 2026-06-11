<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\controller\RouteController;

class RouteLocationsShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-locations';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		return RouteController::loadFrontendView('single-locations', compact('route', 'atts'));
	}
	
}
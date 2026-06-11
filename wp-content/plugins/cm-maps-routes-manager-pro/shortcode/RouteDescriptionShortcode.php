<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\controller\RouteController;

class RouteDescriptionShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-description';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		return RouteController::loadFrontendView('route-description', compact('route'));
	}
	
}
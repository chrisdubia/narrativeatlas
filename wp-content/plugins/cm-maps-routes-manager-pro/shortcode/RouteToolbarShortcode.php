<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Route;

class RouteToolbarShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-toolbar';

	static function shortcodeContent(Route $route, $atts, $content) {
		FrontendController::enqueueStyle();
		return RouteController::loadFrontendView('route-toolbar', compact('route'));
	}

}
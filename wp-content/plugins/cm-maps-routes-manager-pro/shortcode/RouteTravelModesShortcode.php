<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model;
use com\cminds\mapsroutesmanager\helper;

class RouteTravelModesShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-travel-modes';
	
	static function shortcodeContent(model\Route $route, $atts, $content) {
		return helper\RouteView::getTravelModeMenu($route->getTravelMode());
	}

}
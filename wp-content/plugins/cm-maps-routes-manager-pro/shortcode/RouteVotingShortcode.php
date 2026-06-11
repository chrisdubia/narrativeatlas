<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\helper\RouteView;

class RouteVotingShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-voting';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		return RouteView::displayRating($route);
	}
	
}
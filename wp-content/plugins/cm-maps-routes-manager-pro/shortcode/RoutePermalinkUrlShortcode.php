<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;

class RoutePermalinkUrlShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-permalink-url';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		return $route->getPermalink();
	}
	
}
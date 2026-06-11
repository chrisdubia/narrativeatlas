<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;

class RoutePostDateShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-post-date';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		return $route->getCreatedDate();
	}
	
}
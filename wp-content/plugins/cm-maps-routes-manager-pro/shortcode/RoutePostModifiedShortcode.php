<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;

class RoutePostModifiedShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-post-modified';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		return $route->getModifiedDate();
	}
	
}
<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;

class RouteAuthorShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-author';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		return $route->getAuthorDisplayName();
	}
	
}
<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;

class RouteTitleShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-title';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		$result = $route->getTitle();
		if (!empty($atts['escape'])) {
			$result = htmlspecialchars($result);
		}
		return $result;
	}
	
}
<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;

class RoutePostMetaShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-post-meta';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		if (isset($atts['key'])) {
			$result = $route->getPostMeta($atts['key']);
			if (!empty($atts['escape'])) {
				$result = htmlspecialchars($result);
			}
			return $result;
		}
	}
	
}
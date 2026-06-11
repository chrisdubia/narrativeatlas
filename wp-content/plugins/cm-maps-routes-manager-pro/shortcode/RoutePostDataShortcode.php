<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;

class RoutePostDataShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-post-data';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		if (isset($atts['col']) AND $post = $route->getPost()) {
			if (isset($post->{$atts['col']})) {
				$result = $post->{$atts['col']};
				if (!empty($atts['escape'])) {
					$result = htmlspecialchars($result);
				}
				return $result;
			}
		}
	}
	
}
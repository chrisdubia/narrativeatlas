<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;

class RouteAuthorLinkShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-author-link';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		$extra = '';
		if (!empty($atts['newtab'])) $extra .= ' target="_blank"';
		return sprintf('<a href="%s"%s>%s</a>',
				esc_attr($route->getAuthorRoutesPermalink()),
				$extra,
				esc_html($route->getAuthorDisplayName())
			);
	}
	
}
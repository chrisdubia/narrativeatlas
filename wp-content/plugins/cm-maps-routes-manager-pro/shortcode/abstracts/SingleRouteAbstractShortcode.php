<?php
namespace com\cminds\mapsroutesmanager\shortcode\abstracts;

use com\cminds\mapsroutesmanager\shortcode\Shortcode;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\controller\PageController;

abstract class SingleRouteAbstractShortcode extends Shortcode {
	
	static function shortcode($atts, $content = null) {
		if ($route = static::getRoute($atts)) {
			FrontendController::enqueueStyle();
			return static::shortcodeContent($route, $atts, $content);
		}
	}
	
	static function shortcodeContent(Route $route, $atts, $content) {}
	
	static function getRoute($atts) {
		$id = (isset($atts['id']) ? $atts['id'] : null);
		if (empty($id)) {
			$id = get_query_var(PageController::REWRITE_TAG_ROUTE_NAME);
		}
		if (empty($id)) {
			if ($route = FrontendController::getRoute()) {
				$id = $route->getId();
			}
		}
		if ($id) {
			return Route::getInstance($id);
		}
	}
	
}
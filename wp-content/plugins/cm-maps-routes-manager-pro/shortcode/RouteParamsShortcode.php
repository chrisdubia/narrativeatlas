<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Settings;

class RouteParamsShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-params';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		$atts = shortcode_atts(array(
			'fancy' => Settings::getOption(Settings::OPTION_FANCY_STYLE_ENABLE),
			'fancyborder' => Settings::getOption(Settings::OPTION_FANCY_BORDER),
		), $atts);
		$displayParams = Settings::getOption(Settings::OPTION_SINGLE_ROUTE_PARAMS);
		return '<div class="cmmrm-shortcode-route-params" '
			. RouteView::getDisplayParams($displayParams)
			.' data-fancy="'. intval($atts['fancy'])
			.'" data-fancy-border="'. intval($atts['fancyborder']) .'">'
			. RouteController::loadFrontendView('route-params', compact('route'))
		. '</div>';
	}
	
}
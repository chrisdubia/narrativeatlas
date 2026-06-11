<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Route;

class RouteSnippetShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'route-snippet';
	const FEATURED_IMAGE = 'image';
	const FEATURED_MAP = 'map';
	
	static function init() {
		if (App::isPro()) parent::init();
	}
	
	static function shortcode($atts) {
		
		$displayParams = Settings::getOption(Settings::OPTION_INDEX_ROUTE_PARAMS);
		$adjustableParams = Settings::getRouteIndexPageParamsNames();
		
		$defaults = array(
			'id' => null,
			'route' => null,
			'featured' => Settings::getOption(Settings::OPTION_ROUTE_INDEX_FEATURED_IMAGE),
			'params' => 1,
			'layout' => Settings::getOption(Settings::OPTION_INDEX_LAYOUT),
			'fancy' => Settings::getOption(Settings::OPTION_FANCY_STYLE_ENABLE),
		);
		
		foreach ($adjustableParams as $param => $label) {
			$attribute = 'show_' . str_replace('_cmmrm_', '', $param);
			$defaults[$attribute] = intval(in_array($param, $displayParams));
		}
		
		$atts = shortcode_atts($defaults, $atts);
		//var_dump($atts);
		
		if (!empty($atts['id'])) {
			$route = Route::getInstance($atts['id']);
		}
		else if (!empty($atts['route'])) {
			$route = $atts['route'];
		}
		
		foreach ($adjustableParams as $param => $label) {
			$attribute = 'show_' . str_replace('_cmmrm_', '', $param);
			if (!empty($atts[$attribute])) {
				if (!in_array($param, $displayParams)) {
					$displayParams[] = $param;
					//var_dump('added ' . $param);
				}
			} else {
				if (in_array($param, $displayParams)) {
					$displayParams = array_diff($displayParams, array($param));
					//var_dump('removed ' . $param);
				}
			}
		}
		
		if (!empty($route) AND $route instanceof Route AND $route->canView()) {
			FrontendController::enqueueStyle();
			return RouteController::loadFrontendView('snippet', compact('route', 'atts', 'displayParams'));
		}
	}

}
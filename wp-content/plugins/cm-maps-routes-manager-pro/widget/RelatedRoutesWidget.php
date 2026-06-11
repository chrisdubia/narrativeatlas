<?php
namespace com\cminds\mapsroutesmanager\widget;

use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\controller\Controller;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\SettingsAbstract;

class RelatedRoutesWidget extends Widget {
	
	const WIDGET_NAME = 'CM Route Manager Related Routes';
	const WIDGET_DESCRIPTION = 'Displays related routes on the CM Maps Routes Manager\'s route page.';
	
	static protected $widgetFields = array(
		'limit' => array(
			'type' => Settings::TYPE_INT,
			'default' => 5,
			'label' => 'Limit',
		),
	);
	
	function getWidgetContent($args, $instance) {
		$instance = shortcode_atts(array(
			'limit' => static::$widgetFields['limit']['default'],
		), $instance);
		//if (FrontendController::$query->is_single() AND $route = FrontendController::getRoute()) {
		if ($route = FrontendController::getRoute()) {
			$routes = $route->getRelatedRoutes($instance['limit']);
			if (!empty($routes)) {
				return Controller::loadView('frontend/widget/related-routes', compact('args', 'instance', 'routes'));
			}
		}
	}
	
	function canDisplay($args, $instance) {
		return true;
		//return FrontendController::isThePage();
	}

}
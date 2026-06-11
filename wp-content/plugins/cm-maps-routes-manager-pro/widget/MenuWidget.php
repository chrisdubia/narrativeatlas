<?php
namespace com\cminds\mapsroutesmanager\widget;

use com\cminds\mapsroutesmanager\controller\Controller;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\SettingsAbstract;

class MenuWidget extends Widget {

	const WIDGET_NAME = 'CM Route Manager Menu';
	const WIDGET_DESCRIPTION = 'Displays CM Maps Routes Manager menu.';
	
	function getWidgetContent($args, $instance) {
		$route = FrontendController::getRoute();
		return Controller::loadView('frontend/widget/menu', compact('instance', 'route'));
	}
	
	function canDisplay($args, $instance) {
		return true;
		//return FrontendController::isThePage();
	}

}
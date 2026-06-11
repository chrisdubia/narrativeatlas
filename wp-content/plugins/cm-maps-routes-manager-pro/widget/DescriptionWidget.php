<?php
namespace com\cminds\mapsroutesmanager\widget;

use com\cminds\mapsroutesmanager\controller\Controller;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\SettingsAbstract;

class DescriptionWidget extends Widget {

	const WIDGET_NAME = 'CM Route Manager Categories Description';
	const WIDGET_DESCRIPTION = 'Shows the description of the category currently active on that page. It also lists any files assigned to that category.';
	
	function getWidgetContent($args, $instance) {
		$categories = array();
		$currentCategory[] = FrontendController::getCategory();
		$route = FrontendController::getRoute();
		if($route) {
			$categories = $route->getCategories();
		} else if(isset($currentCategory[0]) && $currentCategory[0]->getName()) {
			$categories = $currentCategory;
		}
		return Controller::loadView('frontend/widget/description', compact('args', 'instance', 'route', 'categories'));
	}
	
	function canDisplay($args, $instance) {
		return true;
		//return FrontendController::isThePage();
	}

}
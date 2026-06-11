<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;

class TagController extends Controller {
	
	protected static $actions = array(
		'cmmrm_route_editor_middle' => array('args' => 1, 'priority' => 15),
		'cmmrm_route_after_save' => array('args' => 1),
	);
	
	static function cmmrm_route_editor_middle(Route $route) {
		$route_form_tags = Settings::getOption(Settings::OPTION_ROUTE_FORM_TAGS);
		if ($route_form_tags != 'none') {
			echo self::loadFrontendView('editor', compact('route', 'route_form_tags'));
		}
	}
	
	static function cmmrm_route_after_save(Route $route) {
		if (!empty($_POST['tags'])) {
			$tags = $_POST['tags'];
		} else {
			$tags = array();
		}
		$route->setTags($tags);
	}
	
}
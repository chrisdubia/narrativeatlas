<?php
namespace com\cminds\mapsroutesmanager\widget;

use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\RouteTag;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\controller\Controller;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\SettingsAbstract;

class TagCloudWidget extends Widget {
	
	const WIDGET_NAME = 'CM Route Manager Tag Cloud';
	const WIDGET_DESCRIPTION = 'Displays tag cloud of the CM Maps Routes Manager routes.';
	
	static protected $widgetFields = array(
		'limit' => array(
			'type' => Settings::TYPE_INT,
			'default' => 20,
			'label' => 'Limit',
		),
		'show_numbers' => array(
			'type' => Settings::TYPE_BOOL,
			'default' => true,
			'label' => 'Show numbers of routes',
		),
	);
	
	function getWidgetContent($args, $instance) {
		
		$instance = shortcode_atts(array(
			'limit' => static::$widgetFields['limit']['default'],
			'show_numbers' => static::$widgetFields['show_numbers']['default'],
		), $instance);
		
		if (empty($instance['show_numbers'])) {
			$instance['show_numbers'] = static::$widgetFields['show_numbers']['default'];
		}
		
		$tags = RouteTag::getAll(RouteTag::FIELDS_MODEL, array('number' => $instance['limit']));
		if (!empty($tags)) {
			$maxNumber = 0;
			$minNumber = null;
			foreach ($tags as $tag) {
				$number = $tag->routes_number;
				if ($number > $maxNumber) $maxNumber = $number;
				if (is_null($minNumber) OR $number < $minNumber) $minNumber = $number;
			}
			FrontendController::enqueueStyle();
			return RouteController::loadView('frontend/widget/tag-cloud', compact('args', 'instance', 'tags', 'maxNumber', 'minNumber'));
		}
	}
	
	function canDisplay($args, $instance) {
		return true;
		//return FrontendController::isThePage();
	}

}
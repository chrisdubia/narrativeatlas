<?php
namespace com\cminds\mapsroutesmanager\widget;

use com\cminds\mapsroutesmanager\shortcode\SearchShortcode;
use com\cminds\mapsroutesmanager\model\Settings;

class SearchWidget extends Widget {

	const WIDGET_NAME = 'CM Route Manager Search';
	const WIDGET_DESCRIPTION = 'Displays CM Maps Routes Manager search form.';
	
	static protected $widgetFields = array(
		'title' => array(
			'type' => Settings::TYPE_STRING,
			'default' => 'Search Routes',
			'label' => 'Title',
		),
	);
	
	function getWidgetContent($args, $instance) {
		return SearchShortcode::shortcode(array());
	}

}
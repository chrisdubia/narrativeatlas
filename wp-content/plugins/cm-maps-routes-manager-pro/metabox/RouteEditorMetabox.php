<?php

namespace com\cminds\mapsroutesmanager\metabox;

use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\controller\RouteController;

class RouteEditorMetabox extends MetaBox {
	
	const SLUG = 'route-editor';
	const NAME = 'Route Editor';
	const CONTEXT = 'side';
	const PRIORITY = 'high';
	const META_BOX_PRIORITY = 5;
	const SAVE_POST_PRIORITY = 10;
	
	static protected $supportedPostTypes = array(Route::POST_TYPE);
	
	
	static function render($post) {
		$route = Route::getInstance($post);
		if (!empty($post->ID) AND $post->post_status != 'auto-draft' AND $route) {
			$url = $route->getUserEditUrl();
		} else {
			$url = RouteController::getDashboardUrl('add');
		}
		printf('<a href="%s" class="button">%s</a>', esc_attr($url), 'Open Route Editor');
	}
	
}
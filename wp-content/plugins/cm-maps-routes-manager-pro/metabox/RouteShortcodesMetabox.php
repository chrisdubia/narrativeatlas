<?php

namespace com\cminds\mapsroutesmanager\metabox;

use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\controller\RouteController;

class RouteShortcodesMetabox extends MetaBox {
	
	const SLUG = 'route-shortcodes';
	const NAME = 'Shortcodes';
	const CONTEXT = 'side';
	const PRIORITY = 'high';
	const META_BOX_PRIORITY = 100;
	const SAVE_POST_PRIORITY = 10;
	
	static protected $supportedPostTypes = array(Route::POST_TYPE);
	
	
	static function render($post) {
		$id = $post->ID;
		echo RouteController::loadBackendView('metabox-shortcodes', compact('id'));
	}
	
}
<?php

namespace com\cminds\mapsroutesmanager\helper;

use com\cminds\mapsroutesmanager\model\Route;

class Update {
	
	const OPTION_UPDATES = 'cmmrm_updates';
	
	static function boostrap() {
		$updates = explode(PHP_EOL, get_option(self::OPTION_UPDATES));
		$methods = get_class_methods(__CLASS__);
		foreach ($methods as $method) {
			if (substr($method, 0, 7) == 'update_') {
				if (!in_array($method, $updates)) {
					call_user_func(array(__CLASS__, $method));
					$updates[] = $method;
				}
			}
		}
		update_option(self::OPTION_UPDATES, implode(PHP_EOL, $updates), true);
	}
	
	
	static function update_1_0_1() {
		global $wpdb;
		
		// Update overview path
		$routesIds = $wpdb->get_results($wpdb->prepare("SELECT loc.ID FROM $wpdb->posts route
			LEFT JOIN $wpdb->postmeta path ON path.post_id = route.ID AND path.meta_key = %s
			WHERE route.post_type = %s AND (path.meta_value IS NULL OR path.meta_value = '')",
			Route::META_OVERVIEW_PATH, Route::POST_TYPE));
		foreach ($routesIds as $id) {
			if ($route = Route::getInstance($id)) {
				$route->recalculateOverviewPath();
			}
			unset($route);
			Route::clearInstances();
		}
	}
	
}

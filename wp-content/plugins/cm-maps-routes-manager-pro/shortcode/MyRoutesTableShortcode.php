<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\controller\DashboardController;

class MyRoutesTableShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'my-routes-table';
	
	static function shortcode($atts = array()) {
		
		$atts = shortcode_atts(array(
			'controls' => 1,
			'addbtn' => 1,
		), $atts);
		
		DashboardController::embedAssets();
		
		if (!Route::canCreate()) {
			$out = Labels::getLocalized('dashboard_access_denied_msg');
		} else {
			$query = new \WP_Query(array(
				'author' => get_current_user_id(),
				'post_type' => Route::POST_TYPE,
				'posts_per_page' => -1,
				'post_status' => array('publish', 'draft', 'pending'),
			));

			if(is_plugin_active('cm-maps-routes-buddypress-integration/cm-maps-routes-buddypress-integration.php') || is_plugin_active('cm-maps-routes-buddypress-addon/cm-maps-routes-buddypress-integration.php')) {
				
				if (function_exists('groups_get_user_groups')) {

					$collaborative_routes = get_posts(array(
						'author' => -get_current_user_id(),
						'post_type' => Route::POST_TYPE,
						'posts_per_page' => -1,
						'post_status' => array('publish', 'draft', 'pending'),
						'meta_key'   => '_cmmrm_use_buddypress_collaborative',
						'meta_value' => '1'
					));

					$current_user_groups_arr = groups_get_user_groups(get_current_user_id());
					$current_user_groups = $current_user_groups_arr['groups'];
					$current_user_total = $current_user_groups_arr['total'];
					
					$unique_routes = array();
					if($current_user_total > 0) {
						foreach($current_user_groups as $current_user_group_id) {
							foreach($collaborative_routes as $croute) {
								$route_bp_groups = get_post_meta($croute->ID, '_cmmrm_bp_groups', true);
								if($route_bp_groups) {
									if(is_array($route_bp_groups) && count($route_bp_groups) > 0) {
										if(in_array($current_user_group_id, $route_bp_groups)) {
											if(!in_array($croute->ID, $unique_routes)) {
												$query->posts[] = $croute;
												$unique_routes[] = $croute->ID;
											}
										}
									}
								}
							}
						}
					}

				}

			}

			$routes = array_filter(array_map(array(App::namespaced('model\Route'), 'getInstance'), $query->posts));
			$out = DashboardController::loadFrontendView('index', compact('routes', 'atts'));
		}
		
		return '<div class="cmmrm-my-routes-shortcode">'. $out .'</div>';
		
	}
	
}
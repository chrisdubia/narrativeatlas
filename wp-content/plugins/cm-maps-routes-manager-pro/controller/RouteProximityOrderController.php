<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\model;

class RouteProximityOrderController extends Controller {
	
	const NONCE_REGISTER_LOCATION = 'cmmrm_geolocation';
	
	static $filters = array(
		'posts_join' => array('args' => 2, 'priority' => PHP_INT_MAX),
		'posts_orderby' => array('args' => 2, 'priority' => PHP_INT_MAX),
		//'template_include',
	);
	static $ajax = array(
		'cmmrm_register_user_geolocation',
	);
	
	static function template_include($template) {
		global $wp_query;
		var_dump($wp_query->request);exit;
		return $template;
	}
	
	static function posts_join($join, \WP_Query $query) {
		if (static::isEnabled($query)) {
			global $wpdb;
			$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta AS cmmrm_route_lat
				ON cmmrm_route_lat.post_id = ID AND cmmrm_route_lat.meta_key = %s", model\Route::META_APPROX_LATITUDE);
			$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta AS cmmrm_route_long
				ON cmmrm_route_long.post_id = ID AND cmmrm_route_long.meta_key = %s", model\Route::META_APPROX_LONGITUDE);
			$join .= PHP_EOL;
			//var_dump($join);exit;
		}
		return $join;
	}
	
	static function posts_orderby($orderby, \WP_Query $query) {
		if (static::isEnabled($query)) {
			$loc = model\User::getLastGeolocation();
			$order = model\Settings::getIndexOrder();
			$orderby = '(
			          acos(sin(cmmrm_route_lat.meta_value * 0.0175) * sin('. $loc['lat'] .' * 0.0175)
			               + cos(cmmrm_route_lat.meta_value * 0.0175) * cos('. $loc['lat'] .' * 0.0175) *
			                 cos(('. $loc['long'] .' * 0.0175) - (cmmrm_route_long.meta_value * 0.0175))
			              ) * 3959
			      ) ' . $order;
				//var_dump($orderby);exit;	
		}
		return $orderby;
	}
	
	static function isEnabled(\WP_Query $query) {
		$loc = model\User::getLastGeolocation();
		return (!is_admin() AND FrontendController::isRoutePostType($query)
				AND model\Settings::ORDERBY_PROXIMITY == model\Settings::getIndexOrderBy()
				AND !is_null($loc['lat']) AND !is_null($loc['long']));
	}
	
	static function cmmrm_register_user_geolocation() {
		$nonce = filter_input(INPUT_POST, 'nonce');
		$lat = filter_input(INPUT_POST, 'lat');
		$long = filter_input(INPUT_POST, 'long');
		if (wp_verify_nonce($nonce, static::NONCE_REGISTER_LOCATION) AND !is_null($lat) AND !is_null($long)) {
			model\User::registerLastGeolocation($lat, $long);
			echo 'ok';
			exit;
		}
	}
	
}
<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\shortcode\RouteVotingShortcode;

class RatingController extends Controller {
	
	const NONCE_RATING_ACTION = 'route_rating';
	const FILTER_QUERY_VAR = 'route_rating';
	
	static $actions = array(
		'cmmrm_route_map_before_top' => array('args' => 1),
		//'cmmrm_categories_filter',
		//'pre_get_posts' => array('args' => 1),
	);

	//static $filters = array('query_vars');

	static $ajax = array('cmmrm_route_rating');
	
	static function query_vars($vars) {
		$vars[] = static::FILTER_QUERY_VAR;
		return $vars;
	}
	
	static function cmmrm_route_rating() {
		$response = array('success' => 0);
		if (!empty($_POST['nonce']) AND wp_verify_nonce($_POST['nonce'], self::NONCE_RATING_ACTION)) {
			if (!empty($_POST['routeId']) AND !empty($_POST['rate'])) {
				if ($route = Route::getInstance($_POST['routeId'])) {
					if ($route->canRate()) {
						if (!$route->didUserRate()) {
							if ($route->rate($_POST['rate'])) {
								$response['success'] = 1;
								$response['rate'] = $route->getRate();
							} else {
								$response['msg'] = 'Cannot rate route.';
							}
						} else {
							$response['msg'] = 'User already did rate this route.';
						}
					} else {
						$response['msg'] = 'User is not allowed to rate.';
					}
				} else {
					$response['msg'] = 'Route not found.';
				}
			} else {
				$response['msg'] = 'Invalid request.';
			}
		} else {
			$response['msg'] = 'Access denied.';
		}
		header('content-type: application/json');
		echo json_encode($response);
		exit;
	}
	
	static function cmmrm_route_map_before_top(Route $route) {
		if (Settings::getOption(Settings::OPTION_SINGLE_ROUTE_RATING_SHOW)) {
			echo RouteVotingShortcode::shortcodeContent($route, array(), null);
		}
	}
	
	static function cmmrm_categories_filter() {
		if (!Settings::getOption(Settings::OPTION_INDEX_RATING_FILTER_SHOW)) return;
		
		$current = (empty(FrontendController::$query) ? null : FrontendController::$query->get(static::FILTER_QUERY_VAR));
		$baseUrl = FrontendController::getFilterUrl($includeCategory = true);
		$urlParam = static::FILTER_QUERY_VAR;
		echo self::loadFrontendView('filter', compact('current', 'baseUrl', 'urlParam'));
		
	}
	
	static function pre_get_posts(\WP_Query $query) {
		
		if ($rating = $query->get(static::FILTER_QUERY_VAR)) {
			
			if (isset($query->query['meta_query'])) {
				$metaQuery = $query->query['meta_query'];
			} else {
				$metaQuery = array();
			}
			
			$metaQuery[] = array(
				'key' => Route::META_RATE,
				'value' => $value,
				'compare' => '=',
				'type' => 'CHAR',
			);
			
		}
		
	}
	
}
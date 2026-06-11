<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;

class ApiController extends Controller {
	static function bootstrap() {
		parent::bootstrap();
		add_action( 'rest_api_init', function () {
			// /wp-json/cmmrm/v1/get_routes
			// /wp-json/cmmrm/v1/get_routes?cmmrm_category=slug
			register_rest_route('cmmrm/v1', '/get_routes',
				array(
					'methods'  => \WP_REST_Server::ALLMETHODS,
					'callback' => array( self::class, 'get_routes_action' ),
					'permission_callback' => '__return_true'
				)
			);
		}, 5);
	}

	static function get_routes_action( \WP_REST_Request $request ) {
		
		$output = array();

		$params = array('id' => '', 'cmmrm_category' => '');
		$taxonomies = Settings::getOption('cmmrm_custom_taxonomies');
		if($taxonomies) {
			foreach ($taxonomies as $tax) {
				if (empty($tax['taxonomy'])) {
					continue;
				}
				$params[$tax['taxonomy']] = '';
			}
		}
		$input = shortcode_atts($params, $_REQUEST);
	
		$tax_query = array();
		if($input) {
			foreach ($input as $inputkey=>$inputval) {
				if (empty($inputval)) {
					continue;
				}
				$tax_query[] = array(
				  'taxonomy' => $inputkey,
				  'field' => 'slug', 
				  'terms' => $inputval
				);
			}
		}
		
		if($input['id'] != '' && $input['id'] > 0) {
			$routes_args = array(
				'post_type' => 'cmmrm_route',
				'orderby' => 'title',
				'order' => 'ASC',
				'post_status' => 'publish',
				'numberposts' => -1,
				'include' => $input['id']
			);
		} else {
			$routes_args = array(
				'post_type' => 'cmmrm_route',
				'orderby' => 'title',
				'order' => 'ASC',
				'post_status' => 'publish',
				'numberposts' => -1,
				'tax_query' => $tax_query,
			);
		}
		
		$routes = get_posts($routes_args);
		if(count($routes) > 0) {
			$counter = 0;
			foreach($routes as $route) {

				$rout = Route::getInstance($route->ID);
				$route_locations = $rout->getLocationsIds();

				$output[$counter]['id'] = $route->ID;
				$output[$counter]['post_author'] = $route->post_author;
				$output[$counter]['post_date'] = $route->post_date;
				$output[$counter]['post_date_gmt'] = $route->post_date_gmt;
				$output[$counter]['post_content'] = $route->post_content;
				$output[$counter]['post_title'] = $route->post_title;
				$output[$counter]['post_excerpt'] = $route->post_excerpt;
				$output[$counter]['post_status'] = $route->post_status;
				$output[$counter]['comment_status'] = $route->comment_status;
				$output[$counter]['ping_status'] = $route->ping_status;
				$output[$counter]['post_password'] = $route->post_password;
				$output[$counter]['post_name'] = $route->post_name;
				$output[$counter]['to_ping'] = $route->to_ping;
				$output[$counter]['pinged'] = $route->pinged;
				$output[$counter]['post_modified'] = $route->post_modified;
				$output[$counter]['post_modified_gmt'] = $route->post_modified_gmt;
				$output[$counter]['post_content_filtered'] = $route->post_content_filtered;
				$output[$counter]['post_parent'] = $route->post_parent;
				$output[$counter]['guid'] = $route->guid;
				$output[$counter]['menu_order'] = $route->menu_order;
				$output[$counter]['post_type'] = $route->post_type;
				$output[$counter]['post_mime_type'] = $route->post_mime_type;
				$output[$counter]['comment_count'] = $route->comment_count;
				$locationData = get_field('locations', $route->ID);
                $output[$counter]['locationData'] = $locationData;

				$metas = get_post_meta($route->ID);
				if(count($metas) > 0) {
					foreach($metas as $metakey=>$metaval) {
						$output[$counter][$metakey] = $metaval[0];
					}
				}
				
				/*
				if(count($route_locations) > 0) {
					$rlcounter = 0;
					foreach($route_locations as $rlkey=>$rlval) {
						$location = get_post($rlval);
						$output[$counter]['locations'][$rlcounter]['id'] = $location->ID;
						$output[$counter]['locations'][$rlcounter]['post_author'] = $location->post_author;
						$output[$counter]['locations'][$rlcounter]['post_date'] = $location->post_date;
						$output[$counter]['locations'][$rlcounter]['post_date_gmt'] = $location->post_date_gmt;
						$output[$counter]['locations'][$rlcounter]['post_content'] = $location->post_content;
						$output[$counter]['locations'][$rlcounter]['post_title'] = $location->post_title;
						$output[$counter]['locations'][$rlcounter]['post_excerpt'] = $location->post_excerpt;
						$output[$counter]['locations'][$rlcounter]['post_status'] = $location->post_status;
						$output[$counter]['locations'][$rlcounter]['comment_status'] = $location->comment_status;
						$output[$counter]['locations'][$rlcounter]['ping_status'] = $location->ping_status;
						$output[$counter]['locations'][$rlcounter]['post_password'] = $location->post_password;
						$output[$counter]['locations'][$rlcounter]['post_name'] = $location->post_name;
						$output[$counter]['locations'][$rlcounter]['to_ping'] = $location->to_ping;
						$output[$counter]['locations'][$rlcounter]['pinged'] = $location->pinged;
						$output[$counter]['locations'][$rlcounter]['post_modified'] = $location->post_modified;
						$output[$counter]['locations'][$rlcounter]['post_modified_gmt'] = $location->post_modified_gmt;
						$output[$counter]['locations'][$rlcounter]['post_content_filtered'] = $location->post_content_filtered;
						$output[$counter]['locations'][$rlcounter]['post_parent'] = $location->post_parent;
						$output[$counter]['locations'][$rlcounter]['guid'] = $location->guid;
						$output[$counter]['locations'][$rlcounter]['menu_order'] = $location->menu_order;
						$output[$counter]['locations'][$rlcounter]['post_type'] = $location->post_type;
						$output[$counter]['locations'][$rlcounter]['post_mime_type'] = $location->post_mime_type;
						$output[$counter]['locations'][$rlcounter]['comment_count'] = $location->comment_count;

						$rlmetas = get_post_meta($location->ID);
						if(count($rlmetas) > 0) {
							foreach($rlmetas as $rlmetakey=>$rlmetaval) {
								$output[$counter]['locations'][$rlcounter][$rlmetakey] = $rlmetaval[0];
							}
						}
						$rlcounter++;
					}
				}
				*/

				$counter++;
			}
		}

		return $output;
	}

}
?>
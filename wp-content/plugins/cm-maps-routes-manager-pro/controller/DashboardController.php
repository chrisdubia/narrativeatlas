<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Attachment;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\shortcode\MyRoutesTableShortcode;

class DashboardController extends Controller {
	
	const EDITOR_NONCE = 'cmmrm_route_editor';
	const DELETE_NONCE = 'cmmrm_route_delete';
	const UPDATE_PARAMS_NONCE = 'cmmrm_update_params';
	
	static $actions = array(
		'wp_enqueue_scripts' => array('priority' => PHP_INT_MAX),
		'admin_init',
	);
	
	static $ajax = array(
		'cmmrm_route_params_save',
		'cmmrm_route_image_upload',
		'cmmrm_route_add_video',
		'cmmrm_remove_route',
		'cmmrm_add_category',
		'cmmrm_update_distance',
		'cmmrm_load_google_images',
		'cmmrm_add_google_image',
	);
	
	static $filters = array(
		array('name' => 'wp_insert_post_data', 'args' => 2),
	);
	
	static function bootstrap() {
		parent::bootstrap();
		// Fix for WPML to not redirect the save route requests:
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			add_filter('wpml_is_redirected', '__return_false', PHP_INT_MAX);
		}
	}
	
	static function indexView(\WP_Query $query) {
		global $withcomments, $post, $wp_query;
		$withcomments = true;
		$post = null;
		$out = MyRoutesTableShortcode::shortcode();
		$wp_query->reset_postdata();
		return apply_filters( 'cmmrm_index_view_out', $out );
	}
	
	static function wp_enqueue_scripts() {
		if (FrontendController::isDashboard()) {
			static::embedAssets();
		}
	}
	
	static function embedAssets() {
		FrontendController::enqueueStyle();
		wp_enqueue_style('thickbox');
		wp_enqueue_style('cmmrm-editor');
		wp_enqueue_script('cmmrm-utils');
		wp_localize_script('cmmrm-utils', 'CMMRM_Utils', array(
			'deleteConfirmText' => Labels::getLocalized('Do you really want to delete?'),
		));
	}
	
	static function addView(\WP_Query $query) {
		global $wp_query;
		if (Route::canCreate()) {
			$out = self::getEditorView(new Route());
		} else {
			$out = Labels::getLocalized('dashboard_access_denied_msg');
		}
		$wp_query->reset_postdata();
		return $out;
	}
	
	static function editView(\WP_Query $query) {
		global $wp_query;
		if ($route = FrontendController::getRoute($query)) {
			if ($route->canEdit()) {
				//$route->recalculateOverviewPath();
				add_action('wp_footer', array(__CLASS__, 'loadGoogleChart'), PHP_INT_MAX);
				$out = self::getEditorView($route);
			} else {
				$out = Labels::getLocalized('dashboard_access_denied_msg');
			}
		} else {
			$out = Labels::getLocalized('route_not_found');
		}
		$wp_query->reset_postdata();
		return $out;
	}
	
	static protected function getEditorView(Route $route = null) {
		
		if (!Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY)) {
			return Labels::getLocalized('missing_google_maps_app_key');
		}
		
		remove_action( 'media_buttons', 'media_buttons' );
		
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('cmmrm-route-editor');

		if ($route AND $route->getId()) {
			wp_localize_script('cmmrm-route-editor', 'CMMRM_Editor_Settings', array(
				'newLocationLabel' => Labels::getLocalized('dashboard_new_location'),
				'defaultLat' => Settings::getOption(Settings::OPTION_EDITOR_DEFAULT_LAT),
				'defaultLong' => Settings::getOption(Settings::OPTION_EDITOR_DEFAULT_LONG),
				'defaultZoom' => Settings::getOption(Settings::OPTION_EDITOR_DEFAULT_ZOOM),
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'updateParamsNonce' => wp_create_nonce(self::UPDATE_PARAMS_NONCE),
				'editorWaypointsEnable' => ($route->getOriginalImportFile())?1:0,
			));
		} else {
			wp_localize_script('cmmrm-route-editor', 'CMMRM_Editor_Settings', array(
				'newLocationLabel' => Labels::getLocalized('dashboard_new_location'),
				'defaultLat' => Settings::getOption(Settings::OPTION_EDITOR_DEFAULT_LAT),
				'defaultLong' => Settings::getOption(Settings::OPTION_EDITOR_DEFAULT_LONG),
				'defaultZoom' => Settings::getOption(Settings::OPTION_EDITOR_DEFAULT_ZOOM),
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'updateParamsNonce' => wp_create_nonce(self::UPDATE_PARAMS_NONCE),
				'editorWaypointsEnable' => 0,
			));
		}
		wp_localize_script('cmmrm-editor-images', 'CMMRM_Editor_Images', array(
			'title' => Labels::getLocalized('images'),
			'url' => admin_url('media-upload.php?type=image&TB_iframe=true'),
			'ajax_url' => admin_url('admin-ajax.php'),
		));
		
		$nonce = wp_create_nonce(self::EDITOR_NONCE);
		
		if ($route AND $route->getId()) {
			$formUrl = $route->getUserEditUrl();
			$locations = $route->getLocations();
		} else {
			$formUrl = RouteController::getDashboardUrl('add');
			$locations = null;
		}
		
		$output = '';
		$messageView = '';
		//$_GET['msg'] = 'route_save_success';
		if (!empty($_GET['msg'])) {
			$messageView = static::getMessageView($_GET['msg']);
		}
		$route_form_override = Settings::getOption(Settings::OPTION_ROUTE_FORM_OVERRIDE);
		if($route_form_override == 'yes') {
			$template_path = get_stylesheet_directory().'/CMMRM/editor.php';
			$output .= $messageView;
			ob_start();
			$output .= self::loadFrontendView($template_path, compact('route', 'nonce', 'locations', 'formUrl'), true);
			ob_end_flush();
			return $output;
		} else {
			$output .= $messageView;
			ob_start();
			$output .= self::loadFrontendView('editor', compact('route', 'nonce', 'locations', 'formUrl'));
			ob_end_flush();
			return $output;
		}
	}
	
	static function getMessageView($msg, $class = 'info') {
		$extra = '';
		if ('route_save_success' == $msg AND $route = FrontendController::getRoute()) {
			if(Settings::getOption(Settings::OPTION_ROUTE_MODERATION_ENABLE) != '1') {
				$extra = sprintf('<a href="%s">%s</a>', esc_attr($route->getPermalink()), Labels::getLocalized('menu_view_route') . ' &raquo;');
			}
		}
		return static::loadFrontendView('msg', compact('msg', 'class', 'extra'));
	}

	static function processRequest() {
		if (!is_admin()) {
			// Editor save request
			if (!empty($_POST) AND !empty($_POST[self::EDITOR_NONCE]) AND wp_verify_nonce($_POST[self::EDITOR_NONCE], self::EDITOR_NONCE)) {
				self::processSaveRoute();
			}
			// Delete route
			if (FrontendController::isDashboard() AND FrontendController::getDashboardPage() == FrontendController::DASHBOARD_DELETE) {
				if (!empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], DashboardController::DELETE_NONCE)) {
					self::processDeleteRoute();
				}
			}
		}
	}
	
	static protected function processSaveRoute() {
		
		$data = shortcode_atts(array(
			'name' => '',
			'description' => '',
			'status' => 'draft',
			'duration' => 0,
			'distance' => 0,
			'avg-speed' => 0,
			'max-elevation' => 0,
			'min-elevation' => 0,
			'elevation-gain' => 0,
			'elevation-descent' => 0,
			'directions-response' => '',
			'elevation-response' => '',
			'travel-mode' => '',
			'use-minor-length-units' => 0,
			'use-buddypress-collaborative' => 0,
			'overview-path' => '',
			'waypoints-string' => '',
			'osmtiles' => array(),
            'paths' => ''
		), $_POST);

		if (isset($_GET['id'])) {
			$route = Route::getInstance($_GET['id']);
		} else {
			$route = new Route();
			$route->setAuthor(get_current_user_id());
		}
		
		$valid = apply_filters('cmmrm_dashboard_route_save_validation', true, $route, $data);
		if (!$valid) return;
		
		do_action('cmmrm_dashboard_route_before_save', $route, $data);
		
		$route->setTitle($data['name']);
		$route->setContent($data['description']);
		
		if(isset($_POST['cmmrm_btn_draft'])) {
			$route->setStatus('draft');
		} else {
			$route->setStatus($data['status']);
		}

		$route->setCommentStatus('open');
		
		$id = $route->save();
		
		if ($id) {
			
			$route->setOsmtiles($data['osmtiles']);

			$route->setDistance($data['distance']);
			$route->setDuration($data['duration']);
			$route->setAvgSpeed($data['avg-speed']);
			$route->setMaxElevation($data['max-elevation']);
			$route->setMinElevation($data['min-elevation']);
			$route->setElevationGain($data['elevation-gain']);
			$route->setElevationDescent($data['elevation-descent']);
			$route->setDirectionResponse(stripslashes($data['directions-response']));
			$route->setElevationResponse(stripslashes($data['elevation-response']));
			$route->setTravelMode($data['travel-mode']);
			$route->setMinorLengthUnits(!empty($data['use-minor-length-units']));
			$route->setBuddypressCollaborative(!empty($data['use-buddypress-collaborative']));
			if (strpos($data['waypoints-string'], ',') === false) {
				$route->setWaypointsString(stripslashes($data['waypoints-string']));
			} else {
				$route->setWaypointsString($data['waypoints-string']);
			}
			$route->setOverviewPath(stripslashes($data['overview-path']));
			
			//var_dump(json_decode(stripslashes($data['elevation-response']), true));exit;
			
			self::processSaveRouteLocations($route);
			$route->updateApproxCoords();

			update_post_meta($route->getId(), '_cmmrm_walking_path', $data['paths']);

			do_action('cmmrm_route_after_save', $route);
			
			wp_redirect(add_query_arg('msg', 'route_save_success', $route->getUserEditUrl()));
			exit;
		
		}
	}

	static protected function processSaveRouteLocations(Route $route) {
		
		set_time_limit(3600);
		
		$oldLocationsIds = $route->getLocationsIds();
		$newLocationsIds = array();
		
		if (!empty($_POST['locations']) AND is_array($_POST['locations']) AND !empty($_POST['locations']['id'])) {
			
			foreach ($_POST['locations']['id'] as $i => $id) {
				if ($i > 0) { // ommit the zero-indexed item which is only a placeholder.
					//var_dump(memory_get_usage());flush();ob_flush();
					if ($id == 0) { // insert new location
						$location = new Location(array(
							'post_parent' => $route->getId(),
							'post_author' => get_current_user_id(),
							'post_type' => Location::POST_TYPE,
							'post_status' => 'inherit',
							'ping_status' => 'closed',
							'comment_status' => 'closed',
						));
					} else { // update location
						$location = Location::getInstance($id);
					}
					
					if($location)
					{
						$location->setTitle($_POST['locations']['name'][$i]);
						$location->setContent($_POST['locations']['description'][$i]);
						$location->setMenuOrder($i);
						$id = $location->save();
						$newLocationsIds[] = $id;
						if ($id) {
							//$location->setLat(floatval($_POST['locations']['lat'][$i]));
							//$location->setLong(floatval($_POST['locations']['long'][$i]));
							//$location->setLatLong(floatval($_POST['locations']['lat'][$i]).','.floatval($_POST['locations']['long'][$i]));
							$location->setLat($_POST['locations']['lat'][$i]);
							$location->setLong($_POST['locations']['long'][$i]);
							$location->setLatLong($_POST['locations']['lat'][$i].','.$_POST['locations']['long'][$i]);
							$location->setLocationType($_POST['locations']['type'][$i]);
							$location->setAddress($_POST['locations']['address'][$i]);
							$location->setLinktext($_POST['locations']['linktext'][$i]);
							$location->setLinkurl($_POST['locations']['linkurl'][$i]);
							$location->setCertbgimageid($_POST['locations']['certbgimageid'][$i]);
							$location->setDistance($_POST['locations']['distance'][$i]);
							do_action('cmmrm_location_after_save', $location, $i);
						}
					}
					
				}
				Location::clearInstances();
			}
			
			
			// Remove unused locations
			$toRemove = array_diff($oldLocationsIds, array_filter($newLocationsIds));
			foreach ($toRemove as $id) {
				wp_delete_post($id, $force = true);
			}
			
		}
		
		$route->updateLocationsAltitudes();
	}
	
	static function processDeleteRoute() {
		if (isset($_GET['id']) AND $route = Route::getInstance($_GET['id']) AND $route->canDelete()) {
			wp_delete_post($_GET['id'], $force = true);
			if ($referer = filter_input(INPUT_SERVER, 'HTTP_REFERER')) {
				wp_safe_redirect($referer);
			} else {
				wp_redirect(RouteController::getDashboardUrl('index'));
			}
			exit;
		} else if(isset($_GET['id']) AND $location = get_post($_GET['id'])) {
			if($location->post_type == 'cmloc_object') {
				wp_delete_post($_GET['id'], $force = true);
				if ($referer = filter_input(INPUT_SERVER, 'HTTP_REFERER')) {
					wp_safe_redirect($referer);
				}
				exit;
			}
		} else {
			die('error');
		}
	}
	
	/**
	 * Create slug from title.
	 * 
	 * @param array $data
	 * @param array $postarr
	 * @return array
	 */
	static function wp_insert_post_data($data, $postarr) {
		if ( $data['post_type'] == Route::POST_TYPE AND !in_array( $data['post_status'], array( 'draft', 'pending', 'auto-draft' ) ) ) {
			//$data['post_name'] = wp_unique_post_slug(sanitize_title( $data['post_title'] ), $postarr['ID'], $data['post_status'], $data['post_type'], $data['post_parent']);
		}
		return $data;
	}
	
	static function admin_init() {
		global $pagenow;
		
		//if (Settings::getOption(Settings::OPTION_BACKEND_EDIT_ROUTE_ENABLE)) return;
		
		/*
 		$post_id = intval(isset($_GET['post']) ? (int) $_GET['post'] : -1);
 		if (Route::POST_TYPE == get_post_type($post_id) AND !empty($_GET['action']) AND $_GET['action'] == 'edit') {
 			wp_redirect(FrontendController::getUrl('routes/edit', array('id' => $post_id)));
 			exit;
		}
		*/

		if ( isset($_GET['post_type']) AND $_GET['post_type'] == Route::POST_TYPE AND $pagenow == 'post-new.php' ) {
			wp_redirect(FrontendController::getUrl('routes/add'));
			exit;
		}
		
	}
	
	static function loadGoogleChart() {
		echo "<script>if (typeof google != 'undefined') google.load('visualization', '1', {packages: ['columnchart']});</script>";
	}
	
	static function cmmrm_route_params_save() {
		if (!empty($_POST['nonce']) AND wp_verify_nonce($_POST['nonce'], self::UPDATE_PARAMS_NONCE)) {
			if (!empty($_POST['routeId']) AND $route = Route::getInstance($_POST['routeId'])) {
				if (!empty($_POST['distance'])) $route->setDistance($_POST['distance']);
				if (!empty($_POST['duration'])) $route->setDuration($_POST['duration']);
				if (!empty($_POST['minElevation'])) $route->setMinElevation($_POST['minElevation']);
				if (!empty($_POST['maxElevation'])) $route->setMaxElevation($_POST['maxElevation']);
				if (!empty($_POST['elevationGain'])) $route->setElevationGain($_POST['elevationGain']);
				if (!empty($_POST['elevationDescent'])) $route->setElevationDescent($_POST['elevationDescent']);
				if (!empty($_POST['avgSpeed'])) $route->setAvgSpeed($_POST['avgSpeed']);
				if (!empty($_POST['locations']) AND is_array($_POST['locations'])) {
					foreach ($_POST['locations'] as $data) {
						if ($location = Location::getInstance($data['id'])) {
							$location->setAddress($data['addr']);
						}
						Location::clearInstances();
					}
				}
				echo 'ok';
			}
		}
		echo 'error';
		exit;
	}
	
	static function cmmrm_route_image_upload() {
		$response = array('success' => false, 'msg' => 'An error occurred.');
		$nonce = filter_input(INPUT_POST, 'nonce');
		if (wp_verify_nonce($nonce, 'cmmrm-editor-media')) {
			
			foreach ($_FILES as $file) {
				try {
					
					$filePath = Attachment::uploadMedia($file);
					
					$parentPostId = 0;
					$fileId = Attachment::create($filePath, $file['type'], $parentPostId);
					$attachment = Attachment::getInstance($fileId);
					
					$response['files'][] = array(
						'id' => $fileId,
						'thumb' => $attachment->getImageUrl(Attachment::IMAGE_SIZE_THUMB),
						'url' => $attachment->getImageUrl(),
					);
					
				} catch (\Exception $e) {
					$response['msg'] = $e->getMessage();
				}
			}
			
			if (!empty($response['files'])) {
				$response['msg'] = Labels::getLocalized('upload_success');
				$response['success'] = true;
			}
			
		}
		
		header('content-type: application/json');
		echo json_encode($response);
		exit;
	}
	
	static function cmmrm_update_distance() {
		$response = array('success' => 0, 'msg' => 'Error');

		$routeid = filter_input(INPUT_POST, 'routeid');
		$distance = filter_input(INPUT_POST, 'distance');
		$duration = filter_input(INPUT_POST, 'duration');
		$avg_speed = filter_input(INPUT_POST, 'avg_speed');
		$overview_path = filter_input(INPUT_POST, 'overview_path');

		if (!empty($routeid)) {
			
			update_post_meta( $routeid, '_cmmrm_distance', $distance );
			update_post_meta( $routeid, '_cmmrm_duration', $duration );
			update_post_meta( $routeid, '_cmmrm_avg_speed', $avg_speed );
			update_post_meta( $routeid, '_cmmrm_overview_path', $overview_path );

			$response = array('success' => 1, 'msg' => 'Success');
		}

		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}

//    static function cmmrm_save_walking_path() {
//        $response = array('success' => 0, 'msg' => 'Error');
//
//        $route_id = filter_input(INPUT_POST, 'route_id');
//        $path = filter_input(INPUT_POST, 'path');
//
//        if (!empty($route_id)) {
//            $updated = update_post_meta( $route_id, '_cmmrm_walking_path', $path );
//            if ($updated){
//                $response = array('success' => 1, 'msg' => 'Success');
//            }
//        }
//
//        header('Content-type: application/json');
//        echo json_encode($response);
//        exit;
//    }

	static function cmmrm_add_category() {
		$response = array('success' => 0, 'msg' => 'Error');
		$cat_name = filter_input(INPUT_POST, 'catName');
		$parent = filter_input(INPUT_POST, 'catParentId');
		if (!empty($cat_name)) {
			$termObj = wp_insert_term($cat_name, 'cmmrm_category', array('parent' => $parent));
			
			/* Merge CM Map Locations plugin categories if enabled */
			//if(Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_INTEGRATION) == '1' && Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_MERGE_CATEGORIES) == '1') {
			//	$ltermObj = wp_insert_term($cat_name, 'cmloc_category', array('parent' => 0));
			//}

			$response = array('success' => 1, 'msg' => 'Success', 'lastid' => $termObj['term_id']);
		}
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}

	static function cmmrm_remove_route() {
		$response = array('success' => 0, 'msg' => 'Error');
		$id = filter_input(INPUT_POST, 'id');
		if (!empty($id)) {
			wp_delete_post($id, true);
			$response = array('success' => 1, 'msg' => 'Success');
		}
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}

	static function cmmrm_route_add_video() {
		$response = array('success' => 0, 'msg' => 'Error');
		$nonce = filter_input(INPUT_POST, 'nonce');
		if (wp_verify_nonce($nonce, 'cmmrm-editor-media')) {
			$url = filter_input(INPUT_POST, 'url');
			if (!empty($url)) {
				if (Attachment::isYouTubeUrl($url)) { // YouTube
					$attachment = Attachment::createYouTube(0, $url);
				} else { // remote url
					$attachment = Attachment::getByUrl($url);
					if (empty($attachment)) {
						$url = preg_replace('~(\-[0-9]+x[0-9]+)(\.\w+)~', '$2', $url);
						$attachment = Attachment::getByUrl($url);
					}
				}
				
				if (!empty($attachment)) {
					$response = array(
						'success' => 1,
						'id' => $attachment->getId(),
						'url' => $attachment->getUrl(),
						'thumb' => $attachment->getImageUrl(Attachment::IMAGE_SIZE_THUMB),
					);
				} else {
					$response['msg'] = 'Attachment not found.';
				}
			} else {
				$response['msg'] = 'Missing URL parameter.';
			}
		} else {
			$response['msg'] = 'Invalid request.';
		}
	
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}
	
	static function cmmrm_load_google_images() {
		$out = '';
		
		$apiKey = Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY);
		$searchEngineId = Settings::getOption(Settings::OPTION_GOOGLE_SEARCH_ENGINE_ID);
		$query = filter_input(INPUT_POST, 'route_name');
		$page = filter_input(INPUT_POST, 'page');
		$numResults = 10;
		$start = (($page * $numResults) - ($numResults-1));
		
		$url = "https://www.googleapis.com/customsearch/v1?searchType=image&safe=active";
		$url .= "&key=".$apiKey;
		$url .= "&cx=".$searchEngineId;
		$url .= "&q=".urlencode($query);
		$url .= "&start=".$start;
		$url .= "&num=".$numResults;
		$res = file_get_contents($url);
		$images = json_decode($res, true);
		
		if (isset($images['items'])) {
			foreach ($images['items'] as $item) {
				$out .= '<img src="' . $item['link'] . '" alt="' . $item['title'] . '" class="googleimage" />';
			}
		}
		
		if($out != '') {
			$response = array('success' => 1, 'html' => $out);
		} else {
			$response = array('success' => 1, 'html' => 'No images found.');
		}
		
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}
	
	static function cmmrm_add_google_image() {
		$response = array('success' => 0, 'msg' => 'Error');
		$image_url = $_POST['url'];
		if (!empty($image_url)) {
			$attach_id = Attachment::upload_image_from_url($image_url);
			if (!is_wp_error($attach_id)) {
				$attachment = Attachment::getInstance($attach_id);
				if (!empty($attachment)) {
					$response = array(
						'success' => 1,
						'id' => $attachment->getId(),
						'url' => $attachment->getUrl(),
						'thumb' => $attachment->getImageUrl(Attachment::IMAGE_SIZE_THUMB),
					);
				} else {
					$response['msg'] = 'Attachment not found.';
				}
			} else {
				$response['msg'] = $attach_id->get_error_message();
			}
		} else {
			$response['msg'] = 'Missing URL parameter.';
		}
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}
	
}
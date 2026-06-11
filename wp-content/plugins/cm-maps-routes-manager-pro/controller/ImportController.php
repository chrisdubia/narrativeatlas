<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\model\Attachment;
use com\cminds\mapsroutesmanager\helper\GpxHelper;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\helper\KmlHelper;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\helper\import\GpxImportSource;
use com\cminds\mapsroutesmanager\helper\import\RouteImportHelper;

class ImportController extends Controller {
	
	const NONCE_ACTION_IMPORT = 'cmmrm_route_import_nonce';
	const NONCE_ACTION_IMPORT_CSV = 'cmmrm_route_import_csv_nonce';
	const NONCE_ACTION_EXPORT = 'cmmrm_route_export_nonce';
	const NONCE_ACTION_DUPLICATE = 'cmmrm_route_duplicate_nonce';
	const ACTION_EXPORT_ROUTE = 'cmmrm-export-route';
	const DASHBOARD_IMPORT_NONCE = 'cmmrm_route_import';
	const FRONTEND_NONCE_EXPORT = 'cmmrm_route_export_nonce';
	
	const PARAM_ACTION = 'cmmrm_action';
	
	static $filters = array(
		'post_row_actions' => array('args' => 2),
	);
	
	protected static $actions = array(
		'admin_menu' => array('priority' => 13),
		'cmmrm_route_after_save' => array('method' => 'processSaveRouteImportLocations', 'args' => 1, 'priority' => 10000),
		'cmmrm_route_editor_before_map' => array('args' => 1, 'priority' => 1000),
		'cmmrm_route_single_toolbar_middle' => array('args' => 1),
	);
	
	static $supportedFormats = array('kml', 'gpx', 'kmz');
	
	static function admin_menu() {
		add_submenu_page(App::PREFIX, App::getPluginName() . ' Export/Import', 'Export/Import', 'manage_options', self::getMenuSlug(), array(get_called_class(), 'render'));
	}
	
	static function render() {
		wp_enqueue_style('cmmrm-backend');
		wp_enqueue_style('cmmrm-settings');
		wp_enqueue_script('cmmrm-backend');
		echo self::loadView('backend/template', array(
			'title' => App::getPluginName() . ' Export and Import',
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('import', array(
				'formUrl' => admin_url('admin.php?page='. urlencode(self::getMenuSlug())),
				'nonceField' => self::NONCE_ACTION_IMPORT,
				'nonce' => wp_create_nonce(self::NONCE_ACTION_IMPORT),
				'nonceFieldCsv' => self::NONCE_ACTION_IMPORT_CSV,
				'nonceCsv' => wp_create_nonce(self::NONCE_ACTION_IMPORT_CSV),
			)) . self::loadBackendView('export', array(
				'formUrl' => admin_url('admin.php?page='. urlencode(self::getMenuSlug())),
				'nonceField' => self::NONCE_ACTION_EXPORT,
				'nonce' => wp_create_nonce(self::NONCE_ACTION_EXPORT),
			)),
		));
	}
	
	static function getMenuSlug() {
		return App::PREFIX . '-import';
	}
	
	static function processRequest() {
		//if (!empty($_POST)) { var_dump($_POST);exit; }
		if (!is_admin()) {
			//var_dump(FrontendController::getRoute());exit;
			if (FrontendController::isRouteSinglePage() AND $route = FrontendController::getRoute()) {
				if (!empty($_GET[static::PARAM_ACTION]) AND $_GET[static::PARAM_ACTION] == static::ACTION_EXPORT_ROUTE
						AND !empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], self::FRONTEND_NONCE_EXPORT)) {
					self::exportRoute($route, $useOriginals = true);
				}
			}
			if (FrontendController::isDashboard() AND FrontendController::getDashboardPage() == FrontendController::DASHBOARD_IMPORT) {
				if (!empty($_POST[self::DASHBOARD_IMPORT_NONCE]) AND wp_verify_nonce($_POST[self::DASHBOARD_IMPORT_NONCE], self::DASHBOARD_IMPORT_NONCE)) {
					self::processFrontendImport();
				}
			}
		}
		else if (!empty($_POST[self::NONCE_ACTION_IMPORT]) AND wp_verify_nonce($_POST[self::NONCE_ACTION_IMPORT], self::NONCE_ACTION_IMPORT)) {
			try {
				echo 'Importing...<br />';
				ob_flush();
				flush();
				self::processImportRoutes();
				die('End.');
			} catch (\Exception $e) {
				die('Error: ' . $e->getMessage());
			}
		}
		else if (!empty($_POST[self::NONCE_ACTION_IMPORT_CSV]) AND wp_verify_nonce($_POST[self::NONCE_ACTION_IMPORT_CSV], self::NONCE_ACTION_IMPORT_CSV)) {
			try {
				echo 'Importing...<br />';
				ob_flush();
				flush();
				self::processImportRoutesCsv();
				die('End.');
			} catch (\Exception $e) {
				die('Error: ' . $e->getMessage());
			}
		}
		else if (!empty($_GET[self::NONCE_ACTION_EXPORT]) AND wp_verify_nonce($_GET[self::NONCE_ACTION_EXPORT], self::NONCE_ACTION_EXPORT)) {
			if (!empty($_GET['id']) AND $route = Route::getInstance($_GET['id'])) {
				self::exportRoute($route, $useOriginals = false);
			}
		}
		else if (!empty($_GET[self::NONCE_ACTION_DUPLICATE]) AND wp_verify_nonce($_GET[self::NONCE_ACTION_DUPLICATE], self::NONCE_ACTION_DUPLICATE)) {
			if (!empty($_GET['id']) AND $route = Route::getInstance($_GET['id'])) {
				self::processDuplicateRoute($_GET['id']);
			}
		}
		else if (!empty($_POST[self::NONCE_ACTION_EXPORT]) AND wp_verify_nonce($_POST[self::NONCE_ACTION_EXPORT], self::NONCE_ACTION_EXPORT)) {
				
			$upload_dir = wp_upload_dir();
			$zipPath = $upload_dir['path'] .'/'. App::prefix('-export-'. md5(microtime(true))) . '.zip';
			
			$zip = new \ZipArchive();
			if ($res = $zip->open($zipPath, \ZipArchive::CREATE)) {
				$posts = get_posts(array(
					'posts_per_page' => -1,
					'post_type' => Route::POST_TYPE,
					'post_status' => 'any',
				));
				foreach ($posts as $post) {
					if ($route = Route::getInstance($post)) {
						$fileName = sanitize_title($route->getTitle()) .'.kml';
						$zip->addFromString($fileName, KmlHelper::export($route));
						Route::clearInstances();
					}
				}
				$zip->close();
				header('content-type: application/zip');
				header('Content-Disposition: attachment; filename="maps-export-'. Date('Y-m-d') .'.zip"');
				echo file_get_contents($zipPath);
				unlink($zipPath);
				exit;
				
			}
				
		}
	}
	
	static function processDuplicateRoute($route_id = 0) {
		global $wpdb;

		$route_data = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID='".$route_id."'");
		//echo "<pre>"; print_r($route_data); echo "</pre>";

		$new_route = array(
		  'post_author'			=> $route_data->post_author,
		  'post_content'		=> $route_data->post_content,
		  'post_title'			=> $route_data->post_title,
		  'post_excerpt'		=> $route_data->post_excerpt,
		  'post_status'			=> 'draft',
		  'comment_status'		=> $route_data->comment_status,
		  'ping_status'			=> $route_data->ping_status,
		  'post_password'		=> $route_data->post_password,
		  'post_parent'			=> $route_data->post_parent,
		  'menu_order'			=> $route_data->menu_order,
		  'post_type'			=> $route_data->post_type,
		  'post_mime_type'		=> $route_data->post_mime_type,
		  'comment_count'		=> $route_data->comment_count
		);
		$new_route_id = wp_insert_post($new_route);

		$route_post_data = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id='".$route_id."'");
		//echo "<pre>"; print_r($route_post_data); echo "</pre>";
		if(count($route_post_data) > 0) {
			foreach($route_post_data as $routepostdata) {
				$wpdb->insert($wpdb->prefix.'postmeta', array(
					'post_id' => $new_route_id,
					'meta_key' => $routepostdata->meta_key,
					'meta_value' => $routepostdata->meta_value,
				));
			}
		}

		$route_term_data = $wpdb->get_results("SELECT * FROM $wpdb->term_relationships WHERE object_id='".$route_id."'");
		//echo "<pre>"; print_r($route_term_data); echo "</pre>";
		if(count($route_term_data) > 0) {
			foreach($route_term_data as $routetermdata) {
				$table_name = $wpdb->prefix . "term_relationships";
				$wpdb->insert($table_name, array(
						'object_id' => $new_route_id,
						'term_taxonomy_id' => $routetermdata->term_taxonomy_id,
						'term_order' => $routetermdata->term_order
					)
				);
			}
		}

		$route_locations_data = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_parent='".$route_id."'");
		//echo "<pre>"; print_r($route_locations_data); echo "</pre>";

		if(count($route_locations_data) > 0) {
			foreach($route_locations_data as $location_data) {
				if($location_data->post_type == 'cmmrm_location') {
					$new_location = array(
					  'post_author'			=> $location_data->post_author,
					  'post_content'		=> $location_data->post_content,
					  'post_title'			=> $location_data->post_title,
					  'post_excerpt'		=> $location_data->post_excerpt,
					  'post_status'			=> $location_data->post_status,
					  'comment_status'		=> $location_data->comment_status,
					  'ping_status'			=> $location_data->ping_status,
					  'post_password'		=> $location_data->post_password,
					  'post_parent'			=> $new_route_id,
					  'menu_order'			=> $location_data->menu_order,
					  'post_type'			=> $location_data->post_type,
					  'post_mime_type'		=> $location_data->post_mime_type,
					  'comment_count'		=> $location_data->comment_count
					);
					$new_location_id = wp_insert_post($new_location);
					$location_post_data = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id='".$location_data->ID."'");
					//echo "<pre>"; print_r($location_post_data); echo "</pre>";
					if(count($location_post_data) > 0) {
						foreach($location_post_data as $locationpostdata) {
							$wpdb->insert($wpdb->prefix.'postmeta', array(
								'post_id' => $new_location_id,
								'meta_key' => $locationpostdata->meta_key,
								'meta_value' => $locationpostdata->meta_value,
							));
						}
					}
				}
				if($location_data->post_type == 'attachment') {
					$new_attachment = array(
					  'post_author'			=> $location_data->post_author,
					  'post_content'		=> $location_data->post_content,
					  'post_title'			=> $location_data->post_title,
					  'post_excerpt'		=> $location_data->post_excerpt,
					  'post_status'			=> $location_data->post_status,
					  'comment_status'		=> $location_data->comment_status,
					  'ping_status'			=> $location_data->ping_status,
					  'post_password'		=> $location_data->post_password,
					  'post_parent'			=> $new_route_id,
					  'menu_order'			=> $location_data->menu_order,
					  'post_type'			=> $location_data->post_type,
					  'post_mime_type'		=> $location_data->post_mime_type,
					  'comment_count'		=> $location_data->comment_count
					);
					$new_attachment_id = wp_insert_post($new_attachment);
					$attachment_post_data = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id='".$location_data->ID."'");
					//echo "<pre>"; print_r($attachment_post_data); echo "</pre>";
					if(count($attachment_post_data) > 0) {
						foreach($attachment_post_data as $attachmentpostdata) {
							$wpdb->insert($wpdb->prefix.'postmeta', array(
								'post_id' => $new_attachment_id,
								'meta_key' => $attachmentpostdata->meta_key,
								'meta_value' => $attachmentpostdata->meta_value,
							));
						}
					}
				}
			}
		}

		$admin_url = admin_url('edit.php?post_type=cmmrm_route');
		wp_redirect($admin_url);
	}

	static function frontendImportView() {
		$nonce = wp_create_nonce(self::DASHBOARD_IMPORT_NONCE);
		$nonceField = ImportController::DASHBOARD_IMPORT_NONCE;
		$formAction = RouteController::getDashboardUrl('import');
		return self::loadFrontendView('import', compact('nonce', 'nonceField', 'formAction'));
	}
	
	static function processFrontendImport($redirectUrl = null) {
		if (isset($_FILES['cmmrm_import_file']) AND empty($_FILES['cmmrm_import_file']['error']) AND is_uploaded_file($_FILES['cmmrm_import_file']['tmp_name'])) {
			
			try {
				
				if ('.gpx' == substr($_FILES['cmmrm_import_file']['name'], -4, 4)) {
					
					$importSource = GpxImportSource::createFromFile($_FILES['cmmrm_import_file']['tmp_name']);
					$postdata = array('post_title' => $_FILES['cmmrm_import_file']['name']);
					$route = RouteImportHelper::createRoute($importSource, $postdata);
					$importHelper = new RouteImportHelper($route);
					$importHelper->setPathCoords($importSource);
					$importHelper->importLocations($importSource);
					$importHelper->calculateRouteParamsFromPathCoords($importSource);
					
				} else {
					$route = KmlHelper::importSingleRoute(
						$_FILES['cmmrm_import_file']['tmp_name'],
						$_FILES['cmmrm_import_file']['name'],
						get_current_user_id(),
						ImportController::getMaxWaypointsParam()
					);
				}
				
				$redirectUrl = RouteController::getDashboardUrl('edit', array('id' => $route->getId(), 'recalculate' => 1));
				wp_redirect($redirectUrl);
				exit;
				
			} catch (\Exception $e) {
				die($e->getMessage());
			}
		} else {
			die('File upload error');
		}
	}
	
	static function processSaveRouteImportLocations(Route $route) {
		
		if (isset($_FILES['cmmrm_import_file']) AND empty($_FILES['cmmrm_import_file']['error']) AND is_uploaded_file($_FILES['cmmrm_import_file']['tmp_name'])) {
					
			// Remove old locations
			$locationsIds = $route->getLocationsIds();
			foreach ($locationsIds as $id) {
				wp_delete_post($id, $force = true);
			}
			
			$route->setTravelMode('DIRECT');
			
			try {
				
				if ('.gpx' == substr($_FILES['cmmrm_import_file']['name'], -4, 4)) {
					
					$format = 'gpx';
					
					$importSource = GpxImportSource::createFromFile($_FILES['cmmrm_import_file']['tmp_name']);
					$importHelper = new RouteImportHelper($route);
					$importHelper->setPathCoords($importSource);
					$importHelper->importLocations($importSource);
					$importHelper->updateRouteParamsFromPathCoords($importSource);
				
				} else { // KML
					
					$format = 'kml';
					$kmlSource = $source = KmlHelper::importReadfile($_FILES['cmmrm_import_file']['tmp_name'], $_FILES['cmmrm_import_file']['name']);
					
					if ($xml = KmlHelper::getSimpleXmlInstance($kmlSource)) {
						
						$routeData = KmlHelper::getRouteData($xml);
						$maxWaypoints = ImportController::getMaxWaypointsParam();
						
						// Import route path
						$points = KmlHelper::importRoutePath($route, $xml, $routeData);
						
						// Import locations markers
						KmlHelper::importLocations($route, $xml, $routeData, $points);
						
						// Refresh route params
						KmlHelper::setRouteParameters($xml, $routeData, $route);
						
					} else {
						throw new \Exception('Cannot read the KML file.');
					}
					
				}
				
				// Keep the original file
				Attachment::keepOriginalImportFile($route, $_FILES['cmmrm_import_file']);
				
				wp_redirect(add_query_arg('recalculate', 1, add_query_arg('msg', 'route_save_success', $route->getUserEditUrl())));
				exit;
				
			} catch (Exception $e) {
				wp_redirect(add_query_arg('msg', 'route_save_error', $route->getUserEditUrl()));
				exit;
				//die($e->getMessage());
			}
			
		}
	}
	
	static function processImportRoutes() {
		if (isset($_FILES['cmmrm_import_file']) AND empty($_FILES['cmmrm_import_file']['error']) AND is_uploaded_file($_FILES['cmmrm_import_file']['tmp_name'])) {

			$path = $_FILES['cmmrm_import_file']['tmp_name'];
			$fileName = $_FILES['cmmrm_import_file']['name'];
			if ('.zip' == substr($fileName, -4, 4)) {
				$zip = new \ZipArchive();
				if ($zip->open($path)) {
					$files = KmlHelper::kmzListFiles($zip);
					foreach ($files as $fileName) {
						$source = KmlHelper::kmzGetSource($zip, $fileName);
						self::processImportSingleRoute($source, $fileName);
					}
				}
				$zip->close();
			}
			else if ('.kmz' == substr($fileName, -4, 4)) {
				if ($kmlSource = KmlHelper::importReadfile($path, $fileName)) {
					self::processImportSingleRoute($kmlSource, $fileName);
				}
			}
			else if ('.gpx' == substr($fileName, -4, 4)) {
				
				$importSource = GpxImportSource::createFromFile($path);
				$postdata = array('post_title' => $fileName);
				$route = RouteImportHelper::createRoute($importSource, $postdata);
				$importHelper = new RouteImportHelper($route);
				$importHelper->setPathCoords($importSource);
				$importHelper->importLocations($importSource);
				$importHelper->calculateRouteParamsFromPathCoords($importSource);
				
				echo 'Imported: ' . esc_html($route->getTitle()) . '<br>';

				// Keep the original file
				Attachment::keepOriginalImportFile($route, $_FILES['cmmrm_import_file']);
				
			} else { // KML
				
				$route = self::processImportSingleRoute(file_get_contents($path), $fileName);
				
				// Keep the original file
				Attachment::keepOriginalImportFile($route, $_FILES['cmmrm_import_file']);
				
			}
			
		} else {
			throw new \Exception('Invalid upload');
		}
		
	}
	
	static function processImportRoutesCsv() {
		if (isset($_FILES['cmmrm_import_file_csv']) AND empty($_FILES['cmmrm_import_file_csv']['error']) AND is_uploaded_file($_FILES['cmmrm_import_file_csv']['tmp_name'])) {

			$path = $_FILES['cmmrm_import_file_csv']['tmp_name'];
			$fileName = $_FILES['cmmrm_import_file_csv']['name'];

			if ('.csv' == substr($fileName, -4, 4)) {
				
				$fdata_cols = array();
				$fdata = array();
				$row_cols = 0;
				$row = 0;
				if (($handle = fopen($path, "r")) !== FALSE) {
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						$num = count($data);
						for ($c=0; $c < $num; $c++) {
							if($row_cols == 0) {
								$fdata_cols[$row_cols][$c] = $data[$c];
							} else {
								$fdata[$row][$fdata_cols[0][$c]] = $data[$c];
							}
						}
						$row_cols++;
						if($row_cols > 1) {
							$row++;
						}
					}
					fclose($handle);
				}

				if(count($fdata) > 0) {
					foreach($fdata as $csvroute) {
						//$csv_id = $csvroute['id'];
						$csv_name = $csvroute['name'];
						$csv_slug = $csvroute['slug'];
						$csv_description = $csvroute['description'];
						//$csv_mark = $csvroute['mark'];
						$csv_gpx = $csvroute['gpx'];
						//$csv_tags = $csvroute['tags'];
						//$csv_author = $csvroute['author'];
						//$csv_likes = $csvroute['likes'];
						//$csv_owner = $csvroute['owner'];
						//$csv_checkins_count = $csvroute['checkins_count'];
						
						$postdata = array(
							'post_title' => $csv_name,
							'post_name' => $csv_slug,
							'post_content' => $csv_description,
							'post_status' => 'publish'
						);

						if($csv_gpx == '') {
							$route = RouteImportHelper::createRouteCsv($postdata);
						} else {
							$importSource = GpxImportSource::createFromFileCsv($csv_gpx);
							$route = RouteImportHelper::createRouteCSVWithFile($importSource, $postdata);
							$importHelper = new RouteImportHelper($route);
							$importHelper->setPathCoords($importSource);
							$importHelper->importLocations($importSource);
							$importHelper->calculateRouteParamsFromPathCoords($importSource);
						}

						echo 'Imported: ' . esc_html($route->getTitle()) . '<br>';

					}
				}

			} else {
				throw new \Exception('Invalid file');
			}

		} else {
			throw new \Exception('Invalid upload');
		}
		
	}

	static function processImportSingleRoute($source, $fileName) {
		try {
			$route = KmlHelper::importRouteFile(
				$source,
				get_current_user_id(),
				self::getMaxWaypointsParam(),
				$fileName
			);
			echo 'Success importing '. $fileName .'<br />';
			return $route;
		} catch (\Exception $e) {
			echo $e->getMessage() . '<br />';
			flush();
			ob_flush();
		}
	}
	
	static function getMaxWaypointsParam() {
		if (isset($_POST['max_waypoints']) AND is_numeric($_POST['max_waypoints'])) {
			$maxWaypoints = $_POST['max_waypoints'];
		} else {
			$maxWaypoints = 0;
		}
		if ($maxWaypoints < 1 OR $maxWaypoints > Route::WAYPOINTS_LIMIT) {
			$maxWaypoints = Route::WAYPOINTS_LIMIT;
		}
		return $maxWaypoints;
	}

	static function post_row_actions($actions, $post) {
		if ( $post->post_type === Route::POST_TYPE AND $route = Route::getInstance($post) ) {
			
			// Export as KML
			$url = add_query_arg(array(
				'page' => ImportController::getMenuSlug(),
				'id' => $route->getId(),
				'format' => 'kml',
				ImportController::NONCE_ACTION_EXPORT => wp_create_nonce(ImportController::NONCE_ACTION_EXPORT),
			), admin_url('admin.php'));
			$actions['export_kml'] = sprintf('<a href="%s">%s</a>', esc_attr($url), 'Export as KML');
			
			// Export as GPX
			$url = add_query_arg(array(
				'page' => ImportController::getMenuSlug(),
				'id' => $route->getId(),
				'format' => 'gpx',
				ImportController::NONCE_ACTION_EXPORT => wp_create_nonce(ImportController::NONCE_ACTION_EXPORT),
			), admin_url('admin.php'));
			$actions['export_gpx'] = sprintf('<a href="%s">%s</a>', esc_attr($url), 'Export as GPX');
			
			// Download Original File
			if ($attachment = $route->getOriginalImportFile()) {
				$url = add_query_arg(array(
					'page' => ImportController::getMenuSlug(),
					'id' => $route->getId(),
					'format' => 'original',
					ImportController::NONCE_ACTION_EXPORT => wp_create_nonce(ImportController::NONCE_ACTION_EXPORT),
				), admin_url('admin.php'));
				$actions['cm_download_original'] = sprintf('<a href="%s">%s</a>', esc_attr($url), 'Download Original File');
			}

			// Duplicate Route
			$url = add_query_arg(array(
				'page' => ImportController::getMenuSlug(),
				'id' => $route->getId(),
				ImportController::NONCE_ACTION_DUPLICATE => wp_create_nonce(ImportController::NONCE_ACTION_DUPLICATE),
			), admin_url('admin.php'));
			$actions['duplicate_route'] = sprintf('<a href="%s">%s</a>', esc_attr($url), 'Duplicate Route');
			
		}
		return $actions;
	}
	
	static function cmmrm_route_editor_before_map(Route $route) {
		$route_form_import = Settings::getOption(Settings::OPTION_ROUTE_FORM_IMPORT);
		if ($route_form_import != 'none') {
			echo self::loadFrontendView('editor');
		}
	}
	
	static function exportRoute(Route $route, $useOriginals = true) {
		/* @var $attachment Attachment */
		$format = (isset($_GET['format']) ? $_GET['format'] : 'kml');
		switch ($format) {
			case 'original':
				if ($attachment = $route->getOriginalImportFile()) {
					if ($attachment->isGpx()) {
						$mime = GpxHelper::MIME_TYPE;
						$ext = 'gpx';
					} else {
						$mime = KmlHelper::MIME_TYPE;
						$ext = 'kml';
					}
					header('content-type: ' . $mime);
					header('Content-Disposition: attachment; filename="'. sanitize_title($route->getTitle()) .'.'. $ext .'"');
					echo file_get_contents($attachment->getFilePath());
				}
				break;
			case 'gpx':
				header('content-type: ' . GpxHelper::MIME_TYPE);
				header('Content-Disposition: attachment; filename="'. sanitize_title($route->getTitle()) .'.gpx"');
				if ($useOriginals AND $attachment = $route->getOriginalImportFile()) {
					if ($attachment->isGpx()) {
						//echo file_get_contents($attachment->getFilePath());
						echo GpxHelper::export($route);
					} else { // Convert original KML or KMZ to GPX
						$path = $attachment->getFilePath();
						echo GpxHelper::convertFromKml(KmlHelper::importReadfile($path, basename($path)));
					}
				} else {
					echo GpxHelper::export($route);
				}
				break;
			default:
				if ($useOriginals AND $attachment = $route->getOriginalImportFile()) {
					if ($attachment->isGpx()) {
						//$kmlSource = GpxHelper::convertToKml(file_get_contents($attachment->getFilePath()));
						$kmlSource = KmlHelper::export($route);
					} else { // KML or KMZ
						$path = $attachment->getFilePath();
						$kmlSource = KmlHelper::importReadfile($path, basename($path));
					}
				} else {
					$kmlSource = KmlHelper::export($route);
				}
				header('content-type: ' . KmlHelper::MIME_TYPE);
				header('Content-Disposition: attachment; filename="'. sanitize_title($route->getTitle()) .'.kml"');
				echo $kmlSource;
		}
		exit;
	}

	static function cmmrm_route_single_toolbar_middle(Route $route) {
		
		if (!Settings::getOption(Settings::OPTION_ROUTE_DOWNLOAD_FILE_ENABLE)) return;
		
		if(Settings::getOption(Settings::OPTION_ROUTE_DOWNLOAD_FILE_ENABLE) == '1') {

			$attachment = $route->getOriginalImportFile();
			if($attachment) {
				if($attachment->isGpx()) {
					// Export to KML
					$url = add_query_arg(array(
						static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
						'format' => 'kml',
						'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
					), $route->getPermalink());
					printf('<li><a class="cmmrm-export-kml case1" href="%s" title="%s"><span class="dashicons dashicons-download"></span>KML</a></li>',
						esc_attr($url),
						esc_attr(Labels::getLocalized('route_export_kml'))
					);
					// Export to GPX
					$url = add_query_arg(array(
						static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
						'format' => 'original',
						'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
					), $route->getPermalink());
					printf('<li><a class="cmmrm-export-gpx case1" href="%s" title="%s"><span class="dashicons dashicons-download"></span>GPX</a></li>',
						esc_attr($url),
						esc_attr(Labels::getLocalized('route_export_gpx'))
					);
				} else {
					// Export to KML
					$url = add_query_arg(array(
						static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
						'format' => 'original',
						'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
					), $route->getPermalink());
					printf('<li><a class="cmmrm-export-kml case2" href="%s" title="%s"><span class="dashicons dashicons-download"></span>KML</a></li>',
						esc_attr($url),
						esc_attr(Labels::getLocalized('route_export_kml'))
					);
					/*
					printf('<li><a href="#" onclick="window.open(\'%s\', \'_system\'); return false;" title="%s"><span></span>KML</a></li>',
						esc_attr($url),
						esc_attr(Labels::getLocalized('route_export_kml'))
					);
					*/
					// Export to GPX
					$url = add_query_arg(array(
						static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
						'format' => 'gpx',
						'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
					), $route->getPermalink());
					printf('<li><a class="cmmrm-export-gpx case2" href="%s" title="%s"><span class="dashicons dashicons-download"></span>GPX</a></li>',
						esc_attr($url),
						esc_attr(Labels::getLocalized('route_export_gpx'))
					);
				}
			} else {
				// Export to KML
				$url = add_query_arg(array(
					static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
					'format' => 'kml',
					'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
				), $route->getPermalink());
				printf('<li><a class="cmmrm-export-kml case3" href="%s" title="%s"><span class="dashicons dashicons-download"></span>KML</a></li>',
					esc_attr($url),
					esc_attr(Labels::getLocalized('route_export_kml'))
				);
				// Export to GPX
				$url = add_query_arg(array(
					static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
					'format' => 'gpx',
					'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
				), $route->getPermalink());
				printf('<li><a class="cmmrm-export-gpx case3" href="%s" title="%s"><span class="dashicons dashicons-download"></span>GPX</a></li>',
					esc_attr($url),
					esc_attr(Labels::getLocalized('route_export_gpx'))
				);
			}

		}

		if(Settings::getOption(Settings::OPTION_ROUTE_DOWNLOAD_FILE_ENABLE) == '2') {

			if ( is_user_logged_in() ) {

				$attachment = $route->getOriginalImportFile();
				if($attachment) {
					if($attachment->isGpx()) {
						// Export to KML
						$url = add_query_arg(array(
							static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
							'format' => 'kml',
							'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
						), $route->getPermalink());
						printf('<li><a class="cmmrm-export-kml case4" href="%s" title="%s"><span class="dashicons dashicons-download"></span>KML</a></li>',
							esc_attr($url),
							esc_attr(Labels::getLocalized('route_export_kml'))
						);
						// Export to GPX
						$url = add_query_arg(array(
							static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
							'format' => 'original',
							'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
						), $route->getPermalink());
						printf('<li><a class="cmmrm-export-gpx case4" href="%s" title="%s"><span class="dashicons dashicons-download"></span>GPX</a></li>',
							esc_attr($url),
							esc_attr(Labels::getLocalized('route_export_gpx'))
						);
					} else {
						// Export to KML
						$url = add_query_arg(array(
							static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
							'format' => 'original',
							'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
						), $route->getPermalink());
						printf('<li><a class="cmmrm-export-kml case5" href="%s" title="%s"><span class="dashicons dashicons-download"></span>KML</a></li>',
							esc_attr($url),
							esc_attr(Labels::getLocalized('route_export_kml'))
						);
						// Export to GPX
						$url = add_query_arg(array(
							static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
							'format' => 'gpx',
							'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
						), $route->getPermalink());
						printf('<li><a class="cmmrm-export-gpx case5" href="%s" title="%s"><span class="dashicons dashicons-download"></span>GPX</a></li>',
							esc_attr($url),
							esc_attr(Labels::getLocalized('route_export_gpx'))
						);
					}
				} else {
					// Export to KML
					$url = add_query_arg(array(
						static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
						'format' => 'kml',
						'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
					), $route->getPermalink());
					printf('<li><a class="cmmrm-export-kml case6" href="%s" title="%s"><span class="dashicons dashicons-download"></span>KML</a></li>',
						esc_attr($url),
						esc_attr(Labels::getLocalized('route_export_kml'))
					);
					// Export to GPX
					$url = add_query_arg(array(
						static::PARAM_ACTION => static::ACTION_EXPORT_ROUTE,
						'format' => 'gpx',
						'nonce' => wp_create_nonce(self::FRONTEND_NONCE_EXPORT),
					), $route->getPermalink());
					printf('<li><a class="cmmrm-export-gpx case6" href="%s" title="%s"><span class="dashicons dashicons-download"></span>GPX</a></li>',
						esc_attr($url),
						esc_attr(Labels::getLocalized('route_export_gpx'))
					);
				}

			} else {

				echo '<li class="cmmrm-export-reg-li"><a class="cmmrm-export-reg" href="'.Settings::getOption(Settings::OPTION_ROUTE_DOWNLOAD_LOGIN_PAGE_URL).'"><span class="dashicons dashicons-download"></span>'.Labels::getLocalized('route_login_for_download').'</a></li>';

			}

		}

	}
		
}
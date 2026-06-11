<?php
namespace com\cminds\mapsroutesmanager;

use com\cminds\mapsroutesmanager\core\Core;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\controller\SettingsController;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\User;
use com\cminds\mapsroutesmanager\model\Labels;

require_once dirname(__FILE__) . '/core/Core.php';

class App extends Core {
	
	const PREFIX = 'cmmrm';
	const SLUG = 'cm-maps-routes-manager';
	const PLUGIN_NAME = 'CM Maps Routes Manager';
	const PLUGIN_WEBSITE = 'https://www.cminds.com/';
	
	static function bootstrap($pluginFile) {
		parent::bootstrap($pluginFile);
	}
	
	static protected function getClassToBootstrap() {
		//require App::path('common/autoload.php');
		$classToBootstrap = array_merge(
			parent::getClassToBootstrap(),
			static::getClassNames('controller'),
			static::getClassNames('model')
		);
		if (static::isLicenseOk()) {
			$classToBootstrap = array_merge($classToBootstrap, static::getClassNames('shortcode'),
					static::getClassNames('widget'), static::getClassNames('metabox'));
		}
		return $classToBootstrap;
	}
	
	static function init() {
		parent::init();

		$galleryLib = Settings::getOption(Settings::OPTION_ROUTE_GALLERY_LIB);
		
		wp_register_script('cmmrm-utils', static::url('asset/js/utils.js'), array('jquery'), App::getVersion(), true);
		wp_register_script('cmmrm-google-api-check', static::url('asset/js/google-maps-api-check.js'), array('jquery'), App::getVersion(), true);
		wp_register_script('cmmrm-editor-images', App::url('asset/js/editor-images.js'), array('jquery', 'thickbox', 'jquery-ui-sortable'), App::getVersion(), true);
		wp_register_script('cmmrm-google-jsapi', 'https://www.google.com/jsapi', null, App::getVersion(), false);
		wp_register_script('cmmrm-google-marker-clusterer', static::url('asset/js/maps/markerclusterer.js'), null, App::getVersion(), false);

		$dont_embed_google_maps_js_api = Settings::getOption(Settings::OPTION_DONT_EMBED_GOOGLE_MAPS_JS_API);
		if ($dont_embed_google_maps_js_api == '1') {
			// However embed a dummy script to keep dependencies:
			wp_register_script('cmmrm-google-maps', static::url('asset/js/google-maps-dummy.js'), array('cmmrm-google-jsapi'), App::getVersion(), false);
		} else if($dont_embed_google_maps_js_api == '2') {
			$embed_selected_arr = array_filter(array_map('trim', explode("\n", Settings::getOption(Settings::OPTION_EMBED_SELECTED))));
			if (!is_array($embed_selected_arr)) {
				$embed_selected_arr = array();
			}
			if(count($embed_selected_arr) > 0) {
				$http_s = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
				$current_url = $http_s.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				// Embed Google Maps API with the API key:
				$google_map_key = Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY);
				$google_map_lang = Settings::getOption(Settings::OPTION_GOOGLE_MAPS_LANG);
				foreach($embed_selected_arr as $url) {
					if (strpos($current_url, $url) !== false) {
						wp_register_script('cmmrm-google-maps', 'https://maps.googleapis.com/maps/api/js?key='. urlencode($google_map_key) .'&language='.$google_map_lang.'&libraries=places,geometry,drawing', array('cmmrm-google-jsapi'), App::getVersion(), false);
					}
				}
			} else {
				// However embed a dummy script to keep dependencies:
				wp_register_script('cmmrm-google-maps', static::url('asset/js/google-maps-dummy.js'), array('cmmrm-google-jsapi'), App::getVersion(), false);
			}
		} else {
			// Embed Google Maps API with the API key:
			$google_map_key = Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY);
			$google_map_lang = Settings::getOption(Settings::OPTION_GOOGLE_MAPS_LANG);
			wp_register_script('cmmrm-google-maps', 'https://maps.googleapis.com/maps/api/js?key='. urlencode($google_map_key) .'&language='.$google_map_lang.'&libraries=places,geometry,drawing', array('cmmrm-google-jsapi'), App::getVersion(), false);
		}

		wp_register_script('cmmrm-map-marker', static::url('asset/js/maps/Marker.js'), array('cmmrm-google-maps'), App::getVersion(), true);
		wp_register_script('cmmrm-map-marker-geolocation', static::url('asset/js/maps/MarkerGeolocation.js'), array('cmmrm-google-maps'), App::getVersion(), true);
		wp_register_script('cmmrm-geolocation-feature', static::url('asset/js/maps/GeolocationFeature.js'), array('cmmrm-google-maps', 'cmmrm-map-marker-geolocation'), App::getVersion(), true);
		wp_register_script('cmmrm-map-tooltip', static::url('asset/js/maps/Tooltip.js'), array('cmmrm-google-maps'), App::getVersion(), true);
		wp_register_script('cmmrm-index-geolocation', static::url('asset/js/geolocation.js'), array(), App::getVersion(), true);
		wp_register_script('cmmrm-frontend', static::url('asset/js/frontend.js'), array(), App::getVersion(), true);
		wp_localize_script('cmmrm-frontend', 'CMMRM_Route_Frontend', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
		));
		wp_register_script('cmmrm-widget-single-route', static::url('asset/js/maps/WidgetSingleRoute.js'), array('jquery', 'cmmrm-map', 'cmmrm-route-rating',
			'cmmrm-geolocation-feature', 'cmmrm-fullscreen-feature', 'cmmrm-block-route-params', 'cmmrm-block-directions',
			'cmmrm-block-location-weather', 'cmmrm-route-gallery', 'cmmrm-single-location-renderer', 'cmmrm-map-locations-integration'), App::getVersion());
		wp_register_script('cmmrm-widget-index-map', static::url('asset/js/maps/WidgetIndexMap.js'), array('jquery', 'cmmrm-map',
			'cmmrm-geolocation-feature', 'cmmrm-fullscreen-feature', 'cmmrm-route-index-renderer', 'cmmrm-map-locations-integration'), App::getVersion());
		wp_register_script('cmmrm-map', static::url('asset/js/maps/GoogleMap.js'), array('jquery', 'cmmrm-google-maps', 'cmmrm-route-renderer',
			'cmmrm-location-renderer', 'cmmrm-waypoint-renderer', 'cmmrm-utils', 'cmmrm-map-marker', 'cmmrm-elevation-graph', 'cmmrm-map-tooltip'), App::getVersion());
		wp_register_script('cmmrm-route-model', static::url('asset/js/maps/RouteModel.js'), array('jquery'), App::getVersion());
		wp_register_script('cmmrm-location-model', static::url('asset/js/maps/LocationModel.js'), array('jquery'), App::getVersion());
		wp_register_script('cmmrm-waypoint-model', static::url('asset/js/maps/WaypointModel.js'), array('jquery'), App::getVersion());
		
		wp_register_script('cmmrm-route-renderer', static::url('asset/js/maps/RouteRenderer.js'), array('jquery', 'cmmrm-route-model',
			'cmmrm-request-trail', 'cmmrm-google-marker-clusterer'), App::getVersion());
		wp_localize_script('cmmrm-route-renderer', 'CMMRM_Route_Renderer', array(
			'pathOutlineColor' => Settings::getOption(Settings::OPTION_ROUTE_SETTING_DEFAULT_SHOW_PATH_OUTLINE_BGCOLOR),
		));

		wp_register_script('cmmrm-route-renderer-editor', static::url('asset/js/maps/RouteRendererEditor.js'), array('jquery', 'cmmrm-route-renderer'), App::getVersion());
		wp_register_script('cmmrm-route-index-renderer', static::url('asset/js/maps/RouteIndexRenderer.js'), array('jquery', 'cmmrm-route-model'), App::getVersion());
		wp_localize_script('cmmrm-route-index-renderer', 'CMMRM_Index_Map_Settings', array(
			'markerClickAction' => Settings::getIndexMapMarkerClick(),
			'showFullRoute' => Settings::getOption(Settings::OPTION_INDEX_MAP_FULL_ROUTE_ENABLE), // overview_path (draw polyline)
		));
		wp_register_script('cmmrm-location-renderer', static::url('asset/js/maps/LocationRenderer.js'), array('jquery', 'cmmrm-location-model'), App::getVersion());
		wp_register_script('cmmrm-editor-location-renderer', static::url('asset/js/maps/LocationRendererEditor.js'), array('jquery', 'cmmrm-location-renderer'), App::getVersion());
		wp_register_script('cmmrm-single-location-renderer', static::url('asset/js/maps/LocationRendererSingle.js'), array('jquery', 'cmmrm-location-renderer'), App::getVersion());
		wp_register_script('cmmrm-waypoint-renderer', static::url('asset/js/maps/WaypointRenderer.js'), array('jquery', 'cmmrm-waypoint-model'), App::getVersion());
		//wp_register_script('cmmrm-geolocation-marker', static::url('asset/js/maps/GeolocationMarker.js'), array('jquery', 'cmmrm-map-marker'), App::getVersion());
		wp_register_script('cmmrm-elevation-graph', static::url('asset/js/maps/ElevationGraph.js'), array('jquery', 'cmmrm-map-marker'), App::getVersion());
		wp_register_script('cmmrm-elevation-graph-standalone', static::url('asset/js/maps/ElevationGraphStandalone.js'), array('jquery', 'cmmrm-map-marker', 'cmmrm-elevation-graph'), App::getVersion());
		wp_register_script('cmmrm-elevation-graph-editor', static::url('asset/js/maps/ElevationGraphEditor.js'), array('jquery', 'cmmrm-map-marker', 'cmmrm-elevation-graph'), App::getVersion());
		wp_register_script('cmmrm-fullscreen-feature', static::url('asset/js/maps/FullscreenFeature.js'), array('jquery'), App::getVersion());
		wp_register_script('cmmrm-request-trail', static::url('asset/js/maps/RequestTrail.js'), array('jquery'), App::getVersion());
		wp_localize_script('cmmrm-request-trail', 'CMMRM_RequestTrail_Settings', array(
			'avoidHighways' => true
		));
		wp_register_script('cmmrm-block-route-params', static::url('asset/js/maps/BlockRouteParams.js'), array('jquery'), App::getVersion());
		wp_register_script('cmmrm-block-directions', static::url('asset/js/maps/BlockDirections.js'), array('jquery'), App::getVersion());
		wp_register_script('cmmrm-route-editor', static::url('asset/js/maps/Editor.js'), array('jquery', 'cmmrm-widget-single-route', 'jquery-ui-sortable',
			'cmmrm-editor-images', 'cmmrm-location-editor', 'cmmrm-editor-location-renderer', 'cmmrm-elevation-graph-editor', 'cmmrm-route-renderer-editor'),
				App::getVersion());
		wp_localize_script('cmmrm-route-editor', 'CMMRM_Map_Route_Editor_Settings', array(
			'confirmDeleteMsg' => Labels::getLocalized('confirm_delete_msg'),
			'ajaxurl' => admin_url('admin-ajax.php'),
			'returnurl' => FrontendController::getUrl(),
		));

		wp_register_script('cmmrm-location-editor', static::url('asset/js/maps/LocationEditor.js'), array(), App::getVersion());
		wp_localize_script('cmmrm-location-editor', 'CMMRM_Map_Location_Editor_Settings', array(
			'confirmDeleteMsg' => Labels::getLocalized('confirm_delete_msg'),
		));

		wp_register_script('cmmrm-block-location-weather', static::url('asset/js/maps/BlockLocationWeather.js'), array(), App::getVersion());
		wp_register_script('cmmrm-map-locations-integration', static::url('asset/js/maps/IntegrationMapLocations.js'), array(), App::getVersion());
		wp_localize_script('cmmrm-map-locations-integration', 'CMMRM_Map_Location_Integration', array(
			'shapeTooltipShow' => get_option('cmloc_single_route_shape_tooltip_show', 0) ? 1 : 0,
			'indexMapInfoWindowAutoClose' => get_option('cmloc_tooltip_close_when_another_clicked', 0) ? 1 : 0,
		));
		
		wp_register_script('cmmrm-backend', static::url('asset/js/backend.js'), array('jquery', 'cmmrm-google-api-check'), App::getVersion());
		wp_register_style('cmmrm-font-awesome', static::url('asset/vendor/font-awesome-4.4.0/css/font-awesome.min.css'), null, App::getVersion());
		wp_register_style('cmmrm-settings', static::url('asset/css/settings.css'), null, App::getVersion());
		wp_register_style('cmmrm-backend', static::url('asset/css/backend.css'), null, App::getVersion());
		wp_register_style('cmmrm-editor', static::url('asset/css/editor.css'), array('cmmrm-frontend'), App::getVersion());
		wp_register_style('cmmrm-embed', static::url('asset/css/embed.css'), array('cmmrm-frontend'), App::getVersion());
		
		$lookAndFeel = Settings::getOption(Settings::OPTION_LOOK_AND_FEEL_CSS);
		if ($lookAndFeel) {
			wp_register_style('cmmrm-look-and-feel', static::url('asset/css/look-and-feel/'. $lookAndFeel .'.css'), null, App::getVersion());
		}
		
		$styleDeps = array('cmmrm-font-awesome', 'dashicons');
		if ($lookAndFeel) $styleDeps[] = 'cmmrm-look-and-feel';
		if ($galleryLib == Settings::GALLERY_UNITEGALLERY) $styleDeps[] = 'cmmrm-unitegallery';
		wp_register_style('cmmrm-frontend', static::url('asset/css/frontend.css'), $styleDeps, App::getVersion());
		
		$galleryDeps = array('jquery');
		if ($galleryLib == Settings::GALLERY_UNITEGALLERY) $galleryDeps[] = 'cmmrm-unitegallery';
		wp_register_script('cmmrm-route-gallery', static::url('asset/js/route-gallery.js'), $galleryDeps, App::getVersion(), true);
		wp_register_script('cmmrm-index-ajax', static::url('asset/js/index-ajax.js'), array('jquery'), App::getVersion());
		wp_register_script('cmmrm-index-filter', static::url('asset/js/index-filter.js'), array('jquery', 'cmmrm-route-rating', 'cmmrm-geolocation-feature'), App::getVersion());
		
		// Old:
		wp_register_script('cmmrm-markerwithlabel', static::url('asset/js/markerwithlabel.js'), array('cmmrm-google-maps'), App::getVersion(), true);
		wp_register_script('cmmrm-map-abstract', static::url('asset/js/map-abstract.js'), array('jquery', 'cmmrm-google-maps',
			'cmmrm-map-marker', 'cmmrm-route-gallery', 'cmmrm-markerwithlabel', 'cmmrm-utils'), App::getVersion(), true);
		wp_register_script('cmmrm-index-map', static::url('asset/js/index-map.js'), array('cmmrm-map-abstract', 'cmmrm-route-rating'), App::getVersion(), true);
		wp_register_script('cmmrm-route-map', static::url('asset/js/route-map.js'), array('cmmrm-map-abstract', 'cmmrm-route-rating'), App::getVersion(), true);
		wp_register_script('cmmrm-route-rating', static::url('asset/js/route-rating.js'), array('jquery'), App::getVersion(), true);
		//wp_register_script('cmmrm-editor-media', static::url('asset/js/editor-media.js'), array('jquery'), App::getVersion(), true);
		wp_register_script('cmmrm-editor', static::url('asset/js/editor.js'), array('cmmrm-map-abstract', 'cmmrm-editor-images'), App::getVersion());

		wp_register_script('cmmrm-unitegallery', static::url('asset/vendor/unitegallery/js/unitegallery.min.js'), array('jquery', 'cmmrm-unitegallery-theme'), App::getVersion());
		wp_register_script('cmmrm-unitegallery-theme', static::url('asset/vendor/unitegallery/themes/tilesgrid/ug-theme-tilesgrid.js'), array('jquery'), App::getVersion());
		wp_register_style('cmmrm-unitegallery', static::url('asset/vendor/unitegallery/css/unite-gallery.css'), null, App::getVersion());
		
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		$cmloc_usertrack_user_path_enable = 0;
		if((is_plugin_active('cm-map-locations/cm-map-locations-pro.php') || is_plugin_active('cm-map-locations-pro/cm-map-locations-pro.php')) && get_option('cmloc_usertrack_usertracking_enable', '0') && Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_INTEGRATION) == '1') {
			$cmloc_usertrack_user_path_enable = intval(get_option('cmloc_usertrack_user_path_enable', '0'));
		}

		wp_localize_script('cmmrm-map', 'CMMRM_Map_Settings', apply_filters('cmmrm_map_settings', array(
			'indexRouteCurve' => defined('indexRouteCurve')?1:0,
			'ajaxurl' => admin_url('admin-ajax.php'),
			'change_map_style' => Labels::getLocalized('change_map_style'),
			'lengthUnits' => Settings::getOption(Settings::OPTION_UNIT_LENGTH),
			'feetToMeter' => Settings::FEET_TO_METER,
			'temperatureUnits' => Settings::getOption(Settings::OPTION_UNIT_TEMPERATURE),
			'feetInMile' => Settings::FEET_IN_MILE,
			'mapTileFeature' => Settings::getOption(Settings::OPTION_MAP_SHOW_TILES),
			//'mapTileURL' => _mb_strlen(Settings::getOption('CMMRM_map_tile_url')) > 2 ? Settings::getOption('CMMRM_map_tile_url') : 'https://tile.openstreetmap.org',
			'mapTileURL' => 'https://tile.openstreetmap.org',
			'mapTileURLs' => Settings::getOption('CMMRM_map_tiles'),
			'mapTileAPIKey' => Settings::getOption('CMMRM_map_tile_api_key') ? Settings::getOption('CMMRM_map_tile_api_key') : '',
			'openweathermapAppKey' => Settings::getOption(Settings::OPTION_OPENWEATHERMAP_API_KEY),
			'googleMapAppKey' => Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY),
			'mapType' => Settings::getOption(Settings::OPTION_MAP_TYPE_DEFAULT),
			'indexGeolocation' => Settings::getOption(Settings::OPTION_INDEX_GEOLOCATION_ENABLE) ? 1 : 0,
			'routeGeolocation' => Settings::getOption(Settings::OPTION_ROUTE_PAGE_GEOLOCATION_ENABLE) ? 1 : 0,
			'indexGeolocationFindmeButton' => Settings::getOption(Settings::OPTION_INDEX_GEOLOCATION_FINDME_BUTTON) ? 1 : 0,
			'routeGeolocationFindmeButton' => Settings::getOption(Settings::OPTION_ROUTE_PAGE_GEOLOCATION_FINDME_BUTTON) ? 1 : 0,
			'editorGeolocation' => Settings::getOption(Settings::OPTION_EDITOR_GEOLOCATION_ENABLE) ? 1 : 0,
			'editorTabsFlip' => Settings::getOption(Settings::OPTION_EDITOR_TABS_FLIP_ENABLE) ? 1 : 0,
			'editorCenterMapGeolocation' => Settings::getOption(Settings::OPTION_EDITOR_CENTER_MAP_TO_GEOLOCATION) ? 1: 0,
			'allowInfoWindowAutoOpen' => Settings::getOption(Settings::OPTION_EDITOR_ALLOW_INFO_WINDOW_AUTO_OPEN) ? 1 : 0,
			'allowGenerateWazeButton' => Settings::getOption(Settings::OPTION_EDITOR_ALLOW_GENERATE_WAZE_BUTTON) ? 1 : 0,
			'geolocationIcon' => Settings::getOption(Settings::OPTION_GEOLOCATION_ICON_URL) ?: static::url('asset/img/geolocation.png'),
			'scrollZoom' => Settings::getOption(Settings::OPTION_MAP_WHEEL_SCROLL_ZOOM),
			'editorWaypointsLimit' => 200,
			'indexMapMarkerClustering' => Settings::getOption(Settings::OPTION_INDEX_MAP_MARKER_CLUSTERING_ENABLE) ? 1 : 0,
			'indexMapInfoWindowMarkerClustering' => Settings::getOption(Settings::OPTION_INDEX_MAP_INFOWINDOW_MARKER_CLUSTERING_ENABLE) ? 1 : 0,
			'routeMapMarkerClustering' => Settings::getOption(Settings::OPTION_ROUTE_MAP_MARKER_CLUSTERING_ENABLE) ? 1 : 0,
			'routeMapInfoWindowMarkerClustering' => Settings::getOption(Settings::OPTION_ROUTE_MAP_INFOWINDOW_MARKER_CLUSTERING_ENABLE) ? 1 : 0,
			'routeMapLabelType' => Settings::getOption(Settings::OPTION_ROUTE_MAP_LABEL_TYPE),
			'routeMapLocationsInfoWindow' => Settings::getOption(Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_SHOW) ? 1 : 0,
			'routeMapLocationsHighlightList' => Settings::getOption(Settings::OPTION_ROUTE_PAGE_HIGHLIGHT_MARKER_LIST_ON_CLICK) ? 1 : 0,
			'mapTooltipBgColor' => Settings::getOption(Settings::OPTION_MAP_TOOLTIP_BGCOLOR),
			'zipFilterCountry' => Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_COUNTRY),
			'zipFilterGeolocation' => intval(Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_GEOLOCATION)),
			'indexMapDefaultLat' => Settings::getOption(Settings::OPTION_INDEX_DEFAULT_LAT),
			'indexMapDefaultLong' => Settings::getOption(Settings::OPTION_INDEX_DEFAULT_LONG),
			'indexMapDefaultZoom' => Settings::getOption(Settings::OPTION_INDEX_DEFAULT_ZOOM),
			'editorMapDefaultZoom' => Settings::getOption(Settings::OPTION_EDITOR_DEFAULT_ZOOM),
			'indexCenterMapGeolocation' => Settings::getOption(Settings::OPTION_INDEX_CENTER_MAP_TO_GEOLOCATION) ? 1: 0,
			//'showPathOutline' => Settings::getOption(Settings::OPTION_MAP_SHOW_PATH_OUTLINE) ? 1 : 0,
			'elevationGraphColorAsPathColor' => Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_COLOR_SAME_AS_TRAIL),
			'elevationGraphLabel' => Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_LABEL_ENABLE),
			'showGeolocationErrors' => Settings::getOption(Settings::OPTION_GEOLOCATION_SHOW_ERROR_MSG) ? 1 : 0,
			'geolocationIconBgcolor' => Settings::getOption(Settings::OPTION_GEOLOCATION_BG_COLOR) ? Settings::getOption(Settings::OPTION_GEOLOCATION_BG_COLOR) : '#1496DC',
			'geolocationIconWidth' => Settings::getOption(Settings::OPTION_GEOLOCATION_WIDTH) ? Settings::getOption(Settings::OPTION_GEOLOCATION_WIDTH) : 20,
			'geolocationIconHeight' => Settings::getOption(Settings::OPTION_GEOLOCATION_HEIGHT) ? Settings::getOption(Settings::OPTION_GEOLOCATION_HEIGHT) : 20,
			'directTravelModeForAll' => Settings::getOption(Settings::OPTION_SINGLE_ROUTE_TRAVEL_MODE_DIRECT_FOR_ALL) ? 1 : 0,
			'startingPointMarker' => Settings::getOption(Settings::OPTION_INDEX_MAP_STARTING_POINT_MARKER),
			'mapShowGooglePlaces' =>  Settings::getOption(Settings::OPTION_MAP_SHOW_PLACES) ? 1 : 0,
			'mapDefaultMarkerIconPath' =>  Settings::getOption(Settings::OPTION_MAP_DEFAULT_MARKER_ICON) ? Settings::getOption(Settings::OPTION_MAP_DEFAULT_MARKER_ICON) : '',
			'routeMapPolylineStrokeWeight' => Settings::getOption(Settings::OPTION_ROUTE_MAP_STROKE_WEIGHT),
			'editorMapPolylineStrokeWeight' => Settings::getOption(Settings::OPTION_EDITOR_MAP_STROKE_WEIGHT),
			'indexMapPolylineStrokeWeight' => Settings::getOption(Settings::OPTION_INDEX_MAP_STROKE_WEIGHT),
			'elevation_graph_alt_tooltip' => Labels::getLocalized('elevation_graph_alt_tooltip'),
			'elevation_graph_dist_from_start_tooltip' => Labels::getLocalized('elevation_graph_dist_from_start_tooltip'),
			'elevation_graph_per_request' => Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_PER_REQUEST),
			'cmloc_usertrack_user_path_enable' => $cmloc_usertrack_user_path_enable,
			'cmloc_usertrack_user_last_position_only' => intval(get_option('cmloc_usertrack_user_last_position_only', '0')),
			'cmloc_usertrack_user_path_color' => get_option('cmloc_usertrack_user_path_color', '#ff0000'),
			'roadmapNameText' => (Labels::getLocalized('roadmap_name'))?Labels::getLocalized('roadmap_name'):'Map',
			'roadmapAltText' => (Labels::getLocalized('roadmap_alt'))?Labels::getLocalized('roadmap_alt'):'Show street map',
			'satelliteNameText' => (Labels::getLocalized('satellite_name'))?Labels::getLocalized('satellite_name'):'Satellite',
			'satelliteAltText' => (Labels::getLocalized('satellite_alt'))?Labels::getLocalized('satellite_alt'):'Show satellite imagery',
			'hybridNameText' => (Labels::getLocalized('hybrid_name'))?Labels::getLocalized('hybrid_name'):'Hybrid',
			'hybridAltText' => (Labels::getLocalized('hybrid_alt'))?Labels::getLocalized('hybrid_alt'):'Show imagery with street names',
			'terrainNameText' => (Labels::getLocalized('terrain_name'))?Labels::getLocalized('terrain_name'):'Terrain',
			'terrainAltText' => (Labels::getLocalized('terrain_alt'))?Labels::getLocalized('terrain_alt'):'Show street map with terrain',
		)));
		
		wp_localize_script('cmmrm-route-rating', 'CMMRM_Route_Rating', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('route_rating'),
		));
		
		wp_localize_script('cmmrm-editor-images', 'CMMRM_Editor_Images_Settings', array(
			'icons' => Settings::getMarkerIconsUrls(),
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('cmmrm-editor-media'),
		));
		
		$userPosition = User::getLastGeolocation();
		wp_localize_script('cmmrm-frontend', 'CMMRM_Frontend_Settings', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'gallery' => Settings::getOption(Settings::OPTION_ROUTE_GALLERY_LIB),
			'geolocationNonce' => wp_create_nonce('cmmrm_geolocation'),
			'userLastPositionLat' => (is_null($userPosition[0]) ? '' : $userPosition[0]),
			'userLastPositionLong' => (is_null($userPosition[1]) ? '' : $userPosition[1]),
			'proximityOrderReloadMsg' => Labels::getLocalized('proximity_order_reload'),
		));
		
	}
	
	static function admin_menu() {
		parent::admin_menu();
		$name = static::getPluginName(true);
		$page = add_menu_page($name, $name, 'publish_posts', static::PREFIX, '', 'dashicons-location-alt', 1234);
	}
	
	static function getLicenseAdditionalNames() {
		return array(static::getPluginName(false), static::getPluginName(true));
	}
	
	static function activatePlugin() {
		parent::activatePlugin();
		if (App::isPro()) {
			SettingsController::fixPathesInSettings();
		}
	}

}
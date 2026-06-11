<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Labels;

class ProController extends Controller {

	protected static $filters = array(
		'cmmrm_options_config',
		'cmmrm_route_single_params_names',
		'cmmrm_route_index_params_names',
		'cmmrm_display_author' => array('args' => 3),
	);

	protected static $actions = array(
		'admin_menu' => array('priority' => 56),
		'cmmrm_labels_init' => array('priority' => 10),
		'cmmrm_route_editor_route_settings' => array('args' => 1),
		'cmmrm_route_after_save' => array('args' => 1),
		//'cmmrm_route_editor_after_map' => array('args' => 1, 'method' => 'displayElevationGraphWrapper'),
		'cmmrm_route_single_after_map' => array('args' => 2),
		'cmmrm_single_location_before_images' => array('args' => 2),
		'cmmrm_route_editor_before_map' => array('args' => 1),
		'cmmrm_route_editor_location_bottom' => array('args' => 1),
		'cmmrm_location_after_save' => array('args' => 2),
	);
	
	static function admin_menu() {
		//add_submenu_page(App::PREFIX, 'About ' . App::getPluginName(), 'About', 'manage_options', self::getMenuSlug('about'), array(get_called_class(), 'about'));
		//add_submenu_page(App::PREFIX, App::getPluginName() . ' User Guide', 'User Guide', 'manage_options', self::getMenuSlug('user-guide'), array(get_called_class(), 'userGuide'));
	}
	
	static function getMenuSlug($slug) {
		return App::PREFIX . '-' . $slug;
	}
	
	static function about() {
		echo self::loadView('backend/template', array(
			'title' => 'About ' . App::getPluginName(),
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('about', array(
				'iframeURL' => SettingsController::PAGE_ABOUT_URL,
			)) . SettingsController::getSectionExperts(),
		));
	}
	
	static function userGuide() {
		echo self::loadView('backend/template', array(
			'title' => App::getPluginName() . ' User Guide',
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('about', array(
				'iframeURL' => SettingsController::PAGE_USER_GUIDE_URL,
			)) . SettingsController::getSectionExperts(),
		));
	}

	static function cmmrm_labels_init() {
		Labels::loadLabelFile(App::path('asset/labels/pro.tsv'));
	}

	static function cmmrm_options_config($config) {
		return array_merge($config, array(
			
			Settings::OPTION_DONT_EMBED_GOOGLE_MAPS_JS_API => array(
				'type' => Settings::TYPE_RADIO,
				'category' => 'setup',
				'subcategory' => 'api',
				'default' => 0,
				'options' => array(
					'1' => 'No',		
					'0' => 'Yes (Loaded on all pages)',	
					'2' => 'Selected (Loaded on following pages only)',
				),
				'title' => 'Embedding Google Maps JS API',
				'desc' => 'This option can solve some conflicts with other plugins or themes that also includes the Google Maps API on each page, eg. Geodirectory.',
			),
			Settings::OPTION_EMBED_SELECTED => array(
				'type' => Settings::TYPE_TEXTAREA,
				'category' => 'setup',
				'subcategory' => 'api',
				'default' => site_url().'/maps-routes/',
				'title' => 'Embedding on the selected URLs',
				'desc' => 'Separate URL by new lines on which you want to embed Google Maps JS API. Note: It will work when above setting set "Selected". <br>Example:<br><code>'.site_url().'/maps-routes/</code>',
			),
			Settings::OPTION_OPENWEATHERMAP_API_KEY => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'setup',
				'subcategory' => 'api',
				'title' => 'OpenWeatherMap.org API Key',
				'desc' => 'Enter the OpenWeatherMap.org API key.<br /><a target="_blank" '. 'href="http://openweathermap.org/appid">Get the API key from here</a>.',
			),
			Settings::OPTION_GOOGLE_SEARCH_ENGINE_ID => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'setup',
				'subcategory' => 'api',
				'title' => 'Google Search Engine ID',
				'desc' => 'Enter the Google Search Engine ID. This is required for load google images feature.',
			),
			Settings::OPTION_SINGLE_ROUTE_PARAMS_ABOVE_MAP => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Show route params above the map',
				'desc' => 'If enabled the route params will be displayed directly above the map. If disabled it will be visible below the map by default.'
			),
			Settings::OPTION_SINGLE_ROUTE_TRAVEL_MODE_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Show travel mode switch',
				'desc' => 'If enabled user will be able to switch the travel modes directly on the map\'s page and recalculate the route.<br />'
							.'If disabled then user will be able to see only a route with a travel mode specified by the map author.',
			),
			Settings::OPTION_SINGLE_ROUTE_RATING_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Show rating on the route\'s page',
			),
			Settings::OPTION_SINGLE_ROUTE_DIRECTIONAL_ARROWS => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'map',
				'title' => 'Enable directional arrows by default for all routes',
				'desc' => 'If enabled the directional arrow will be added to the route\'s path polyline on the map.',
			),
			Settings::OPTION_ROUTE_MAP_LABEL_TYPE => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					Settings::LABEL_TYPE_SHOW_BELOW => 'Show always below the marker',
					Settings::LABEL_TYPE_TOOLTIP => 'Show tooltip on mouse over',
					//Settings::LABEL_TYPE_NONE => 'Do not show label',
				),
				'default' => Settings::LABEL_TYPE_SHOW_BELOW,
				'category' => 'route',
				'subcategory' => 'map',
				'title' => 'Marker label type',
				'desc' => 'Choose the label type with the marker\'s name on the route\'s map.',
			),
			Settings::OPTION_ROUTE_MAP_STROKE_WEIGHT => array(
				'type' => Settings::TYPE_INT,
				'default' => 3,
				'category' => 'route',
				'subcategory' => 'map',
				'title' => 'Trails stroke weight',
				'desc' => 'Choose the stroke\'s weight for the trail on the map.',
			),
			
			// General Appearance
			Settings::OPTION_DISABLE_DEFAULT_ICONS => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'general',
				'subcategory' => 'icons',
				'title' => 'Disappear default google maps icons',
				'desc' => 'If enabled then default google maps icon will disappear.',
			),
			Settings::OPTION_CUSTOM_ICONS => array(
				'type' => Settings::TYPE_TEXTAREA,
				'category' => 'general',
				'subcategory' => 'icons',
				'title' => 'Custom icons',
				'desc' => 'Enter the icon\'s URL addresses separated by new lines that will be available for a route\'s or location\'s marker icon.',
			),
			Settings::OPTION_ROUTE_PARAMS_VALUES_TOP => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'general',
				'subcategory' => 'appearance',
				'title' => 'Show values on top for each route param',
				'desc' => 'If enabled a value of a route param will be displayed on the top of the cell and the value\'s label will be shown below.<br />'
						. 'If disabled the label will shown first and value below.',
			),
			/*
 			Settings::OPTION_FANCY_STYLE_ENABLE => array(
 				'type' => Settings::TYPE_BOOL,
 				'default' => false,
 				'category' => 'general',
 				'subcategory' => 'appearance',
 				'title' => 'Enable fancy style',
 			),
			*/
			Settings::OPTION_FANCY_BGCOLOR => array(
				'type' => Settings::TYPE_COLOR,
				'default' => '#2688ca',
				'category' => 'general',
				'subcategory' => 'appearance',
				'title' => 'Background color for the fancy style',
				'desc' => 'Set a color that will be background for the white text.'
			),
			Settings::OPTION_FANCY_BORDER => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'general',
				'subcategory' => 'appearance',
				'title' => 'Divide route params cells in the fancy style',
				'desc' => 'If enabled the route params cells will be divided by an extra dashed line.'
			),
			Settings::OPTION_INDEX_LAYOUT => array(
				'type' => Settings::TYPE_RADIO,
				'default' => Settings::INDEX_LAYOUT_LIST,
				'options' => array(
					Settings::INDEX_LAYOUT_LIST => 'List',
					Settings::INDEX_LAYOUT_TILES => 'Tiles',
				),
				'category' => 'index',
				'subcategory' => 'layout',
				'title' => 'Index page layout',
			),
			Settings::OPTION_INDEX_TILE_WIDTH => array(
				'type' => Settings::TYPE_INT,
				'default' => 300,
				'category' => 'index',
				'subcategory' => 'layout',
				'title' => 'Index tile width [px]',
				'desc' => 'Width of the single tile with a route snippet on the index page (only when using the tiles layout).',
			),
			Settings::OPTION_INDEX_SHOW_ROUTES_LABELS => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'index',
				'subcategory' => 'layout',
				'title' => 'Show route name below the marker',
				'desc' => 'If enabled, show routes names/labels below the marker on map.',
			),
			Settings::OPTION_UNIT_TEMPERATURE => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(Settings::UNIT_TEMP_C => 'Celsius', Settings::UNIT_TEMP_F => 'Fahrenheit'),
				'default' => Settings::UNIT_TEMP_C,
				'category' => 'general',
				'subcategory' => 'units',
				'title' => 'Temperature units',
				'desc' => 'Used to display the weather.',
			),
			Settings::OPTION_MAP_LABEL_BGCOLOR => array(
				'type' => Settings::TYPE_COLOR,
				'default' => Settings::DEFAULT_MAP_LABEL_BGCOLOR,
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Map labels background',
				'desc' => 'Choose the background color of the location\'s labels displayed on the map.',
			),
			Settings::OPTION_MAP_TOOLTIP_BGCOLOR => array(
				'type' => Settings::TYPE_COLOR,
				'default' => '#FFFF66',
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Background for the tooltip',
			),
			Settings::OPTION_MAP_SHOW_PLACES => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 1,
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Show Google Places on the map',
				'desc' => 'If enabled, the places from Google Places API (such as restaurants, gas stations) will be shown on the map.',
			),
			Settings::OPTION_MAP_DEFAULT_MARKER_ICON => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Default marker icon file path',
				'desc' => 'Enter the default marker icon full path such as <code>'.site_url().'/wp-content/uploads/'.date('Y').'/'.date('m').'/xxx.png</code>. Leave blank to disable.',
			),
			Settings::OPTION_AUTHOR_LINKS_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'author',
				'title' => 'Enable author links',
				'desc' => 'If enabled the route\'s author name will be a link to the page where all his routes will be displayed.',
			),
			Settings::OPTION_AUTHOR_LINKS_NEW_WINDOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'author',
				'title' => 'Open author links in new window',
			),
			Settings::OPTION_AUTHOR_AVATAR_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'author',
				'title' => 'Show author avatar',
				'desc' => 'If enabled the route\'s author\'s avatar will be displayed on the single route\'s page.',
			),
			Settings::OPTION_AUTHOR_AVATAR_USERNAME_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'author',
				'title' => 'Show author name below the avatar',
				'desc' => 'If showing avatar is enabled you can also show the user name under the avatar image.',
			),

			Settings::OPTION_INDEX_ORDERBY => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					Settings::ORDERBY_TITLE => 'title',
					Settings::ORDERBY_CREATED => 'created date',
					Settings::ORDERBY_DISTANCE => 'distance',
					Settings::ORDERBY_VIEWS => 'views',
					Settings::ORDERBY_RATING => 'rating',
					Settings::ORDERBY_PROXIMITY => 'user location proximity',
				),
				'default' => Settings::DEFAULT_INDEX_ORDERBY,
				'category' => 'index',
				'subcategory' => 'pagination',
				'title' => 'Order routes by',
				'desc' => 'Notice: user location proximity uses web browser\'s geolocation API which requires using SSL (https).',
			),
			Settings::OPTION_INDEX_ORDER => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					Settings::ORDER_ASC => 'ascending',
					Settings::ORDER_DESC => 'descending',
				),
				'default' => Settings::DEFAULT_INDEX_ORDER,
				'category' => 'index',
				'subcategory' => 'pagination',
				'title' => 'Sorting order',
			),
			Settings::OPTION_INDEX_SEARCH_WHOLE_WORDS => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'index',
				'subcategory' => 'pagination',
				'title' => 'Search for whole words',
				'desc' => 'If enabled, the search engine will search for a whole words. If disabled also parts of the words will be matched.',
			),
			
			Settings::OPTION_INDEX_GEOLOCATION_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'index',
				'subcategory' => 'geolocation',
				'title' => 'Show user\'s position using browser\'s geolocation',
				'desc' => 'If enabled the user\'s marker will be displayed on the map using web browser\'s geolocation API.<br>'
					. '<strong>Notice: this works only when using HTTPS protocol (that is the web browser\'s security restricion).</strong>',
			),
			Settings::OPTION_INDEX_GEOLOCATION_FINDME_BUTTON => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'index',
				'subcategory' => 'geolocation',
				'title' => 'Enable find user location button on map',
				'desc' => 'If enabled then find user location button will show on the embed map.<br>'
					. '<strong>Notice: this works only when above option is enabled</strong>',
			),
			Settings::OPTION_INDEX_MAP_MARKER_CLUSTERING_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Enable marker clustering',
				'desc' => 'If enabled, the specific route\'s markers that are overlaping on the map will be clustered in a single icon with a number of locations. '
						. 'The single marker will be showed up after clicking on the cluster icon or zooming in the area.'
			),
			Settings::OPTION_INDEX_MAP_INFOWINDOW_MARKER_CLUSTERING_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Enable info window on marker clustering',
				'desc' => 'If enabled, the info window will open on click the marker cluster.'
			),
			Settings::OPTION_INDEX_MAP_STROKE_WEIGHT => array(
				'type' => Settings::TYPE_INT,
				'default' => 3,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Trails stroke weight',
				'desc' => 'Choose the stroke\'s weight for the trail on the map.',
			),
			Settings::OPTION_INDEX_SNIPPET_BGCOLOR_FROM_ROUTE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'index',
				'subcategory' => 'appearance',
				'title' => 'Apply the route\'s color to its snippet background',
				'desc' => 'If enabled, the specific route\'s color will be applied as the background of its snippet on the index page.'
			),
			/*
 			Settings::OPTION_INDEX_RATING_FILTER_SHOW => array(
 				'type' => Settings::TYPE_BOOL,
 				'default' => 0,
 				'category' => 'index',
 				'subcategory' => 'appearance',
 				'title' => 'Show rating filter',
 			),
			*/
			
			// Route Form
			Settings::OPTION_ROUTE_FORM_OVERRIDE => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'no' => 'No',
					'yes' => 'Yes',
				),
				'default' => 'no',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Template Override',
				'desc' => 'If enabled, then you can override form template in your theme. After enable you need to create <strong>CMMRM</strong> directory in your active theme and need to place <strong>editor.php</strong> file from plugin <strong>views/frontend/dashboard/editor.php</strong>',
			),
			Settings::OPTION_ROUTE_FORM_DESCRIPTION => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Optional',
					'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Description',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_STATUS => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Status',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_CATEGORY => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Category',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_CATEGORY_CREATE => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'1' => 'Yes',
					'0' => 'No',
					//'required' => 'Required',
				),
				'default' => '0',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Create Category',
				'desc' => 'If enabled, then user able to create new categories from frontend form.
				<br>Notice: This option will show if above category field set visible.',
			),
			Settings::OPTION_ROUTE_FORM_TAXONOMY => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Taxonomies',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_TAGS => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Tags',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_SETTINGS => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Route Settings',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_PATHCOLOR => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Path Color',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_CTABUTTON => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'none',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'CTA Button',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_IMAGES => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Images',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_GOOGLE_IMAGES => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'none',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Google Images',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_VIDEOS => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Videos',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_OSMTILES => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'none',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'OSM Tiles',
				'desc' => 'If enabled, then user able to add up to 6 OSM Tiles',
			),
			Settings::OPTION_ROUTE_FORM_IMPORT => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
					//'required' => 'Required',
				),
				'default' => 'optional',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Import KML, KMZ or GPX file',
				'desc' => '',
			),
			Settings::OPTION_EDITOR_INSTRUCTIONS_BUTTON_ENABLE => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'0' => 'None',
					'1' => 'Visible',
					//'required' => 'Required',
				),
				'default' => '1',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Instructions',
				'desc' => '',
			),
			Settings::OPTION_ROUTE_FORM_TERMS => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
				),
				'default' => 'none',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Terms & Conditions',
				'desc' => 'Terms & conditions checkbox only will be visible in to add route form and without accepting user can not be submit',
			),
			Settings::OPTION_ROUTE_FORM_LINKSHARING => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'none' => 'None',
					'optional' => 'Visible',
				),
				'default' => 'none',
				'category' => 'routeform',
				'subcategory' => 'routeform',
				'title' => 'Link Sharing',
				'desc' => '',
			),

			// Access Control
			Settings::OPTION_ACCESS_MAP_INDEX => array(
				'type' => Settings::TYPE_SELECT,
				'options' => array(App::namespaced('model\Settings'), 'getAccessOptions'),
				'default' => Settings::ACCESS_GUEST,
				'category' => 'access',
				'subcategory' => 'access',
				'title' => 'List maps',
				'desc' => 'Select who can access the maps index and also search or filter maps.',
			),
			Settings::OPTION_ACCESS_MAP_INDEX_CAP => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'access',
				'subcategory' => 'access',
				'default' => '',
				'title' => 'List maps custom capability',
				'desc' => 'Enter a capability name which will be required for user to show maps index, search or filter maps.<br />'
				. 'Read about <a href="https://codex.wordpress.org/Roles_and_Capabilities">Roles and Capabilities</a> on Wordpress Codex.',
			),
			Settings::OPTION_ACCESS_MAP_VIEW => array(
				'type' => Settings::TYPE_SELECT,
				'options' => array(App::namespaced('model\Settings'), 'getAccessOptions'),
				'default' => Settings::ACCESS_GUEST,
				'category' => 'access',
				'subcategory' => 'access',
				'title' => 'View map',
				'desc' => 'Select who can display the map page.',
			),
			Settings::OPTION_ACCESS_MAP_VIEW_CAP => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'access',
				'subcategory' => 'access',
				'default' => '',
				'title' => 'View map custom capability',
				'desc' => 'Enter a capability name which will be required for user to display a map page.<br />'
					. 'Read about <a href="https://codex.wordpress.org/Roles_and_Capabilities">Roles and Capabilities</a> on Wordpress Codex.',
			),
			Settings::OPTION_ACCESS_MAP_CREATE => array(
				'type' => Settings::TYPE_SELECT,
				'options' => array(App::namespaced('model\Settings'), 'getAccessOptionsWithoutGuest'),
				'default' => Settings::ACCESS_USER,
				'category' => 'access',
				'subcategory' => 'access',
				'title' => 'Create map',
				'desc' => 'Select who can create maps.',
			),
			Settings::OPTION_ACCESS_MAP_CREATE_CAP => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'access',
				'subcategory' => 'access',
				'default' => 'read',
				'title' => 'Create map capability',
				'desc' => 'Enter a capability name which will be required for user to create a map.<br />'
					. 'Read about <a href="https://codex.wordpress.org/Roles_and_Capabilities">Roles and Capabilities</a> on Wordpress Codex.',
			),
			Settings::OPTION_ACCESS_MAP_EDIT => array(
				'type' => Settings::TYPE_SELECT,
				'options' => array(App::namespaced('model\Settings'), 'getAccessOptionsWithoutGuest'),
				'default' => Settings::ACCESS_USER,
				'category' => 'access',
				'subcategory' => 'access',
				'title' => 'Update own map',
				'desc' => 'Select who can update own maps.',
			),
			Settings::OPTION_ACCESS_MAP_EDIT_CAP => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'access',
				'subcategory' => 'access',
				'default' => 'read',
				'title' => 'Update own map capability',
				'desc' => 'Enter a capability name which will be required for user to update own map.<br />'
				. 'Read about <a href="https://codex.wordpress.org/Roles_and_Capabilities">Roles and Capabilities</a> on Wordpress Codex.',
			),
			
			Settings::OPTION_LOCATION_ICON_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Enable custom icon per location',
				'desc' => 'Allow users to set icon for each location in the route.',
			),
			Settings::OPTION_ROUTE_PAGE_GEOLOCATION_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'geolocation',
				'title' => 'Show user\'s position using browser\'s geolocation',
				'desc' => 'If enabled the user\'s marker will be displayed on the map using web browser\'s geolocation API.<br>'
					. '<strong>Notice: this works only when using HTTPS protocol (that is the web browser\'s security restricion).</strong>',
			),
			Settings::OPTION_ROUTE_PAGE_GEOLOCATION_FINDME_BUTTON => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'geolocation',
				'title' => 'Enable find user location button on map',
				'desc' => 'If enabled then find user location button will show on the embed map.<br>'
					. '<strong>Notice: this works only when above option is enabled</strong>',
			),
			Settings::OPTION_ROUTE_PAGE_HIGHLIGHT_MARKER_LIST_ON_CLICK => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'marker',
				'title' => 'Enable highlight marker list on click',
				'desc' => 'If enabled, then highlight marker list after user clicks on the location\'s marker on the map.',
			),
			Settings::OPTION_ROUTE_MAP_MARKER_CLUSTERING_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'map',
				'title' => 'Enable marker clustering',
				'desc' => 'If enabled, the specific route\'s markers that are overlaping on the map will be clustered in a single icon with a number of locations. '
				. 'The single marker will be showed up after clicking on the cluster icon or zooming in the area.'
			),
			Settings::OPTION_ROUTE_MAP_INFOWINDOW_MARKER_CLUSTERING_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'map',
				'title' => 'Enable info window on marker clustering',
				'desc' => 'If enabled, the info window will open on click the marker cluster.'
			),
			/*
			Settings::OPTION_ROUTE_DOWNLOAD_FILE_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Show download GPX/KML buttons',
				'desc' => 'If enabled, then only logged in users will be able to download GPX or KML files with the route.'
			),
			*/
			Settings::OPTION_ROUTE_DOWNLOAD_FILE_ENABLE => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					'0' => 'Dont show',
					'1' => 'Show to everyone',
					'2' => 'Show to logged in users only',
				),
				'default' => '1',
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Show download GPX/KML buttons',
				'desc' => 'If enabled, then only logged in users will be able to download GPX or KML files with the route.'
			),
			Settings::OPTION_ROUTE_DOWNLOAD_LOGIN_PAGE_URL => array(
				'type' => Settings::TYPE_STRING,
				'default' => '#',
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Login/Register page URL',
				'desc' => 'If above option is selected "Show to logged in users only" then this URL used on download link for guest users.'
			),
			Settings::OPTION_ROUTE_FULLSCREEN_BTN_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'route',
				'subcategory' => 'map',
				'title' => 'Show fullscreen mode button',
			),
			Settings::OPTION_INDEX_FULLSCREEN_BTN_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Show fullscreen mode button',
			),

			Settings::OPTION_ROUTE_SHARE_LINK_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'route',
				'subcategory' => 'map',
				'title' => 'Show share link in toolbar above map',
			),
			Settings::OPTION_INDEX_SHARE_LINK_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Show share link in toolbar above map',
			),
			/*
 			Settings::OPTION_BACKEND_EDIT_ROUTE_ENABLE => array(
 				'type' => Settings::TYPE_BOOL,
 				'default' => true,
 				'category' => 'dashboard',
 				'subcategory' => 'other',
 				'title' => 'Allow to edit route in wp-admin dashboard',
 				'desc' => 'If enabled, then admin will be able to enter the wp-admin dashboard\'s page to edit '
 						. 'the route and e.g. access the Yoast SEO settings for a route page. '
 						. 'If disabled admin will be redirected to the route\'s front-end editor.',
 			),
			*/
			Settings::OPTION_EDITOR_GEOLOCATION_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'geolocation',
				'title' => 'Show user\'s position using browser\'s geolocation',
				'desc' => 'If enabled the user\'s marker will be displayed on the map using web browser\'s geolocation API.<br>'
					. '<strong>Notice: this works only when using HTTPS protocol (that is the web browser\'s security restricion).</strong>',
			),
			Settings::OPTION_IMPORT_CREATE_START_LOCATION => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'dashboard',
				'subcategory' => 'import',
				'title' => 'Create starting point marker for imported routes',
				'desc' => 'If enabled a marker for the route\'s first path coordinate will be created during import.',
			),
			Settings::OPTION_IMPORT_CREATE_END_LOCATION => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'dashboard',
				'subcategory' => 'import',
				'title' => 'Create ending point marker for imported routes',
				'desc' => 'If enabled a marker for the route\'s last  path coordinate will be created during import.',
			),
			Settings::OPTION_TRAVEL_MODE_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'map',
				'title' => 'Enable travel mode',
				'desc' => 'If enabled then user able to change travel mode while add/edit route.',
			),
			Settings::OPTION_TRAVEL_MODE_MULTIPLE_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'map',
				'title' => 'Enable travel mode multiple',
				'desc' => 'If enabled then user able to add multiple travel mode while add/edit route.',
			),
			Settings::OPTION_DEFAULT_TRAVEL_MODE => array(
				'type' => Settings::TYPE_RADIO,
				'default' => Route::DEFAULT_TRAVEL_MODE,
				'options' => array_combine(Route::$travelModes, array_map('strtolower', Route::$travelModes)),
				'category' => 'dashboard',
				'subcategory' => 'map',
				'title' => 'Default travel mode',
			),
			Settings::OPTION_EDITOR_TRAVEL_MODE_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'dashboard',
				'subcategory' => 'map',
				'title' => 'Show travel mode select in editor',
			),
			Settings::OPTION_EDITOR_MAP_STROKE_WEIGHT => array(
				'type' => Settings::TYPE_INT,
				'default' => 3,
				'category' => 'dashboard',
				'subcategory' => 'map',
				'title' => 'Trails stroke weight',
				'desc' => 'Choose the stroke\'s weight for the trail on the map.',
			),
			
			// Route Location Info Window
			Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'route',
				'subcategory' => 'infowindow',
				'title' => 'Show info window for the locations',
				'desc' => 'If enabled, the info window will be opened after user clicks on the location\'s marker on the map.',
			),
			Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_TEMPLATE => array(
				'type' => Settings::TYPE_RICH_TEXT,
				'default' => '<h5>[title]</h5>' . PHP_EOL . '<div>[description]</div>' . PHP_EOL . '<div>[address]</div>' . PHP_EOL . '<div>[latitude] [longitude]</div>' . PHP_EOL . '<div><a href="[linkurl]">[linktext]</a></div>',
				'category' => 'route',
				'subcategory' => 'infowindow',
				'title' => 'Template for the location\'s info window content',
				'desc' => 'You can use HTML and the following shortcodes: ' . implode(' ', array_keys(Location::getShortcodeTokensFuncMap())).'<br><br>[editlink] and [deletelink] will work only for admin privilege user and return output with anchor tag.',
			),
			Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_MAX_WIDTH => array(
				'type' => Settings::TYPE_INT,
				'default' => 500,
				'category' => 'route',
				'subcategory' => 'infowindow',
				'title' => 'Max width for the info window content',
			),
			Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_MAX_HEIGHT => array(
				'type' => Settings::TYPE_INT,
				'default' => 500,
				'category' => 'route',
				'subcategory' => 'infowindow',
				'title' => 'Max height for the info window content',
			),
			Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_IMAGE_MAX_WIDTH => array(
				'type' => Settings::TYPE_INT,
				'default' => 150,
				'category' => 'route',
				'subcategory' => 'infowindow',
				'title' => 'Max width for images inside the info window',
				'desc' => 'Set the max width of the images inserted into the info window content.',
			),
			
			Settings::OPTION_EDITOR_CENTER_MAP_TO_GEOLOCATION => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'dashboard',
				'subcategory' => 'geolocation',
				'title' => 'Center map to user\'s location',
				'desc' => 'If enabled, the editor map will be centered by default to the user\'s location. It uses the web browser\'s geolocation API, '
						. '<strong>it works only when using the https protocol</strong>.',
			),
			Settings::OPTION_INDEX_CENTER_MAP_TO_GEOLOCATION => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'index',
				'subcategory' => 'geolocation',
				'title' => 'Center map to user\'s location',
				'desc' => 'If enabled, the map will be centered by default to the user\'s location. It uses the web browser\'s geolocation API, '
				. '<strong>it works only when using the https protocol</strong>.',
			),
			/*
 			Settings::OPTION_MAP_SHOW_PATH_OUTLINE => array(
 				'type' => Settings::TYPE_BOOL,
 				'category' => 'general',
 				'subcategory' => 'map',
 				'title' => 'Show trail outline',
 				'desc' => 'If enabled, the white outline will be applied to the trail polyline.',
 				'default' => 1,
 			),
			*/
			Settings::OPTION_ELEVATION_GRAPH_COLOR_SAME_AS_TRAIL => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'general',
				'subcategory' => 'appearance',
				'title' => 'Elevation graph color the same as the route color',
				'default' => 1,
			),
			Settings::OPTION_GEOLOCATION_SHOW_ERROR_MSG => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'general',
				'subcategory' => 'geolocation',
				'title' => 'Show geolocation error messages',
				'desc' => 'If enabled, the geolocation errors will be shown to the user as a popup text messages for the debugging puprpose. '
				. 'Disable if you don\'t want to show the errors for users. You can still see the errors in the web browser\'s console.'
			),
			Settings::OPTION_GEOLOCATION_BG_COLOR => array(
				'type' => Settings::TYPE_COLOR,
				'default' => '#1496DC',
				'category' => 'general',
				'subcategory' => 'geolocation',
				'title' => 'User location icon background color',
				'desc' => 'Set a background color of user location icon.'
			),
			Settings::OPTION_GEOLOCATION_WIDTH => array(
				'type' => Settings::TYPE_INT,
				'default' => '20',
				'category' => 'general',
				'subcategory' => 'geolocation',
				'title' => 'User location icon width',
				'desc' => 'Set a width of user location icon.'
			),
			Settings::OPTION_GEOLOCATION_HEIGHT => array(
				'type' => Settings::TYPE_INT,
				'default' => '20',
				'category' => 'general',
				'subcategory' => 'geolocation',
				'title' => 'User location icon height',
				'desc' => 'Set a height of user location icon.'
			),
			/*
 			Settings::OPTION_GEOLOCATION_ICON_URL => array(
 				'type' => Settings::TYPE_STRING,
 				'category' => 'general',
 				'subcategory' => 'appearance',
 				'title' => 'Custom geolocation icon URL',
 				'desc' => 'Choose the icon URL for the user\'s position marker.',
 			),
			*/
			Settings::OPTION_EDITOR_ALLOW_INFO_WINDOW_AUTO_OPEN => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'infowindow',
				'title' => 'Choose locations to make the info window auto open',
				'desc' => 'If enabled, user will be able to choose the locations for that the info window will be open by default after entering the route\'s page.'
			),
			Settings::OPTION_EDITOR_ALLOW_GENERATE_WAZE_BUTTON => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'waze',
				'title' => 'Choose locations to generate waze button',
				'desc' => 'If enabled, user will be able to generate waze button.'
			),
			Settings::OPTION_EDITOR_VISIBLE_ROUTE_PARAMS => array(
				'type' => Settings::TYPE_MULTICHECKBOX,
				'default' => array_keys(Route::getRouteParamsNames()),
				'options' => Route::getRouteParamsNames(),
				'category' => 'dashboard',
				'subcategory' => 'params',
				'title' => 'Visible parameters',
			),
			Settings::OPTION_LOOK_AND_FEEL_CSS => array(
				'type' => Settings::TYPE_SELECT,
				'options' => array(
					'' => '-- none --',
					'2016-fancy' => 'Fancy style 2016',
					'2017-june' => 'June 2017',
				),
				'category' => 'general',
				'subcategory' => 'css',
				'title' => 'Look and feel CSS',
				'desc' => 'You can select additional look and feel CSS file to be loaded.',
			),

			// share
			Settings::OPTION_LINK_SHARE_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'general',
				'subcategory' => 'share',
				'title' => 'Enable share link box',
				'desc' => 'If enabled then route and category share link box will show on the route page.',
			),
			Settings::OPTION_LINK_SHARE_PAGE_ID => array(
				'type' => Settings::TYPE_SELECT,
				'options' => array(App::namespaced('model\Settings'), 'getAllPages'),
				'default' => '',
				'category' => 'general',
				'subcategory' => 'share',
				'title' => 'Share page',
				'desc' => 'Set the share page and it will use in category share link. This page should <code>[cm-route-index showonlybyparams="1"]</code> shortcode.',
			),
			
			// exclude
			Settings::OPTION_EXCLUDE_AVADA_BUILDER_CSS_CLASSES => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'exclude',
				'title' => 'Avada Builder CSS Classes',
				'desc' => 'This setting will help with Avada Builder plugin if content showing duplicate. You can add CSS classes with vertical bar ( | ) such as <code>fusion-builder-row-1|fusion-builder-row-2|fusion-builder-row-3</code> etc.',
			),
			
			// other
			Settings::OPTION_THE_EVENTS_CALENDAR_INTEGRATION_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'general',
				'subcategory' => 'other',
				'title' => 'Enable "The Events Calendar" integration',
				'desc' => '',
			),

			Settings::OPTION_INDEX_TEXT_TOP_SHOW_CATEGORY_DESC => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'index',
				'subcategory' => 'appearance',
				'title' => 'Show category\'s description on the top',
				'desc' => 'If enabled, the category\'s description will be displayed on the top (if set). '
						. 'Else the default "text on top" will be displayed.'
			),
			Settings::OPTION_SINGLE_ROUTE_PROCESS_SHORTCODES_IN_DESC => array(
				'type' => Settings::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Process shortcodes in descriptions',
				'desc' => 'If enabled, the shortcodes entered by a user in the route\'s or location\'s description will be processed.'
			),
			Settings::OPTION_SINGLE_ROUTE_SHORTCODES_WHITELIST => array(
				'type' => Settings::TYPE_CSV_LINE,
				'default' => '',
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Shortcodes whitelist',
				'desc' => 'You can enter the comma-separated optional shortcodes list that will be processed by the plugin, '
						. '(only names, without brackets) e.g.: <kbd>profile,avatar</kbd>'
			),
			Settings::OPTION_SINGLE_ROUTE_EMBED_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Show "Embed" feature',
				'desc' => 'If enabled, the "Embed" button will be display next to the route\'s map and users will be able to copy the iframe HTML code '
					. 'in order to embed the route on an external website.'
			),
			Settings::OPTION_ROUTE_GALLERY_LIB => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					Settings::GALLERY_BUILDIN => 'Default build-in library',
					Settings::GALLERY_UNITEGALLERY => 'Unite Gallery',
				),
				'default' => Settings::GALLERY_BUILDIN,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Gallery library',
				'desc' => 'Choose the library for the gallery support to show the images and videos.'
			),
			
			Settings::OPTION_INDEX_MAP_LOCATIONS_INTEGRATION => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Integrate with CM Map Locations plugin',
				'desc' => 'If enabled, the locations and user track locations from CM Map Locations plugin will be added to the index page map.'
			),
			Settings::OPTION_INDEX_MAP_LOCATIONS_MERGE_CATEGORIES => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Merge CM Map Locations plugin categories',
				//'desc' => 'If enabled, the locations categories from CM Map Locations plugin will be merged in to the index page category filter. This option will work if you will enabled above option.'
				'desc' => 'If enabled, the locations will comes as per selected route category if route and location category slug is same. This option will work if you will enabled above option.'
			),
			Settings::OPTION_INDEX_MAP_FULL_ROUTE_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Enable full route on map',
				'desc' => 'If enabled, then full route (polyline) will be show on index map.'
			),
			Settings::OPTION_INDEX_MAP_STARTING_POINT_MARKER => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					Settings::STARTING_POINT_PATH => 'First path waypoint',
					Settings::STARTING_POINT_LOCATION => 'First location',
					Settings::STARTING_POINT_LOCATIONS => 'All locations',
				),
				'default' => Settings::STARTING_POINT_PATH,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Starting point for a route',
				'desc' => 'Choose which point will be shown as the starting point for the route displayed in the index page map.'
			),
			Settings::OPTION_INDEX_MAP_MARKER_CLICK => array(
				'type' => Settings::TYPE_RADIO,
				'default' => Settings::DEFAULT_INDEX_MAP_MARKER_CLICK,
				'options' => array(
					Settings::ACTION_CLICK_NONE => 'None',
					Settings::ACTION_CLICK_REDIRECT => 'Open the route\'s page',
					Settings::ACTION_CLICK_TOOLTIP => 'Show tooltip with information about the location',
					Settings::ACTION_CLICK_CUSTOM_REDIRECT => 'Open the linked location URL',
				),
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Clicking on the map marker will',
			),
			Settings::OPTION_ROUTE_MAP_LOCATIONS_INTEGRATION => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'route',
				'subcategory' => 'map',
				'title' => 'Integrate with CM Map Locations plugin',
				'desc' => 'If enabled, the locations from CM Map Locations plugin will be added to the single route\'s page map.'
			),
			Settings::OPTION_SINGLE_ROUTE_LOCATIONS_NUMBERS_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 1,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Show location\'s numbers',
				'desc' => 'If enabled, the locations will be numbered.'
			),
			Settings::OPTION_SINGLE_ROUTE_TRAVEL_MODE_DIRECT_FOR_ALL => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Use direct travel mode for all modes',
				'desc' => 'If enabled, the no matter which travel mode will be selected, the direct travel mode will be applied.'
			),
			
			Settings::OPTION_ELEVATION_GRAPH_SLOPES_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 1,
				'category' => 'route',
				'subcategory' => 'slopes',
				'title' => 'Enable showing slopes',
				'desc' => 'If enabled the slopes will be displayed on the elevation graph.',
			),
			Settings::OPTION_ELEVATION_GRAPH_SLOPES_LABEL_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 1,
				'category' => 'route',
				'subcategory' => 'slopes',
				'title' => 'Enable showing slopes label',
				'desc' => 'If enabled the slopes label will be displayed on the elevation graph.',
			),
			Settings::OPTION_ELEVATION_GRAPH_SLOPES_DOWNWARD_BGCOLOR => array(
				'type' => Settings::TYPE_COLOR,
				'default' => '#2688ca',
				'category' => 'route',
				'subcategory' => 'slopes',
				'title' => 'Downward slope color',
				'desc' => 'Set a background color of downward slope.'
			),
			Settings::OPTION_ELEVATION_GRAPH_SLOPES_UPWARD_BGCOLOR => array(
				'type' => Settings::TYPE_COLOR,
				'default' => '#2688ca',
				'category' => 'route',
				'subcategory' => 'slopes',
				'title' => 'Upward slope color',
				'desc' => 'Set a background color of upward slope.'
			),
			Settings::OPTION_ELEVATION_GRAPH_SLOPES_MIN_VALUE => array(
				'type' => Settings::TYPE_INT,
				'default' => 10,
				'category' => 'route',
				'subcategory' => 'slopes',
				'title' => 'Minimum slope to show [%]',
				'desc' => 'The minimum slope % to show.',
			),
			Settings::OPTION_ELEVATION_GRAPH_SLOPES_MIN_WIDTH => array(
				'type' => Settings::TYPE_INT,
				'default' => 15,
				'category' => 'route',
				'subcategory' => 'slopes',
				'title' => 'Minimum slope width [px]',
				'desc' => 'The minimum slope width (in pixels) that is allowed to display.',
			),
			Settings::OPTION_ELEVATION_GRAPH_SLOPES_ALLOW_USER_EDIT => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 1,
				'category' => 'route',
				'subcategory' => 'slopes',
				'title' => 'Allow users to change the slope settings per route',
				'desc' => 'If enabled the users will be able to enable/disable the slopes per route and change the slope parameters (min slope and min width).',
			),
			
		));
	}
	
	static function cmmrm_route_editor_route_settings(Route $route) {
		echo static::loadView('frontend/dashboard/editor-route-settings-pro', compact('route'));
		if (Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_ENABLE)
				AND Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_ALLOW_USER_EDIT)) {
			echo static::loadView('frontend/dashboard/editor-route-slopes', compact('route'));
		}
	}
	
	static function cmmrm_route_after_save(Route $route) {
		$route->setWeatherPerLocation(!empty($_POST['show-weather-per-location']));
		
		if(is_array($_POST['travel-mode']))
		{
			$_POST['travel-mode'] = implode(",", $_POST['travel-mode']);
		}

		if (!empty($_POST['travel-mode'])) {
			$route->setTravelMode($_POST['travel-mode']);
		}
		if (!empty($_POST['path-color'])) {
			$route->setPathColor($_POST['path-color']);
		}
		if (!empty($_POST['cta-button-text'])) {
			$route->setCtaButtonText($_POST['cta-button-text']);
		}
		if (!empty($_POST['cta-button-url'])) {
			$route->setCtaButtonUrl($_POST['cta-button-url']);
		}
		if (!empty($_POST['slope-downward-color'])) {
			$route->setSlopeDownwardColor($_POST['slope-downward-color']);
		}
		if (!empty($_POST['slope-upward-color'])) {
			$route->setSlopeUpwardColor($_POST['slope-upward-color']);
		}
		if (!empty($_POST['directional-arrows'])) {
			$route->setShowDirectionalArrows(true);
		} else {
			$route->setShowDirectionalArrows(false);
		}
		if (!empty($_POST['show-locations-section'])) {
			$route->setShowLocationsSection(true);
		} else {
			$route->setShowLocationsSection(false);
		}
		if (!empty($_POST['show-path-outline'])) {
			$route->setShowPathOutline(true);
		} else {
			$route->setShowPathOutline(false);
		}
		if (!empty($_POST['hide-on-index'])) {
			$route->sethideOnIndex(true);
		} else {
			$route->sethideOnIndex(false);
		}
		if (!empty($_POST['slopes-enable'])) {
			$route->setSlopesShowingEnabled(true);
		} else {
			$route->setSlopesShowingEnabled(false);
		}
		if (!empty($_POST['slope-min-value'])) {
			$route->setSlopeMinValue(intval($_POST['slope-min-value']));
		}
		if (!empty($_POST['slope-min-width'])) {
			$route->setSlopeMinWidth(intval($_POST['slope-min-width']));
		}
	}

	static function cmmrm_route_single_params_names($params) {
		$params['weather'] = 'Weather';
		$params['elevation_graph'] = 'Elevation graph';
		$params['publish_date'] = 'Publish date';
		$params['author'] = 'Author';
		//$params['difficulty'] = 'Route difficulty';
		//$params['route_type'] = 'Route type';
		return $params;
	}
	
	static function cmmrm_route_index_params_names($params) {
		$params['publish_date'] = 'Publish date';
		$params['author'] = 'Author';
		$params['rating'] = 'Rating';
		return $params;
	}
	
	static function cmmrm_single_location_before_images(Location $location, $i) {
		if ($i == 1 OR $location->getRoute()->showWeatherPerLocation()) {
			if(Settings::getOption(Settings::OPTION_OPENWEATHERMAP_API_KEY)) {
				printf('<a target="_blank" class="cmmrm-weather" title="%s"></a>', esc_attr(Labels::getLocalized('check_weather')));
			}
		}
	}
	
	static function displayElevationGraphWrapper(Route $route) {
		echo RouteController::loadFrontendView('elevation-graph', compact('route'));
	}
	
	static function cmmrm_route_single_after_map(Route $route, $atts) {
		if (!isset($atts['graph']) OR $atts['graph'] == 1) {
			self::displayElevationGraphWrapper($route);
		}
	}
	
	static function cmmrm_route_editor_before_map(Route $route) {
		$route_form_pathcolor = Settings::getOption(Settings::OPTION_ROUTE_FORM_PATHCOLOR);
		if ($route_form_pathcolor != 'none') {
			echo DashboardController::loadFrontendView('editor-path-color', compact('route'));
		}
		$route_form_ctabutton = Settings::getOption(Settings::OPTION_ROUTE_FORM_CTABUTTON);
		if ($route_form_ctabutton != 'none') {
			echo DashboardController::loadFrontendView('editor-cta-button', compact('route'));
		}
	}
	
	static function cmmrm_display_author($text, $userId, $context) {
		if (Settings::getOption(Settings::OPTION_AUTHOR_LINKS_ENABLE)) {
			if ($user = get_userdata($userId)) {
				$url = FrontendController::getUrl('', array(FrontendController::PARAM_FILTER_AUTHOR => $user->user_nicename));
				$extra = '';
				if (Settings::getOption(Settings::OPTION_AUTHOR_LINKS_NEW_WINDOW)) {
					$extra .= ' target="_blank"';
				}
				$text = sprintf('<a href="%s"%s>%s</a>', esc_attr($url), $extra, $text);
			}
		}
		return $text;
	}
	
	static function cmmrm_route_editor_location_bottom($route) {
		echo DashboardController::loadFrontendView('editor-location-pro', compact('route'));
	}
	
	static function cmmrm_location_after_save(Location $location, $i) {
		if (Settings::getOption(Settings::OPTION_EDITOR_ALLOW_INFO_WINDOW_AUTO_OPEN)) {
			$location->setInfoWindowOpen(!empty($_POST['locations']['info_window_open'][$i]));
		}
		if (Settings::getOption(Settings::OPTION_EDITOR_ALLOW_GENERATE_WAZE_BUTTON)) {
			$location->setGenerateWazeButton(!empty($_POST['locations']['generate_waze_button'][$i]));
		}
	}

}
?>
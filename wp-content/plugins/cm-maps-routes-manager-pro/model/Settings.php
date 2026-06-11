<?php
namespace com\cminds\mapsroutesmanager\model;

use com\cminds\mapsroutesmanager\helper\GoogleMapsIcons;
use com\cminds\mapsroutesmanager\shortcode\RouteSnippetShortcode;
use com\cminds\mapsroutesmanager\App;

class Settings extends SettingsAbstract {
	
	public static $categories = array(
		'setup' => 'Setup',
		'general' => 'General',
		'index' => 'Index Page',
		'route' => 'Route Page',
		'dashboard' => 'Dashboard',
		'moderation' => 'Moderation',
		'access' => 'Access Control',
		'routeform' => 'Route Form',
		'labels' => 'Labels',
	);
	
	public static $subcategories = array(
		'setup' => array(
			'api' => 'API Keys',
			'navigation' => 'Navigation',
		),
		'general' => array(
			'template' => 'Template',
			'appearance' => 'Appearance',
			'map' => 'Map',
			'author' => 'Author',
			'units' => 'Units',
			'geolocation' => 'Geolocation',
			'icons' => 'Icons',
			'css' => 'Custom CSS',
			'share' => 'Link Sharing',
			'exclude' => 'Exclude',
			'other' => 'Other',
		),
		'index' => array(
			'layout' => 'Layout',
			'pagination' => 'Pagination, order, search',
			'filters' => 'Filters',
			'fields' => 'Visible fields',
			'appearance' => 'Appearance',
			'map' => 'Map',
			'images' => 'Images',
			'zip' => 'ZIP code searching',
			'geolocation' => 'Geolocation',
		),
		'route' => array(
			'template' => 'Template',
			'order' => 'Routes order',
			'fields' => 'Visible fields',
			'appearance' => 'Appearance',
			'map' => 'Map',
			'slopes' => 'Slopes',
			'infowindow' => 'Info window',
			'geolocation' => 'Geolocation',
			'marker' => 'Marker',
		),
		'dashboard' => array(
			'default' => 'Add route default settings',
			'general' => 'General',
			'editor' => 'Editor',
			'map' => 'Map',
			'params' => 'Route parameters',
			'import' => 'Importing',
			'infowindow' => 'Info window',
			'waze' => 'Waze',
			'geolocation' => 'Geolocation',
			'other' => 'Other',		
		),
		'moderation' => array(
			'moderation' => 'Moderation',
			'notifications' => 'Notifications',
			'email' => 'Email settings',
		),
		'access' => array(
			'access' => '',
		),
		'routeform' => array(
			'routeform' => 'Form Settings',
		),
		'labels' => array(
			'other' => 'Other',
		),
	);
	
	const ACTION_CLICK_NONE = 'none';
	const ACTION_CLICK_REDIRECT = 'redirect';
	const ACTION_CLICK_CUSTOM_REDIRECT = 'custom_redirect';
	const ACTION_CLICK_TOOLTIP = 'tooltip';
	const DEFAULT_INDEX_MAP_MARKER_CLICK = self::ACTION_CLICK_REDIRECT;

	const OPTION_MAP_SHOW_TILES = 'cmmrm_map_show_tiles';
	const OPTION_MAP_THEMES = 'cmmrm_map_themes';
	const OPTION_PERMALINK_PREFIX = 'cmmrm_permalink_prefix';
	const OPTION_INDEX_PAGE_ENABLE = 'cmmrm_index_page_enable';
	const OPTION_DASHBOARD_PAGE_ENABLE = 'cmmrm_dashboard_page_enable';
	const OPTION_SINGLE_PAGE_ENABLE = 'cmmrm_single_page_enable';
	const OPTION_REWRITE_WITH_FRONT = 'cmmrm_rewrite_with_front';
	const OPTION_EXCLUDE_FROM_SEARCH = 'cmmrm_exclude_from_search';
	const OPTION_LANG_RIGHT_TO_LEFT_ENABLE = 'cmmrm_lang_right_to_left_enable';
	const OPTION_PAGE_ROUTE_INDEX = 'cmmrm_special_page_route_index';
	const OPTION_PAGE_ROUTE_SINGLE = 'cmmrm_special_page_route_single';
	const OPTION_PAGE_DASHBOARD_INDEX = 'cmmrm_special_page_dashboard_index';
	const OPTION_PAGE_DASHBOARD_EDITOR = 'cmmrm_special_page_dashboard_editor';
	const OPTION_PAGE_TEMPLATE = 'cmmrm_page_template';
	const OPTION_PAGE_TEMPLATE_OTHER = 'cmmrm_page_template_other';
	const OPTION_PAGINATION_LIMIT = 'cmmrm_pagination_limit';
	const OPTION_INDEX_LAYOUT = 'cmmrm_index_layout';
	const OPTION_INDEX_TILE_WIDTH = 'cmmrm_index_tile_width';
	const OPTION_INDEX_SHOW_ROUTES_LABELS = 'cmmrm_index_show_routes_labels';
	const OPTION_INDEX_ORDERBY = 'cmmrm_index_orderby';
	const OPTION_INDEX_ORDER = 'cmmrm_index_order';
	const OPTION_INDEX_TEXT_TOP = 'cmmrm_index_text_top';
	const OPTION_INDEX_TEXT_TOP_SHOW_CATEGORY_DESC = 'cmmrm_index_text_top_show_category_desc';
	const OPTION_INDEX_MAP_SHOW = 'cmmrm_index_map_show';
	const OPTION_INDEX_MAP_LOCATIONS_INTEGRATION = 'cmmrm_index_map_locations_integration';
	const OPTION_INDEX_MAP_LOCATIONS_MERGE_CATEGORIES = 'cmmrm_index_map_locations_merge_categories';
	const OPTION_UNIT_LENGTH = 'cmmrm_unit_length';
	const OPTION_UNIT_LENGTH_DEC = 'cmmrm_unit_length_dec';
	const OPTION_UNIT_TEMPERATURE = 'cmmrm_unit_temperature';
	const OPTION_GEOLOCATION_SHOW_ERROR_MSG = 'cmmrm_geolocation_show_error_msg';

	const OPTION_GEOLOCATION_BG_COLOR = 'cmmrm_geolocation_bg_color';
	const OPTION_GEOLOCATION_WIDTH = 'cmmrm_geolocation_width';
	const OPTION_GEOLOCATION_HEIGHT = 'cmmrm_geolocation_height';
	
	const OPTION_INDEX_ROUTE_PARAMS = 'cmmrm_index_route_params';
	const OPTION_INDEX_GEOLOCATION_ENABLE = 'cmmrm_index_geolocation_enable';
	const OPTION_INDEX_GEOLOCATION_FINDME_BUTTON = 'cmmrm_index_geolocation_findme_button';
	const OPTION_ROUTE_PAGE_GEOLOCATION_FINDME_BUTTON = 'cmmrm_route_page_geolocation_findme_button';
	const OPTION_ROUTE_PAGE_HIGHLIGHT_MARKER_LIST_ON_CLICK = 'cmmrm_route_page_highlight_marker_list_on_click';
	const OPTION_INDEX_RATING_FILTER_SHOW = 'cmmrm_index_rating_filter_show';
	const OPTION_INDEX_MAP_MARKER_CLUSTERING_ENABLE = 'cmmrm_index_map_marker_clustering_enable';
	const OPTION_INDEX_MAP_INFOWINDOW_MARKER_CLUSTERING_ENABLE = 'cmmrm_index_map_infowindow_marker_clustering_enable';
	const OPTION_INDEX_SNIPPET_BGCOLOR_FROM_ROUTE = 'cmmrm_index_snippet_bgcolor_from_route';
	const OPTION_INDEX_SEARCH_WHOLE_WORDS = 'cmmrm_index_search_whole_words';
	const OPTION_INDEX_MAP_STROKE_WEIGHT = 'cmmrm_index_map_stroke_weight';
	const OPTION_ROUTE_MAP_LOCATIONS_INTEGRATION = 'cmmrm_route_map_locations_integration';
	const OPTION_ROUTE_MAP_MARKER_CLUSTERING_ENABLE = 'cmmrm_route_map_marker_clustering_enable';
	const OPTION_ROUTE_MAP_INFOWINDOW_MARKER_CLUSTERING_ENABLE = 'cmmrm_route_map_infowindow_marker_clustering_enable';
	const OPTION_ROUTE_MAP_LABEL_TYPE = 'cmmrm_route_map_label_type';
	const OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_SHOW = 'cmmrm_route_map_location_info_window_show';
	const OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_TEMPLATE = 'cmmrm_route_map_location_info_window_template';
	const OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_IMAGE_MAX_WIDTH = 'cmmrm_route_map_loc_infowindow_img_max_w';
	const OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_MAX_WIDTH = 'cmmrm_route_map_loc_infowindow_max_w';
	const OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_MAX_HEIGHT = 'cmmrm_route_map_loc_infowindow_max_h';
	const OPTION_ROUTE_MAP_STROKE_WEIGHT = 'cmmrm_route_map_stroke_weight';
	const OPTION_ROUTE_PAGE_GEOLOCATION_ENABLE = 'cmmrm_route_page_geolocation_enable';
	const OPTION_ROUTE_DOWNLOAD_FILE_ENABLE = 'cmmrm_route_download_file_enable';
	const OPTION_ROUTE_DOWNLOAD_LOGIN_PAGE_URL = 'cmmrm_route_download_login_page_url';
	const OPTION_ROUTE_FULLSCREEN_BTN_SHOW = 'cmmrm_route_fullscreen_btn_show';
	const OPTION_ROUTE_GALLERY_LIB = 'cmmrm_route_gallery_lib';
	const OPTION_INDEX_FULLSCREEN_BTN_SHOW = 'cmmrm_index_fullscreen_btn_show';
	const OPTION_EDITOR_GEOLOCATION_ENABLE = 'cmmrm_editor_geolocation_enable';
	const OPTION_SINGLE_ROUTE_PARAMS = 'cmmrm_single_route_params';
	const OPTION_SINGLE_ROUTE_RATING_SHOW = 'cmmrm_single_route_rating_show';
	const OPTION_SINGLE_ROUTE_MAP_SCRIPT_IN_FOOTER = 'cmmrm_single_route_map_script_in_footer';
	const OPTION_SINGLE_ROUTE_PROCESS_SHORTCODES_IN_DESC = 'cmmrm_single_route_process_shortcodes_in_desc';
	const OPTION_SINGLE_ROUTE_SHORTCODES_WHITELIST = 'cmmrm_single_route_shortcodes_whitelist';
	const OPTION_SINGLE_ROUTE_EMBED_ENABLE = 'cmmrm_single_route_embed_enable';
	const OPTION_SINGLE_ROUTE_LOCATIONS_NUMBERS_SHOW = 'cmmrm_single_route_locations_numbers_show';
	const OPTION_SINGLE_ROUTE_TRAVEL_MODE_DIRECT_FOR_ALL = 'cmmrm_single_route_travel_mode_direct_for_all';
	const OPTION_ELEVATION_GRAPH_SLOPES_ENABLE = 'cmmrm_elevation_graph_slopes_enable';
	const OPTION_ELEVATION_GRAPH_SLOPES_LABEL_ENABLE = 'cmmrm_elevation_graph_slopes_label_enable';
	const OPTION_ELEVATION_GRAPH_SLOPES_DOWNWARD_BGCOLOR = 'cmmrm_elevation_graph_slopes_downward_bgcolor';
	const OPTION_ELEVATION_GRAPH_SLOPES_UPWARD_BGCOLOR = 'cmmrm_elevation_graph_slopes_upward_bgcolor';
	const OPTION_ELEVATION_GRAPH_SLOPES_MIN_VALUE = 'cmmrm_elevation_graph_slopes_min_value';
	const OPTION_ELEVATION_GRAPH_SLOPES_MIN_WIDTH = 'cmmrm_elevation_graph_slopes_min_width';
	const OPTION_ELEVATION_GRAPH_SLOPES_ALLOW_USER_EDIT = 'cmmrm_elevation_graph_slopes_allow_user_edit';
	const OPTION_INDEX_MAP_STARTING_POINT_MARKER = 'cmmrm_index_map_starting_point_marker';
	const OPTION_INDEX_MAP_FULL_ROUTE_ENABLE = 'cmmrm_index_map_full_route_enable';
	const OPTION_INDEX_MAP_MARKER_CLICK = 'cmmrm_index_map_marker_click';
	const OPTION_GOOGLE_MAPS_APP_KEY = 'cmmrm_google_maps_app_key';
	const OPTION_GOOGLE_MAPS_LANG = 'cmmrm_google_maps_lang';
	const OPTION_GOOGLE_ELEVATION_API_KEY = 'cmmrm_google_elevation_api_key';
	const OPTION_DONT_EMBED_GOOGLE_MAPS_JS_API = 'cmmrm_dont_embed_google_maps_js_api';
	const OPTION_EMBED_SELECTED = 'cmmrm_embed_selected';
	const OPTION_OPENWEATHERMAP_API_KEY = 'cmmrm_openweathermap_api_key';
	const OPTION_GOOGLE_SEARCH_ENGINE_ID = 'cmmrm_google_search_engine_id';
	const OPTION_COMMENTS_ENABLE = 'cmmrm_comments_enable';
	const OPTION_PAGETITLE_ENABLE = 'cmmrm_pagetitle_enable';
	const OPTION_INDEX_MENU_ENABLE = 'cmmrm_index_menu_enable';
	const OPTION_LOCATION_ICON_ENABLE = 'cmmrm_location_icon_enable';
	const OPTION_MAP_LABEL_BGCOLOR = 'cmmrm_map_label_bgcolor';
	const OPTION_MAP_TOOLTIP_BGCOLOR = 'cmmrm_map_tooltip_bgcolor';
	const OPTION_AUTHOR_LINKS_ENABLE = 'cmmrm_author_links_enable';
	const OPTION_AUTHOR_LINKS_NEW_WINDOW = 'cmmrm_author_links_new_window';
	const OPTION_AUTHOR_AVATAR_SHOW = 'cmmrm_author_avatar_show';
	const OPTION_AUTHOR_AVATAR_USERNAME_SHOW = 'cmmrm_author_avatar_username_show';
	const OPTION_MAP_TYPE_DEFAULT = 'cmmrm_map_type_default';
	const OPTION_MAP_SCROLL_ZOOM_ENABLE = 'cmmrm_map_scroll_zoom_enable';
	const OPTION_MAP_WHEEL_SCROLL_ZOOM = 'cmmrm_map_wheel_scroll_zoom';
	//const OPTION_MAP_SHOW_PATH_OUTLINE = 'cmmrm_map_show_path_outline';
	const OPTION_SINGLE_ROUTE_DIRECTIONAL_ARROWS = 'cmmrm_single_route_directional_arrows';
	const OPTION_SINGLE_ROUTE_PARAMS_ABOVE_MAP = 'cmmrm_single_route_params_above_map';
	const OPTION_ROUTE_PARAMS_VALUES_TOP = 'cmmrm_route_params_values_top';
	const OPTION_FANCY_STYLE_ENABLE = 'cmmrm_fancy_style_enable';
	const OPTION_SINGLE_ROUTE_TRAVEL_MODE_SHOW = 'cmmrm_single_route_travel_mode_show';
	const OPTION_ELEVATION_GRAPH_HEIGHT = 'cmmrm_elevation_graph_height';
	const OPTION_ELEVATION_GRAPH_PER_REQUEST = 'cmmrm_elevation_graph_per_request';
	const OPTION_FANCY_BGCOLOR = 'cmmrm_fancy_bgcolor';
	const OPTION_FANCY_BORDER = 'cmmrm_fancy_border';
	const OPTION_GEOLOCATION_ICON_URL = 'cmmrm_geolocation_icon_url';
	
	const OPTION_INDEX_ZIP_RADIUS_FILTER_ENABLE = 'cmmrm_index_zip_radius_filter_enable';
	const OPTION_INDEX_ZIP_RADIUS_COUNTRY = 'cmmrm_index_zip_radius_country';
	const OPTION_INDEX_ZIP_RADIUS_MIN = 'cmmrm_index_zip_radius_min';
	const OPTION_INDEX_ZIP_RADIUS_MAX = 'cmmrm_index_zip_radius_max';
	const OPTION_INDEX_ZIP_RADIUS_STEP = 'cmmrm_index_zip_radius_step';
	const OPTION_INDEX_ZIP_RADIUS_DEFAULT = 'cmmrm_index_zip_radius_default';
	const OPTION_INDEX_ZIP_RADIUS_GEOLOCATION = 'cmmrm_index_zip_radius_geolocation';
	const OPTION_INDEX_DEFAULT_LAT = 'cmmrm_index_default_lat';
	const OPTION_INDEX_DEFAULT_LONG = 'cmmrm_index_default_long';
	const OPTION_INDEX_DEFAULT_ZOOM = 'cmmrm_index_default_zoom';
	const OPTION_INDEX_MAP_SCRIPT_IN_FOOTER = 'cmmrm_index_map_script_in_footer';
	
	const OPTION_ROUTE_MODERATION_ENABLE = 'cmmrm_route_moderation_enable';
	const OPTION_ROUTE_MODERATION_EMAILS = 'cmmrm_route_moderation_emails';
	const OPTION_MODERATOR_EMAIL_SUBJECT = 'cmmrm_moderator_email_subject';
	const OPTION_MODERATOR_EMAIL_CONTENT = 'cmmrm_moderator_email_content';
	const OPTION_ROUTE_ACCEPTED_USER_EMAIL_SUBJECT = 'cmmrm_route_accepted_user_email_subject';
	const OPTION_ROUTE_ACCEPTED_USER_EMAIL_CONTENT = 'cmmrm_route_accepted_user_email_content';
	const OPTION_EMAIL_TO_HEADER_WHEN_USING_BCC = 'cmmrm_email_to_header_when_using_bcc';
	
	const OPTION_IMPORT_CREATE_END_LOCATION = 'cmmrm_import_create_end_location';
	const OPTION_IMPORT_CREATE_START_LOCATION = 'cmmrm_import_create_start_location';
	
	const OPTION_ACCESS_MAP_CREATE_CAP = 'cmmrm_access_map_create_cap';
	const OPTION_ACCESS_MAP_CREATE = 'cmmrm_access_map_create';
	const OPTION_ACCESS_MAP_EDIT_CAP = 'cmmrm_access_map_edit_cap';
	const OPTION_ACCESS_MAP_EDIT = 'cmmrm_access_map_edit';
	const OPTION_ACCESS_MAP_INDEX_CAP = 'cmmrm_access_map_index_cap';
	const OPTION_ACCESS_MAP_INDEX = 'cmmrm_access_map_index';
	const OPTION_ACCESS_MAP_VIEW_CAP = 'cmmrm_access_map_view_cap';
	const OPTION_ACCESS_MAP_VIEW = 'cmmrm_access_map_view';

	const OPTION_ROUTE_FORM_OVERRIDE = 'cmmrm_route_form_override';
	const OPTION_ROUTE_FORM_DESCRIPTION = 'cmmrm_route_form_description';
	const OPTION_ROUTE_FORM_STATUS = 'cmmrm_route_form_status';
	const OPTION_ROUTE_FORM_CATEGORY = 'cmmrm_route_form_category';
	const OPTION_ROUTE_FORM_CATEGORY_CREATE = 'cmmrm_route_form_category_create';
	const OPTION_ROUTE_FORM_TAXONOMY = 'cmmrm_route_form_taxonomy';
	const OPTION_ROUTE_FORM_TAGS = 'cmmrm_route_form_tags';
	const OPTION_ROUTE_FORM_SETTINGS = 'cmmrm_route_form_settings';
	const OPTION_ROUTE_FORM_PATHCOLOR = 'cmmrm_route_form_pathcolor';
	const OPTION_ROUTE_FORM_CTABUTTON = 'cmmrm_route_form_ctabutton';
	const OPTION_ROUTE_FORM_IMAGES = 'cmmrm_route_form_images';
	const OPTION_ROUTE_FORM_GOOGLE_IMAGES = 'cmmrm_route_form_google_images';
	const OPTION_ROUTE_FORM_VIDEOS = 'cmmrm_route_form_videos';
	const OPTION_ROUTE_FORM_OSMTILES = 'cmmrm_route_form_osmtiles';
	const OPTION_ROUTE_FORM_IMPORT = 'cmmrm_route_form_import';
	const OPTION_EDITOR_INSTRUCTIONS_BUTTON_ENABLE = 'cmmrm_editor_instructions_button_enable';
	const OPTION_ROUTE_FORM_TERMS = 'cmmrm_route_form_terms';
	const OPTION_ROUTE_FORM_LINKSHARING = 'cmmrm_route_form_linksharing';
	
	const OPTION_ELEVATION_GRAPH_COLOR_SAME_AS_TRAIL = 'cmmrm_elevation_graph_color_same_as_trail';
	const OPTION_ROUTE_DEFAULT_IMAGE = 'cmmrm_route_default_image';
	const OPTION_ROUTE_INDEX_FEATURED_IMAGE = '_cmmrm_route_index_featured_image';
	const OPTION_EDITOR_DEFAULT_LAT = 'cmmrm_editor_default_lat';
	const OPTION_EDITOR_DEFAULT_LONG = 'cmmrm_editor_default_long';
	const OPTION_EDITOR_DEFAULT_ZOOM = 'cmmrm_editor_default_zoom';
	const OPTION_EDITOR_CENTER_MAP_TO_GEOLOCATION = 'cmmrm_editor_center_map_to_geolocation';
	const OPTION_INDEX_CENTER_MAP_TO_GEOLOCATION = 'cmmrm_index_center_map_to_geolocation';
	const OPTION_DEFAULT_TRAVEL_MODE = 'cmmrm_default_travel_mode';
	const OPTION_EDITOR_TRAVEL_MODE_SHOW = 'cmmrm_editor_travel_mode_show';
	const OPTION_TRAVEL_MODE_SHOW = 'cmmrm_travel_mode_show';
	const OPTION_TRAVEL_MODE_MULTIPLE_SHOW = 'cmmrm_travel_mode_multiple_show';
	const OPTION_EDITOR_RICH_TEXT_ENABLE = 'cmmrm_editor_rich_text_enable';

	const OPTION_EDITOR_TABS_FLIP_ENABLE = 'cmmrm_editor_tabs_flip_enable';
	
	const OPTION_EDITOR_ALLOW_INFO_WINDOW_AUTO_OPEN = 'cmmrm_editor_allow_info_window_auto_open';
	const OPTION_EDITOR_ALLOW_GENERATE_WAZE_BUTTON = 'cmmrm_editor_allow_generate_waze_button';
	const OPTION_EDITOR_VISIBLE_ROUTE_PARAMS = 'cmmrm_editor_visible_route_params';
	const OPTION_EDITOR_MAP_STROKE_WEIGHT = 'cmmrm_editor_map_stroke_weight';
	const OPTION_LABEL_EDITOR_INSTRUCTION = 'cmmrm_label_editor_instruction';
	const OPTION_CUSTOM_CSS = 'cmmrm_custom_css';
	const OPTION_CUSTOM_ICONS = 'cmmrm_custom_icons';
	const OPTION_DISABLE_DEFAULT_ICONS = 'cmmrm_disable_default_icons';
	const OPTION_LOOK_AND_FEEL_CSS = 'cmmrm_look_and_feel_css';
	
	const OPTION_LINK_SHARE_ENABLE = 'cmmrm_link_share_enable';
	const OPTION_LINK_SHARE_PAGE_ID = 'cmmrm_link_share_page_id';

	const OPTION_EXCLUDE_AVADA_BUILDER_CSS_CLASSES = 'cmmrm_exclude_avada_builder_css_classes';

	const OPTION_THE_EVENTS_CALENDAR_INTEGRATION_ENABLE = 'cmmrm_the_events_calendar_integration_enable';

	const OPTION_BACKEND_EDIT_ROUTE_ENABLE = 'cmmrm_backend_edit_route_enable';
	const OPTION_MAP_SHOW_PLACES = 'cmmrm_map_show_places';
	const OPTION_MAP_DEFAULT_MARKER_ICON = 'cmmrm_map_default_marker_icon';

	const OPTION_ROUTE_SHARE_LINK_SHOW = 'cmmrm_route_share_link_show';
	const OPTION_INDEX_SHARE_LINK_SHOW = 'cmmrm_index_share_link_show';
	
	const ACCESS_GUEST = 'cmmrm_guest';
	const ACCESS_USER = 'cmmrm_user';
	const ACCESS_CAPABILITY = 'cmmrm_capability';
	
	const ORDERBY_TITLE = 'post_title';
	const ORDERBY_CREATED = 'post_date';
	const ORDERBY_DISTANCE = 'distance';
	const ORDERBY_VIEWS = 'views';
	const ORDERBY_RATING = 'rating';
	const ORDERBY_PROXIMITY = 'proximity';
	
	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';
	
	const UNIT_METERS = 'meters';
	const UNIT_FEET = 'feet';
	const UNIT_TEMP_F = 'temp_f';
	const UNIT_TEMP_C = 'temp_c';
	const FEET_TO_METER = 0.3048;
	const FEET_IN_MILE = 5280;
	
	const DEFAULT_MAP_LABEL_BGCOLOR = '#FFFF00';
	const DEFAULT_INDEX_ORDERBY = self::ORDERBY_CREATED;
	const DEFAULT_INDEX_ORDER = self::ORDER_DESC;
	
	const MAP_TYPE_ROADMAP = 'roadmap';
	const MAP_TYPE_SATELLITE = 'satellite';
	const MAP_TYPE_HYBRID = 'hybrid';
	const MAP_TYPE_TERRAIN = 'terrain';
	const MAP_TYPE_OSM = 'OSM';

	const INDEX_LAYOUT_LIST = 'list';
	const INDEX_LAYOUT_TILES = 'tiles';
	
	const TRAVEL_MODE_WALKING = 'WALKING';
	const TRAVEL_MODE_DRIVING = 'DRIVING';
	const TRAVEL_MODE_BICYCLING = 'BICYCLING';
	const TRAVEL_MODE_DIRECT = 'DIRECT';
	
	const LABEL_TYPE_SHOW_BELOW = 'below';
	const LABEL_TYPE_TOOLTIP = 'tooltip';
	const LABEL_TYPE_NONE = 'none';
	
	const WHEEL_SCROLL_ZOOM_DISABLE = 'disable';
	const WHEEL_SCROLL_ZOOM_ENABLE = 'enable';
	const WHEEL_SCROLL_ZOOM_AFTER_CLICK = 'after_click';
	
	const GALLERY_BUILDIN = 'buildin';
	const GALLERY_UNITEGALLERY = 'unitegallery';
	
	const STARTING_POINT_PATH = 'path';
	const STARTING_POINT_LOCATION = 'location';
	const STARTING_POINT_LOCATIONS = 'locations';

	const OPTION_ROUTE_SETTING_DEFAULT_USE_ONLY_METERS = 'cmmrm_route_setting_default_use_only_meters';
	const OPTION_ROUTE_SETTING_DEFAULT_SHOW_WEATHER_PER_EACH_LOCATION = 'cmmrm_route_setting_default_show_weather_per_each_location';
	const OPTION_ROUTE_SETTING_DEFAULT_SHOW_DIRECTIONAL_ARROWS = 'cmmrm_route_setting_default_show_directional_arrows';
	const OPTION_ROUTE_SETTING_DEFAULT_SHOW_LOCATIONS_SECTION = 'cmmrm_route_setting_default_show_locations_section';
	const OPTION_ROUTE_SETTING_DEFAULT_SHOW_PATH_OUTLINE = 'cmmrm_route_setting_default_show_path_outline';
	const OPTION_ROUTE_SETTING_DEFAULT_HIDE_ON_INDEX = 'cmmrm_route_setting_default_hide_on_index';
	const OPTION_ROUTE_SETTING_DEFAULT_SHOW_PATH_OUTLINE_BGCOLOR = 'cmmrm_route_setting_default_show_path_outline_bgcolor';
	
	public static function getOptionsConfig() {
		
		return apply_filters('cmmrm_options_config', array(
			
			// General Navigation
			self::OPTION_PERMALINK_PREFIX => array(
				'type' => self::TYPE_STRING,
				'default' => 'maps-routes',
				'category' => 'setup',
				'subcategory' => 'navigation',
				'title' => 'Permalink prefix',
				'desc' => 'Enter the prefix of the index and routes permalinks, eg. <kbd>maps-routes</kbd> '
							. 'will give permalinks such as: <kbd>/<strong>maps-routes</strong>/paris-trip</kbd>.',
			),
			self::OPTION_REWRITE_WITH_FRONT => array(
				'type' => self::TYPE_BOOL,
				'default' => true,
				'category' => 'setup',
				'subcategory' => 'navigation',
				'title' => 'Respect permalink structure (enable with_front)',
				'desc' => 'Enable this option in order to respect the <a href="'. esc_attr(admin_url('options-permalink.php'))
						. '">Custom permalink structure</a> from the Wordpress settings. It will enable with_front=true option for the registered post type '
						. 'e.g. having permalink structure <kbd><strong>/blog/</strong>%postname%/</kbd> will generate links like: '
						. '<kbd><strong>/blog/</strong>maps-routes/single-route/</kbd>.'
						. '<br>Disable this option if you want to make the routes permalinks more general (without using the WP permalink structure) '
						. 'like: <kbd>/maps-routes/</kbd>.',
			),
			self::OPTION_EXCLUDE_FROM_SEARCH => array(
				'type' => self::TYPE_BOOL,
				'default' => true,
				'category' => 'setup',
				'subcategory' => 'navigation',
				'title' => 'Exclude from search',
				'desc' => 'Enable this option if you want to exclude the routes from the blog search results and use only the search box displayed '
						. 'on the routes index page.<br>Disable this option if you want to display the routes in the blog search results.',
			),
			self::OPTION_LANG_RIGHT_TO_LEFT_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'setup',
				'subcategory' => 'navigation',
				'title' => 'Enable right to left language',
				'desc' => 'If enabled, then plugin will add new css class "cmmrm-rtl" in body tag.',
			),

			/*
 			self::OPTION_PAGE_ROUTE_INDEX => array(
 				'type' => self::TYPE_SELECT,
 				'options' => static::getPagesOptions(),
 				'category' => 'setup',
 				'subcategory' => 'navigation',
 				'title' => 'Routes index page',
 			),
			*/

			self::OPTION_PAGE_ROUTE_SINGLE => array(
				'type' => self::TYPE_SELECT,
				'options' => static::getPagesOptions(),
				'category' => 'route',
				'subcategory' => 'template',
				'title' => 'Custom page for a single route',
				'desc' => 'You can setup a custom Wordpress page for the single route page and use shortcodes to display the route\'s elements in the order you wish.',
			),

			/*
 			self::OPTION_PAGE_DASHBOARD_INDEX => array(
 				'type' => self::TYPE_SELECT,
 				'options' => static::getPagesOptions(),
 				'category' => 'setup',
 				'subcategory' => 'navigation',
 				'title' => 'User dashboard routes list page',
 			),
 			self::OPTION_PAGE_DASHBOARD_EDITOR => array(
 				'type' => self::TYPE_SELECT,
 				'options' => static::getPagesOptions(),
 				'category' => 'setup',
 				'subcategory' => 'navigation',
 				'title' => 'User dashboard route editor page',
 			),
			*/
			
			// General Template
			self::OPTION_PAGE_TEMPLATE => array(
				'type' => self::TYPE_SELECT,
				'options' => array(__CLASS__, 'getPageTemplatesOptions'),
				'default' => 'page.php',
				'category' => 'general',
				'subcategory' => 'template',
				'title' => 'Page template',
				'desc' => 'Choose the page template of the current theme to use on the index page, route\'s pages and the front-end user\'s dashboard pages.',
			),
			self::OPTION_PAGE_TEMPLATE_OTHER => array(
				'type' => self::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'template',
				'title' => 'Other page template file',
				'desc' => 'Enter the other file path of the page template you want to use (relative to your theme directory) if your template is not on the list above. '
					. 'This option have priority over the selected page template. Leave blank to reset.',
			),
			self::OPTION_INDEX_PAGE_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'general',
				'subcategory' => 'template',
				'title' => 'Enable default route index page',
				'desc' => 'If enabled, then will work default route index page',
			),
			self::OPTION_DASHBOARD_PAGE_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'general',
				'subcategory' => 'template',
				'title' => 'Enable default user dashboard page',
				'desc' => 'If enabled, then will work default user dashboard page',
			),
			self::OPTION_SINGLE_PAGE_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'general',
				'subcategory' => 'template',
				'title' => 'Enable default route\'s page',
				'desc' => 'If enabled, then will work default route\'s page',
			),
			
			// General Appearance
			self::OPTION_MAP_TYPE_DEFAULT => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					self::MAP_TYPE_ROADMAP => 'Roadmap',
					self::MAP_TYPE_SATELLITE => 'Pure Satellite Without Labels',		
					self::MAP_TYPE_HYBRID => 'Hybrid Satellite With Labels',
					self::MAP_TYPE_TERRAIN => 'Terrain',
					self::MAP_TYPE_OSM => 'Default OSM tile',
				),
				'default' => self::MAP_TYPE_ROADMAP,
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Default map view',
				'desc' => 'You can set custom OSM tile under "Insert OSM tiles" setting with "Set as Default OSM Tile" checkbox'
			),
			/*
			'CMMRM_map_tile_url' => array(
				'type' => self::TYPE_STRING,
				'default' => 'https://tile.openstreetmap.org',
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Insert map tile URL',
				'desc' => 'Enter the URL of a tile, such as <a href="https://israelhiking.osm.org.il/English/Tiles" target="_blank">https://israelhiking.osm.org.il/English/Tiles</a> to show custom layers when the OSM tile view is enabled. If left blank, the plugin will apply tiles from <u>OpenStreetMap</u>.<br><a href="https://creativeminds.helpscoutdocs.com/article/2573-cm-maps-route-manager-cmmrm-how-to-add-tiles-layers-to-map" target="_blank">Learn more in the documentation</a>',
			),
			*/
			self::OPTION_MAP_SHOW_TILES => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 1,
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Enable OSM tiles button',
				'desc' => 'If enabled, then OSM tiles button will be shown on the map and user able to on/off from front end.',
			),
			'CMMRM_map_tiles' => array(
				'type' => Settings::TYPE_CUSTOM,
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Insert OSM tiles',
				'desc' => 'Enter the Label and URL of a tiles. You can add up to 6 tiles. <a href="https://creativeminds.helpscoutdocs.com/article/2572-cm-map-locations-cmml-how-to-add-tiles-layers-to-maps" target="_blank">Learn more in the documentation</a><br><br>e.g. of tiles URL<br><a href="https://israelhiking.osm.org.il/English/Tiles/" target="_blank">https://israelhiking.osm.org.il/English/Tiles/</a><br><a href="https://israelhiking.osm.org.il/English/mtbTiles/" target="_blank">https://israelhiking.osm.org.il/English/mtbTiles/</a><br><br><strong>Note:</strong> "Set as Default OSM Tile" use for "Default OSM tile" default map view.',
				'content' => array(get_called_class(), 'getMapTilesSettingsField'),
			),
			'CMMRM_map_tile_api_key' => array(
				'type' => self::TYPE_STRING,
				'default' => '',
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Insert map tiles API key',
				'desc' => 'Enter the map tiles API key if required, such as if you are using thunderforest map tiles then you should add API key. You can get key from here: <a href="https://www.thunderforest.com/pricing/" target="_blank">https://www.thunderforest.com/pricing/</a>',
			),
			self::OPTION_MAP_THEMES => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					'standard'=> 'Standard',
					'silver' => 'Silver',
					'retro' => 'Retro',
					'dark' => 'Dark',
					'night' => 'Night',
					'aubergine' => 'Aubergine',
				),
				'default' => 'standard',
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Map theme',
			),
			self::OPTION_MAP_WHEEL_SCROLL_ZOOM => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					static::WHEEL_SCROLL_ZOOM_DISABLE => 'disable',
					static::WHEEL_SCROLL_ZOOM_ENABLE => 'enable',
					static::WHEEL_SCROLL_ZOOM_AFTER_CLICK => 'after clicked the map',
				),
				'default' => static::WHEEL_SCROLL_ZOOM_ENABLE,
				'category' => 'general',
				'subcategory' => 'map',
				'title' => 'Zoom map when using mouse wheel',
				'desc' => 'If enabled then scrolling by mouse when on the map will zoom out or zoom in.',
			),
			self::OPTION_CUSTOM_CSS => array(
				'type' => self::TYPE_TEXTAREA,
				'category' => 'general',
				'subcategory' => 'css',
				'title' => 'Custom CSS',
				'desc' => 'You can enter a custom CSS which will be embeded on every page that contains a CM Map Routes interface.',
			),
			
			// General Units
			self::OPTION_UNIT_LENGTH => array(
				'type' => self::TYPE_RADIO,
				'options' => array(self::UNIT_METERS => 'Meter/Kilometer', self::UNIT_FEET => 'Feet/Mile'),
				'default' => self::UNIT_METERS,
				'category' => 'general',
				'subcategory' => 'units',
				'title' => 'Length units',
				'desc' => 'Used to display the trail\'s length or the location\'s altitude. Short forms are:<br><strong>Meter = m</strong>, <strong>Kilometer = km</strong><br><strong>Feet = ft</strong>, <strong>Mile = mi</strong>',
			),
			self::OPTION_MAP_SCROLL_ZOOM_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'general',
				'title' => 'Zoom map when using mouse wheel',
				'desc' => 'If enabled then scrolling by mouse when on the map will zoom out or zoom in.',
			),
			self::OPTION_UNIT_LENGTH_DEC => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					'0'=> 'No decimal',
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
				'default' => '0',
				'category' => 'general',
				'subcategory' => 'units',
				'title' => 'Length decimal',
				'desc' => 'Sets the number of decimal digits for trail\'s distance.',
			),

			// Index Pagination
			self::OPTION_PAGINATION_LIMIT => array(
				'type' => self::TYPE_INT,
				'default' => 10,
				'category' => 'index',
				'subcategory' => 'pagination',
				'title' => 'Routes per page',
				'desc' => 'Limit the routes visible on each page.',
			),
			
			// Index Appearance
			self::OPTION_INDEX_TEXT_TOP => array(
				'type' => (App::isPro() ? self::TYPE_RICH_TEXT : self::TYPE_TEXTAREA),
				'category' => 'index',
				'subcategory' => 'appearance',
				'title' => 'Text on top',
				'desc' => 'You can enter text which will be displayed on the top of the index page, below the page title.',
			),
			
			// Index Map
			self::OPTION_INDEX_MAP_SHOW => array(
				'type' => Settings::TYPE_BOOL,
				'default' => true,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Show map with all routes on the index page',
			),
			self::OPTION_INDEX_DEFAULT_LAT => array(
				'type' => self::TYPE_STRING,
				'default' => '51',
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Index map default latitude',
				'desc' => 'Enter the latitude of the default view on the index page map if no routes to show.',
			),
			self::OPTION_INDEX_DEFAULT_LONG => array(
				'type' => self::TYPE_STRING,
				'default' => 0,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Index map default longitude',
				'desc' => 'Enter the longitude of the default view on the index page map if no routes to show.',
			),
			self::OPTION_INDEX_DEFAULT_ZOOM => array(
				'type' => self::TYPE_SELECT,
				'options' => array_combine(range(0, 18), range(0, 18)),
				'default' => 5,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Index map default zoom',
				'desc' => 'Greater zoom number = closer.'
			),
			self::OPTION_INDEX_MAP_SCRIPT_IN_FOOTER => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'index',
				'subcategory' => 'map',
				'title' => 'Embed the map script in footer',
				'desc' => 'Enable this option to solve some JavaScript issues.',
			),
			
			// Index Fields
			self::OPTION_INDEX_ROUTE_PARAMS => array(
				'type' => self::TYPE_MULTICHECKBOX,
				'options' => self::getRouteIndexPageParamsNames(),
				'default' => array_keys(self::getRouteIndexPageParamsNames()),
				'category' => 'index',
				'subcategory' => 'fields',
				'title' => 'Information visible on the index page',
				'desc' => 'Check which route parameters will be displayed on the index page on the route\'s snippet.',
			),
			
			// Index Images
			Settings::OPTION_ROUTE_INDEX_FEATURED_IMAGE => array(
				'type' => Settings::TYPE_RADIO,
				'default' => RouteSnippetShortcode::FEATURED_IMAGE,
				'options' => array(RouteSnippetShortcode::FEATURED_IMAGE => 'First route image', RouteSnippetShortcode::FEATURED_MAP => 'Map thumbnail'),
				'category' => 'index',
				'subcategory' => 'images',
				'title' => 'Route featured image',
				'desc' => 'Choose what kind of featured image to display on the index page.',
			),
			Settings::OPTION_ROUTE_DEFAULT_IMAGE => array(
				'type' => Settings::TYPE_STRING,
				'default' => App::url('asset/img/world-map-small.png'),
				'category' => 'index',
				'subcategory' => 'images',
				'title' => 'Route default image',
				'desc' => 'Enter the URL of the default featured image of the route map.',
			),
			
			// Route Fields
			self::OPTION_SINGLE_ROUTE_PARAMS => array(
				'type' => self::TYPE_MULTICHECKBOX,
				'options' => self::getRouteSinglePageParamsNames(),
				'default' => array_keys(self::getRouteSinglePageParamsNames()),
				'category' => 'route',
				'subcategory' => 'fields',
				'title' => 'Information visible on the route\'s page',
				'desc' => 'Check which route parameters will be displayed on the single route\'s page.',
			),
			
			// Route Map
			self::OPTION_SINGLE_ROUTE_MAP_SCRIPT_IN_FOOTER => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'map',
				'title' => 'Embed the map script in footer',
				'desc' => 'Enable this option to solve some JavaScript issues.',
			),
			
			// Route Appearance
			self::OPTION_PAGETITLE_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Enable route title',
			),
			self::OPTION_INDEX_MENU_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Enable menu',
			),
			self::OPTION_COMMENTS_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Enable WP comments',
			),
			self::OPTION_ELEVATION_GRAPH_HEIGHT => array(
				'type' => self::TYPE_INT,
				'default' => 225,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Height of the elevation graph [pixels]',
			),
			self::OPTION_ELEVATION_GRAPH_PER_REQUEST => array(
				'type' => self::TYPE_INT,
				'default' => 450,
				'category' => 'route',
				'subcategory' => 'appearance',
				'title' => 'Elevation graph per request',
				'desc' => 'Set minimum 2 points per request and maximum 512 points per request. 450 points per request is default',
			),
			
			// Dashboard Map
			self::OPTION_EDITOR_DEFAULT_LAT => array(
				'type' => self::TYPE_STRING,
				'default' => '51',
				'category' => 'dashboard',
				'subcategory' => 'map',
				'title' => 'Editor default location\'s latitude',
				'desc' => 'Enter the latitude of the default location shown in the editor.',
			),
			self::OPTION_EDITOR_DEFAULT_LONG => array(
				'type' => self::TYPE_STRING,
				'default' => 0,
				'category' => 'dashboard',
				'subcategory' => 'map',
				'title' => 'Editor default location\'s longitude',
				'desc' => 'Enter the longitude of the default location shown in the editor.',
			),
			self::OPTION_EDITOR_DEFAULT_ZOOM => array(
				'type' => self::TYPE_SELECT,
				'options' => array_combine(range(0, 18), range(0, 18)),
				'default' => 5,
				'category' => 'dashboard',
				'subcategory' => 'map',
				'title' => 'Editor default zoom',
				'desc' => 'Greater zoom number = closer'
			),

			self::OPTION_ROUTE_SETTING_DEFAULT_USE_ONLY_METERS => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'default',
				'title' => Labels::getLocalized('dashboard_use_minor_length_units', 'Use only meters/feet instead of kilometers/miles'),
				'desc' => 'If enabled, then same setting will be auto select while create new route',
			),
			self::OPTION_ROUTE_SETTING_DEFAULT_SHOW_WEATHER_PER_EACH_LOCATION => array(
				'type' => self::TYPE_BOOL,
				'default' => true,
				'category' => 'dashboard',
				'subcategory' => 'default',
				'title' => Labels::getLocalized('dashboard_show_weather_per_location', 'Show weather per each location (disabled to show it once per trail)'),
				'desc' => 'If enabled, then same setting will be auto select while create new route',
			),
			self::OPTION_ROUTE_SETTING_DEFAULT_SHOW_DIRECTIONAL_ARROWS => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'default',
				'title' => Labels::getLocalized('dashboard_show_directional_arrows', 'Show directional arrows for the trail path'),
				'desc' => 'If enabled, then same setting will be auto select while create new route',
			),
			self::OPTION_ROUTE_SETTING_DEFAULT_SHOW_LOCATIONS_SECTION => array(
				'type' => self::TYPE_BOOL,
				'default' => true,
				'category' => 'dashboard',
				'subcategory' => 'default',
				'title' => Labels::getLocalized('dashboard_show_locations_section', 'Show locations section under the map on the single route page'),
				'desc' => 'If enabled, then same setting will be auto select while create new route',
			),
			self::OPTION_ROUTE_SETTING_DEFAULT_SHOW_PATH_OUTLINE => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'default',
				'title' => Labels::getLocalized('dashboard_show_path_outline', 'Show path outline'),
				'desc' => 'If enabled, then same setting will be auto select while create new route',
			),
			self::OPTION_ROUTE_SETTING_DEFAULT_HIDE_ON_INDEX => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'default',
				'title' => Labels::getLocalized('dashboard_hide_on_index', 'Hide on index page'),
				'desc' => 'If enabled, then same setting will be auto select while create new route',
			),
			self::OPTION_ROUTE_SETTING_DEFAULT_SHOW_PATH_OUTLINE_BGCOLOR => array(
				'type' => Settings::TYPE_COLOR,
				'default' => '#ee3113',
				'category' => 'dashboard',
				'subcategory' => 'default',
				'title' => 'Path outline color',
				'desc' => 'Set a color of path outline.'
			),
			self::OPTION_EDITOR_TABS_FLIP_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'general',
				'title' => 'Flip map tabs',
				'desc' => 'If enabled, then editor tabs will flip while create new route.',
			),

			// Dashboard Editor
			self::OPTION_EDITOR_RICH_TEXT_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => false,
				'category' => 'dashboard',
				'subcategory' => 'editor',
				'title' => 'Enable rich text editor',
				'desc' => 'Allow users to use WYSIWYG editor when creating the map description. If disabled then simple textarea will be displayed.',
			),
			
			// General API
			self::OPTION_GOOGLE_MAPS_APP_KEY => array(
				'type' => self::TYPE_STRING,
				'category' => 'setup',
				'subcategory' => 'api',
				'title' => 'Google Maps App Key',
				'desc' => 'Enter the Google Maps <strong>Server App Key</strong>.<br /><a target="_blank" '
					. 'href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend&keyType=CLIENT_SIDE&reusekey=true">Get the API key from here</a> and if you are facing "<strong>For development purposes only</strong>" message on Google Maps please <a href="https://creativeminds.helpscoutdocs.com/article/2239-general-support-api-google-maps-for-development-purposes-only-message" target="_blank">read more</a> here.'
					. '<br><br><a href="#" class="button cminds-google-maps-api-check-btn" data-api-key-field-selector="input[name=cmmrm_google_maps_app_key]">Test Configuration</a>',
			),
			self::OPTION_GOOGLE_MAPS_LANG => array(
				'type' => self::TYPE_SELECT,
				'options' => array(
					'af' => 'Afrikaans', 'sq' => 'Albanian', 'am' => 'Amharic', 'ar' => 'Arabic', 'hy' => 'Armenian', 'az' => 'Azerbaijani', 'eu' => 'Basque', 'be' => 'Belarusian', 'bn' => 'Bengali', 'bs' => 'Bosnian', 'bg' => 'Bulgarian', 'my' => 'Burmese', 'ca' => 'Catalan', 'zh' => 'Chinese', 'zh-CN' => 'Chinese (Simplified)', 'zh-HK' => 'Chinese (Hong Kong)', 'zh-TW' => 'Chinese (Traditional)', 'hr' => 'Croatian', 'cs' => 'Czech', 'da' => 'Danish', 'nl' => 'Dutch', 'en' => 'English', 'en-AU' => 'English (Australian)', 'en-GB' => 'English (Great Britain)', 'et' => 'Estonian', 'fa' => 'Farsi', 'fi' => 'Finnish', 'fil' => 'Filipino', 'fr' => 'French', 'fr-CA' => 'French (Canada)', 'gl' => 'Galician', 'ka' => 'Georgian', 'de' => 'German', 'el' => 'Greek', 'gu' => 'Gujarati', 'iw' => 'Hebrew', 'hi' => 'Hindi', 'hu' => 'Hungarian', 'is' => 'Icelandic', 'id' => 'Indonesian', 'it' => 'Italian', 'ja' => 'Japanese', 'kn' => 'Kannada', 'kk' => 'Kazakh', 'km' => 'Khmer', 'ko' => 'Korean', 'ky' => 'Kyrgyz', 'lo' => 'Lao', 'lv' => 'Latvian', 'lt' => 'Lithuanian', 'mk' => 'Macedonian', 'ms' => 'Malay', 'ml' => 'Malayalam', 'mr' => 'Marathi', 'mn' => 'Mongolian', 'ne' => 'Nepali', 'no' => 'Norwegian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'pt-BR' => 'Portuguese (Brazil)', 'pt-PT' => 'Portuguese (Portugal)', 'pa' => 'Punjabi', 'ro' => 'Romanian', 'ru' => 'Russian', 'sr' => 'Serbian', 'si' => 'Sinhalese', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'es' => 'Spanish', 'es-419' => 'Spanish (Latin America)', 'sw' => 'Swahili', 'sv' => 'Swedish', 'ta' => 'Tamil', 'te' => 'Telugu', 'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Ukrainian', 'ur' => 'Urdu', 'uz' => 'Uzbek', 'vi' => 'Vietnamese', 'zu' => 'Zulu'
				),
				'default' => 'en',
				'category' => 'setup',
				'subcategory' => 'api',
				'title' => 'Google Maps Language',
				'desc' => 'Here you able to set Language of google map. Default Language is "English".',
			),
			
			/*
 			self::OPTION_GOOGLE_MAPS_JSAPI_DONT_EMBED => array(
 				'type' => self::TYPE_BOOL,
 				'category' => 'setup',
 				'subcategory' => 'api',
 				'title' => 'Do not embed Google Maps JavaScript API',
 				'desc' => 'Enable this option if you receiving JavaScript warning ',
 			),
 			self::OPTION_GOOGLE_ELEVATION_API_KEY => array(
 				'type' => self::TYPE_STRING,
 				'category' => 'general',
 				'subcategory' => 'api',
 				'title' => 'Google Elevation Service API key',
 				'desc' => 'Enter the Google Elevation Service server API Key.',
 			),
			*/

			// Label
			self::OPTION_LABEL_EDITOR_INSTRUCTION => array(
				'type' => self::TYPE_RICH_TEXT,
				'category' => 'labels',
				'subcategory' => 'other',
				'default' => '<iframe src="https://player.vimeo.com/video/161036537" width="500" height="281" frameborder="0" '
					. 'webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
					. '<ul>'
					. '<li><a href="http://creativeminds.helpscoutdocs.com/article/536-cm-maps-route-manager-cmmrm-first-steps-to-create-a-route">First Steps for route drawing</a></li>'
					. '<li><a href="http://creativeminds.helpscoutdocs.com/article/539-cm-maps-route-manager-cmmrm-drawing-a-route">Drawing and editing routes</a></li>'
					. '<li><a href="http://creativeminds.helpscoutdocs.com/article/538-cm-maps-route-manager-cmmrm-adding-route-locations">Adding locations</a></li>'
					. '</ul>',
				'title' => 'editor_instructions',
			),
			
			
		));
		
	}
	
	static function getRouteIndexPageParamsNames() {
		return apply_filters('cmmrm_route_index_params_names', array_merge(Route::getRouteParamsNames(), array(
			'featured_image' => 'Featured image',
			//'overview_path' => 'Overview path',
			'publish_date' => 'Publish date',
			'author' => 'Author',
		)));
	}
	
	static function getRouteSinglePageParamsNames() {
		return apply_filters('cmmrm_route_single_params_names', array_merge(Route::getRouteParamsNames(), array(
			'altitude' => 'Location altitude',
			'address' => 'Location address',
		)));
	}
	
	static function getAccessOptionsWithoutGuest() {
		return static::getAccessOptions(false);
	}
	
	static function getAccessOptions($guests = true) {
		if ($guests) {
			$result = array(self::ACCESS_GUEST => 'Everyone including guests');
		} else {
			$result = array();
		}
		return array_merge($result, array(
			self::ACCESS_USER => 'Only logged in users',
		),
		self::getRolesOptions(),
		array(
			self::ACCESS_CAPABILITY => 'Custom capability...',
		));
	}
	
	public static function getPageTemplate() {
		if ($template = Settings::getOption(Settings::OPTION_PAGE_TEMPLATE_OTHER)) {
			return $template;
		} else {
			$template = Settings::getOption(Settings::OPTION_PAGE_TEMPLATE);
			$available = Settings::getPageTemplatesOptions();
			if (!empty($template) AND isset($available[$template])) {
				return $template;
			} else {
				return 'page.php';
			}
		}
	}
	
	static function getMapLabelBgcolor() {
		$val = static::getOption(static::OPTION_MAP_LABEL_BGCOLOR);
		if (empty($val)) $val = static::DEFAULT_MAP_LABEL_BGCOLOR;
		return $val;
	}
	
	static function getIndexOrderBy() {
		$val = static::getOption(static::OPTION_INDEX_ORDERBY);
		if (empty($val)) $val = static::DEFAULT_INDEX_ORDERBY;
		return $val;
	}
	
	static function getIndexOrder() {
		$val = static::getOption(static::OPTION_INDEX_ORDER);
		if (empty($val)) $val = static::DEFAULT_INDEX_ORDER;
		return $val;
	}
	
	static function getMarkerIconsUrls() {
		$custom = array_filter(array_map('trim', explode("\n", Settings::getOption(Settings::OPTION_CUSTOM_ICONS))));
		if (!is_array($custom)) $custom = array();
		if(!Settings::getOption(Settings::OPTION_DISABLE_DEFAULT_ICONS)) {
			return array_merge($custom, GoogleMapsIcons::getAll());
		} else {
			return $custom;
		}
	}
	
	static function getSpecialPagesOptionsNames() {
		return array(
			Settings::OPTION_PAGE_ROUTE_INDEX,
			Settings::OPTION_PAGE_ROUTE_SINGLE,
			Settings::OPTION_PAGE_DASHBOARD_INDEX,
			Settings::OPTION_PAGE_DASHBOARD_EDITOR,
		);
	}
	
	static function getSpecialPagesIds() {
		return array_map(array(get_called_class(), 'getOption'), static::getSpecialPagesOptionsNames());
		/*
 		return array(
 			Settings::getOption(Settings::OPTION_PAGE_ROUTE_INDEX),
 			Settings::getOption(Settings::OPTION_PAGE_ROUTE_SINGLE),
 			Settings::getOption(Settings::OPTION_PAGE_DASHBOARD_INDEX),
 			Settings::getOption(Settings::OPTION_PAGE_DASHBOARD_EDITOR),
 		);
		*/
	}

	public static function getIndexMapMarkerClick() {
		$val = Settings::getOption(Settings::OPTION_INDEX_MAP_MARKER_CLICK);
		if (empty($val)) $val = self::DEFAULT_INDEX_MAP_MARKER_CLICK;
		return $val;
	}

	public static function getAllPages() {
		$result = array(null => '--');
		if(is_admin()) {
			$pages = \get_posts(array('post_type' =>'page', 'post_status' =>'publish', 'numberposts' => -1, 'fields' => 'ids'));
			if (is_array($pages)) {
				foreach ($pages as $page_id) {
					$result[$page_id] = \get_the_title($page_id);
				}
			}
		}
		return $result;
	}
	
	static function getMapTilesSettingsField($name) {
		$value = Settings::getOption($name);
		if (!is_array($value)) {
			$value = array();
		}
		$emptyTile = array(
			'tile_name' => '',
			'tile_url' => '',
			'tile_default' => false,
		);
		$renderItem = function($tile, $counter) use ($name) {
			$template = '<div class="cmmrm-map-tile-item">
				<label><span>Label: </span><input type="text" name="'. esc_attr($name) .'[tile_name]['.$counter.']" class="cmmrm-map-tile-name" value="%s" /></label>
				<label><span>URL: </span><input type="text" name="'. esc_attr($name) .'[tile_url]['.$counter.']" class="cmmrm-map-tile-url" value="%s" /></label>
				<div>
					<label style="float:left;">
						<span>Set as Default OSM Tile: </span>
						<input type="hidden" name="'. esc_attr($name) .'[tile_default]['.$counter.']" value="0" />
						<input type="checkbox" name="'. esc_attr($name) .'[tile_default]['.$counter.']" class="cmmrm-map-tile-default" value="1" %s />
					</label>
					<div class="cmmrm-map-tile-delete" style="float:right;margin-right:35px;"><a href="#">Delete</a></div>
				</div>
			</div>';
			$tileDefault = (!isset($tile['tile_default']) ? false : !empty($tile['tile_default']));
			return sprintf($template, $tile['tile_name'], $tile['tile_url'], checked($tileDefault, true, false));
		};
		$out = '';
		$counter = 0;
		foreach ($value as $tile) {
			$out .= $renderItem($tile, $counter);
			$counter++;
		}
		return '<div class="cmmrm-map-tile-setting" data-template="'. esc_attr($renderItem($emptyTile, $counter)) .'">'.$out.'<a href="#" class="button cmmrm-map-tile-add-btn">Add new</a></div>';
	}

}
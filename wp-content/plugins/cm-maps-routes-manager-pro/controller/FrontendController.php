<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\model\RouteTag;
use com\cminds\mapsroutesmanager\model\Category;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\User;

class FrontendController extends DummyPageController {
	
	const URL_DASHBOARD = 'routes';
	const QUERY_DASHBOARD_PAGE = 'cmmrm_dashboard_page';
	const DASHBOARD_ADD = 'add';
	const DASHBOARD_IMPORT = 'import';
	const DASHBOARD_EDIT = 'edit';
	const DASHBOARD_DELETE = 'delete';
	
	const PARAM_FILTER_AUTHOR = 'route_author';
	const PARAM_LIMIT = 'perpage';
	
	static $actions = array(
		'init',
		'pre_get_posts' => array('args' => 1),
		'template_redirect'
	);

	static $filters = array(
		'query_vars',
		'body_class',
		'cmmrm_get_index_filter_url' => array('args' => 2),
		'posts_where' => array('args' => 1),
		'posts_join' => array('args' => 1),
	);
	
	static function template_redirect() {
		global $wp_query, $wp;
		if(!Settings::getOption(Settings::OPTION_INDEX_PAGE_ENABLE)) {
			if(isset($wp_query->query['post_type']) && $wp_query->query['post_type'] == 'cmmrm_route' && !isset($wp_query->query['name'])) {
				wp_redirect(home_url());
			    exit();
			}
		}
		if(!Settings::getOption(Settings::OPTION_DASHBOARD_PAGE_ENABLE)) {
			if(isset($wp_query->query['cmmrm_dashboard_page']) && $wp_query->query['cmmrm_dashboard_page'] == 'index') {
				wp_redirect(home_url());
			    exit();
			}
		}
		if(!Settings::getOption(Settings::OPTION_SINGLE_PAGE_ENABLE)) {
			if(isset($wp_query->query['post_type']) && $wp_query->query['post_type'] == 'cmmrm_route' && isset($wp_query->query['name'])) {
				wp_redirect(home_url());
			    exit();
			}
		}

		if(!is_404()) {
			return;
		}
		if(isset( $_GET['cm-flush'])) { // WPCS: CSRF ok.
			return;
		}

		$parts = explode('/', $wp->request);
		if(isset($parts[2])) {
			return;
		}
		if(false === get_transient('cmmrm_refresh_404_permalinks')) {
			$slug = Settings::getOption(Settings::OPTION_PERMALINK_PREFIX);
			if($slug !== $parts[0]) {
				return;
			}
			flush_rewrite_rules(false);
			set_transient('cmmrm_refresh_404_permalinks', 1, HOUR_IN_SECONDS * 12);
			$redirect_url = home_url(add_query_arg(array('cm-flush'=> 1), $wp->request));
			wp_safe_redirect(esc_url_raw($redirect_url), 302);
			exit();
		}
	}

	static function init() {
		$slug = Settings::getOption(Settings::OPTION_PERMALINK_PREFIX);
		add_rewrite_rule( $slug . '/'. static::URL_DASHBOARD .'/(\w+)', add_query_arg(array(
			static::QUERY_DASHBOARD_PAGE => '$matches[1]'
		), 'index.php'), 'top' );
		flush_rewrite_rules();
	}
	
	static function template_include($template) {
		global $wp_query;
		//var_dump($wp_query->request);exit;
		$template = parent::template_include($template);
		if (static::isDummyPageRequired() AND FrontendController::isDashboard() AND 'edit' == static::getDashboardPage()) {
			// Custom template for editor page
			//$template = App::path('view/frontend/dashboard/editor-template.php');
		}
		return $template;
	}
	
	static function query_vars($vars) {
		$vars[] = static::QUERY_DASHBOARD_PAGE;
		return $vars;
	}
	
	static function isDummyPageRequired(\WP_Query $query = null) {
		return (static::isThePage($query));
	}
	
	static function isThePage(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		return (static::isRoutePostType($query) OR static::isDashboard($query));
	}
	
	static function getDummyPostTitle() {
		if(isset($_GET['cmmrm_route_type']) && $_GET['cmmrm_route_type'] != '') {
			$cmmrm_route_type_obj = get_term_by('slug', $_GET['cmmrm_route_type'], 'cmmrm_route_type');
			$cmmrm_route_type_name = $cmmrm_route_type_obj->name;
			$title = $cmmrm_route_type_name;
		} else {
			$title = Labels::getLocalized('route_index_title');
		}

		if (static::isDashboard()) {
			switch (static::getDashboardPage()) {
				case static::DASHBOARD_ADD:
					$title = Labels::getLocalized('dashboard_add_route_title');
					break;
				case static::DASHBOARD_IMPORT:
					$title = Labels::getLocalized('dashboard_import_route_title');
					break;
				case static::DASHBOARD_EDIT:
					$title = Labels::getLocalized('dashboard_edit_route_title');
					/*
					if ($route = self::getRoute()) {
						$title .= ' | ' . $route->getTitle();
					}
					*/
					break;
				default:
					$title = Labels::getLocalized('dashboard_my_routes_title');
			}
		}
		else if (static::$query AND static::$query->is_404()) {
			$title = Labels::getLocalized('route_not_found');
		}
		else if (static::$query AND static::$query->is_single()) {
			$title = static::$query->post->post_title;
		}
		else if ($category = static::getCategory()) {
			$title = $category->getName();
		}
		else if ($author = filter_input(INPUT_GET, static::PARAM_FILTER_AUTHOR) AND $user = get_user_by('slug', $author)) {
			$title = Labels::getLocalized('route_index_for_author_title') .' '. $user->display_name;
		}
		
		if ($tag = static::getTag()) {
			if (empty($category)) $title = '';
			else if (!empty($title)) $title .= ', ';
			$title .= 'Tag: ' . $tag->getName();
		}

		return $title;
	}
	
	static function getCategory($query = null) {
		if (empty($query)) $query = static::$query;
		if (!empty($query->query['cmmrm_category']) AND $category = Category::getInstance($query->query['cmmrm_category'])) {
			return $category;
		}
	}
	
	static function getTag($query = null) {
		if (empty($query)) $query = static::$query;
		if (!empty($query->query['tag']) AND $tag = RouteTag::getInstance($query->query['tag'])) {
			return $tag;
		}
	}
	
	static function the_content($content) {
		
		//return $content;

		$pattern = Settings::getOption(Settings::OPTION_EXCLUDE_AVADA_BUILDER_CSS_CLASSES);
		if(trim($content) == '') {
        	return $content;
        } else if(is_plugin_active('fusion-builder/fusion-builder.php') && $pattern !='' && preg_match('('.$pattern.')', $content) === 1) {
			return $content;
		} else {

			global $withcomments, $post;
			
			if (static::isDummyPageRequired()) {
				
				$withcomments = true;

				$pageId = Settings::getOption(Settings::OPTION_PAGE_ROUTE_SINGLE);
				if(!$pageId) {
					$post = null;
				}
				
				// Prevent from comments displaying on the index and dashboard pages
				if (FrontendController::isDashboard() OR !FrontendController::isRouteSinglePage()) {
					add_filter('the_comments', function() { return array(); }, PHP_INT_MAX-1);
				}
				
				if (static::isDashboard()) {
					if (Route::canCreate()) {
						$method = array(App::namespaced('controller\DashboardController'), static::getDashboardPage() . 'View');
						if (method_exists($method[0], $method[1]) AND is_callable($method)) {
							return call_user_func($method, static::$query);
						} else {
							return Labels::getLocalized('dashboard_unknown_action_msg');
						}
					} else {
						return Labels::getLocalized('dashboard_access_denied_msg');
					}
				}
				/*
				else if (static::$query AND static::$query->is_404()) {
					return Labels::getLocalized('page_not_found');
				}
				*/
				else if (static::$query AND static::$query->is_single()) {
					$pageId = Settings::getOption(Settings::OPTION_PAGE_ROUTE_SINGLE);
					if($pageId) {
						return $content;
					} else {
						if(is_plugin_active('fusion-builder/fusion-builder.php')) {
							echo RouteController::singleView(static::$query).'<div class="cmmrm_single_view_default_content">'.$content.'</div>';
						} else {
							return RouteController::singleView(static::$query).'<div class="cmmrm_single_view_default_content">'.$content.'</div>';
						}
					}
				}
				else {
					if(is_plugin_active('fusion-builder/fusion-builder.php')) {
						echo RouteController::indexView(static::$query);
					} else {
						return RouteController::indexView(static::$query);
					}
				}
			}
			return $content;

		}

	}
	
	static function isRoutePostType(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		return (!empty($query) AND ($query->get('post_type') == Route::POST_TYPE OR $query->get(Category::TAXONOMY)));
	}
	
	static function getRoute(\WP_Query $query = null) {
		$route = null;
		if (empty($query)) $query = static::$query;
		if (self::isDashboard($query) AND isset($_GET['id'])) {
			$route = Route::getInstance($_GET['id']);
		}
		else if (self::isRoutePostType($query) AND $query->is_single() AND !empty($query->posts[0])) {
			$route = Route::getInstance($query->posts[0]);
		}
		else if (static::isRouteCustomSinglePage($query)) {
			$routeId = $query->get(PageController::REWRITE_TAG_ROUTE_NAME);
			$route = Route::getInstance($routeId);
		} else {
			global $route;
		}
		return $route;
	}
	
	static function isRouteSinglePage(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		return ((self::isRoutePostType($query) AND $query->is_single()) OR static::isRouteCustomSinglePage($query));
	}
	
	static function isRouteCustomSinglePage(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		if ($pageId = Settings::getOption(Settings::OPTION_PAGE_ROUTE_SINGLE) AND !empty($query->posts) AND $post = reset($query->posts)) {
			return ($post->ID == $pageId);
		} else {
			return false;
		}
	}
	
	static function isDashboard(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		$page = self::getDashboardPage($query);
		return (!empty($page));
	}
	
	static function getDashboardPage(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		if (!empty($query)) return $query->get(static::QUERY_DASHBOARD_PAGE);
	}
	
	static function getUrl($action = '', $params = array()) {
		//$slug = Settings::getOption(Settings::OPTION_PERMALINK_PREFIX);
		//$url = home_url($slug . '/'. $action);
		$url = trailingslashit(Route::getArchiveLink()) . $action;
		return add_query_arg(urlencode_deep($params), trailingslashit($url));
	}
	
	static function pre_get_document_title($title, $sep = '', $seplocation = 'right') {
		if (static::isDummyPageRequired()) {
			$title = static::getDummyPostTitle();
			if (!FrontendController::isDashboard() AND (static::$query AND static::$query->is_single()) OR static::getCategory() OR static::getTag()) {
				$title .= ' | ' . Labels::getLocalized('single_route_title_part');
			}
			$title .= ' | ' . get_option('blogname');
		}
		return $title;
	}
	
	static function enqueueStyle() {
		wp_enqueue_style('cmmrm-frontend');
		wp_enqueue_script('cmmrm-frontend');
		add_action('wp_footer', array(__CLASS__, 'displayCustomCSS'));
	}
	
	static function displayCustomCSS() {
		$bgcolor = implode(',',
			array_map('hexdec',
				str_split(
					str_replace('#', '',
						Settings::getMapLabelBgcolor()
					), 2
				)
			)
		);

		if(Settings::getOption(Settings::OPTION_INDEX_SHOW_ROUTES_LABELS) == true) {
			$marker_label_display = 'block';
		} else {
			$marker_label_display = 'none';
		}
		
		echo '<style type="text/css">
			.cmmrm-custom-elevation-graph {height: '. intval(Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_HEIGHT)) .'px !important;}	
			.cmmrm-map-label {display:'.$marker_label_display.'; background-color: rgba(' . $bgcolor . ', 0.9) !important;}
			body .cmmrm-routes-archive-tiles .cmmrm-shortcode-route-snippet {width: '. intval(Settings::getOption(Settings::OPTION_INDEX_TILE_WIDTH)) .'px}
			.cmmrm-routes-archive-tiles .cmmrm-shortcode-route-snippet .cmmrm-route-featured-image-large {height: '. RouteView::getTileImageMaxHeight() .'px !important;}
			.cmmrm-infowindow, .gm-style-iw {min-width: 150px; max-width: '. Settings::getOption(Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_MAX_WIDTH) .'px !important; max-height: '. Settings::getOption(Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_MAX_HEIGHT) .'px !important;}
			.cmmrm-infowindow img, .gm-style-iw img {max-width: '. Settings::getOption(Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_IMAGE_MAX_WIDTH) .'px !important;}';
		
		if (Settings::getOption(Settings::OPTION_LOOK_AND_FEEL_CSS) == '2016-fancy') {
			echo '.cmmrm-route-params, .cmmrm-shortcode-route-snippet[data-layout="tiles"] {background-color: '. Settings::getOption(Settings::OPTION_FANCY_BGCOLOR) .' !important;}
			[data-layout="tiles"] .cmmrm-route-params {background-color: transparent !important;}' . PHP_EOL;
		}
		
		echo PHP_EOL . '/* CMMRM Custom CSS */' . PHP_EOL;
		echo Settings::getOption(Settings::OPTION_CUSTOM_CSS) . '
		</style>';
	}
	
	static function cmmrm_get_index_filter_url($url, $includeCategory) {
		return static::getFilterUrl($includeCategory);
	}
	
	static function getFilterUrl($includeCategory = false) {
		
		if (!FrontendController::$query) {
			return FrontendController::getUrl();
		}
		
		if ($includeCategory AND $slug = FrontendController::$query->get(Category::TAXONOMY) AND $category = Category::getInstance($slug)) {
			$url = $category->getPermalink();
		} else {
			$url = FrontendController::getUrl();
		}
		
		/*
 		if ($slug = FrontendController::$query->get(Difficulty::TAXONOMY)) {
 			$url = add_query_arg(Difficulty::TAXONOMY, urlencode($slug), $url);
 		}
 		if ($slug = FrontendController::$query->get(RouteType::TAXONOMY)) {
 			$url = add_query_arg(RouteType::TAXONOMY, urlencode($slug), $url);
		}
		*/
		
		return apply_filters('cmmrm_get_filter_url', $url, FrontendController::$query, $includeCategory);
		
	}
	
	static function body_class($class) {
		global $wp_query;
		
		$isRoute = static::isRoutePostType();
		$isDashboard = static::isDashboard();
		
		if ($isRoute) {
			if (static::isRouteSinglePage()) {
				$class[] = 'cmmrm-single';
			} else {
				$class[] = 'cmmrm-archive';
			}
		}
		
		if ($isDashboard) {
			$class[] = 'cmmrm-dashboard';
			if ($page = static::getDashboardPage()) {
				$class[] = 'cmmrm-dashboard-' . $page;
			}
		}
		
		if ($isRoute OR $isDashboard) {
			// Divi theme fix:
			$class[] = 'et_right_sidebar';
		}
		
		if(Settings::getOption(Settings::OPTION_LANG_RIGHT_TO_LEFT_ENABLE)) {
			$class[] = 'cmmrm-rtl';
		}

		return $class;
	}

	static function posts_where($where) {
		global $wpdb;
		$str = (isset($_GET['s']))?$_GET['s']:'';
		if($str != '') {
			$str = '%' . $str .'%';
			$where .= " OR cmmrm_lat_long2.meta_value LIKE '".$str."' ";
		}
		return $where;
	}

	static function posts_join($join) {
		global $wpdb;
		$str = (isset($_GET['s']))?$_GET['s']:'';
		if($str != '') {
			$join .= "LEFT JOIN $wpdb->postmeta cmmrm_lat_long2 ON $wpdb->posts.ID = cmmrm_lat_long2.post_id AND cmmrm_lat_long2.meta_key = '_cmmrm_latitude_longitude' ";
		}
		return $join;
	}

	static function pre_get_posts(\WP_Query $query) {
		if ($author = filter_input(INPUT_GET, static::PARAM_FILTER_AUTHOR) AND $query->get('post_type') == Route::POST_TYPE) {
			$query->set('author_name', $author);
		}
		if ($limit = filter_input(INPUT_GET, static::PARAM_LIMIT) AND $query->get('post_type') == Route::POST_TYPE) {
			$query->set('posts_per_archive_page', $limit);
			$query->set('posts_per_page', $limit);
		}
		//echo $query->request;
	}
	
}
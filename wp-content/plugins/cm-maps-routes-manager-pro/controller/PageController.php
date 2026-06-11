<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\model;

class PageController extends Controller {
	
	const REWRITE_TAG_ROUTE_NAME = 'cmmrm_route_name';
	
	static $actions = array(
		'init' => array('priority' => 2000),
		//'pre_get_posts' => array('args' => 1),
	);

	static $filters = array(
		//'template_include' => array('priority' => 200000), //mkk
		'body_class',
		//'post_type_link' => array('args' => 4),
		'posts_results' => array('args' => 2, 'priority' => 200000),
		//'post_type_archive_link' => array('args' => 2),
	);

	static function init() {
		/*
 		add_rewrite_tag('%'. static::REWRITE_TAG_ROUTE_NAME.'%', '([^&]+)');
 		if ($pageId = model\Settings::getOption(model\Settings::OPTION_PAGE_ROUTE_SINGLE)) {
 			$permalink = substr(parse_url(get_permalink($pageId), PHP_URL_PATH), 1, 9999);
 			$rule = '^'. $permalink .'([^/]+)/?';
 			add_rewrite_rule($rule, 'index.php?page_id='. $pageId .'&' . static::REWRITE_TAG_ROUTE_NAME . '=$matches[1]', 'top');
 		}
		*/		
	}

	static function template_include($template) {
		/*
		//mkk
		global $wp_query;
		if ($wp_query->get(FrontendController::QUERY_DUMMY_PAGE)) {
			if (FrontendController::$query->is_single() AND $pageId = model\Settings::getOption(model\Settings::OPTION_PAGE_ROUTE_SINGLE)
					AND $page = get_page($pageId)) {
				remove_filter('the_content', 'wpautop');
				add_filter('the_content', 'nl2br', 8);
				add_filter('the_content', function($content) { // remove <br> tags added between raw shortcodes
					$content = preg_replace("~\]\<br /\>\s+\[~", '][', $content);
					// 				var_dump($content);exit;
					return $content;
				}, 9);
				add_filter('the_content', function($content) use ($page) {
					$content = nl2br($page->post_content);
					$content = preg_replace("~\]\<br /\>\s+\[~", '][', $content);
					$content = do_shortcode($content);
					return '<div class="cmmrm-route cmmrm-route-single">' . $content . '</div>';
				}, PHP_INT_MAX);
			}
		}
		return $template;
		*/
		
		/*
 		$specialPagesIds = model\Settings::getSpecialPagesIds();
 		$currentPostId = get_query_var('page_id');
 		if ($currentPostId AND in_array($currentPostId, $specialPagesIds)) {
 			remove_filter('the_content', 'wpautop');
 			add_filter('the_content', 'nl2br', 8);
 			add_filter('the_content', function($content) {
 				$content = preg_replace("~\]\<br /\>\s+\[~", '][', $content);
				//var_dump($content);exit;
 				return $content;
 			}, 9);
 			add_filter('the_content', function($content) {
 				return '<div class="cmmrm-route cmmrm-route-single">' . $content . '</div>';
 			}, PHP_INT_MAX);
 		}
 		return $template;
		*/

	}
	
	static function body_class($class) {
		$currentId = get_query_var('page_id');
		$optionsNames = model\Settings::getSpecialPagesOptionsNames();
		foreach ($optionsNames as $name) {
			$id = model\Settings::getOption($name);
			if ($id AND $currentId == $id) {
				$class[] = $name;
			}
		}
		return $class;
	}
	
	static function post_type_link($post_link, $post, $leavename, $sample) {
		if ($post->post_type == model\Route::POST_TYPE) {
			if ($pageId = model\Settings::getOption(model\Settings::OPTION_PAGE_ROUTE_SINGLE)) {
				$post_link = get_permalink($pageId) . $post->post_name . '/';
			}
		}
		return $post_link;
	}
	
	static function post_type_archive_link($link, $type) {
		if ($type == model\Route::POST_TYPE) {
			if ($pageId = model\Settings::getOption(model\Settings::OPTION_PAGE_ROUTE_INDEX)) {
				$link = get_permalink($pageId);
			}
		}
		return $link;
	}

	static function pre_get_posts(\WP_Query $wp_query) {
		$specialPagesIds = model\Settings::getSpecialPagesIds();
		$currentPostId = get_query_var('page_id');
		$wp_query->set('cmmrm_category', '');
		/*
 		var_dump($wp_query);exit;
 		if ($currentPostId AND in_array($currentPostId, $specialPagesIds)) {
 			var_dump($wp_query);exit;
 		}
		*/
	}

	static function posts_results($posts, $query) {
		if ($query->get(FrontendController::QUERY_DUMMY_PAGE)) {
			if (FrontendController::$query->is_single() AND $pageId = model\Settings::getOption(model\Settings::OPTION_PAGE_ROUTE_SINGLE)) {
				$route = reset(FrontendController::$query->posts);
				// Replace the single post with special page to inherit its all theme settings
				$page = get_post($pageId);
				// But keep the route's title
				$page->ID = $route->ID;
				$page->post_title = $route->post_title;
				$posts = array($page);
			}
		}
		return $posts;
	}

}
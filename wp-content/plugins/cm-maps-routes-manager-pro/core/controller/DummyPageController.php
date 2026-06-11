<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\core\Core;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\helper\DateTimeHelper;
use com\cminds\mapsroutesmanager\model\User;

abstract class DummyPageController extends Controller {
	
	const QUERY_DUMMY_PAGE = 'cmmrm_dummy_page';
	const DUMMY_POST_TYPE = 'page';
	const DUMMY_POST_ID = 'cmmrm';
	const TITLE_SEPARATOR = '&gt;';
	
	/**
	 * Original query instance.
	 *
	 * @var \WP_Query
	 */
	static $query;
	
	static function bootstrap() {
		// Add extra filters
		static::$filters = array_merge(array(
				//array('name' => 'template_include', 'priority' => 0, 'method' => 'grabQuery'),
				array('name' => 'template_include', 'priority' => 100000),
				array('name' => 'posts_results', 'args' => 2, 'priority' => 100000),
				array('name' => 'the_title', 'args' => 2, 'priority' => PHP_INT_MAX),
				array('name' => 'pre_get_document_title', 'args' => 3, 'priority' => PHP_INT_MAX-1),
				array('name' => 'the_content', 'method' => 'the_content', 'priority' => 100000),
				array('name' => 'thrive_template_structure', 'method' => 'the_content', 'priority' => 100000),
			),
			static::$filters
		);
		static::$actions = array_merge(
			array(
				array(
					'name' => 'template_redirect',
					'priority' => 0,
					'method' => 'grabQuery'
				)
			), static::$actions);
		parent::bootstrap();
	}
	
	static function grabQuery() {
		global $wp_the_query;
		static::$query = $wp_the_query;
	}
	
	static function template_include($template) {
		global $wp_query, $wp_the_query, $post, $page;
		if (static::isDummyPageRequired()) {
			
			// Call this filter to set the WP SEO title before the $wp_query instance will be replaced:
			$wp_seo_title = apply_filters('wp_title', static::getDummyPostTitle(), '', '');

			// Replace the archive query with single-page query
			$newQuery = new \WP_Query(array(static::QUERY_DUMMY_PAGE => 1, 'ignore_sticky_posts' => 1));
			$post = $newQuery->posts[0];
			$newQuery->is_singular = true;
			$newQuery->is_single = true;
			$newQuery->is_page = true;
			$newQuery->is_home = false;
			//$post = $posts[0];
			
			if (empty($post->post_content)) {
				$post->post_content = '<p></p>';
			}
			$wp_query = $newQuery;
			$wp_the_query = $newQuery;
			
			// Get template path from child theme or parent theme if doesn't exists.
			$pageTemplate = Settings::getPageTemplate();
			$template = get_stylesheet_directory() . '/' . $pageTemplate;
			if (!file_exists($template)) {
				$template = get_template_directory() . '/' . $pageTemplate;
			}
			
		}
	
		return $template;
	}
	
	static function posts_results($posts, $query) {
		
		if ($query->get(static::QUERY_DUMMY_PAGE)) {
			
			if (self::$query->is_single()) {
				$posts = self::$query->posts;
			} else {
				
				$post = (object)array(
					'ID' => static::getDummyPostId(),
					'post_title' => static::getDummyPostTitle(),
					'post_content' => static::getDummyPostContent(),
					'post_type' => static::DUMMY_POST_TYPE,
					'post_parent' => '',
					'post_author' => User::getSomeAdminUserId(),
					'post_date' => DateTimeHelper::getMysqlDatetime(),
					'post_name' => Settings::getOption(Settings::OPTION_PERMALINK_PREFIX),
					'comment_status' => 'closed',
				);
				
				$posts = array($post);
				
			}
		}
		return $posts;
	}
	
	static function getDummyPostId() {
		return static::DUMMY_POST_ID;
	}
	
	static function getDummyPostTitle() {
		return App::getPluginName();
	}
	
	static function getDummyPostContent() {
		return App::getPluginName();
	}
	
	static function the_title($title, $postId = null) {
		$iscategory = get_term_by('id', $postId, 'cmmrm_category');
		if(is_plugin_active('fusion-builder/fusion-builder.php') && $iscategory) {
			if(isset($_GET['cmmrm_category']) && $_GET['cmmrm_category'] == '') {
				$title = 'All Routes';
			} else if(isset($_GET['cmmrm_category']) && $_GET['cmmrm_category'] != '') {
				$category = get_term_by('slug', $_GET['cmmrm_category'], 'cmmrm_category');
				if($category) {
					$title = $category->name;
				}
			}
		} else {
			if (static::getDummyPostId() === $postId) {
				$title = static::getDummyPostTitle();
			}
		}
		return $title;
	}
	
	static function pre_get_document_title($title, $sep = '', $seplocation = 'right') {
		if (static::isDummyPageRequired()) {
			$title = static::getDummyPostTitle();
			$title .= ' | ' . get_option('blogname');
		}
		return $title;
	}

	static function the_content($content) {
		return $content;
	}

	static function isDummyPageRequired(\WP_Query $query = null) {
		return false;
	}
	
}
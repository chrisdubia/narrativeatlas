<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\TaxonomyTerm;

class TaxonomyController extends Controller {
	
	const OPTION_CUSTOM_TAXONOMIES = 'cmmrm_custom_taxonomies';
	const SETTINGS_CATEGORY = 'taxonomies';
	const SETTINGS_SUBCATEGORY = 'taxonomies';
	
	const ROUTE_FIELD_PREFIX = 'custom_taxonomy';
	
	const DEFAULT_ROUTE_DIFFICULTY_TAXONOMY = 'cmmrm_route_difficulty';
	const DEFAULT_ROUTE_TYPE_TAXONOMY = 'cmmrm_route_type';
	
	protected static $actions = array(
		'init',
		'cmmrm_route_editor_middle' => array('args' => 1, 'priority' => 10),
		'cmmrm_route_after_save' => array('args' => 1),
		'cmmrm_categories_filter' => array('args' => 1),
		'cmmrm_single_route_properties' => array('args' => 1),
		array('name' => 'admin_menu', 'priority' => 12),
	);
	
	protected static $filters = array(
		'cmmrm_options_config',
		'cmmrm_settings_pages',
		'cmmrm_settings_pages_groups',
		'cmmrm_settings_before_save_value' => array('args' => 3),
		'cmmrm_get_filter_url' => array('args' => 3),
		'cmmrm_route_post_type_taxonomies',
		'cmmrm_routes_shortcode_atts_defaults' => array('args' => 3),
		'cmmrm_routes_shortcode_query' => array('args' => 3),
		'term-ordering-default-taxonomies' => array('method' => 'geckaTermsOrderingTaxonomies'), // support gecka terms ordering
		'term_link' => array('args' => 2),
	);
	
	static function init() {
		
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		foreach ($taxonomies as $tax) {
			
			if (empty($tax['taxonomy'])) continue;
			
			//$slug = Settings::getOption(Settings::OPTION_PERMALINK_PREFIX) .'-' . str_replace('_', '-', str_replace('cmmrm_', '', $tax['taxonomy']));
			
			// Register taxonomy
			$args = array(
				'hierarchical' => true,
				'labels' => static::getTaxonomyLabels($tax),
				'show_ui' => true, // to override in pro
				'query_var' => true,
				'show_admin_column' => true,
				'post_types' => array(Route::POST_TYPE),
				'public' => true,
				//'rewrite' => array('slug' => $slug),
			);
			register_taxonomy($tax['taxonomy'], $args['post_types'], $args);
			
		}
		
	}
	
	static function getTaxonomyLabels($tax) {
		$plural = $tax['name_plural'];
		$singular = $tax['name_singular'];
		return array(
			'name' => $plural,
			'singular_name' => $singular,
			'search_items' => 'Search ' . $plural,
			'popular_items' => 'Popular ' . $plural,
			'all_items' => 'All ' . $plural,
			'parent_item' => 'Parent ' . $singular,
			'parent_item_colon' => 'Parent ' . $singular . ':',
			'edit_item' => 'Edit ' . $singular,
			'update_item' => 'Update ' . $singular,
			'add_new_item' => 'Add New ' . $singular,
			'new_item_name' => 'New ' . $singular . ' Name',
			'menu_name' => $plural,
		);
	}
	
	static function cmmrm_options_config($config) {
		
		$config[static::OPTION_CUSTOM_TAXONOMIES] = array(
			'type' => Settings::TYPE_CUSTOM,
			'default' => array(
				array(
					'taxonomy' => static::DEFAULT_ROUTE_TYPE_TAXONOMY,
					'name_singular' => 'Route type',
					'name_plural' => 'Route types',
					'show_index_filter' => true,
				),
				array(
					'taxonomy' => static::DEFAULT_ROUTE_DIFFICULTY_TAXONOMY,
					'name_singular' => 'Difficulty grade',
					'name_plural' => 'Difficulty grades',
					'show_index_filter' => true,
				),
			),
			'category' => static::SETTINGS_CATEGORY,
			'subcategory' => static::SETTINGS_SUBCATEGORY,
			'content' => array(get_called_class(), 'getCustomTaxonomiesSettingsField'),
			'title' => 'Custom taxonomies',
		);
		
		return $config;
	}
	
	static function cmmrm_settings_pages($pages) {
		$pages[static::SETTINGS_CATEGORY] = 'Taxonomies';
		return $pages;
	}
	
	static function cmmrm_settings_pages_groups($groups) {
		$groups[static::SETTINGS_CATEGORY][static::SETTINGS_SUBCATEGORY] = 'Custom taxonomies';
		return $groups;
	}
	
	static function getCustomTaxonomiesSettingsField($name) {
		
		$value = Settings::getOption($name);
		if (!is_array($value)) $value = array();
		
		$emptyTax = array(
			'taxonomy' => '',
			'name_singular' => '',
			'name_plural' => '',
			'show_index_filter' => true,
		);
		
		$renderItem = function($tax) use ($name) {
			$template = '<div class="cmmrm-custom-tax-item">
				<label><span>Key:</span><input type="text" name="'. esc_attr($name) .'[taxonomy][]" class="cmmrm-custom-tax-taxonomy" value="%s" placeholder="Only alphanumeric characters and underscore" /></label>
				<label><span>Singular name:</span><input type="text" name="'. esc_attr($name) .'[name_singular][]" class="cmmrm-custom-tax-name-singular" value="%s" /></label>
				<label><span>Plural name:</span><input type="text" name="'. esc_attr($name) .'[name_plural][]" class="cmmrm-custom-tax-name-plural" value="%s" /></label>
				<label><input type="checkbox" name="'. esc_attr($name) .'[show_index_filter][]" value="1" %s /> Show filter on the index page for this taxonomy</label>
				<div class="cmmmrm-custom-tax-delete"><a href="#">Delete</a></div>
			</div>';
			$showIndexFilter = (!isset($tax['show_index_filter']) ? true : !empty($tax['show_index_filter']));
			return sprintf($template, $tax['taxonomy'], $tax['name_singular'], $tax['name_plural'], checked($showIndexFilter, true, false));
		};
		
		$out = '';
		foreach ($value as $tax) {
			$out .= $renderItem($tax);
		}
		
		return '<div class="cmmrm-custom-tax-setting" data-template="'. esc_attr($renderItem($emptyTax)) .'">'. $out
			.'<div><a href="#" class="button cmmrm-custom-tax-add-btn">Add new</a></div></div>';
		
	}
	
	static function cmmrm_settings_before_save_value($value, $name, $field) {
		if (static::OPTION_CUSTOM_TAXONOMIES == $name) {
			$newValue = array();
			if (!empty($value['taxonomy'])) foreach ($value['taxonomy'] as $i => $taxonomy) {
				//if ($i == 0) continue; // in this case we count from 0
				$newValue[] = array(
					'taxonomy' => $taxonomy,
					'name_singular' => $value['name_singular'][$i],
					'name_plural' => $value['name_plural'][$i],
					'show_index_filter' => !empty($value['show_index_filter'][$i]),
				);
			}
			$value = $newValue;
		}
		return $value;
	}
	
	static function cmmrm_route_editor_middle(Route $route) {
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		$route_form_taxonomy = Settings::getOption(Settings::OPTION_ROUTE_FORM_TAXONOMY);
		foreach ($taxonomies as $tax) {
			$label = $tax['name_singular'];
			$slug = str_replace("_", "-", $tax['taxonomy']);
			$slug = str_replace("cmmrm-", "", $slug);
			$fieldName = static::ROUTE_FIELD_PREFIX . '['. $tax['taxonomy'] .'][]';
			$options[0] = get_terms($tax['taxonomy'], array(
				'hide_empty' => 0,
				'fields' => TaxonomyTerm::FIELDS_ID_NAME,
			));
			//$options = array(0 => '--') + $options;
			$currentValue = wp_get_post_terms($route->getId(), $tax['taxonomy'], array(
				'fields' => TaxonomyTerm::FIELDS_IDS,
			));
			//$currentValue = reset($currentValue);
			if ($route_form_taxonomy != 'none') {
				echo self::loadFrontendView('editor', compact('route', 'label', 'options', 'currentValue', 'fieldName', 'slug'));
			}
		}
	}
	
	static function cmmrm_route_after_save(Route $route) {
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		foreach ($taxonomies as $tax) {
			/*
			if (isset($_POST[static::ROUTE_FIELD_PREFIX]) AND isset($_POST[static::ROUTE_FIELD_PREFIX][$tax['taxonomy']]) AND is_scalar($_POST[static::ROUTE_FIELD_PREFIX][$tax['taxonomy']])) {
				$id = intval($_POST[static::ROUTE_FIELD_PREFIX][$tax['taxonomy']]);
				wp_set_post_terms($route->getId(), array_filter(array($id)), $tax['taxonomy'], $append = false);
			}
			*/
			if (isset($_POST[static::ROUTE_FIELD_PREFIX]) AND isset($_POST[static::ROUTE_FIELD_PREFIX][$tax['taxonomy']])) {
				wp_set_post_terms($route->getId(), $_POST[static::ROUTE_FIELD_PREFIX][$tax['taxonomy']], $tax['taxonomy'], $append = false);
			}
		}
	}
	
	static function admin_menu() {
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		foreach ($taxonomies as $tax) {
			$url = htmlspecialchars(add_query_arg(urlencode_deep(array(
				'taxonomy' => $tax['taxonomy'],
				'post_type' => Route::POST_TYPE
			)), 'edit-tags.php'));
		
			add_submenu_page(App::PREFIX, App::getPluginName() . ' ' . $tax['name_plural'], $tax['name_plural'], 'manage_options', $url);
			if( isset($_GET['taxonomy']) && $_GET['taxonomy'] == $tax['taxonomy'] && isset($_GET['post_type']) && $_GET['post_type'] == Route::POST_TYPE ) {
				add_filter('parent_file', function($q) { return App::PREFIX; }, 999);
			}
			
		}
	}
	
	static function cmmrm_categories_filter($atts) {
		if (empty($atts['customtax'])) return;
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		
		if($taxonomies) {

			$mergetaxonomies = '0';
			if((is_plugin_active('cm-map-locations/cm-map-locations-pro.php') || is_plugin_active('cm-map-locations-pro/cm-map-locations-pro.php')) && Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_INTEGRATION) == '1' && Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_MERGE_CATEGORIES) == '1') {
				$mergetaxonomies = '1';
			}

			foreach ($taxonomies as $tax) {
				if (!empty($tax['show_index_filter'])) {
					$taxonomy = $tax['taxonomy'];
					$terms = get_terms($taxonomy, array(
						'hide_empty' => 0,
						'fields' => TaxonomyTerm::FIELDS_ALL,
					));
					$current = (empty(FrontendController::$query) ? null : FrontendController::$query->get($taxonomy));
					$baseUrl = FrontendController::getFilterUrl($includeCategory = true);
					echo self::loadFrontendView('filter', compact('terms', 'current', 'baseUrl', 'taxonomy', 'tax', 'mergetaxonomies'));
				}
			}

		}

	}
	
	static function cmmrm_get_filter_url($url, $query, $includeCategory) {
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		foreach ($taxonomies as $tax) {
			$taxonomy = $tax['taxonomy'];
			if ($slug = $query->get($taxonomy)) {
				$url = add_query_arg($taxonomy, urlencode($slug), $url);
			}
		}
		return $url;
	}
	
	
	static function cmmrm_route_post_type_taxonomies($array) {
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		foreach ($taxonomies as $tax) {
			$array[] = $tax['taxonomy'];
		}
		return $array;
	}
	
	
	static function cmmrm_routes_shortcode_atts_defaults($defaults, $atts, $shortcodeName) {
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		foreach ($taxonomies as $tax) {
			$defaults[$tax['taxonomy']] = null;
		}
		return $defaults;
	}
	
	
	static function cmmrm_routes_shortcode_query($query, $atts, $shortcodeName) {
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		foreach ($taxonomies as $tax) {
			$taxonomy = $tax['taxonomy'];
			if (isset($atts[$taxonomy])) {
				$query[$taxonomy] = $atts[$taxonomy];
			}
		}
		return $query;
	}
	
	
	static function cmmrm_single_route_properties(Route $route) {
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		foreach ($taxonomies as $tax) {
			$currentValue = wp_get_post_terms($route->getId(), $tax['taxonomy'], array(
				'fields' => TaxonomyTerm::FIELDS_IDS,
			));
			
			/*
			$currentValue = reset($currentValue);
			if (!empty($currentValue) AND $term = get_term($currentValue, $tax['taxonomy'])) {
				printf('<div class="cmmrm-custom-taxonomy cmmrm-custom-taxonomy-%s"><strong>%s:</strong> <span>%s</span></div>', esc_attr($tax['taxonomy']), $tax['name_singular'], $term->name);
			}
			*/

			if (!empty($currentValue)) {
				$term_names = '';
				foreach ($currentValue as $cvtax) {
					if($term = get_term($cvtax, $tax['taxonomy'])) {
						$term_names .= ', '.$term->name;
					}
				}
				$term_names = substr($term_names, 2);
				printf('<div class="cmmrm-custom-taxonomy cmmrm-custom-taxonomy-%s"><strong>%s:</strong> <span>%s</span></div>', esc_attr($tax['name_singular']), $tax['name_singular'], $term_names);
			}

		}
	}
	
	
	static function geckaTermsOrderingTaxonomies(array $taxonomies) {
		$customTaxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		foreach ($customTaxonomies as $tax) {
			$taxonomies[] = $tax['taxonomy'];
		}
		return $taxonomies;
	}
	
	
	static function term_link($link, $term) {
		$taxonomies = Settings::getOption(static::OPTION_CUSTOM_TAXONOMIES);
		$keys = array_map(function($tax) { return $tax['taxonomy']; }, $taxonomies);
		
		if (in_array($term->taxonomy, $keys)) {
			$link = FrontendController::getUrl('', array($term->taxonomy => $term->slug));
		}
		return $link;
	}
	
	
}
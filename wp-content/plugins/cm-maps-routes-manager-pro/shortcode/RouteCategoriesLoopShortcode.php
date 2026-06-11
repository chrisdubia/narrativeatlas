<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Category;

class RouteCategoriesLoopShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-categories-loop';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		$atts = shortcode_atts(array(
			'tax' => Category::TAXONOMY,
		), $atts);
		if (empty($content)) $content = '{link} ';
		
		$terms = wp_get_post_terms($route->getId(), $atts['tax']);
		
		$out = '';
		foreach ($terms as $term) {
			$url = get_term_link($term, $term->taxonomy);
			$out .= strtr($content, array(
				'{term_id}' => $term->term_id,
				'{term_taxonomy_id}' => $term->term_taxonomy_id,
				'{name}' => esc_html($term->name),
				'{slug}' => esc_html($term->slug),
				'{description}' => esc_html($term->description),
				'{url}' => $url,
				'{link}' => sprintf('<a href="%s">%s</a>', esc_attr($url), esc_html($term->name)),
			));
		}
		
		return $out;
		
	}
	
}
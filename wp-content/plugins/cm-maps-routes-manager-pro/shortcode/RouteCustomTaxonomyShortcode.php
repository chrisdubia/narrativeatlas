<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\TaxonomyTerm;

class RouteCustomTaxonomyShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-custom-taxonomy';
	
	static function shortcodeContent(Route $route, $atts, $content) {
		if (isset($atts['key'])) {
			$term_names = '';
			$currentValue = wp_get_post_terms($route->getId(), $atts['key'], array(
				'fields' => TaxonomyTerm::FIELDS_IDS,
			));
			if (!empty($currentValue)) {
				foreach ($currentValue as $tax) {
					if($term = get_term($tax, $atts['key'])) {
						$term_names .= ', '.$term->name;
					}
				}
				$term_names = substr($term_names, 2);
			}
			return $term_names;
		}
	}
	
}
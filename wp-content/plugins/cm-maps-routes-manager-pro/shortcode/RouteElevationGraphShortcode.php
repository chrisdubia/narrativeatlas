<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\shortcode\abstracts\SingleRouteAbstractShortcode;
use com\cminds\mapsroutesmanager\model;
use com\cminds\mapsroutesmanager\controller;

class RouteElevationGraphShortcode extends SingleRouteAbstractShortcode {
	
	const SHORTCODE_NAME = 'route-elevation-graph';
	
	static function shortcodeContent(model\Route $route, $atts, $content) {
		controller\FrontendController::enqueueStyle();
		do_action('cmmrm_load_single_page_scripts');
		wp_enqueue_script('cmmrm-elevation-graph-standalone');
		$widgetId = mt_rand();
		add_action('wp_footer', function() {
			echo '<script>var route_elevation_graph_counter = 0;
			jQuery(document).ready(function() {
				if(route_elevation_graph_counter == 0) {
					jQuery(".cmmrm-elevation-graph").each(function() {
						if (jQuery(this).parents(".cmmrm-route-single").length == 0) { new CMMRM_ElevationGraphStandalone(this); } return false;
					}); route_elevation_graph_counter = 1;
				}
			});
			var route_elevation_graph_load_counter = 0;
			jQuery(window).load(function() {
				if(route_elevation_graph_load_counter == 0) {
					setInterval(function() {
						jQuery(".cmmrm-elevation-graph").each(function() {
							if (jQuery(this).find(".cmmrm-elevation-graph-canvas").html() == "<div class=\"cmmrm-graph-loader\"></div>") { new CMMRM_ElevationGraphStandalone(this); return false; }
						});
					}, 1000); route_elevation_graph_load_counter = 1;
				}
			});</script>';
		});
		return controller\RouteController::loadFrontendView('elevation-graph', compact('route', 'widgetId'));
	}
	
	static function wp_footer() {
		?><script type="text/javascript">
			jQuery(function() {
				jQuery(".cmmrm-elevation-graph").each(function() {
					var container = $(this);
					if (container.parents('.cmmrm-route-single').length == 0) {
						new CMMRM_ElevationGraphStandalone(this);
					}
				});
			});
		</script><?php
	}
	
}
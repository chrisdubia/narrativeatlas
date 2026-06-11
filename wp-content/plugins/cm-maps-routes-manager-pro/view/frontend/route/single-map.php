<?php
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\shortcode\RouteMapCanvasShortcode;
use com\cminds\mapsroutesmanager\shortcode\RouteToolbarShortcode;
if (!isset($zoom)) $zoom = null;
?>

<?php if (!empty($atts['toolbar'])): ?>
	<?php echo RouteToolbarShortcode::shortcodeContent($route, $atts, ''); ?>
<?php endif; ?>

<?php echo RouteMapCanvasShortcode::shortcodeContent($route, $atts, ''); ?>

<?php do_action('cmmrm_route_single_after_map', $route, $atts); ?>

<?php if (!isset($atts['map']) OR $atts['map'] == 1): ?>
	<?php if (isset($atts['showtravelmode']) AND is_numeric($atts['showtravelmode'])): ?>
		<?php if (!empty($atts['showtravelmode'])): ?>
			<?php echo RouteView::getTravelModeMenu($route->getTravelMode()); ?>
		<?php endif; ?>
	<?php elseif ((!App::isPro() OR Settings::getOption(Settings::OPTION_SINGLE_ROUTE_TRAVEL_MODE_SHOW))): ?>
		<?php echo RouteView::getTravelModeMenu($route->getTravelMode()); ?>
	<?php endif; ?>
<?php endif; ?>

<?php if (!isset($atts['params']) OR $atts['params'] == 1): ?>
	<?php if (!Settings::getOption(Settings::OPTION_SINGLE_ROUTE_PARAMS_ABOVE_MAP)): ?>
		<?php echo RouteController::loadFrontendView('route-params', compact('route')); ?>
	<?php endif; ?>
<?php endif; ?>
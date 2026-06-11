<?php
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Route;
$shortcodeId = 'cmmrm-shortcode-' . rand();
?>
<div id="<?php echo $shortcodeId; ?>" class="cmmrm-shortcode-route-map cmmrm-route-snippet cmmrm-route-single"<?php
	echo RouteView::getDisplayParams($displayParams);
	?> data-fancy="<?php echo (Settings::getOption(Settings::OPTION_FANCY_STYLE_ENABLE) ? '1': '0');
	?>" style="<?php if (!empty($atts['width'])) echo 'width:' . intval($atts['width']) .'px;'; ?>">
	
	<?php if ($atts['showdate']): ?>
		<div class="cmmrm-date">
			<?php
			$date = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_time('U', true, $route->getId()));
			echo $date;
			?>
		</div>
	<?php endif; ?>
	
	<?php if ($atts['showtitle']): ?>
		<h2><a href="<?php echo esc_attr($route->getPermalink()); ?>" target="_blank"><?php echo esc_html($route->getTitle()); ?></a></h2>
	<?php endif; ?>
	
	<?php if ($atts['topinfo']):
		do_action('cmmrm_route_single_before', array_merge($atts, array('params' => 0, 'toolbar' => 0)));
	endif; ?>
	
	<?php if (!isset($atts['params']) OR $atts['params'] == 1): ?>
		<?php if (Settings::getOption(Settings::OPTION_SINGLE_ROUTE_PARAMS_ABOVE_MAP)): ?>
			<?php echo RouteController::loadFrontendView('route-params', compact('route')); ?>
		<?php endif; ?>
	<?php endif; ?>
	
	<div class="cmmrm-route-map"><?php
		echo RouteView::getFullMap($route, $atts);
	?></div>
	<div class="clear"></div>
	
</div>
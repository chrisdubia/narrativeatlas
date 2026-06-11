<?php
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\controller\DashboardController;
use com\cminds\mapsroutesmanager\controller\FrontendController;

$index_menu_enable = Settings::getOption(Settings::OPTION_INDEX_MENU_ENABLE);
if((isset($atts['menu']) && $atts['menu'] == '1') || (!isset($atts['menu']) && $index_menu_enable == '1')) {
?>
<div class="cmmrm-index-menu">
	<ul>
		<li><a href="<?php echo esc_attr(FrontendController::getUrl()); ?>"><?php echo Labels::getLocalized('menu_all_routes'); ?></a></li>
		<?php if (Route::canCreate()): ?>
			<li><a href="<?php echo esc_attr(RouteController::getDashboardUrl('index')); ?>"><?php echo Labels::getLocalized('menu_my_routes'); ?></a></li>
			<li><a href="<?php echo esc_attr(RouteController::getDashboardUrl('add')); ?>"><?php echo Labels::getLocalized('menu_add_route'); ?></a></li>
			<?php if (!empty($route) AND $route->canEdit()): ?>
				<?php if (FrontendController::isDashboard() AND 'publish' == $route->getStatus()): ?>
					<li><a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo Labels::getLocalized('menu_view_route'); ?></a></li>
				<?php else: ?>
					<li><a href="<?php echo esc_attr($route->getUserEditUrl()); ?>"><?php echo Labels::getLocalized('menu_edit_route'); ?></a></li>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</ul>
</div>
<?php
}
?>
<div class="cmmrm-route-index-map" id="cmmrm-route-index-map-<?php echo $atts['mapId']; ?>">

	<div class="cmmrm-toolbar">
		<?php if (Settings::getOption(Settings::OPTION_INDEX_FULLSCREEN_BTN_SHOW)): ?>
			<a class="cmmrm-map-fullscreen-btn dashicons dashicons-editor-expand" href="#" title="<?php echo esc_attr(Labels::getLocalized('show_fullscreen_title')); ?>"></a>
		<?php endif; ?>
	</div>
	
	<div class="cmmrm-route-map-canvas-outer ">
		<div id="cmmrm-route-index-map-canvas-<?php echo $atts['mapId']; ?>" data-map-style='<?php echo isset($mapStyle)?$mapStyle:'[]'; ?>'
             class="cmmrm-route-map-canvas" style="<?php
			if (!empty($atts['mapwidth'])) echo 'width:'. intval($atts['mapwidth']) .'px;';
			if (!empty($atts['mapheight'])) echo 'height:'. intval($atts['mapheight']) .'px;';
		?>"></div>
	</div>
	
</div>

<?php $writeScript = function() use ($routes, $atts) { ?>
	<script type="text/javascript" data-cmmrm-script="index-map">
	jQuery(function($) {
      var mapId = <?php echo json_encode($atts['mapId']); ?>;
      var widgetContainerId = 'cmmrm-route-index-map-canvas-' + mapId;
      var widget;

      function recursiveWaitSettings(settingsObject, iteration) {
        iteration = iteration || 1;
        const tries = 4000;
        if (iteration >= tries) {
          return false;
        }
        if (window[settingsObject] === undefined) {
          iteration++;
          setTimeout(() => {
            recursiveWaitSettings(settingsObject, iteration);
          }, 400);
        } else {

          if (document.getElementById(widgetContainerId)) {
            widget = new CMMRM_WidgetIndexMap(widgetContainerId, <?php echo json_encode( $routes ); ?>);
          }
        }
      }
      recursiveWaitSettings('CMMRM_Map_Settings')
	});
	</script>
<?php }; ?>

<?php
if (Settings::getOption(Settings::OPTION_INDEX_MAP_SCRIPT_IN_FOOTER)):
	add_action('wp_footer', $writeScript, 20);
else:
	$writeScript();
endif;
?>

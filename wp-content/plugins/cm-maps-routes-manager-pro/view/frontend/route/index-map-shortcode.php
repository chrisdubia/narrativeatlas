<?php
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\helper\RouteView;
?>
<div class="cmmrm-map-shortcode cmmrm-routes-archive"<?php echo RouteView::getDisplayParams($displayParams);
	?> style="<?php if (!empty($atts['width'])) echo 'width:'. intval($atts['width']) .'px;'; ?>">
	<?php
	$mapStyle = isset($mapStyle)?$mapStyle:'';
    echo RouteController::loadFrontendView('index-map', compact('mapStyle','routes', 'atts')); ?>
</div>
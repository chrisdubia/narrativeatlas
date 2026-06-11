<?php
use com\cminds\mapsroutesmanager\model\Route;

if (empty($widgetId)) $widgetId = mt_rand();
global $wp;
$form = '';
if($wp->request != '') { $urlsplit = explode("/", $wp->request); $form = $urlsplit[count($urlsplit)-1]; }
?>
<div class="cmmrm-elevation-graph" id="cmmrm-elevation-graph-<?php echo $widgetId;
		?>" data-route-id="<?php echo $route->getId();
		?>" data-path="<?php echo esc_attr($route->getOverviewPath());
		?>" data-travel-mode="<?php echo esc_attr($route->getTravelMode());
		?>" data-path-color="<?php echo esc_attr($route->getPathColor());
		?>" data-slopedown-color="<?php echo esc_attr($route->getSlopeDownwardColor());
		?>" data-slopeup-color="<?php echo esc_attr($route->getSlopeUpwardColor());
		?>" data-distance="<?php echo esc_attr($route->getDistance()); ?>">
	<div class="cmmrm-elevation-graph-canvas"><?php if($form != 'add') { ?><div class="cmmrm-graph-loader"></div><?php } ?></div>
	<div class="cmmrm-elevation-graph-crosshair-x"></div>
	<div class="cmmrm-elevation-graph-crosshair-y"></div>
	<div class="cmmrm-elevation-graph-crosshair-label">Label</div>
</div>
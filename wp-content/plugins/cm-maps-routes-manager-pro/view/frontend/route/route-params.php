<?php
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Route;
?>
<ul class="cmmrm-route-params" data-values-top="<?php echo (Settings::getOption(Settings::OPTION_ROUTE_PARAMS_VALUES_TOP) ? '1': '0');
	?>" data-use-minor-length-units="<?php echo intval($route->useMinorLengthUnits());
	?>">
	<li class="cmmrm-route-distance">
		<strong><?php echo Labels::getLocalized('route_distance'); ?></strong>
		<span><?php echo $route->getFormattedDistance(); ?></span>
	</li>
	<li class="cmmrm-route-duration">
		<strong><?php echo Labels::getLocalized('route_duration'); ?></strong>
		<span><?php echo Route::formatTime($route->getDuration()); ?></span>
	</li>
	<li class="cmmrm-route-avg-speed">
		<strong><?php echo Labels::getLocalized('route_avg_speed'); ?></strong>
		<span><?php echo Route::formatSpeed($route->getAvgSpeed()); ?></span>
	</li>
	<li class="cmmrm-min-elevation">
		<strong><?php echo Labels::getLocalized('route_min_elevation'); ?></strong>
		<span><?php echo Route::formatElevation($route->getMinElevation()); ?></span>
	</li>
	<li class="cmmrm-max-elevation">
		<strong><?php echo Labels::getLocalized('route_max_elevation'); ?></strong>
		<span><?php echo Route::formatElevation($route->getMaxElevation()); ?></span>
	</li>
	<li class="cmmrm-elevation-gain">
		<strong><?php echo Labels::getLocalized('route_elevation_gain'); ?></strong>
		<span><?php echo Route::formatElevation($route->getElevationGain()); ?></span>
	</li>
	<li class="cmmrm-elevation-descent">
		<strong><?php echo Labels::getLocalized('route_elevation_descent'); ?></strong>
		<span><?php echo Route::formatElevation($route->getElevationDescent()); ?></span>
	</li>
</ul>
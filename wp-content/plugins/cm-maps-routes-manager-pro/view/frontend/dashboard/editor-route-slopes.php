<?php
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Route;
?>
<div class="cmmrm-field cmmrm-field-elevation-graph-settings">
	<strong><?php echo Labels::getLocalized('dashboard_elevation_graph_settings_header'); ?></strong>
	<label><input type="checkbox" name="slopes-enable" value="1" <?php checked($route->isSlopesShowingEnabled()); ?> />
		<span><?php echo Labels::getLocalized('dashboard_show_slopes'); ?></span>
	</label>
	<label><span><?php echo Labels::getLocalized('dashboard_slope_min_val'); ?></span>
		<div><input type="number" max="100" min="1" step="1" name="slope-min-value" value="<?php echo esc_attr($route->getSlopeMinValue()); ?>" /></div>
	</label>
	<label><span><?php echo Labels::getLocalized('dashboard_slope_min_width'); ?></span>
		<div><input type="number" max="2000" min="1" step="1" name="slope-min-width" value="<?php echo esc_attr($route->getSlopeMinWidth()); ?>" /></div>
	</label>
	<label><span><?php echo Labels::getLocalized('dashboard_slope_downward_color'); ?></span>
		<div><input type="color" name="slope-downward-color" value="<?php echo esc_attr($route->getSlopeDownwardColor()); ?>" /></div>
	</label>
	<label><span><?php echo Labels::getLocalized('dashboard_slope_upward_color'); ?></span>
		<div><input type="color" name="slope-upward-color" value="<?php echo esc_attr($route->getSlopeUpwardColor()); ?>" /></div>
	</label>
</div>
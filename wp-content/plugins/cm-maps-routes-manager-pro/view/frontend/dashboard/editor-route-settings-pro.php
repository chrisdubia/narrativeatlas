<?php
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Settings;
$checked_route_setting_default_show_weather_per_each_location = '';
if($route->showWeatherPerLocation()) {
	if($route->showWeatherPerLocation() == '1') {
		$checked_route_setting_default_show_weather_per_each_location = 'checked="checked"';
	}
} else {
	if(Settings::getOption(Settings::OPTION_ROUTE_SETTING_DEFAULT_SHOW_WEATHER_PER_EACH_LOCATION) && !isset($_GET['id'])) {
		$checked_route_setting_default_show_weather_per_each_location = 'checked="checked"';
	}
}
printf('<label><input id="customcheckbox3" type="checkbox" name="show-weather-per-location" value="1" %s /><label for="customcheckbox3" class="checker3"></label>
<span>%s</span></label>',
	$checked_route_setting_default_show_weather_per_each_location,
	Labels::getLocalized('dashboard_show_weather_per_location', 'Show weather per each location (disabled to show it once per trail)')
);
$checked_route_setting_default_show_directional_arrows = '';
if($route->showDirectionalArrows()) {
	if($route->showDirectionalArrows() == '1') {
		$checked_route_setting_default_show_directional_arrows = 'checked="checked"';
	}
} else {
	if(Settings::getOption(Settings::OPTION_ROUTE_SETTING_DEFAULT_SHOW_DIRECTIONAL_ARROWS) && !isset($_GET['id'])) {
		$checked_route_setting_default_show_directional_arrows = 'checked="checked"';
	}
}
?>
<label><input id="customcheckbox4" type="checkbox" name="directional-arrows" value="1" <?php echo $checked_route_setting_default_show_directional_arrows; ?> /><label for="customcheckbox4" class="checker4"></label>
<span><?php echo Labels::getLocalized('dashboard_show_directional_arrows', 'Show directional arrows for the trail path'); ?></span></label>
<?php
$checked_route_setting_default_show_locations_section = '';
if($route->showLocationsSection()) {
	if($route->showLocationsSection() == '1') {
		$checked_route_setting_default_show_locations_section = 'checked="checked"';
	}
} else {
	if(Settings::getOption(Settings::OPTION_ROUTE_SETTING_DEFAULT_SHOW_LOCATIONS_SECTION) && !isset($_GET['id'])) {
		$checked_route_setting_default_show_locations_section = 'checked="checked"';
	}
}
?>
<label><input id="customcheckbox5" type="checkbox" name="show-locations-section" value="1" <?php echo $checked_route_setting_default_show_locations_section; ?> /><label for="customcheckbox5" class="checker5"></label>
<span><?php echo Labels::getLocalized('dashboard_show_locations_section', 'Show locations section under the map on the single route page'); ?></span></label>
<?php
$checked_route_setting_default_show_path_outline = '';
if($route->showPathOutline()) {
	if($route->showPathOutline() == '1') {
		$checked_route_setting_default_show_path_outline = 'checked="checked"';
	}
} else {
	if(Settings::getOption(Settings::OPTION_ROUTE_SETTING_DEFAULT_SHOW_PATH_OUTLINE) && !isset($_GET['id'])) {
		$checked_route_setting_default_show_path_outline = 'checked="checked"';
	}
}
?>
<label><input id="customcheckbox6" type="checkbox" name="show-path-outline" value="1" <?php echo $checked_route_setting_default_show_path_outline; ?> /><label for="customcheckbox6" class="checker6"></label>
<span><?php echo Labels::getLocalized('dashboard_show_path_outline', 'Show path outline'); ?></span></label>
<?php
$checked_route_setting_default_hide_on_index = '';
if($route->hideOnIndex()) {
	if($route->hideOnIndex() == '1') {
		$checked_route_setting_default_hide_on_index = 'checked="checked"';
	}
} else {
	if(Settings::getOption(Settings::OPTION_ROUTE_SETTING_DEFAULT_HIDE_ON_INDEX) && !isset($_GET['id'])) {
		$checked_route_setting_default_hide_on_index = 'checked="checked"';
	}
}
?>
<label><input id="customcheckbox7" type="checkbox" name="hide-on-index" value="1" <?php echo $checked_route_setting_default_hide_on_index; ?> /><label for="customcheckbox7" class="checker7"></label>
<span><?php echo Labels::getLocalized('dashboard_hide_on_index', 'Hide on index page'); ?></span></label>
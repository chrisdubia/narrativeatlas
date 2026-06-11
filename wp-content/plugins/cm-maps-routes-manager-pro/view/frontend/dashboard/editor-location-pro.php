<?php
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Settings;
?>
<?php if (Settings::getOption(Settings::OPTION_EDITOR_ALLOW_INFO_WINDOW_AUTO_OPEN)): ?>
	<div class="cmmrm-info-window-open"><label>
		<input type="hidden" name="locations[info_window_open][]" value="0">
		<input type="checkbox">
		<?php echo Labels::getLocalized('location_open_info_window'); ?>
	</label></div>
<?php endif; ?>
<?php if (Settings::getOption(Settings::OPTION_EDITOR_ALLOW_GENERATE_WAZE_BUTTON)): ?>
	<div class="cmmrm-generate-waze-button"><label>
		<input type="hidden" name="locations[generate_waze_button][]" value="0">
		<input type="checkbox">
		<?php echo Labels::getLocalized('location_generate_waze_button'); ?>
	</label></div>
<?php endif; ?>
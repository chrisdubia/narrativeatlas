<?php
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Labels;
$disabled = '';
if(!is_admin()) {
	$disabled = 'disabled="disabled"';
}
$accept = apply_filters('cmmrm_accepted_file_ext', array('.kml','.kmz','.gpx'));
$desc = apply_filters('cmmrm_import_file_description', Labels::getLocalized('dashboard_import_form_desc'));
?>
<div class="cmmrm-field cmmrm-field-route-import">
	<span><?php echo $desc; ?></span>
	<label><input type="file" name="cmmrm_import_file" accept="<?php echo implode(',', $accept); ?>" <?php echo $disabled; ?> /></label>
</div>
<?php /*
<div class="cmmrm-field cmmrm-field-route-max-waypoints">
	<span><?php echo Labels::getLocalized('dashboard_import_max_waypoints'); ?></span>
	<label><select name="max_waypoints">
		<option value="10">Very low (10)</option>
		<option value="50">Low (50)</option>
		<option value="100">Medium (100)</option>
		<option value="200">High (200)</option>
		<option value="300">Very high (300)</option>
		<option value="512">Maximum (512)</option>
	</select></label>
</div>
*/
?>
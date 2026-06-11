<?php
use com\cminds\mapsroutesmanager\addon\customfields\model\Settings;
use com\cminds\mapsroutesmanager\addon\customfields\helper\RateGradeHelper;

if (empty($location)) $location = null;
if (empty($route)) $route = null;

if($value != '' && $value != '0') {
	?>
	<div class="cmmrm-custom-field" data-custom-field="<?php echo esc_attr($field['meta_key']); ?>">
		<strong><?php echo RateGradeHelper::getFieldLabel($field); ?></strong>
		<?php if ($field['type'] == Settings::FIELD_TYPE_5_GRADE_SCALE): ?>
			<?php echo RateGradeHelper::getFrontend($field, $value); ?>
		<?php else: ?>
			<span><?php echo apply_filters('cmmrm_route_single_custom_field_value', esc_html($value), $field, $route, $location); ?></span>
		<?php endif; ?>
	</div>
	<?php
}
?>
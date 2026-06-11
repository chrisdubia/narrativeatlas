<?php
use com\cminds\mapsroutesmanager\addon\customfields\model\Settings;
use com\cminds\mapsroutesmanager\addon\customfields\helper\RateGradeHelper;

if($value != '' && $value != '0') {
	?>
	<div class="cmmrm-custom-field" data-custom-field="<?php echo esc_attr($field['meta_key']); ?>">
		<strong><?php echo RateGradeHelper::getFieldLabel($field); ?></strong>
		<?php if ($field['type'] == Settings::FIELD_TYPE_5_GRADE_SCALE): ?>
			<?php echo RateGradeHelper::getFrontend($field, $value); ?>
		<?php else: ?>
			<span><?php echo esc_html($value); ?></span>
		<?php endif; ?>
	</div>
	<?php
}
?>
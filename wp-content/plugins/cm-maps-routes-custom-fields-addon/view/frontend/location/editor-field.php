<?php
use com\cminds\mapsroutesmanager\addon\customfields\model\Settings;
use com\cminds\mapsroutesmanager\addon\customfields\model\Location;
use com\cminds\mapsroutesmanager\addon\customfields\helper\RateGradeHelper;
?>
<div class="cmmrm-field cmmrm-custom-field cmmrm-location-custom-field" data-custom-field="<?php echo esc_attr($field['meta_key']); ?>">
	<label><?php echo RateGradeHelper::getFieldLabel($field); ?></label>
	<?php switch ($field['type']):
		case Settings::TYPE_TEXTAREA: ?>
			<textarea name="location_custom_fields[<?php echo esc_attr($field['meta_key']); ?>][]"></textarea>
			<?php break;
		case Settings::FIELD_TYPE_5_GRADE_SCALE: ?>
			<?php echo RateGradeHelper::getEditorsField('location_custom_fields[%s][]', $field); ?>
			<?php break;
		case Settings::TYPE_STRING:
		default: ?>
			<input type="text" name="location_custom_fields[<?php echo esc_attr($field['meta_key']); ?>][]" value="" />
			<?php break;
	endswitch; ?>
</div>
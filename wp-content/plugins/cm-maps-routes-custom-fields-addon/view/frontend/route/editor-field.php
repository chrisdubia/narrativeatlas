<?php
use com\cminds\mapsroutesmanager\addon\customfields\model\Settings;
use com\cminds\mapsroutesmanager\addon\customfields\model\Route;
use com\cminds\mapsroutesmanager\addon\customfields\helper\RateGradeHelper;
?>
<div class="cmmrm-field cmmrm-custom-field cmmrm-route-custom-field" data-custom-field="<?php echo esc_attr($field['meta_key']); ?>">
	<label><?php echo RateGradeHelper::getFieldLabel($field); ?></label>
	<?php switch ($field['type']):
		case Settings::TYPE_TEXTAREA: ?>
			<textarea name="route_custom_fields[<?php echo esc_attr($field['meta_key']); ?>]"><?php echo esc_html($value); ?></textarea>
			<?php break;
		case Settings::FIELD_TYPE_5_GRADE_SCALE: ?>
			<?php
			$currentGrade = 0;
			if(!empty($route)) {
				$currentGrade = intval($route->getCustomField($field['meta_key']));
			}
			?>
			<?php echo RateGradeHelper::getEditorsField('route_custom_fields[%s]', $field, $currentGrade); ?>
			<?php break;
		case Settings::TYPE_STRING:
		default: ?>
			<input type="text" name="route_custom_fields[<?php echo esc_attr($field['meta_key']); ?>]" value="<?php echo esc_attr($value); ?>" />
			<?php break;
	endswitch; ?>
</div>
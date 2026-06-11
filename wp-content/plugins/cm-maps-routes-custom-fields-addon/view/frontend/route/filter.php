<?php 
use com\cminds\mapsroutesmanager\addon\customfields\model\Labels;
use com\cminds\mapsroutesmanager\addon\customfields\helper\RateGradeHelper;
?>
<div class="cmmrm-filter cmmrm-route-custom-field-grade-filter cmmrm-route-custom-field-grade-filter-<?php echo $field['meta_key']; ?>">
	<select name="route_custom_field_<?php echo $field['meta_key']; ?>">
		<option value="<?php //echo esc_attr(remove_query_arg($urlParam, $baseUrl)); ?>"><?php echo sprintf(Labels::getLocalized('cmmrmcf_filter_any'), str_replace(":", "",RateGradeHelper::getFieldLabel($field))); ?></option>
		<?php for ($i=1; $i<=5; $i++):
			//$url = add_query_arg($urlParam, $i, $baseUrl);
			$url = $i;
			printf('<option value="%s"%s>%s</option>', esc_attr($url), selected($i, $current, false), str_replace(":", "", RateGradeHelper::getFieldLabel($field)) .' '. $i);
		endfor; ?>
	</select>
</div>
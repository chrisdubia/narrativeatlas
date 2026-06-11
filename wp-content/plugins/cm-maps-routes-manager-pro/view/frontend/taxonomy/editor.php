<?php
use com\cminds\mapsroutesmanager\helper\FormHtml;
?>
<div class="cmmrm-field cmmrm-field-<?php echo $slug; ?>">
	<label><?php echo $label; ?></label>
	<?php
		//echo FormHtml::selectBox($fieldName, $options, $currentValue);
		echo FormHtml::checkboxTree($fieldName, $currentValue, $options);
	?>
</div>
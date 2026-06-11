<?php
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\addon\buddypress\model;
use com\cminds\mapsroutesmanager\addon\buddypress\helper\FormHtml;
?>
<div class="cmmrm-field cmmrm-field-categories">
	<div class="cmmrm-field-categories-inner">
		<label>
			<?php echo model\Labels::getLocalized('buddypress_groups'); ?>
			<?php echo '<img src="'.App::url('asset/img/editor/dropdown.png').'" class="cmmrm-field-categories-img" style="display:none;" />'; ?>
		</label>
		<?php
		echo '<div class="cmmrm_field_categories_row_container">';
		echo FormHtml::renderCheckboxGroup('bpgroups[]', $buddypressGroups, $buddypressCurrentGroups);
		echo '</div>';
		?>
	</div>
</div>
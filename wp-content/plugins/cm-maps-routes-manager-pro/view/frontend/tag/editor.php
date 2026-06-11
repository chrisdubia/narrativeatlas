<?php
use com\cminds\mapsroutesmanager\model\TaxonomyTerm;
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-field cmmrm-field-route-tags">
	<label><?php echo Labels::getLocalized('route_tags'); ?></label>
	<input type="text" name="tags" value="<?php echo esc_attr(implode(', ', $route->getTags(TaxonomyTerm::FIELDS_NAMES))); ?>" <?php echo ($route_form_tags == 'required')?'required="required"':''; ?> />
</div>
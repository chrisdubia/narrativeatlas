<?php
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\helper\FormHtml;
?>
<div class="cmmrm-zip-filter">
	<label class="cmmrm-zip-filter-code"><span><?php echo Labels::getLocalized('filter_zip_code'); ?></span><input type="text" name="zipcode" value="<?php echo esc_attr($zipcodeValue); ?>" /></label>
	<label class="cmmrm-zip-filter-radius"><span><?php echo Labels::getLocalized('filter_zip_radius'); ?></span><?php echo FormHtml::selectBox('zipradius', $radiusOptions, $radiusValue); ?></label>
</div>
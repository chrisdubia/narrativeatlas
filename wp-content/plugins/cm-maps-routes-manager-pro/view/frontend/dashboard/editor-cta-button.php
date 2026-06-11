<?php
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-field cmmrm-field-cta-button">
	<img src="<?php echo App::url('asset/img/editor/arrow.png'); ?>" class="cmmrm-field-cta-button-img" style="display:none;" />
	<label><?php echo Labels::getLocalized('dashboard_cta_button'); ?></label>
	<input type="text" name="cta-button-text" value="<?php echo esc_attr($route->getCtaButtonText()); ?>" placeholder="<?php echo Labels::getLocalized('dashboard_cta_button_text'); ?>" style="margin-bottom:5px;" />
	<input type="text" name="cta-button-url" value="<?php echo esc_attr($route->getCtaButtonUrl()); ?>" placeholder="<?php echo Labels::getLocalized('dashboard_cta_button_url'); ?>" />
</div>
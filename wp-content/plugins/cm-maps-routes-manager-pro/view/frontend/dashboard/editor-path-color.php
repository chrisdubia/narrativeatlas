<?php
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-field cmmrm-field-path-color">
	<img src="<?php echo App::url('asset/img/editor/color.png'); ?>" class="cmmrm-field-path-color-img" style="display:none;" />
	<label><?php echo Labels::getLocalized('dashboard_path_color'); ?></label>
	<input type="color" name="path-color" value="<?php echo esc_attr($route->getPathColor()); ?>" />
</div>
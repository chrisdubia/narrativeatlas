<?php
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\controller\DashboardController;
?>
<form method="post" action="<?php echo esc_attr($formAction); ?>" enctype="multipart/form-data" class="cmmrm-import-route">
	<?php echo ImportController::loadFrontendView('import-form-fields'); ?>
	<p>
		<input type="hidden" name="<?php echo $nonceField; ?>" value="<?php echo $nonce; ?>" />
		<input type="submit" value="<?php echo esc_attr(Labels::getLocalized('dashboard_import_form_submit_btn')); ?>" />
		<div class="loader" style="display:none"><?php echo Labels::getLocalized('dashboard_import_loader_text'); ?></div>
	</p>
</form>
<script type="text/javascript">
jQuery(function($) {
	$('.cmmrm-import-route').submit(function() {
		var form = $(this);
		form.find('input[type=submit]').hide();
		form.find('.loader').show();
	});
});
</script>
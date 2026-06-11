<?php
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\controller\ImportController;
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-field cmmrm-import-from-files">
	<div class="cmmrm-import-kml-link">
		<a href="#" class="cmmrm-import-kml-btn">
			<img src="<?php echo App::url('asset/img/editor/import.png'); ?>" class="cmmrm-import-kml-img" style="display:none;" />
			<?php echo Labels::getLocalized('editor_import_kml_gpx'); ?>
		</a>
	</div>
	<div class="cmmrm-import-kml-wrapper">
		<?php echo ImportController::loadFrontendView('import-form-fields'); ?>
		<p class="cmmrm_editor_import_kml_gpx_warning"><?php echo Labels::getLocalized('editor_import_kml_gpx_warning'); ?></p>
	</div>
</div>
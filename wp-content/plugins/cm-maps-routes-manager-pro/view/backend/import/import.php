<?php
use com\cminds\mapsroutesmanager\controller\ImportController;
?>
<div class="cmmrm-block">
	<form action="<?php echo esc_attr($formUrl); ?>" method="post" enctype="multipart/form-data" id="cmmrm-import-route-form" target="cmmrm-import-frame">
		<h3>Import Routes</h3>
		<?php echo ImportController::loadFrontendView('import-form-fields'); ?>
		<p>
			<input type="hidden" name="<?php echo $nonceField; ?>" value="<?php echo $nonce; ?>" />
			<input type="submit" value="Import" style="cursor:pointer;" />
		</p>
	</form>
	<iframe id="cmmrm-import-frame" name="cmmrm-import-frame"></iframe>
</div>
<div class="cmmrm-block">
	<form action="<?php echo esc_attr($formUrl); ?>" method="post" enctype="multipart/form-data" id="cmmrm-import-route-form-csv" target="cmmrm-import-frame-csv">
		<div style="clear:both;">
			<h3 style="float:left;">Import Routes From CSV with external GPX file</h3>
			<a href="<?php echo plugin_dir_url( __FILE__ ); ?>../../../asset/sample.csv" style="float:right;">Sample File</a>
		</div>
		<div class="cmmrm-field cmmrm-field-route-import">
			<label><input type="file" name="cmmrm_import_file_csv" accept=".csv"></label>
		</div>
		<p>
			<input type="hidden" name="<?php echo $nonceFieldCsv; ?>" value="<?php echo $nonceCsv; ?>" />
			<input type="submit" value="Import" style="cursor:pointer;" />
		</p>
	</form>
	<iframe id="cmmrm-import-frame-csv" name="cmmrm-import-frame-csv"></iframe>
</div>
<?php
use com\cminds\mapsroutesmanager\controller\DashboardController;
?>
<div class="cmmrm-block">
	<form action="<?php echo esc_attr($formUrl); ?>" method="post" enctype="multipart/form-data" id="cmmrm-export-form">
		<h3>Export All Routes</h3>
		<p>
			<input type="hidden" name="<?php echo $nonceField; ?>" value="<?php echo $nonce; ?>" />
			<input type="submit" value="Export" style="cursor:pointer;" />
		</p>
	</form>
</div>
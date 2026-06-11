<?php
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-msg cmmrm-msg-<?php echo $class; ?>">
	<div class="cmmrm-msg-inner">
		<span><?php echo apply_filters('cmmrm_dashboard_msg', Labels::getLocalized($msg), $msg, $class); ?></span>
		<div class="cmmrm-msg-extra"><?php echo $extra; ?></div>
	</div>
</div>
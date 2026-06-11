<?php
use com\cminds\mapsroutesmanager\App;
?>
<div class="cm-licensing-box">
	<?php
	if (App::isPro()) {
		echo do_shortcode('[cminds_pro_ads id=cmmrm]');
	} else {
		echo do_shortcode('[cminds_free_registration id="cmmrm"]');
	}
	?>
</div>
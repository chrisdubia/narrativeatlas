<?php
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-route-embed">
	<p class="route_embed_copy_html"><?php echo Labels::getLocalized('route_embed_copy_html'); ?></p>
	<textarea readonly><?php echo esc_html($iframe); ?></textarea>
	<button class="cmmrm-route-embed-copy-btn"><?php echo Labels::getLocalized('route_embed_copy_to_clipboard'); ?></button>
</div>
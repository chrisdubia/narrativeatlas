<?php
use com\cminds\mapsroutesmanager\shortcode\RouteSnippetShortcode;
use com\cminds\mapsroutesmanager\shortcode\RouteMapShortcode;
?>
<div class="cmmrm-embed-shortcode">
	<div>Route snippet:</div>
	<textarea readonly>[<?php echo RouteSnippetShortcode::SHORTCODE_NAME; ?> id=<?php echo $id; ?>]</textarea>
</div>
<div class="cmmrm-embed-shortcode">
	<div>Route map:</div>
	<textarea readonly>[<?php echo RouteMapShortcode::SHORTCODE_NAME; ?> id=<?php echo $id; ?>]</textarea>
</div>
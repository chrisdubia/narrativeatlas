<?php 
$mapId = mt_rand();
?><div class="cmmrm-route cmmrm-route-single cmmrm-shortcode-route-map" data-map-id="<?php echo $mapId;
	?>" data-route-id="<?php echo $id; ?>"><?php
	//echo do_shortcode(sprintf('[route-snippet id=%d]', $id));
	echo '<h2><a href="'. do_shortcode(sprintf('[route-permalink-url id=%s]', $id)) .'" target="_blank">'
			. do_shortcode(sprintf('[route-title id=%d escape=1]', $id)) . '</a></h2>';
	echo do_shortcode(sprintf('[route-map-canvas id=%d mapId=%d]', $id, $mapId));
	echo do_shortcode(sprintf('[route-elevation-graph id=%d mapId=%d]', $id, $mapId));
	echo do_shortcode(sprintf('[route-params id=%d]', $id));
?></div>
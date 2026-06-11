<?php
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-widget-related-routes">
	<?php echo $args['before_title']; ?><?php echo Labels::getLocalized('widget_title_related_routes'); ?><?php echo $args['after_title']; ?>
	<ul>
		<?php
		foreach ($routes as $route):
		printf('<li><a href="%s">%s</a></li>', esc_attr($route->getPermalink()), esc_html($route->getTitle()));
		endforeach;
		?>
	</ul>
</div>
<?php
use com\cminds\mapsroutesmanager\model\Labels;
$minFont = 100;
$maxFont = $minFont + 100;
?>
<div class="cmmrm-widget-tag-cloud">
	<?php echo $args['before_title']; ?><?php echo Labels::getLocalized('widget_title_tag_cloud'); ?><?php echo $args['after_title']; ?>
	<ul style="font-size:10px;">
		<?php
		foreach ($tags as $tag):
		$label = esc_html($tag->getName());
		$number = $tag->routes_number;
		if ($instance['show_numbers']) {
			$label .= ' ('. $number .')';
		}
		if ($maxNumber == $minNumber) $fontSize = 100;
		else $fontSize = ($number-$minNumber)/($maxNumber-$minNumber)*100 + $minFont;
		printf('<li style="font-size:%d%%;"><a href="%s">%s</a></li>', $fontSize, esc_attr($tag->getPermalink()), $label);
		endforeach;
		?>
	</ul>
</div>
<?php
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\TaxonomyTerm;
use com\cminds\mapsroutesmanager\model\Attachment;
?>
<?php do_action('cmmrm_before_single_route_details', $route); ?>
<div class="cmmrm-route cmmrm-route-single cmmrm-theme-style" data-map-style='<?php echo isset($mapStyle)?$mapStyle:'[]'; ?>' data-map-id="<?php echo $atts['mapId'];
	?>" data-route-id="<?php echo $route->getId();
	?>" data-fancy="<?php echo (Settings::getOption(Settings::OPTION_FANCY_STYLE_ENABLE) ? '1': '0');
	?>" data-fancy-border="<?php echo (Settings::getOption(Settings::OPTION_FANCY_BORDER) ? '1': '0');
	?>" <?php echo RouteView::getDisplayParams($displayParams); ?>>
	<?php do_action('cmmrm_route_single_before', $atts); ?>
	<?php get_template_part('cmmrm', 'route-single-map'); ?>
	<?php get_template_part('cmmrm', 'route-single-details'); ?>
	<?php get_template_part('cmmrm', 'route-single-locations'); ?>
</div>
<?php
if (Settings::getOption(Settings::OPTION_COMMENTS_ENABLE)) {
	comments_template('', true);
}
?>
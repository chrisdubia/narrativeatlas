<?php
use com\cminds\mapsroutesmanager\model;
use com\cminds\mapsroutesmanager\helper;

$link_share_page_id = model\Settings::getOption(model\Settings::OPTION_LINK_SHARE_PAGE_ID);

global $post;

$route_link_url = '';
$route_category_link_url = '';
if($link_share_page_id) {

	$route_link_url = get_the_permalink($link_share_page_id).'?route='.$post->post_name;

	$categories_id = '';
	$categories = $route->getCategories();
	if(count($categories) > 0) {
		foreach ($categories as $item) {
			$categories_id .= ','.$item->getSlug();
		}
		$categories_id = substr($categories_id, 1);
	}
	if($categories_id) {
		$route_category_link_url = get_the_permalink($link_share_page_id).'?category='.$categories_id;
	} else {
		$route_category_link_url = get_the_permalink($link_share_page_id);
	}
}

?>
<ul class="cmmrm-inline-nav cmmrm-toolbar">
	<?php do_action('cmmrm_route_single_toolbar_start', $route); ?>
	<li class="e10"><a href="<?php echo esc_attr(helper\RouteView::getRefererUrl()); ?>" title="<?php echo esc_attr(model\Labels::getLocalized('route_backlink')); ?>" class="dashicons dashicons-controls-back"></a></li>
	<?php
	if ($route->getTravelMode() !== 'DIRECT') { ?>
		<li class="e20"><a class="dashicons dashicons-list-view cmmrm-directions-steps-btn" href="#" title="Show directions steps"></a></li>
		<?php
	}
	if (model\Settings::getOption(model\Settings::OPTION_ROUTE_SHARE_LINK_SHOW)) {
		?>
		<li class="e30"><input type="text" value="<?php echo $route_link_url; ?>" id="cmmrm_top_route_input" style="opacity:0; position:absolute; top:100px; left:0;" /><a href="javascript:void(0);" onclick="cmmrm_top_route_link()" class="dashicons dashicons-share" title="<?php echo model\Labels::getLocalized('route_copy_route'); ?>"></a></li>
		<li class="e40"><input type="text" value="<?php echo $route_category_link_url; ?>" id="cmmrm_top_route_category_input" style="opacity:0; position:absolute; top:100px; left:0;" /><a href="javascript:void(0);" onclick="cmmrm_top_route_category_link()" class="dashicons dashicons-share-alt" title="<?php echo model\Labels::getLocalized('route_copy_route_category'); ?>"></a></li>
		<?php
	}
	do_action('cmmrm_route_single_toolbar_middle', $route);
	if (model\Settings::getOption(model\Settings::OPTION_ROUTE_FULLSCREEN_BTN_SHOW)) {
		?>
		<li class="e50" style="float:right"><a class="cmmrm-map-fullscreen-btn dashicons dashicons-editor-expand" href="#" title="<?php echo esc_attr(model\Labels::getLocalized('show_fullscreen_title')); ?>"></a></li>
		<?php
	}
	?>
	<li class="e60" style="float:right"><a class="cmmrm-map-center-btn dashicons dashicons-update" href="#" title="<?php echo esc_attr(model\Labels::getLocalized('show_all_locations')); ?>"></a></li>
	<?php do_action('cmmrm_route_single_toolbar_end', $route); ?>
</ul>

<?php if (model\Settings::getOption(model\Settings::OPTION_ROUTE_SHARE_LINK_SHOW)): ?>
<?php $writeSingleScript = function() { ?>
<script type="text/javascript">
function cmmrm_top_route_link() {
	var copyText = document.getElementById("cmmrm_top_route_input");
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	document.execCommand("copy");
	alert('<?php echo model\Labels::getLocalized("route_copied"); ?>');
} 
function cmmrm_top_route_category_link() {
	var copyText = document.getElementById("cmmrm_top_route_category_input");
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	document.execCommand("copy");
	alert('<?php echo model\Labels::getLocalized("route_copied"); ?>');
} 
</script>
<?php }; ?>
<?php
if (model\Settings::getOption(model\Settings::OPTION_SINGLE_ROUTE_MAP_SCRIPT_IN_FOOTER)):
	add_action('wp_footer', $writeSingleScript, 20);
else:
	$writeSingleScript();
endif;
?>
<?php endif; ?>

<?php do_action('cmmrm_route_toolbar_after', $route); ?>
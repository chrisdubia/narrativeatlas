<?php
use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Labels;

$link_share_enable = Settings::getOption(Settings::OPTION_LINK_SHARE_ENABLE);
$link_share_page_id = Settings::getOption(Settings::OPTION_LINK_SHARE_PAGE_ID);

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
<div class="cmmrm-route-details" data-id="<?php echo $route->getId(); ?>">
	<?php echo RouteController::loadFrontendView('route-images', compact('route')); ?>
	<?php echo RouteController::loadFrontendView('route-description', compact('route')); ?>
</div>
<?php if($link_share_enable == '1') { ?>
<div class="route_share_link_box">
	<h6><?php echo Labels::getLocalized('route_link_sharing'); ?></h6>
	<div class="route_share_link_row route_share_link">
		<input type="text" value="<?php echo $route_link_url; ?>" id="cmmrm_route_input" readonly="readonly" />
		<button onclick="cmmrm_route_link()" id="cmmrm_route_button"><?php echo Labels::getLocalized('route_copy_route'); ?></button>
	</div>
	<div class="route_share_link_row route_category_share_link">
		<input type="text" value="<?php echo $route_category_link_url; ?>" id="cmmrm_route_category_input" readonly="readonly" />
		<button onclick="cmmrm_route_category_link()" id="cmmrm_route_category_button"><?php echo Labels::getLocalized('route_copy_route_category'); ?></button>
	</div>
</div>
<script type="text/javascript">
function cmmrm_route_link() {
	var copyText = document.getElementById("cmmrm_route_input");
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	document.execCommand("copy");
	document.getElementById("cmmrm_route_button").innerHTML = '<?php echo Labels::getLocalized("route_copied"); ?>';
	setTimeout(function(){ document.getElementById("cmmrm_route_button").innerHTML = '<?php echo Labels::getLocalized("route_copy_route"); ?>'; }, 3000);
} 
function cmmrm_route_category_link() {
	var copyText = document.getElementById("cmmrm_route_category_input");
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	document.execCommand("copy");
	document.getElementById("cmmrm_route_category_button").innerHTML = '<?php echo Labels::getLocalized("route_copied"); ?>';
	setTimeout(function(){ document.getElementById("cmmrm_route_category_button").innerHTML = '<?php echo Labels::getLocalized("route_copy_route_category"); ?>'; }, 3000);
} 
</script>
<?php } ?>
<?php do_action('cmmrm_after_single_route_details', $route); ?>

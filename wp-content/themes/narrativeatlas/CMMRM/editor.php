<?php
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\controller\DashboardController;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\helper\FormHtml;
use com\cminds\mapsroutesmanager\controller\RouteController;
global $wpdb;
if (empty($route)) $route = null;
$route_form_description = Settings::getOption(Settings::OPTION_ROUTE_FORM_DESCRIPTION);
$route_form_status = Settings::getOption(Settings::OPTION_ROUTE_FORM_STATUS);
$route_form_settings = Settings::getOption(Settings::OPTION_ROUTE_FORM_SETTINGS);
$editor_instructions_button_enable = Settings::getOption(Settings::OPTION_EDITOR_INSTRUCTIONS_BUTTON_ENABLE);
$route_form_terms = Settings::getOption(Settings::OPTION_ROUTE_FORM_TERMS);
$route_form_osmtiles = Settings::getOption(Settings::OPTION_ROUTE_FORM_OSMTILES);
?>
<style>
@font-face {
  font-family: 'GothamRnd-Medium';
  src: URL('<?php echo App::url("asset/img/editor/fonts/GothamRnd-Medium.otf"); ?>') format('opentype');
}
@font-face {
  font-family: 'GothamRnd-Book';
  src: URL('<?php echo App::url("asset/img/editor/fonts/GothamRnd-Book.otf"); ?>') format('opentype');
}
</style>
<div class="cmmrm-route-editor">

	<?php do_action('cmmrm_route_editor_top', $route); ?>

	<form action="<?php echo esc_attr($formUrl); ?>" method="post" enctype="multipart/form-data" data-route-id="<?php echo $route->getId(); ?>">

		<div class="cmmrm-field cmmrm-field-route-name">
			<label><?php echo Labels::getLocalized('route_name'); ?></label>
			<input type="text" name="name" value="<?php echo esc_attr($route->getTitle()); ?>" required />
		</div>
		
		<?php if ($route_form_description != 'none') { ?>
		<div class="cmmrm-field cmmrm-field-description">
			<label><?php echo Labels::getLocalized('route_description'); ?></label>
			<?php if (Settings::getOption(Settings::OPTION_EDITOR_RICH_TEXT_ENABLE)): ?>
				<?php
				if(is_plugin_active('buddyboss-platform/bp-loader.php')) {
					?>
					<div class="apply-medium-editor-container">
						<textarea class="apply-medium-editor" name="description" <?php echo ($route_form_description == 'required')?'required="required"':''; ?>><?php echo esc_html($route->getContent()); ?></textarea>
					</div>
					<?php
				} else {
					wp_editor($route->getContent(), 'cmmrm_route_description', array('textarea_name' => 'description'));
				}
				?>
			<?php else :?>
				<textarea name="description" <?php echo ($route_form_description == 'required')?'required="required"':''; ?>><?php echo esc_html($route->getContent()); ?></textarea>
			<?php endif; ?>
		</div>
		<?php } ?>

		<?php
		if(Settings::getOption(Settings::OPTION_TRAVEL_MODE_SHOW) == 1) {
			?>
			<div class="cmmrm-field cmmrm-field-travel-mode">
				<div class="cmmrm-field-travel-mode-inner">
					<?php
					$travel_mode_array = array_combine(Route::$travelModes, array_map('strtolower', Route::$travelModes));
					if(count($travel_mode_array) > 0) {
						?>
						<select name="travel-mode" class="ui dropdown ui_dropdown_single">
							<option value=""><?php echo Labels::getLocalized('travel_mode'); ?>:</option>
							<?php
							foreach($travel_mode_array as $modekey=>$modeval) {
								if(get_post_meta($route->getId(), '_cmmrm_travel_mode', true) == $modekey) {
									echo '<option value="'.$modekey.'" selected="selected">'.Labels::getLocalized('travel_mode_'.$modeval).'</option>';
								} else {
									echo '<option value="'.$modekey.'">'.Labels::getLocalized('travel_mode_'.$modeval).'</option>';
								}
							}
							?>
						</select>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		} else {
			?>
			<input type="hidden" name="travel-mode" value="<?php echo esc_attr($route->getTravelMode()); ?>" />
			<?php
		}
		?>
		
		<?php if ($route_form_status != 'none') { ?>
		<div class="cmmrm-field cmmrm-field-route-status" <?php if(!current_user_can('manage_options') && Settings::getOption(Settings::OPTION_ROUTE_MODERATION_ENABLE)) { echo "style='display:none;'"; } ?>>
			<label><?php echo Labels::getLocalized('route_status'); ?></label>
			<?php echo FormHtml::selectBox('status', apply_filters('cmmrm_editor_allowed_statuses', array(
				'publish' => Labels::getLocalized('route_status_publish'),
				'draft' => Labels::getLocalized('route_status_draft'),
			),  $route), $route->getStatus()); ?>
		</div>
		<?php } else {
		?>
		<div class="cmmrm-field cmmrm-field-route-status" style="display:none;">
			<label><?php echo Labels::getLocalized('route_status'); ?></label>
			<?php echo FormHtml::selectBox('status', array(
				'publish' => Labels::getLocalized('route_status_publish'),
			), $route->getStatus()); ?>
		</div>
		<?php
		}
		?>
		
		<?php
		//do_action('cmmrm_route_editor_middle', $route);
		?>

		<div class="cmmrm-field cmmrm-field-categories">
			<div class="cmmrm-field-categories-inner">
				<?php
				$bp_groups_privacy = get_option('cmmrm_buddypress_groups_privacy_for_route', 'all');
				if($bp_groups_privacy == 'all') {
					$current_user_id = get_current_user_id();
					if($current_user_id) {
						$buddypressGroups = $wpdb->get_results("SELECT g.*, gm.is_admin FROM ".$wpdb->prefix."bp_groups as g, ".$wpdb->prefix."bp_groups_members as gm where g.id = gm.group_id and gm.user_id='".$current_user_id."' and gm.is_confirmed='1' order by g.name asc", ARRAY_A);
					} else {
						$buddypressGroups = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."bp_groups order by name asc", ARRAY_A);
					}
				} else {
					$buddypressGroups = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."bp_groups WHERE status='".$bp_groups_privacy."' order by name asc", ARRAY_A);
				}
				?>
				<select name="bpgroups[]" multiple="" class="ui fluid dropdown ui_dropdown_multiple">
					<option value=""><?php echo Labels::getLocalized('buddypress_groups'); ?>:</option>
					<?php

					if($route->getId()) {
						$buddypressCurrentGroups = get_post_meta($route->getId(), '_cmmrm_bp_groups', true);
						if($buddypressCurrentGroups == '') {
							$buddypressCurrentGroups = array();
						}
					} else {
						$buddypressCurrentGroups = array();
					}

					if(count($buddypressGroups) > 0) {
						foreach($buddypressGroups as $optionval) {
							if(in_array($optionval['id'], $buddypressCurrentGroups)) {
								echo '<option value="'.$optionval['id'].'" selected="selected">'.$optionval['name'].'</option>';
							} else {
								echo '<option value="'.$optionval['id'].'">'.$optionval['name'].'</option>';
							}
						}
					}
					?>
				</select>
			</div>
		</div>
		
		<?php if ($route_form_settings != 'none') { ?>
		<div class="cmmrm-field cmmrm-field-route-settings">
			<strong><?php echo Labels::getLocalized('dashboard_route_settings'); ?></strong>

			<?php
			$use_buddypress_collaborative_label = ' style="display:none;"';
			if(is_plugin_active('cm-maps-routes-buddypress-integration/cm-maps-routes-buddypress-integration.php') || is_plugin_active('cm-maps-routes-buddypress-addon/cm-maps-routes-buddypress-integration.php')) {
				$use_buddypress_collaborative_label = '';
			}
			?>
			<label class="use-buddypress-collaborative-label"<?php echo $use_buddypress_collaborative_label; ?>>
				<?php
				$checked_use_buddypress_collaborative = '';
				if($route->useBuddypressCollaborative()) {
					if($route->useBuddypressCollaborative() == '1') {
						$checked_use_buddypress_collaborative = 'checked="checked"';
					}
				}
				?>
				<input id="customcheckbox1" type="checkbox" name="use-buddypress-collaborative" value="1" <?php echo $checked_use_buddypress_collaborative; ?> />
				<label for="customcheckbox1" class="checker1"></label>
				<span><?php echo Labels::getLocalized('buddypress_collaborative'); ?></span>
			</label>
				
			<label class="use-minor-length-units-label">
				<?php
				$checked_route_setting_default_use_only_meters = '';
				if($route->useMinorLengthUnits()) {
					if($route->useMinorLengthUnits() == '1') {
						$checked_route_setting_default_use_only_meters = 'checked="checked"';
					}
				} else {
					if(Settings::getOption(Settings::OPTION_ROUTE_SETTING_DEFAULT_USE_ONLY_METERS) && !isset($_GET['id'])) {
						$checked_route_setting_default_use_only_meters = 'checked="checked"';
					}
				}
				?>
				<input id="customcheckbox2" type="checkbox" name="use-minor-length-units" value="1" <?php echo $checked_route_setting_default_use_only_meters; ?> />
				<label for="customcheckbox2" class="checker2"></label>
				<span><?php echo Labels::getLocalized('dashboard_use_minor_length_units'); ?></span>
			</label>

			<?php do_action('cmmrm_route_editor_route_settings', $route); ?>

		</div>
		<?php } ?>

		<?php do_action('cmmrm_route_editor_before_map', $route); ?>

		<?php
		if ($route_form_osmtiles != 'none') {
		$osmtiles = $route->getOsmtiles();
		?>
		<div class="cmmrm-field cmmrm-osmtiles">
			<label><?php echo Labels::getLocalized('route_osmtiles'); ?></label>
			<?php
			if(count($osmtiles) > 0) {
				$tilecounter = 0;
				$dashicons_class = ' dashicons-plus-alt';
				foreach($osmtiles as $tile) {
					if($tilecounter > 0) { $dashicons_class = ' dashicons-dismiss'; }
					?>
					<div class="row">
						<div class="left">
							<input type="text" name="osmtiles[]" value="<?php echo $tile; ?>" />
						</div>
						<div class="right">
							<span class="plus dashicons<?php echo $dashicons_class; ?>"></span>
						</div>
					</div>
					<?php
					$tilecounter++;
				}
			} else {
				?>
				<div class="row">
					<div class="left">
						<input type="text" name="osmtiles[]" value="" />
					</div>
					<div class="right">
						<span class="plus dashicons dashicons-plus-alt"></span>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php } ?>
		
		<?php
		if($editor_instructions_button_enable == '1') {
			?>
			<div class="cmmrm-field cmmrm-editor-instructions-container">
				<a href="" class="cmmrm-editor-instructions-btn">
					<img src="<?php echo App::url('asset/img/editor/instructions.png'); ?>" />
					<?php echo Labels::getLocalized('instructions_btn') ?>
				</a>
			</div>
			<?php
		}
		?>
		
		<?php
		if($editor_instructions_button_enable == '1') {
			?>
			<div class="cmmrm-editor-instructions">
				<?php echo Settings::getOption(Settings::OPTION_LABEL_EDITOR_INSTRUCTION); ?>
			</div>
		<?php
		}
		?>

		<div id="cmmrm-editor-map">		
			<ul class="cmmrm-inline-nav cmmrm-toolbar">
				<li class="rem-separator">
					<ul class="cmmrm-locations-editor-mode">
						<?php
						if(Settings::getOption(Settings::OPTION_EDITOR_TABS_FLIP_ENABLE)) {
							?>
							<li class="current">
								<a href="" data-mode="waypoint" title="<?php
								echo esc_attr(Labels::getLocalized('editor_draw_path_mode_btn_desc')); ?>">
									<img src="<?php echo App::url('asset/img/editor/draw.png'); ?>" />
									<span><?php echo Labels::getLocalized('editor_draw_path_mode_btn_text'); ?></span>
								</a>
							</li>
							<li>
								<img src="<?php echo App::url('asset/img/editor/arrow.png'); ?>" class="arrowimg" />
							</li>
							<li>
								<a href="" data-mode="location" title="<?php
								echo esc_attr(Labels::getLocalized('editor_add_locations_mode_btn_desc')); ?>">
									<img src="<?php echo App::url('asset/img/editor/addl.png'); ?>" />
									<span><?php echo Labels::getLocalized('editor_add_locations_mode_btn_text'); ?></span>
								</a>
							</li>
							<?php
						} else {
							?>
							<li class="current">
								<a href="" data-mode="location" title="<?php
								echo esc_attr(Labels::getLocalized('editor_add_locations_mode_btn_desc')); ?>">
									<img src="<?php echo App::url('asset/img/editor/addl.png'); ?>" class="arrowimg" />
									<span><?php echo Labels::getLocalized('editor_add_locations_mode_btn_text'); ?></span>
								</a>
							</li>
							<li>
								<img src="<?php echo App::url('asset/img/editor/arrow.png'); ?>" />
							</li>
							<li>
								<a href="" data-mode="waypoint" title="<?php
								echo esc_attr(Labels::getLocalized('editor_draw_path_mode_btn_desc')); ?>">
									<img src="<?php echo App::url('asset/img/editor/draw.png'); ?>" />
									<span><?php echo Labels::getLocalized('editor_draw_path_mode_btn_text'); ?></span>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
				</li>
				<li class="right">
					<div class="cmmrm-find-location-container">
						<img src="<?php echo App::url('asset/img/editor/search.png'); ?>" />
						<input type="text" class="cmmrm-find-location" placeholder="<?php echo esc_attr(Labels::getLocalized('dashboard_map_search')); ?>" />
					</div>
				</li>
			</ul>
			
			<div id="cmmrm-editor-map-canvas"></div>
			
			<?php do_action('cmmrm_route_editor_after_map', $route); ?>
			
			<?php do_action('cmmrm_route_single_after_map', $route, array()); ?>
			
			<?php if (!App::isPro() OR Settings::getOption(Settings::OPTION_EDITOR_TRAVEL_MODE_SHOW)): ?>
				<?php echo RouteView::getTravelModeMenu($route->getTravelMode()); ?>
			<?php endif; ?>
			
			<?php
			echo '<div class="cmmrm-route-params-edit"';
			echo RouteView::getDisplayParams(Settings::getOption(Settings::OPTION_EDITOR_VISIBLE_ROUTE_PARAMS));
			echo '>';
			echo RouteController::loadFrontendView('route-params', compact('route'));
			echo '</div>';
			?>

			<div class="recalculate-btn-container">
				<div class="cmmrm-btn cmmrm-route-params-recalculate-btn"><img src="<?php echo App::url('asset/img/editor/recalculate.png'); ?>" class="recalculateimgicon" /><span class="text"><?php echo Labels::getLocalized('recalculate_route_params'); ?></span></div>

				<div class="cmmrm-btn cmmrm-custom-params-recalculate-btn"><img src="<?php echo App::url('asset/img/editor/recalculate.png'); ?>" class="recalculateimgicon" /><span class="text"><?php echo Labels::getLocalized('recalculate_custom_params'); ?></span></div>
				
				<input type="hidden" name="routeid" value="<?php echo esc_attr($route->getId()); ?>" />
				<input type="hidden" name="distance" value="<?php echo esc_attr($route->getDistance()); ?>" />
				<input type="hidden" name="duration" value="<?php echo esc_attr($route->getDuration()); ?>" />
				<input type="hidden" name="avg-speed" value="<?php echo esc_attr($route->getAvgSpeed()); ?>" />
				<input type="hidden" name="max-elevation" value="<?php echo esc_attr($route->getMaxElevation()); ?>" />
				<input type="hidden" name="min-elevation" value="<?php echo esc_attr($route->getMinElevation()); ?>" />
				<input type="hidden" name="elevation-gain" value="<?php echo esc_attr($route->getElevationGain()); ?>" />
				<input type="hidden" name="elevation-descent" value="<?php echo esc_attr($route->getElevationDescent()); ?>" />
				<input type="hidden" name="overview-path" value="<?php echo esc_attr($route->getOverviewPath()); ?>" />
				<input type="hidden" name="waypoints-string" value="<?php echo esc_attr($route->getWaypointsString()); ?>" />
				<input type="hidden" name="directions-response" value="" />
				<input type="hidden" name="elevation-response" value="" />
                <input type="hidden" name="paths" value="" />
			</div>
			
		</div>
		
		<div id="cmmrm-editor-locations">
			<h4><?php echo Labels::getLocalized('locations_markers'); ?></h4>
			<ul class="cmmrm-locations-list">
				<li data-id="0" style="display:none">
					<input class="location-id" type="hidden" name="locations[id][]" value="0" />
					<input class="location-name" type="text" name="locations[name][]" value="" placeholder="<?php echo esc_attr(Labels::getLocalized('location_name')); ?>" />
					<input class="location-lat" type="text" name="locations[lat][]" value="" placeholder="<?php echo esc_attr(Labels::getLocalized('location_latitude')); ?>" />
					<input class="location-long" type="text" name="locations[long][]" value="" placeholder="<?php echo esc_attr(Labels::getLocalized('location_longitude')); ?>" />
					<input class="location-address" type="text" name="locations[address][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_address')); ?>" placeholder="<?php echo esc_attr(Labels::getLocalized('location_address')); ?>" />
					<input class="location-linktext" type="text" name="locations[linktext][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_linktext')); ?>" placeholder="<?php echo esc_attr(Labels::getLocalized('location_linktext')); ?>" />
					<input class="location-linkurl" type="text" name="locations[linkurl][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_linkurl')); ?>" placeholder="<?php echo esc_attr(Labels::getLocalized('location_linkurl')); ?>" />
					<input class="location-distance" type="number" name="locations[distance][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_distance')); ?>" placeholder="<?php echo esc_attr(Labels::getLocalized('location_distance')); ?>" min="0" step="0.1" />
					<input class="location-type" type="hidden" name="locations[type][]" value="location" />
					<?php /* <input type="button" class="cmmrm-location-convert" value="<?php echo esc_attr(Labels::getLocalized('dashboard_location_convert_btn')); ?>" /> */ ?>
					<div>
						<?php
						$editorClass = "";
						if (Settings::getOption(Settings::OPTION_EDITOR_RICH_TEXT_ENABLE)) {
							if(is_plugin_active('buddyboss-platform/bp-loader.php')) {
								$editorClass = " apply-medium-editor-loop";
							} else {
								$editorClass = " tinyMCEeditor";
							}
						}
						?>
						<textarea class="location-description<?php echo $editorClass; ?>" name="locations[description][]" placeholder="<?php echo esc_attr(Labels::getLocalized('location_description')); ?>"></textarea>
					</div>
					<?php do_action('cmmrm_route_editor_location_bottom', $route); ?>
					<div class="cmmrm-location-remove-outer-container">
						<div class="cmmrm-location-remove-outer">
							<input type="button" class="cmmrm-btn cmmrm-location-remove" value="<?php echo esc_attr(Labels::getLocalized('dashboard_location_remove_btn')); ?>" />
						</div>
					</div>
				</li>
			</ul>
			
			<?php if (!defined('CMMRM_ROUTE_JS')): define('CMMRM_ROUTE_JS', 1); ?>
				<?php add_action('wp_footer', function() use ($route) { ?>
					<script type="text/javascript">
					jQuery(function($) {
						$('.cmmrm-images').each(CMMRM_Editor_Images_init);
						var routeData = <?php echo json_encode($route->getJSRouteData()); ?>;
						var waypointsString = <?php echo json_encode($route->getWaypointsString()); ?>;
						var locations = <?php echo json_encode($route->getJSLocations()); ?>;
						var editor = new CMMRM_Editor('cmmrm-editor-map-canvas', routeData, waypointsString, locations);
						<?php do_action('cmmrm_editor_wp_footer_js', $route); ?>
					});
					</script>
				<?php }); ?>
			<?php endif; ?>
			
		</div>
		
		<?php do_action('cmmrm_editor_before_form_summary', $route); ?>

		<?php if ($route_form_terms != 'none' && !$route->getId()) { ?>
		<div class="form-terms">
			<input type="checkbox" name="terms-conditions" required />
			<?php echo Labels::getLocalized('route_terms'); ?>
		</div>
		<?php } ?>
		
		<div class="form-summary">
			<input type="hidden" name="<?php echo esc_attr(DashboardController::EDITOR_NONCE); ?>" value="<?php echo esc_attr($nonce); ?>" class="cmmrm-nonce" />
			
			<span class="cmmrm-img-input-btn blue">
				<img src="<?php echo App::url('asset/img/editor/publish.png'); ?>" />
				<input type="submit" name="btn_save" value="<?php echo esc_attr(Labels::getLocalized('dashboard_save_btn')); ?>" class="button button-primary addeditroute" />
			</span>
			
			<span class="cmmrm-img-input-btn grey">
				<img src="<?php echo App::url('asset/img/editor/save.png'); ?>" />
				<input type="submit" name="cmmrm_btn_draft" value="<?php echo esc_attr(Labels::getLocalized('dashboard_save_draft_btn')); ?>" class="button button-primary addeditdraftroute" />
			</span>

			<?php if($route->getId()) { ?>
			<input type="button" name="btn_remove" value="<?php echo esc_attr(Labels::getLocalized('dashboard_remove_btn')); ?>" class="button button-primary btn_remove" data-id="<?php echo $route->getId(); ?>" />
			<?php } ?>
		</div>
	
	</form>
	
	<?php
	if($route->getId()) {

	$link_share_enable = Settings::getOption(Settings::OPTION_ROUTE_FORM_LINKSHARING);
	$link_share_page_id = Settings::getOption(Settings::OPTION_LINK_SHARE_PAGE_ID);
	
	$post = get_post($route->getId());

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

	<?php if($link_share_enable == 'optional') { ?>
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
	<?php }} ?>

	<?php echo apply_filters( 'cmmrm_add_route_below_form', '' ); ?>
	
</div>

<script src="https://semantic-ui.com/dist/semantic.min.js"></script>
<link rel="stylesheet" type="text/css" class="ui" href="https://semantic-ui.com/dist/semantic.min.css" />
<script>
jQuery('.ui_dropdown_single').dropdown({
	clearable: false,
	placeholder: 'value'
});
jQuery('.ui_dropdown_multiple').dropdown({
	allowAdditions: true
});
</script>
<?php
if(is_plugin_active('buddyboss-platform/bp-loader.php')) {
?>
<link rel='stylesheet' href='<?php echo get_site_url(); ?>/wp-content/plugins/buddyboss-platform/bp-core/css/medium-editor.min.css' type='text/css' media='all' />
<link rel='stylesheet' href='<?php echo get_site_url(); ?>/wp-content/plugins/buddyboss-platform/bp-core/css/medium-editor-beagle.min.css' type='text/css' media='all' />
<style>
.medium-editor-hidden { display:none !important; }
.apply-medium-editor { background: #fff; font-size: 16px; padding: 5px; }
.apply-medium-editor-loop { background: #fff; border-top: 1px solid #dedfe2; border-left: 1px solid #dedfe2; border-right: 1px solid #dedfe2; font-size: 16px; padding: 5px; }
.medium-editor-toolbar { position: initial; background-color: #fff; z-index: 9999 !important; visibility: visible !important; border-radius:0px !important; }
.apply-medium-editor-container { border: 1px solid #dedfe2; background:#fff; }
.medium-editor-toolbar li .medium-editor-action-pre { padding:10px 0; }
.medium-editor-toolbar .medium-editor-toolbar-actions { padding: 0; background:#fff; border-radius:10px !important; }
.medium-editor-toolbar li button:hover { background-color:#fff; color:#000; }
.medium-editor-toolbar li button:active { box-shadow:none; }
.medium-editor-toolbar li .medium-editor-button-active { background-color:#fff; }
.medium-editor-toolbar .medium-editor-toolbar-actions li { border-radius:0; }
.medium-editor-toolbar .medium-editor-toolbar-actions button:hover { box-shadow:none !important; }
.medium-editor-toolbar .medium-editor-toolbar-actions button:focus { box-shadow:none !important; }
.medium-editor-toolbar li .medium-editor-button-active { color:#000; }
.cmmrm-locations-list .medium-editor-toolbar { border-left:1px solid #ddd; border-right:1px solid #ddd; border-bottom:1px solid #ddd; }
.cmmrm-locations-list .cmmrm-location-icon { margin-top:20px; }
</style>
<script type='text/javascript' src='<?php echo get_site_url(); ?>/wp-content/plugins/buddyboss-platform/bp-core/js/vendor/medium-editor.min.js'></script>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.cmmrm-graph-loader').remove();
	
	if(typeof window.MediumEditor !== 'undefined') {
		if(jQuery('.apply-medium-editor').length) {
			window.forums_medium_forum_editor = [];
			jQuery('.apply-medium-editor').each(function(i, element) {
				var key = jQuery(element).data('key');
				var $this = jQuery(this);
				var whatsnewcontent = $this.closest('div')[0];
				window.forums_medium_forum_editor[key] = new window.MediumEditor( element, {
					placeholder: {
						text: '',
						hideOnClick: true
					},
					toolbar: {
						buttons: [ 'bold', 'italic', 'unorderedlist', 'orderedlist', 'quote', 'anchor', 'pre' ],
						relativeContainer: whatsnewcontent,
						static: true,
						updateOnEmptySelection: true
					},
					imageDragging: false
				});
			});
		}
	}

});
</script>
<?php
} else {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.cmmrm-graph-loader').remove();
});
</script>
<?php
}
?>
<script>
jQuery(document).ready(function() {
	jQuery('body').on('click', '.cmmrm-field-path-color-img', function() {
		jQuery('input[name="path-color"]').click();
	});
	jQuery('body').on('click', '.cmmrm-field-path-color > label', function() {
		jQuery('input[name="path-color"]').click();
	});
	jQuery('body').on('click', '.cmmrm-images > label', function() {
		jQuery(this).parent('.cmmrm-images').find('.cmmrm-images-upload').click();
	});
	jQuery('body').on('click', '.cmmrm-images > span', function() {
		jQuery(this).parent('.cmmrm-images').find('.cmmrm-images-upload').click();
	});
	jQuery('body').on('click', '.cmmrm-images > img.imgicon', function() {
		jQuery(this).parent('.cmmrm-images').find('.cmmrm-images-upload').click();
	});
});
jQuery(window).load(function() {
	jQuery("#cmmrm-editor-locations ul.cmmrm-locations-list > li").each(function(index) {
		jQuery(this).find(".cmmrm-video").append(jQuery(this).find('.cmmrm-location-remove-outer-container .cmmrm-location-remove-outer').html());
		jQuery(this).find('.cmmrm-location-remove-outer-container').remove();
	});
	jQuery('.cmmrm-import-kml-btn').unbind('click');
	jQuery('.cmmrm-import-kml-btn').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		jQuery(this).parents('.cmmrm-import-from-files').find('input[name="cmmrm_import_file"]').removeAttr('disabled').trigger('click');
	});
});
</script>
<style>
body .cmmrm-inline-nav.cmmrm-route-travel-mode li a > * { vertical-align: initial !important; }
body { background-color:#fff !important; }
body .cmmrm-route-editor { font-weight:normal !important; font-family: "GothamRnd-Medium" !important; }

body .cmmrm-route-editor .cmmrm-field label { font-weight: normal !important; font-size: 16px !important; padding-left: 20px !important; padding-bottom: 15px !important; color:#666666 !important; font-family: "GothamRnd-Medium" !important; }
body .cmmrm-route-editor .cmmrm-field.cmmrm-field-route-settings label { padding-bottom: 0px !important; }
body .cmmrm-route-editor .cmmrm-field input[type="text"] { color: #666666 !important; height: 64px !important; background: #FFFFFF 0% 0% no-repeat padding-box !important; box-shadow: 0px 3px 6px #00000029 !important; border-radius: 20px !important; opacity: 1 !important; padding-left: 20px !important; padding-right: 20px !important; }

body .cmmrm-field-description { margin-top: 60px !important; float: left !important; width: 100% !important; margin-bottom: 60px !important; color:#666666 !important; }
body .cmmrm-route-editor input[type="text"] { color: #666666 !important; }
body .cmmrm-route-editor input[type="text"]::placeholder { color: #999999 !important; }
body .cmmrm-route-editor input[type="text"]:-ms-input-placeholder { color: #999999 !important; }
body .cmmrm-route-editor input[type="text"]::-ms-input-placeholder { color: #999999 !important; }
body .medium-editor-element { color: #666666 !important; min-height:130px !important; padding-top:10px !important; padding-left:0px !important; font-family: "GothamRnd-Book" !important; }
body .apply-medium-editor-container { box-shadow: 0px 3px 6px #00000029 !important; border-radius: 20px !important; opacity: 1 !important; padding-left: 20px !important; padding-right: 20px !important; }

body .ui.label { background-color:#fff !important; color:#000 !important; font-weight:normal !important; }
body .ui_dropdown_multiple  .ui.label { color: #999 !important; font-size: 16px !important; font-family: "GothamRnd-Medium" !important; }
body .ui.selection.dropdown { box-shadow: 0px 3px 6px #00000029 !important; }
body .ui.selection.dropdown:hover { box-shadow: 0px 3px 6px #00000029 !important; }
body .ui.selection.dropdown > .dropdown.icon { background: transparent !important; box-shadow: none !important; color:#fff !important; }
body .ui.selection.dropdown > .dropdown.icon:not(.clear) { background-image:url("<?php echo App::url('asset/img/editor/dropdown.png'); ?>") !important; width: 30px !important; height: 30px !important; background-repeat: no-repeat !important; opacity: 1; background-size: cover !important; margin-top: -5px; margin-right: -5px; }
body .ui.dropdown > .dropdown.icon:not(.clear)::before { content:''; }
body .ui.dropdown { min-width:250px !important; }
body .ui.selection.dropdown .menu > .item { color: #999999 !important; font-weight:normal; font-size: 16px !important; font-family: "GothamRnd-Book" !important; }
body .ui.selection.dropdown .menu > .item:hover { color: #999999 !important; font-weight:normal; font-size: 16px !important; font-family: "GothamRnd-Book" !important; }

body .cmmrm-field.cmmrm-field-travel-mode { float:left; width:50%; clear:initial; }
body .cmmrm-field.cmmrm-field-travel-mode .cmmrm-field-travel-mode-inner { width: 60%; margin: auto; }
body .ui_dropdown_single { background:#0E6AAA !important; color:#fff !important; border-radius:10px !important; width:300px !important; }
body .ui_dropdown_single:not(.button) > .text { color:#fff !important; font-size: 16px !important; font-family: "GothamRnd-Medium" !important; }

body .cmmrm-field.cmmrm-field-categories { float:left; width:50%; clear:initial; }
body .cmmrm-field.cmmrm-field-categories .cmmrm-field-categories-inner { width: 60%; margin: auto; }
body .ui_dropdown_multiple { background:#0E6AAA !important; color:#fff !important; border-radius:10px !important; width:300px !important; }
body .ui_dropdown_multiple:not(.button) > .text { color:#fff !important; }

body .cmmrm-route-editor .cmmrm-field strong { font-size: 16px !important; padding-left: 20px !important; font-family: "GothamRnd-Medium" !important; }
body .cmmrm-route-editor .cmmrm-field label span { color:#999999 !important; font-size:16px !important; font-family: "GothamRnd-Book" !important; font-weight:normal !important; }
body .cmmrm-field.cmmrm-field-route-settings { clear:both; margin-bottom: 40px; float: left; width: 100%; margin-top:20px; }
body .cmmrm-field.cmmrm-field-route-settings strong { font-weight: normal !important; clear:both; float:left; width:100%; margin-bottom:20px; color:#666666 !important; }
body .cmmrm-field.cmmrm-field-route-settings label { float:left; width:50%; clear:initial; }

body .cmmrm-field.cmmrm-field-path-color { float:left; width:50%; clear:both; padding-left:65px; margin-bottom: 0.5em !important; }
body .cmmrm-field.cmmrm-field-path-color .cmmrm-field-path-color-img { display: block !important; float:left; margin-top: -4px; height: 32px; margin-right:10px; }
body .cmmrm-field.cmmrm-field-path-color label { padding-left:0px !important; color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight:normal !important; margin-right:10px; float:left; }
body .cmmrm-field.cmmrm-field-path-color input { float:left; height: 25px; width: 100px; }

body .cmmrm-field.cmmrm-images { float:left; width:50%; clear:initial; padding-left:44px; margin-bottom: 0.5em !important; }
body .cmmrm-images { width: 100%; float: left; clear: both; }
body .cmmrm-images .imgicon { display:block !important; float:left; width:40px; margin-right:10px; }

body .cmmrm-field.cmmrm-import-from-files { float:left; width:50%; clear:both; padding-left:65px; }
body .cmmrm-field.cmmrm-editor-instructions-container { float:left; width:50%; clear:initial; padding-left:65px; }

body .cmmrm-location-icon { display:none; }

body .cmmrm-route-editor input[type="submit"] { margin:0px; }

body .cmmrm-img-input-btn { display: inline-block; position: relative; padding:0px 14px; border-radius:10px; margin-top:30px; box-shadow: 0px 3px 6px #00000029; }
body .cmmrm-img-input-btn img { vertical-align:middle !important; }
body .cmmrm-img-input-btn.blue { background-color:#0E6AAA !important; color:#fff !important; }
body .cmmrm-img-input-btn.blue .button-primary { background-color:#0E6AAA !important; color:#fff !important; box-shadow:none !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; }
body .cmmrm-img-input-btn.blue .button-primary:hover { background-color:#0E6AAA !important; color:#fff !important; box-shadow:none !important; }
body .cmmrm-img-input-btn.grey { background-color:#656C71 !important; color:#fff !important; margin-left:15px; }
body .cmmrm-route-editor .form-summary .button-primary.addeditdraftroute { background-color:#656C71 !important; color:#fff !important; box-shadow:none !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; }
body .cmmrm-route-editor .form-summary .button-primary.addeditdraftroute:hover { background-color:#656C71 !important; color:#fff !important; box-shadow:none !important; }

body .cmmrm-img-input-btn.blue img { width:20px; }
body .cmmrm-img-input-btn.grey img { width:26px; }

body .cmmrm-route-params-recalculate-btn { margin-right:20px !important; }
body .cmmrm-custom-params-recalculate-btn { margin-left:20px !important; }

body .cmmrm-btn { font-weight:normal !important; background-color:#0E6AAA !important; color:#fff !important; border-radius:10px !important; padding:15px 30px !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; box-shadow: 0px 3px 6px #00000029; }
body .cmmrm-btn:hover { font-weight:normal !important; background-color:#0E6AAA !important; color:#fff !important; border-radius:10px !important; padding:15px 30px !important; }
body .cmmrm-route-editor input[type="button"].btn_remove { font-weight:normal !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; background-color:#0E6AAA !important; color:#fff !important; border-radius:10px !important; padding:0px 30px !important; box-shadow: 0px 3px 6px #00000029 !important; }
body .cmmrm-route-editor input[type="button"].btn_remove:hover { font-weight:normal !important; background-color:#0E6AAA !important; color:#fff !important; border-radius:10px !important; padding:0px 30px !important; }

body .cmmrm-route-editor .form-summary .button-primary { font-weight:normal !important; padding:0 10px !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; }

body .cmmrm-video { width: 100%; float: left; clear: both; margin-top:30px; }
body .cmmrm-video .imgicon { display:block !important; float:left; width:40px; }
body .cmmrm-video .cmmrm-add-video-btn { background: transparent !important; color: #000 !important; padding:10px 10px !important; }

body .cmmrm-locations-list > li { width:100% !important; clear:both !important; border-radius:10px !important; padding:2em; background:#FAFBFD !important; float:left !important; border: 1px solid #DBDBDB; }
body .cmmrm-locations-list > li > div .medium-editor-element { border-radius: 10px 10px 0px 0px !important; border:2px solid #e6e9ee; border-bottom:none; }
body .cmmrm-locations-list > li > div .medium-editor-toolbar { border-radius: 0px 0px 10px 10px !important; border:2px solid #e6e9ee; border-top:none; }

body .cmmrm-route-editor ul.cmmrm-route-params { background:#FAFBFD; border: 1px solid #DBDBDB; border-radius: 10px; opacity: 1; }
body .cmmrm-route-editor ul.cmmrm-route-params li strong { font-size:16px !important; font-family: "GothamRnd-Medium" !important; }

body .recalculate-btn-container { text-align: center; margin-bottom:30px; }
body .recalculateimgicon { vertical-align: middle; margin-right: 20px; width: 20px; }

body .cmmrm-inline-nav.cmmrm-route-travel-mode li a { border-color: #A7A7A7 !important; border-width:1px !important; text-transform:capitalize !important; padding:0 25px !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; border: 1px solid #DBDBDB !important; border-radius: 10px !important; }
body .cmmrm-inline-nav.cmmrm-route-travel-mode li a:hover, .cmmrm-inline-nav.cmmrm-route-travel-mode li a.current { border-width:1px !important; border-color: #A7A7A7 !important; background: #F5F5F5 !important; color:#A7A7A7 !important; border: 1px solid #DBDBDB !important; border-radius: 10px !important; }

body .travelModeImgs { display: block !important; float: left; margin-right: 10px; height: 24px; margin-top: 10px; }
body .travelModeIcons { display:none !important; }

body .cmmrm-find-location-container { width: 100%; margin-bottom: 0px; }
body .cmmrm-find-location-container img { position: absolute; padding: 10px; width: 36px; margin-top:6px; }
body .cmmrm-find-location-container .cmmrm-find-location { width: 96% !important; padding: 10px 10px 10px 30px !important; margin-top:8px !important; margin-right:15px !important; box-shadow: 0px 3px 6px #00000029; border-radius: 10px; opacity: 1; text-align: right; color:#999999 !important; }

body .cmmrm-inline-nav li { margin-bottom: 0px !important; }
body .cmmrm-toolbar li a { color:#A7A7A7 !important; }
body .cmmrm-toolbar li.current a { color:#A7A7A7 !important;  background:#f9f9f9 !important; }
body .cmmrm-toolbar a:hover { background:#f9f9f9 !important; }
body .cmmrm-toolbar li a img { height:32px; vertical-align:top; margin-right:5px; }

body .cmmrm-route-editor .cmmrm-editor-instructions-btn { background:transparent !important; padding:0 !important; color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight:normal !important; }
body .cmmrm-route-editor .cmmrm-editor-instructions-btn img { vertical-align: middle; height: 32px; margin-right:10px; }

body .cmmrm-route-editor .cmmrm-import-kml-btn { background:transparent !important; padding:0 !important; color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight:normal !important; line-height:30px !important; }
body .cmmrm-route-editor .cmmrm-import-kml-btn .cmmrm-import-kml-img { display: block !important; float:left; margin-top: 2px; height: 32px; margin-right:10px; }

body .cmmrm-images-img { display: block !important; float:left; margin-top: -4px; height: 22px; margin-right:8px; }
body .dashboard_upload_route_image { display:none; }
body .cmmrm-add-video-btn { display:none; }

body #cmmrm-editor-locations h4 { color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight: normal !important; margin-bottom: 0 !important; margin-left: 10px !important; }

body .cmmrm-field.cmmrm-images label { color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight:normal !important; margin-right:10px; float:left; }
body .cmmrm-images { margin-top:0px; }
body .cmmrm-locations-list .cmmrm-images { margin-top:40px !important; padding-left: 30px; }
body .cmmrm-locations-list .cmmrm-images-list { margin-top:20px !important; }

body .cmmrm-images-list { float: left; width: 100%; margin-left: 20px !important; margin-top: 15px !important; }
body .cmmrm-locations-list .cmmrm-images-list { margin-left: 0px !important; }
body .cmmrm-route-editor .cmmrm-field-desc { clear:both; color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight:normal !important; margin-left:20px; }
body .cmmrm-field.cmmrm-field-route-import { margin-bottom:10px; }
body .cmmrm-field.cmmrm-field-route-import span { clear:both; color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight:normal !important; }
body p.cmmrm_editor_import_kml_gpx_warning { clear:both; color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight:normal !important; }

body .cmmrm-locations-list .cmmrm-add-video-btn { margin: 0 !important; display:inline-block; clear:both; color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight:normal !important; padding-left:2px !important; padding-top: 2px !important; box-shadow:none !important; line-height:24px !important; }
body .cmmrm-locations-list .cmmrm-add-video-btn:hover { margin: 0 !important; display:inline-block; clear:both; color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; font-weight:normal !important; padding-left:2px !important; padding-top: 2px !important; background-color:transparent !important; padding-bottom: 10px !important; }

body .cmmrm-locations-list .location-distance { display:none !important; }

body .cmmrm-field-route-settings input[type="checkbox"] { vertical-align:text-top !important; }

body ul.cmmrm-locations-editor-mode { margin:0 !important; }

body ul.cmmrm-route-travel-mode li { margin-left: 40px !important; }
body ul.cmmrm-route-travel-mode li strong { color:#666666 !important; font-weight:normal !important; font-size: 16px !important; font-family: "GothamRnd-Medium" !important; }
body .cmmrm-inline-nav.cmmrm-route-travel-mode li + li { margin-left: 40px !important; }

body .cmmrm-location-remove { font-weight:normal !important; line-height:5px !important; float:right; box-shadow: 0px 3px 6px #00000029 !important; }
body .cmmrm-location-remove:hover { font-weight:normal !important; box-shadow: 0px 3px 6px #00000029 !important; }

body #cmmrm-editor-map-canvas button { border-radius: initial !important; }
body #cmmrm-editor-map .cmmrm-toolbar { border-radius:10px 10px 0px 0px !important; padding-top: 5px !important; }
body #cmmrm-editor-map .cmmrm-toolbar li.rem-separator { border-radius:10px 0px 0px 0px !important; }
body #cmmrm-editor-map .cmmrm-toolbar li.rem-separator ul { border-radius:10px 0px 0px 0px !important; }
body #cmmrm-editor-map .cmmrm-toolbar li.rem-separator ul li:first-child { border-radius:10px 0px 0px 0px !important; }
body #cmmrm-editor-map .cmmrm-toolbar li.rem-separator ul li:first-child a { border-radius:10px 0px 0px 0px !important; padding-left: 20px; }

body .cmmrm-route-editor .cmmrm-field.cmmrm-field-route-import > label { padding-left:0px !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; }

body .dropdown.ui_dropdown_single, body .dropdown.ui_dropdown_multiple { overflow:visible !important; }

body .cmmrm-field-path-color-img { cursor:pointer !important; }
body .cmmrm-field-path-color > label { cursor:pointer !important; }
body input[name="path-color"] { display:none !important; }

body .cmmrm-images > label { cursor:pointer !important; }
body .cmmrm-images > span { cursor:pointer !important; color:#666666 !important; font-size:16px !important; font-family: "GothamRnd-Medium" !important; line-height:28px !important; padding-left:1px !important; }
body .cmmrm-images > img.imgicon { cursor:pointer !important; }
body .cmmrm-images-upload { display:none !important; }

body #customcheckbox1 { display: none; }
body .checker1 { background-image: url(<?php echo App::url('asset/img/editor/checkbox.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }
body #customcheckbox1:checked + .checker1 { background-image: url(<?php echo App::url('asset/img/editor/checkboxs.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }

body #customcheckbox2 { display: none; }
body .checker2 { background-image: url(<?php echo App::url('asset/img/editor/checkbox.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }
body #customcheckbox2:checked + .checker2 { background-image: url(<?php echo App::url('asset/img/editor/checkboxs.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }

body #customcheckbox3 { display: none; }
body .checker3 { background-image: url(<?php echo App::url('asset/img/editor/checkbox.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }
body #customcheckbox3:checked + .checker3 { background-image: url(<?php echo App::url('asset/img/editor/checkboxs.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }

body #customcheckbox4 { display: none; }
body .checker4 { background-image: url(<?php echo App::url('asset/img/editor/checkbox.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }
body #customcheckbox4:checked + .checker4 { background-image: url(<?php echo App::url('asset/img/editor/checkboxs.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }

body #customcheckbox5 { display: none; }
body .checker5 { background-image: url(<?php echo App::url('asset/img/editor/checkbox.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }
body #customcheckbox5:checked + .checker5 { background-image: url(<?php echo App::url('asset/img/editor/checkboxs.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }

body #customcheckbox6 { display: none; }
body .checker6 { background-image: url(<?php echo App::url('asset/img/editor/checkbox.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }
body #customcheckbox6:checked + .checker6 { background-image: url(<?php echo App::url('asset/img/editor/checkboxs.png'); ?>); background-position: center center; width: 20px !important; height: 20px !important; background-repeat: no-repeat; cursor:pointer; margin-right:5px; background-size: cover; }

body .arrowimg { margin-top: 16px; margin-left: 16px; margin-right: 16px; width: 24px; }
body .cmmrm-locations-editor-mode li a span { font-weight: normal !important; color:#666666 !important; font-size: 16px !important; font-family: "GothamRnd-Medium" !important; }

body .cmmrm-locations-list .medium-editor-placeholder-relative::after { color: #999999 !important; font-family: "GothamRnd-book" !important; padding-left:0px !important; }
body .cmmrm-locations-list .medium-editor-element { padding-left:14px !important; color: #666666 !important; font-family: "GothamRnd-Book" !important; }
body .cmmrm-locations-list .medium-editor-element p { color: #666666 !important; font-family: "GothamRnd-Book" !important; }

@media only screen and (max-width: 768px) {
	body .cmmrm-field.cmmrm-field-path-color { padding-left:35px !important; }
	body .cmmrm-field.cmmrm-images { padding-left:24px !important; }
	body .cmmrm-field.cmmrm-import-from-files { padding-left:35px !important; }
	body .cmmrm-field.cmmrm-field-path-color label { font-size: 16px !important; font-family: "GothamRnd-Medium" !important; }
	body .cmmrm-field.cmmrm-editor-instructions-container { padding-left: 35px !important; }
	body .cmmrm-field.cmmrm-images label { font-size: 16px !important; font-family: "GothamRnd-Medium" !important; }
	body .cmmrm-route-editor .cmmrm-import-kml-btn { font-size: 16px !important; font-family: "GothamRnd-Medium" !important; }
	body .cmmrm-route-editor .cmmrm-editor-instructions-btn { font-size: 16px !important; font-family: "GothamRnd-Medium" !important; }
	body ul.cmmrm-route-travel-mode li { margin-left: 0px !important; }
	body .cmmrm-inline-nav.cmmrm-route-travel-mode li + li { margin-left: 0px !important; }
	body .cmmrm-field.cmmrm-field-travel-mode .cmmrm-field-travel-mode-inner { width:75% !important; }
	body .cmmrm-field.cmmrm-field-categories { width:8% !important; }
	body .cmmrm-route-editor .cmmrm-field.cmmrm-images label { padding-left:10px !important; }
}

@media only screen and (max-width: 480px) {
	body .cmmrm-field.cmmrm-field-path-color { padding-left:25px !important; width:100% !important; margin-bottom:30px !important; }
	body .cmmrm-field.cmmrm-import-from-files { padding-left:25px !important; width:100% !important; }
	body .cmmrm-field.cmmrm-editor-instructions-container { padding-left: 25px !important; width:100% !important; }
	body .cmmrm-field.cmmrm-field-travel-mode { width:100% !important; }
	body .cmmrm-field.cmmrm-field-categories { width:100% !important; }
	body .cmmrm-field.cmmrm-field-route-settings > label { width:100% !important; }
	body .cmmrm-field.cmmrm-images { padding-left:0px !important; width:100% !important; margin-bottom:10px; }
	body ul.cmmrm-route-travel-mode li:first-child { width:100% !important; margin-bottom: 10px !important; }
	body .cmmrm-locations-list .cmmrm-images { padding-left:10px; }
	body .cmmrm-location-remove-outer { text-align: center; padding-top:30px; }
	body .cmmrm-locations-list .cmmrm-add-video-btn { width:auto; }
	body .ui.dropdown { margin-left: -45px !important; }
	body .cmmrm-locations-editor-mode li a span { display:none !important; }
	body .cmmrm-toolbar li a img { vertical-align:bottom !important; margin-left: 5px !important; }
	body .form-summary { text-align:center !important; }
	body .cmmrm-route-editor input[type="button"].btn_remove { margin-top:20px !important; float:none !important; }
	body .cmmrm-btn { font-size:16px !important; font-family: "GothamRnd-Medium" !important; }
	body .cmmrm-inline-nav.cmmrm-route-travel-mode li a { padding:0 20px !important; }
	body .travelModeImgs { margin-right:0 !important; }
	body #cmmrm-editor-map .cmmrm-toolbar li.rem-separator ul li:first-child a { padding-left: 5px; }
	body .arrowimg { margin-left: 5px; margin-right: 5px; }
	body .cmmrm-toolbar .right { width: 200px; }
	body .cmmrm-location-remove { float: left; margin-top: 40px; }
	body .cmmrm-route-params-recalculate-btn { margin-right:0px !important; }
	body .cmmrm-custom-params-recalculate-btn { margin-left:0px !important; }
	body .cmmrm-images-img { margin-right:13px !important; }
	body .cmmrm-route-editor .cmmrm-editor-instructions-btn img { margin-left: 4px !important; margin-right:13px !important; }
	body .cmmrm-field.cmmrm-field-travel-mode .cmmrm-field-travel-mode-inner { width:55% !important; }
	body .cmmrm-field.cmmrm-field-categories .cmmrm-field-categories-inner { width:55% !important; }
	body .cmmrm-route-editor .cmmrm-field.cmmrm-images label { padding-left:25px !important; }
}
</style>
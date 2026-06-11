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

if (empty($route)) $route = null;
$route_form_description = Settings::getOption(Settings::OPTION_ROUTE_FORM_DESCRIPTION);
$route_form_status = Settings::getOption(Settings::OPTION_ROUTE_FORM_STATUS);
$route_form_settings = Settings::getOption(Settings::OPTION_ROUTE_FORM_SETTINGS);
$editor_instructions_button_enable = Settings::getOption(Settings::OPTION_EDITOR_INSTRUCTIONS_BUTTON_ENABLE);
$route_form_terms = Settings::getOption(Settings::OPTION_ROUTE_FORM_TERMS);
$route_form_osmtiles = Settings::getOption(Settings::OPTION_ROUTE_FORM_OSMTILES);
?>
<div class="cmmrm-route-editor">

	<?php do_action('cmmrm_route_editor_top', $route); ?>

	<form action="<?php echo esc_attr($formUrl); ?>" method="post" enctype="multipart/form-data" data-route-id="<?php echo $route->getId(); ?>">

		<div class="cmmrm-field cmmrm-field-route-name">
			<label><?php echo Labels::getLocalized('route_name'); ?></label>
			<input type="text" name="name" id="cmmrm_route_name" value="<?php echo esc_attr($route->getTitle()); ?>" required />
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
				<?php
				$travel_mode_array = array_combine(Route::$travelModes, array_map('strtolower', Route::$travelModes));
				if(count($travel_mode_array) > 0) {
					echo '<label>'.Labels::getLocalized('travel_mode').'</label>';
					foreach($travel_mode_array as $modekey=>$modeval) {
						if(Settings::getOption(Settings::OPTION_TRAVEL_MODE_MULTIPLE_SHOW) == 1) {
							$route_travel_mode = $route->getTravelMode();
							if(!is_array($route_travel_mode)) {
								$route_travel_mode_arr = explode(',', $route_travel_mode);
							} else {
								$route_travel_mode_arr = $route_travel_mode;
							}
							?>
							<input name="travel-mode[]" id="travel-mode_<?php echo $modekey; ?>" value="<?php echo $modekey; ?>" <?php echo (in_array($modekey, $route_travel_mode_arr))?'checked="checked"':''; ?> type="checkbox" />
							<?php echo Labels::getLocalized('travel_mode_'.$modeval); ?>
							<br>
							<?php
						} else {
							?>
							<input name="travel-mode" id="travel-mode_<?php echo $modekey; ?>" value="<?php echo $modekey; ?>" <?php echo ($route->getTravelMode() == $modekey)?'checked="checked"':''; ?> type="radio" />
							<?php echo Labels::getLocalized('travel_mode_'.$modeval); ?>
							<br>
							<?php
						}
					}
				}
				?>
			</div>
			<?php
		} else {
			?>
			<input type="hidden" name="travel-mode" value="<?php echo esc_attr($route->getTravelMode()); ?>" />
			<?php
		}
		?>
		
		<?php if ($route_form_status != 'none') {
			?>
			<div class="cmmrm-field cmmrm-field-route-status" <?php if(!current_user_can('manage_options') && Settings::getOption(Settings::OPTION_ROUTE_MODERATION_ENABLE)) { echo "style='display:none;'"; } ?>>
				<label><?php echo Labels::getLocalized('route_status'); ?></label>
				<?php echo FormHtml::selectBox('status', apply_filters('cmmrm_editor_allowed_statuses', array(
					'publish' => Labels::getLocalized('route_status_publish'),
					'draft' => Labels::getLocalized('route_status_draft'),
				),  $route), $route->getStatus()); ?>
			</div>
			<?php
		} else {
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
		
		<?php do_action('cmmrm_route_editor_middle', $route); ?>
		
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
				<input type="checkbox" name="use-buddypress-collaborative" value="1" <?php echo $checked_use_buddypress_collaborative; ?> />
				<span><?php echo Labels::getLocalized('buddypress_collaborative'); ?></span>
			</label>

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
			<label class="use-minor-length-units-label"><input type="checkbox" name="use-minor-length-units" value="1" <?php echo $checked_route_setting_default_use_only_meters; ?> />
			<span><?php echo Labels::getLocalized('dashboard_use_minor_length_units', 'Use only meters/feet instead of kilometers/miles'); ?></span></label>

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
				<a href="" class="cmmrm-btn cmmrm-editor-instructions-btn"><span class="dashicons dashicons-editor-help"></span><?php echo Labels::getLocalized('instructions_btn') ?></a>
				<div class="cmmrm-editor-instructions">
					<?php echo Settings::getOption(Settings::OPTION_LABEL_EDITOR_INSTRUCTION); ?>
				</div>
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
								<a href="" data-mode="waypoint" class="dashicons dashicons-admin-customizer" title="<?php
								echo esc_attr(Labels::getLocalized('editor_draw_path_mode_btn_desc')); ?>"><span><?php
								echo Labels::getLocalized('editor_draw_path_mode_btn_text'); ?></span></a>
							</li>
							<li>
								<a href="" data-mode="location" class="dashicons dashicons-location" title="<?php
								echo esc_attr(Labels::getLocalized('editor_add_locations_mode_btn_desc')); ?>"><span><?php
								echo Labels::getLocalized('editor_add_locations_mode_btn_text'); ?></span></a>
							</li>
							<?php
						} else {
							?>
							<li class="current">
								<a href="" data-mode="location" class="dashicons dashicons-location" title="<?php
								echo esc_attr(Labels::getLocalized('editor_add_locations_mode_btn_desc')); ?>"><span><?php
								echo Labels::getLocalized('editor_add_locations_mode_btn_text'); ?></span></a>
							</li>
							<li>
								<a href="" data-mode="waypoint" class="dashicons dashicons-admin-customizer" title="<?php
								echo esc_attr(Labels::getLocalized('editor_draw_path_mode_btn_desc')); ?>"><span><?php
								echo Labels::getLocalized('editor_draw_path_mode_btn_text'); ?></span></a>
							</li>
							<?php
						}
						?>
					</ul>
				</li>
				<li><?php // echo RouteView::getTravelModeMenu($route->getTravelMode(), $showTitle = false, $labelsAsTooltip = true); ?></li>
				<li class="right"><input type="text" class="cmmrm-find-location" placeholder="<?php echo esc_attr(Labels::getLocalized('dashboard_map_search')); ?>" /></li>
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
				<div class="cmmrm-btn cmmrm-route-params-recalculate-btn"><span class="dashicons dashicons-update"></span><span class="text"><?php echo Labels::getLocalized('recalculate_route_params'); ?></span></div>

				<div class="cmmrm-btn cmmrm-custom-params-recalculate-btn"><span class="dashicons dashicons-update"></span><span class="text"><?php echo Labels::getLocalized('recalculate_custom_params'); ?></span></div>
				
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
					<input class="location-distance" type="number" name="locations[distance][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_distance')); ?>" placeholder="<?php echo esc_attr(Labels::getLocalized('location_distance')); ?>" min="0" step="0.001" />
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
					
					<div class="cmmrm-field-route-location-certificate-bg-image">
						<div class="cmmrm-images">

							<input type="hidden" name="locations[certbgimageid][]" value="" />

							<label for="route-location-cert-bg">Postcard Images</label>
							<input type="file" class="cmmrm-images-upload" name="route-location-cert-bg" id="route-location-cert-bg" accept="image/jpeg,image/jpg,image/png" multiple>

							<ul class="cmmrm-images-list" style="margin-top: 1em !important;">
								<li data-id="0" style="display:none">
									<a href="" target="_blank" title="<?php echo esc_attr(Labels::getLocalized('dashboard_image_open')); ?>">
										<img src="" alt="Image" />
									</a>
									<span class="cmmrm-image-delete" title="<?php echo esc_attr(Labels::getLocalized('dashboard_image_remove')); ?>">&times;</span>
								</li>
							</ul>

						</div>
					</div>
					
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
			
			<?php
			if(is_plugin_active('cm-maps-virtual-races-addon/plugin.php')) {
				?>
				<div style="margin-top:30px;">
					<div class="cmmrm-btn btn_calc_loc_distance"><span class="dashicons dashicons-update"></span><span class="text">Calculate location markers distance from start</span></div>
				</div>
				<?php
			}
			?>

			<input type="hidden" name="<?php echo esc_attr(DashboardController::EDITOR_NONCE); ?>" value="<?php echo esc_attr($nonce); ?>" class="cmmrm-nonce" />
			
			<input type="submit" name="btn_save" value="<?php echo esc_attr(Labels::getLocalized('dashboard_save_btn')); ?>" class="button button-primary addeditroute" />
			<input type="submit" name="cmmrm_btn_draft" value="<?php echo esc_attr(Labels::getLocalized('dashboard_save_draft_btn')); ?>" class="button button-primary addeditdraftroute" />

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
.medium-editor-toolbar .medium-editor-toolbar-actions { padding: 0; background:#fff; }
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
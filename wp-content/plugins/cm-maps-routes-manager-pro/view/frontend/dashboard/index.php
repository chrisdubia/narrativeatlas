<?php
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Labels;
?>
<?php if (Route::canCreate() AND !empty($atts['addbtn'])): ?>
	<p class="cmmrm-route-add"><a href="<?php echo RouteController::getDashboardUrl('add'); ?>"><?php echo Labels::getLocalized('dashboard_add_route_btn'); ?></a></p>
<?php endif; ?>
<?php if (is_user_logged_in()): ?>
<?php if (count($routes) > 0): ?>
	<table>
		<thead>
			<tr>
				<th><?php echo Labels::getLocalized('route_name'); ?></th>
				<?php
				if(is_plugin_active('cm-maps-routes-buddypress-integration/cm-maps-routes-buddypress-integration.php') || is_plugin_active('cm-maps-routes-buddypress-addon/cm-maps-routes-buddypress-integration.php')) {
					?>
					<th><?php echo Labels::getLocalized('buddypress_collaborative_text'); ?></th>
					<?php
				}
				?>
				<th style="width:7em"><?php echo Labels::getLocalized('route_status'); ?></th>
				<?php if (!empty($atts['controls'])): ?>
					<th style="width:15em"><?php echo Labels::getLocalized('dashboard_routes_actions'); ?></th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody><?php foreach ($routes as $route): ?>
			<tr>
				<td><a href="<?php echo esc_attr($route->getUserEditUrl()); ?>"><?php echo esc_html($route->getTitle()); ?></a></td>
				<?php
				if(is_plugin_active('cm-maps-routes-buddypress-integration/cm-maps-routes-buddypress-integration.php') || is_plugin_active('cm-maps-routes-buddypress-addon/cm-maps-routes-buddypress-integration.php')) {
					?>
					<td>
						<?php
						if($route->useBuddypressCollaborative() == '1') {
							?>
							<span class="cmmrm-collaborative"><?php echo Labels::getLocalized('buddypress_collaborative_yes'); ?></span>
							<?php
						} else {
							?>
							<span class="cmmrm-collaborative"><?php echo Labels::getLocalized('buddypress_collaborative_no'); ?></span>
							<?php
						}
						?>
					</td>
					<?php
				}
				?>
				<td><?php echo Labels::getLocalized('route_status_' . $route->getStatus()); ?></td>
				<?php if (!empty($atts['controls'])): ?>
					<td>
						<ul class="cmmrm-inline-nav">
							<li><a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo Labels::getLocalized('dashboard_view'); ?></a></li>
							<li><a href="<?php echo esc_attr($route->getUserEditUrl()); ?>"><?php echo Labels::getLocalized('dashboard_edit'); ?></a></li>
							<li><a href="<?php echo esc_attr($route->getUserDeleteUrl()); ?>" class="cmmrm-delete-confirm"><?php echo Labels::getLocalized('dashboard_delete'); ?></a></li>
                            <?php do_action('cmmrm_dashboard_routes_actions_list', $route); ?>
                        </ul>
					</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?></tbody>
		</table>
<?php else: ?>
	<p class="dashboard_no_routes"><?php echo Labels::getLocalized('dashboard_no_routes'); ?></p>
<?php endif; ?>
<?php endif; ?>
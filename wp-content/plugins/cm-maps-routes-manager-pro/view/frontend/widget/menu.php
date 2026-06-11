<?php
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\controller\DashboardController;
use com\cminds\mapsroutesmanager\controller\FrontendController;
?>
<div class="cmmrm-widget-menu">
	<ul>
		<li><a href="<?php echo esc_attr(FrontendController::getUrl()); ?>"><?php echo Labels::getLocalized('menu_all_routes'); ?></a></li>
		<?php if (Route::canCreate()): ?>
			<li><a href="<?php echo esc_attr(RouteController::getDashboardUrl('index')); ?>"><?php echo Labels::getLocalized('menu_my_routes'); ?></a></li>
			<li><a href="<?php echo esc_attr(RouteController::getDashboardUrl('add')); ?>"><?php echo Labels::getLocalized('menu_add_route'); ?></a></li>
			<?php if (!empty($route) AND $route->canEdit()): ?>
				<?php if (FrontendController::isDashboard() AND 'publish' == $route->getStatus()): ?>
					<li><a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo Labels::getLocalized('menu_view_route'); ?></a></li>
				<?php else: ?>
					<li><a href="<?php echo esc_attr($route->getUserEditUrl()); ?>"><?php echo Labels::getLocalized('menu_edit_route'); ?></a></li>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</ul>
</div>
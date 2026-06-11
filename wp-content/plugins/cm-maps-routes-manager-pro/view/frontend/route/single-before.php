<?php
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\helper\RouteView;
?>
<div class="cmmrm-route-map-before">
	
	<?php if (Settings::getOption(Settings::OPTION_PAGETITLE_ENABLE)): ?>
		<h1 class="cmmrm-route-title"><?php echo $route->getTitle(); ?></h1>
	<?php endif; ?>

	<?php
	$ctaButtonText = $route->getCtaButtonText();
	$ctaButtonUrl = $route->getCtaButtonUrl();
	if($ctaButtonText != '' && $ctaButtonUrl != '') {
		?>
		<div class="cmmrm-cta-button single">
			<?php
			if($ctaButtonUrl == '#') {
				?>
				<a href="<?php echo $ctaButtonUrl; ?>" class="cmmrm-cta-button-a"><?php echo $ctaButtonText; ?></a>
				<?php
			} else {
				?>
				<a href="<?php echo $ctaButtonUrl; ?>" class="cmmrm-cta-button-a" target="_blank"><?php echo $ctaButtonText; ?></a>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>

	<?php do_action('cmmrm_route_map_before_top', $route); ?>
	
	<?php if (Settings::getOption(Settings::OPTION_AUTHOR_AVATAR_SHOW)): ?>
		<div class="cmmrm-author cmmrm-author-avatar">
			<?php echo apply_filters('cmmrm_display_author',
				get_avatar($route->getAuthorId(), $size = 96, $default = '', $alt = $route->getAuthorDisplayName(), $args = array('title' => $route->getAuthorDisplayName())),
				$route->getAuthorId(), $route); ?>
			<?php if (Settings::getOption(Settings::OPTION_AUTHOR_AVATAR_USERNAME_SHOW)): ?>
				<div class="cmmrm-author-name">
					<?php echo apply_filters('cmmrm_display_author', $route->getAuthorDisplayName(), $route->getAuthorId(), $route); ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<ul class="cmmrm-route-properties">
		<li class="cmmrm-author">
			<strong><?php echo Labels::getLocalized('route_author'); ?>:</strong>
			<span><?php echo apply_filters('cmmrm_display_author', $route->getAuthorDisplayName(), $route->getAuthorId(), $route); ?></span>
		</li>
		<?php $created = $route->formatCreatedDate(); ?>
		<li class="cmmrm-date cmmrm-publish-date"><strong><?php echo Labels::getLocalized('route_created'); ?>:</strong> <span><?php echo $created; ?></span></li>
		<?php if ($updated = $route->formatModifiedDate() AND $updated != $created): ?>
			<li class="cmmrm-date cmmrm-update-date"><strong><?php echo Labels::getLocalized('route_updated'); ?>:</strong> <span><?php echo $updated; ?></span></li>
		<?php endif; ?>
	</ul>
	
	<?php if ($categories = $route->getCategories()) RouteView::displayTermsInlineNav(Labels::getLocalized('categories'), 'categories', $categories); ?>
	<?php if ($tags = $route->getTags()) RouteView::displayTermsInlineNav(Labels::getLocalized('tags'), 'tags', $tags); ?>
	
	<?php do_action('cmmrm_single_route_properties', $route); ?>
	
	<?php if (!isset($atts['params']) OR $atts['params'] == 1): ?>
		<?php if (Settings::getOption(Settings::OPTION_SINGLE_ROUTE_PARAMS_ABOVE_MAP)): ?>
			<?php echo RouteController::loadFrontendView('route-params', compact('route')); ?>
		<?php endif; ?>
	<?php endif; ?>
	
	<?php if (!empty($atts['toolbar'])): ?>
		<?php echo RouteController::loadFrontendView('route-toolbar', compact('route')); ?>
	<?php endif; ?>

</div>
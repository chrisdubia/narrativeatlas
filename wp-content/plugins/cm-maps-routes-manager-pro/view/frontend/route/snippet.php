<?php
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\User;
use com\cminds\mapsroutesmanager\model\Labels;

if ($userCoords = User::getLastGeolocation()) {
	$distance = $route->getDistanceFromCoords($userCoords[0], $userCoords[1]);
	$distanceKm = ceil($distance/1000);
} else {
	$distanceKm = '';
}
?>
<div class="cmmrm-shortcode-route-snippet flex-container" <?php echo RouteView::getDisplayParams($displayParams); ?> data-layout="<?php echo $atts['layout']; ?>" data-fancy="<?php echo $atts['fancy']; ?>" data-fancy-border="<?php echo (Settings::getOption(Settings::OPTION_FANCY_BORDER) ? '1': '0'); ?>" <?php $pathColor = $route->getPathColor();
		$slopeDownwardColor = $route->getSlopeDownwardColor();
		$slopeUpwardColor = $route->getSlopeUpwardColor();
		if (Settings::getOption(Settings::OPTION_INDEX_SNIPPET_BGCOLOR_FROM_ROUTE) AND strlen($pathColor) > 0) {
			echo ' style="background-color:'. esc_attr($pathColor) .'"';
		}
		?>>
	<?php do_action('route_snippet_start', $route, $atts); ?>
	<div class="cmmrm-route-snippet flex-item-stretch" data-route-id="<?php echo $route->getId(); ?>">
		<?php do_action('cmmrm_route_snippet_top', $route, $atts); ?>
		<div class="cmmrm-route-featured-image"><?php echo RouteView::getFeaturedImageThumb($route, $atts); ?></div>
		<?php echo RouteView::getFeaturedImageLarge($route, $atts); ?>
		<?php echo RouteView::displayRating($route); ?>
		<h2>
			<?php do_action('cmmrm_single_snippet_h2_before_a', $route); ?>
			<a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo esc_html($route->getTitle()); ?></a>
			<?php do_action('cmmrm_single_snippet_h2_after_a', $route); ?>
			<?php
			if($route->useBuddypressCollaborative() == '1') {
				?>
				<span class="cmmrm-collaborative" title="<?php echo Labels::getLocalized('buddypress_collaborative_tooltip_text'); ?>">
					<a href="<?php echo esc_attr($route->getUserEditUrl()); ?>"><?php echo Labels::getLocalized('buddypress_collaborative_text'); ?></a>
				</span>
				<?php
			}
			?>
		</h2>

		<?php
		$ctaButtonText = $route->getCtaButtonText();
		$ctaButtonUrl = $route->getCtaButtonUrl();
		if($ctaButtonText != '' && $ctaButtonUrl != '') {
			?>
			<div class="cmmrm-cta-button index">
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

		<?php if (!isset($atts['params']) OR $atts['params'] == 1): ?>
			<?php echo RouteController::loadFrontendView('route-params', compact('route')); ?>
		<?php endif; ?>
		<div class="cmmrm-date cmmrm-update-date"><?php echo $route->formatModifiedDate(); ?></div>
		<div class="cmmrm-author"><?php echo apply_filters('cmmrm_display_author', $route->getAuthorDisplayName(), $route->getAuthorId(), $route); ?></div>
		<?php do_action('cmmrm_route_snippet_bottom', $route, $atts); ?>
		<div class="clear"></div>
	</div>
	<?php do_action('route_snippet_end', $route, $atts); ?>
</div>
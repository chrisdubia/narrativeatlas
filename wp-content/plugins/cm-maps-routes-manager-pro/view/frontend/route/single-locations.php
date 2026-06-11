<?php
use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\helper\RouteView;
$showNumbers = Settings::getOption(Settings::OPTION_SINGLE_ROUTE_LOCATIONS_NUMBERS_SHOW);
$i = 0;
$j = 0;
$scroll_class = '';
$scroll_style = '';
if(!empty($atts['height'])) {
	$scroll_class = ' cmmrm_scroll_div';
	$scroll_style = 'style="height:'.$atts['height'].'px;overflow-x:hidden;padding-right:5px;"';
}
?><div class="cmmrm-route-locations<?php echo $scroll_class; ?>" <?php echo $scroll_style; ?>>
	<?php foreach ($route->getLocations() as $location): ?>
		<?php if (Location::TYPE_LOCATION == $location->getLocationType()): ?>
			<?php $i++; ?>
			<div class="cmmrm-location-details location-<?php echo $location->getId(); ?>" data-id="<?php echo $location->getId();
				?>" data-lat="<?php echo $location->getLat(); ?>" data-long="<?php echo $location->getLong(); ?>">
				<h3 data-index="<?php echo $j; ?>" data-id="<?php echo $location->getId();
				?>"><?php
					if ($showNumbers) echo $i . '. ';
					echo esc_html($location->getTitle());
				?></h3>
				<div class="cmmrm-altitude"><strong><?php echo Labels::getLocalized('location_altitude'); ?>:</strong> <span><?php echo $location->formatAltitude(); ?></span></div>
				<?php if ($address = $location->getAddress()): ?>
					<div class="cmmrm-address">
						<strong><?php echo Labels::getLocalized('location_address'); ?>:</strong>
						<span><?php echo esc_html($address); ?></span>
					</div>
				<?php endif; ?>
				
				<?php if($location->getLinkUrl() != '' || $location->getLinktext() != '') { ?>
					<div class="cmmrm-link">
						<?php
						if($location->getLinkUrl() != '') { echo '<a href="'.$location->getLinkUrl().'">'; }
						if($location->getLinktext() != '') { echo $location->getLinktext(); }
						if($location->getLinkUrl() != '') { echo '</a>'; }
						?>
					</div>
				<?php } ?>

				<?php if($location->getGenerateWazeButton()) { ?>
				<div class="cmmrm-waze-button-container">
					<a class="button button-primary waze-button" href="https://www.waze.com/location?ll=<?php echo $location->getLat(); ?>,<?php echo $location->getLong(); ?>&navigate=yes" target="_blank"><?php echo Labels::getLocalized('start_waze_navigation'); ?></a>
				</div>
				<?php } ?>
				
				<?php do_action('cmmrm_single_location_before_images', $location, $i); ?>
				
				<?php if ($images = $location->getImages()):
					RouteView::displayImages($images, 'location', $location->getId());
				endif; ?>
				
				<div class="cmmrm-description"><?php echo RouteView::processDescription($location->getContent()); ?></div>

			</div>
		<?php endif; ?>
		<?php $j++; ?>
	<?php endforeach; ?>
</div>
<?php do_action('cmmrm_route_single_after_locations', $route); ?>
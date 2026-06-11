<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\controller\TecController;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Route;

class RouteViewEventsShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'route-view-events';

	static function shortcode($atts) {
		
		$atts = shortcode_atts(array(
			'id' => null,
			'text' => 'Take this Route on An Adventure',
		), $atts);
		
		if (!empty($atts['id'])) {
			$route = Route::getInstance($atts['id']);
		} else {
			global $post;
			$route = Route::getInstance($post->ID);
		}

		if (!empty($route) AND $route instanceof Route) {
			$out = '';
			ob_start();
			$the_events_calendar_integration_enable = Settings::getOption(Settings::OPTION_THE_EVENTS_CALENDAR_INTEGRATION_ENABLE);
			if($the_events_calendar_integration_enable) {

				wp_enqueue_style('cmmrm-frontend');
				wp_enqueue_script('cmmrm-frontend');
				
				$saved_events = TecController::getSavedEvents($route->getId());
				if(count($saved_events) > 0) {
					?>
					<div class="cmmrm-tec-list-conatiner route-view-events-shortcode" data-id="<?php echo $route->getId(); ?>">
						<div class="cmmrm-tec-list-heading"><?php echo $atts['text']; ?></div>
						<?php
						foreach($saved_events as $cmmrm_tec_event_key=>$cmmrm_tec_event) {

							$event_start_data = get_post_meta($cmmrm_tec_event, '_EventStartDate', true);
							$event_start_data_utc = get_post_meta($cmmrm_tec_event, '_EventStartDateUTC', true);
							$event_end_data = get_post_meta($cmmrm_tec_event, '_EventEndDate', true);
							$event_end_data_utc = get_post_meta($cmmrm_tec_event, '_EventEndDateUTC', true);
							
							$event_venue_id = get_post_meta($cmmrm_tec_event, '_EventVenueID', true);
							$event_organizer_id = get_post_meta($cmmrm_tec_event, '_EventOrganizerID', true);
							$event_content = get_the_content(null, false, $cmmrm_tec_event);
							$event_thumbnail_id = get_post_meta($cmmrm_tec_event, '_thumbnail_id', true);

							?>
							<div class="cmmrm-tec-list-row">
								<div class="cmmrm-tec-list-row-left">
									<div class="cmmrm-tec-list-row-top">
										<?php echo strtoupper(date('D', strtotime($event_start_data))); ?>
									</div>
									<div class="cmmrm-tec-list-row-bottom">
										<?php echo date('d', strtotime($event_start_data)); ?>
									</div>
								</div>
								<div class="cmmrm-tec-list-row-middle">
									<div class="cmmrm-tec-list-row-first">
										<?php echo date('F d, Y @ H:i:s', strtotime($event_start_data)); ?>
										-
										<?php echo date('F d, Y @ H:i:s', strtotime($event_end_data)); ?>
									</div>
									<div class="cmmrm-tec-list-row-second">
										<a href="<?php echo get_permalink($cmmrm_tec_event); ?>" target="_blank">
											<?php echo get_the_title($cmmrm_tec_event); ?>
										</a>
									</div>
									<?php
									if($event_venue_id != '' && $event_venue_id > 0) {
										?>
										<div class="cmmrm-tec-list-row-third">
											<?php
											echo "<strong>".get_the_title($event_venue_id)."</strong>";
											echo " ";
											echo trim(get_post_meta($event_venue_id, '_VenueAddress', true)." ".get_post_meta($event_venue_id, '_VenueCity', true)." ".get_post_meta($event_venue_id, '_VenueProvince', true)." ".get_post_meta($event_venue_id, '_VenueZip', true)." ".get_post_meta($event_venue_id, '_VenueCountry', true));
											?>
										</div>
										<?php
									}
									/*
									if($event_organizer_id != '' && $event_organizer_id > 0) {
										?>
										<div class="cmmrm-tec-list-row-forth">
											<?php
											echo "<strong>".get_the_title($event_organizer_id)."</strong>";
											?>
										</div>
										<?php
									}
									*/
									if($event_content != '') {
										?>
										<div class="cmmrm-tec-list-row-fifth">
											<?php
											echo wpautop($event_content);
											?>
										</div>
										<?php
									}
									?>
								</div>
								<div class="cmmrm-tec-list-row-right">
									<?php
									if($event_thumbnail_id != '') {
										$event_image = wp_get_attachment_image( $event_thumbnail_id, 'large' );
										echo '<a href="'.get_permalink($cmmrm_tec_event).'" target="_blank">';
											echo $event_image;
										echo '</a>';
									}
									?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				}

			}
			$out = ob_get_clean();
			return $out;
		}
		
	}

}
?>
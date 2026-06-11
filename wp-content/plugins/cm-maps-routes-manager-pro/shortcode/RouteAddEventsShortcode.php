<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\controller\TecController;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Route;

class RouteAddEventsShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'route-add-events';

	static function shortcode($atts) {
		
		$atts = shortcode_atts(array(
			'id' => null,
			'text' => 'Add to Adventure',
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
				if(is_user_logged_in()) {
					wp_enqueue_style('cmmrm-frontend');
					wp_enqueue_script('cmmrm-frontend');
					$cmmrm_tec_events = TecController::getTheEventsCalendar(get_current_user_id(), '');
					if(count($cmmrm_tec_events) > 0) {
						$saved_events = get_user_meta(get_current_user_id(), 'cmmr_tec_'.$route->getId(), true);
						if($saved_events != '') {
							$saved_events_arr = explode(",", $saved_events);
						} else {
							$saved_events_arr = array();
						}
						?>
						<div class="cmmrm-tec route-add-events-shortcode">
							<div class="cmmrm-tec-conatiner" data-id="<?php echo $route->getId(); ?>">
								<span><img src="<?php echo App::url('asset/img/ajax-loader.gif'); ?>" /> <?php echo $atts['text']; ?></span>
								<div class="cmmrm-tec-conatiner-inner">
									<?php
									foreach($cmmrm_tec_events as $cmmrm_tec_event_key=>$cmmrm_tec_event) {
										echo '<div class="cmmrm_tec_event_item">';
											if(in_array($cmmrm_tec_event->ID, $saved_events_arr)) {
												echo '<input type="checkbox" id="cmmrm_tec_event_'.$cmmrm_tec_event_key.'" class="cmmrm_tec_event" value="'.$cmmrm_tec_event->ID.'" checked="checked" />';
											} else {
												echo '<input type="checkbox" id="cmmrm_tec_event_'.$cmmrm_tec_event_key.'" class="cmmrm_tec_event" value="'.$cmmrm_tec_event->ID.'" />';
											}
											echo '<label for="cmmrm_tec_event_'.$cmmrm_tec_event_key.'">'.get_the_title($cmmrm_tec_event->ID).'</label>';
										echo '</div>';
									}
									?>
									<div class="cmmrm-tec-conatiner-inner-footer">
										<a href="javascript:void(0);" class="cmmrm_tec_cancel">Cancel</a>
										<a href="javascript:void(0);" class="cmmrm_tec_apply">Apply</a>
									</div>
								</div>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="cmmrm-tec route-add-events-shortcode">
							<div class="cmmrm-tec-conatiner" data-id="<?php echo $route->getId(); ?>">
								<span><?php echo $atts['text']; ?></span>
								<div class="cmmrm-tec-conatiner-inner">
									<div class="cmmrm_tec_event_item">
										No Adventure Found
									</div>
									<div class="cmmrm-tec-conatiner-inner-footer">
										<a href="javascript:void(0);" class="cmmrm_tec_cancel">Cancel</a>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
				}
			}
			$out = ob_get_clean();
			return $out;
		}
		
	}

}
?>
<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Labels;

class TecController extends Controller {
	
	protected static $actions = array(
		'cmmrm_single_snippet_h2_after_a' => array('args' => 1),
		'tribe_events_single_event_after_the_content' => array('priority' => 10),
		'cmmrm_route_index_search_form_bottom' => array('args' => 1),
		'cmmrm_route_single_toolbar_end' => array('args' => 1, 'priority' => 999),
		'cmmrm_route_single_after_locations' => array('args' => 1),
	);
	
	static $ajax = array(
		'cmmrm_apply_events'
	);
	
	static function cmmrm_apply_events() {
		$post_id = $_POST['post_id'];
		$events = $_POST['events'];
		update_user_meta(get_current_user_id(), 'cmmr_tec_'.$post_id, $events);
		wp_die();
	}

	static function cmmrm_single_snippet_h2_after_a($route) {
		$the_events_calendar_integration_enable = Settings::getOption(Settings::OPTION_THE_EVENTS_CALENDAR_INTEGRATION_ENABLE);
		if($the_events_calendar_integration_enable) {
			$saved_events = TecController::getSavedEvents($route->getId());
			if(count($saved_events) > 0) {
				echo '<span class="cmmrm_tec_tag">Adventure</span>';
			}

		}
	}

	static function tribe_events_single_event_after_the_content() {
		$out = '';
		global $wpdb, $post;
		if(!empty($post->ID)) {
			$wpdb_prefix = $wpdb->prefix;
			$tablename = $wpdb_prefix.'usermeta';
			$results = $wpdb->get_results("select * from ".$tablename." where meta_key like 'cmmr_tec_%' and meta_value like '%".$post->ID."%'");
			if(count($results) > 0) {
				$out .= '<div class="cmmrm_tec_single_event_container">';
					$out .= '<div class="cmmrm_tec_single_event_container_heading">Routes</div>';
					foreach($results as $result) {
						$id = str_replace('cmmr_tec_', '', $result->meta_key);
						$out .= '<div class="cmmrm_tec_single_event_container_row">';
							$out .= do_shortcode('[route-snippet id="'.$id.'"]');
						$out .= '</div>';
					}
				$out .= '</div>';
				$out .= '<style>';
				$out .= '.cmmrm_tec_tag { display:none; }';
				$out .= '</style>';
			}
		}
		echo $out;
	}
	
	static function cmmrm_route_index_search_form_bottom($args) {
		$the_events_calendar_integration_enable = Settings::getOption(Settings::OPTION_THE_EVENTS_CALENDAR_INTEGRATION_ENABLE);
		if($the_events_calendar_integration_enable) {
			echo '<label class="cmmrm-field-tec">';
				if(isset($_GET['tec']) && $_GET['tec'] == '1') {
					echo '<input type="checkbox" name="tec" value="1" onChange="this.form.submit()" checked="checked" />';
				} else {
					echo '<input type="checkbox" name="tec" value="1" onChange="this.form.submit()" />';
				}	
				echo ' ';
				echo 'Only show adventure routes';
			echo '</label>';
		}
	}
	
	static function cmmrm_route_single_toolbar_end($route) {
		$the_events_calendar_integration_enable = Settings::getOption(Settings::OPTION_THE_EVENTS_CALENDAR_INTEGRATION_ENABLE);
		if($the_events_calendar_integration_enable) {
			if(is_user_logged_in()) {
				$cmmrm_tec_events = TecController::getTheEventsCalendar(get_current_user_id(), '');
				if(count($cmmrm_tec_events) > 0) {
					$saved_events = get_user_meta(get_current_user_id(), 'cmmr_tec_'.$route->getId(), true);
					if($saved_events != '') {
						$saved_events_arr = explode(",", $saved_events);
					} else {
						$saved_events_arr = array();
					}
					?>
					<li class="cmmrm-tec inbuild">
						<div class="cmmrm-tec-conatiner" data-id="<?php echo $route->getId(); ?>">
							<span><img src="<?php echo App::url('asset/img/ajax-loader.gif'); ?>" /> Add to Adventure</span>
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
					</li>
					<?php
				} else {
					?>
					<li class="cmmrm-tec inbuild">
						<div class="cmmrm-tec-conatiner" data-id="<?php echo $route->getId(); ?>">
							<span>Add to Adventure</span>
							<div class="cmmrm-tec-conatiner-inner">
								<div class="cmmrm_tec_event_item">
									No Adventure Found
								</div>
								<div class="cmmrm-tec-conatiner-inner-footer">
									<a href="javascript:void(0);" class="cmmrm_tec_cancel">Cancel</a>
								</div>
							</div>
						</div>
					</li>
					<?php
				}
			}
		}
	}

	static function cmmrm_route_single_after_locations($route) {
		$the_events_calendar_integration_enable = Settings::getOption(Settings::OPTION_THE_EVENTS_CALENDAR_INTEGRATION_ENABLE);
		if($the_events_calendar_integration_enable) {
			
			$saved_events = TecController::getSavedEvents($route->getId());
			if(count($saved_events) > 0) {
				?>
				<div class="cmmrm-tec-list-conatiner inbuild" data-id="<?php echo $route->getId(); ?>">
					<div class="cmmrm-tec-list-heading">Take this Route on An Adventure</div>
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
	}

	static function getTheEventsCalendar($user_id = 0, $cat_slug = '') {
		$events_args = array(
			'post_type' => 'tribe_events',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby' => 'title',
			'order' => 'asc',
		);
		if($user_id > 0) {
			$events_args['author'] = $user_id;
		}
		if($cat_slug != '') {
			$cat_info = get_term_by('slug', $cat_slug, 'tribe_events_cat');
			if($cat_info) {
				$events_args['tax_query'] = array(
					array(
						'taxonomy' => 'tribe_events_cat',
						'field'    => 'slug',
						'terms'    => $cat_slug,
					)
				);
			}
		}
		$events = get_posts($events_args);
		return $events;
	}
	
	static function getSavedEvents($route_id = 0) {
		$saved_events = array();
		if($route_id > 0) {
			global $wpdb;
			$wpdb_prefix = $wpdb->prefix;
			$tablename = $wpdb_prefix.'usermeta';
			$results = $wpdb->get_results("select * from ".$tablename." where meta_key like 'cmmr_tec_".$route_id."'");
			if(count($results) > 0) {
				foreach($results as $result) {
					$saved_events_list_arr = explode(",", $result->meta_value);
					foreach($saved_events_list_arr as $cmlocteckey=>$cmloctecval) {
						$post_status = get_post_status($cmloctecval);
						if($post_status != 'publish') {
							unset($saved_events_list_arr[$cmlocteckey]);
						} else {
							if (!in_array($cmloctecval, $saved_events)) {
								$saved_events[] = $cmloctecval;
							}
						}
					}
					$saved_events_list_arr = array_values($saved_events_list_arr);
				}
			}
		}
		return $saved_events;
	}

}
?>
<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\controller;

use com\cminds\mapsroutesmanager\addon\buddypress\controller\abstracts\ValidLicenseController;
use com\cminds\mapsroutesmanager\addon\buddypress\App;
use com\cminds\mapsroutesmanager\addon\buddypress\model;

/**
 * Create new activity each time user has published a new map.
 *
 */
class ActivityController extends ValidLicenseController {

    const CM_MAPS_ROUTES_BP_ACTIVITY_COMPONENT = 'cmmrm_buddypress_route_activity';
    const CM_MAPS_ROUTES_BP_ACTIVITY_TYPE = 'cmmrm_buddypress_route_created';

    static $filters = array(
	);

	protected static $actions = array(
		'transition_post_status' => array('args' => 3),
		'post_updated' => array('args' => 3),
	);
	
	static function transition_post_status($new_status, $old_status, $post) {
		if ( $new_status != $old_status AND $new_status == 'publish' AND $post->post_type == model\Route::POST_TYPE) {
            if ( class_exists(App::PARENT_NAMESPACE . '\App') ) {
				static::routePublished($post);
			}
		}
	}

	static function post_updated($post_id, $post_after, $post_before) {
		$post = get_post($post_id);

		$bp_groups_email_sent = get_post_meta($post_id, '_cmmrm_bp_groups_email_sent', true);
		if($bp_groups_email_sent == '') {
			$bp_groups_email_sent = 'no';
		}
		
		if ($bp_groups_email_sent == 'no' && $post->post_status == 'publish' && $post->post_type == model\Route::POST_TYPE) {
            if ( class_exists(App::PARENT_NAMESPACE . '\App') ) {

				if (bp_is_active('activity') && bp_is_active('groups') && model\Settings::getOption(model\Settings::OPTION_USER_PUBLISHED_MAP_CREATE_ACTIVITY) && model\Settings::getOption(model\Settings::OPTION_BP_GROUPS_ENABLE_FOR_ROUTE)) {
					
					if($post_id) {
						$buddypressCurrentGroups = get_post_meta($post_id, '_cmmrm_bp_groups', true);
						if($buddypressCurrentGroups == '') {
							$buddypressCurrentGroups = array();
						}
					} else {
						$buddypressCurrentGroups = array();
					}

					$action = model\Activity::getBuddyPressUserLink( $post->post_author ) . ' ' . model\Labels::getLocalized('buddypress_activity_map_created');
					$content = model\Settings::getOption(model\Settings::OPTION_FEED_USER_PUBLISHED_MAP_TEMPLATE);
					$content = strtr($content, array(
						'[fullname]'	=> get_the_author_meta('display_name', $post->post_author),
						'[title]'		=> esc_html($post->post_title),
						'[permalink]'	=> get_post_permalink($post->ID),
						'[excerpt]'		=> $post->post_excerpt,
					));

					foreach($buddypressCurrentGroups as $route_group_id) {
						$gactivityArgs = array(
							'user_id'				 => $post->post_author,
							'action'				 => $action,
							'content'				 => $content,
							'component'				 => 'groups',
							'type'					 => 'activity_update',
							'item_id'				 => $route_group_id,
							'secondary_item_id'      => $post_id,
							'primary_link'			 => get_post_permalink($post->ID),
						);
						
						model\Activity::addBuddyPressActivity($gactivityArgs);
					}

					update_post_meta( $post_id, '_cmmrm_bp_groups_email_sent', 'yes' );

				}

			}
		}
	}

	static function routePublished($post) {
		
        if ( !bp_is_active( 'activity' ) ||
             !model\Settings::getOption(model\Settings::OPTION_USER_PUBLISHED_MAP_CREATE_ACTIVITY)) {
            return;
        }

        $route = model\Route::getInstance($post);
        $action = model\Activity::getBuddyPressUserLink( $post->post_author ) . ' ' . model\Labels::getLocalized('buddypress_activity_map_created');
        $content = model\Settings::getOption(model\Settings::OPTION_FEED_USER_PUBLISHED_MAP_TEMPLATE);
        $content = strtr($content, array(
            '[fullname]'  => $route->getAuthorDisplayName(),
            '[title]'     => esc_html($route->getTitle()),
            '[permalink]' => $route->getPermalink(),
            '[excerpt]'   => $route->getContentFragment(),
        ));

        $activityArgs = array(
            'user_id'      => $post->post_author,
            'action'       => $action,
            'content'      => $content,
            'component'    => self::CM_MAPS_ROUTES_BP_ACTIVITY_COMPONENT,
            'type'         => self::CM_MAPS_ROUTES_BP_ACTIVITY_TYPE,
            'primary_link' => $route->getPermalink(),
        );

        model\Activity::addBuddyPressActivity($activityArgs);

	}

}
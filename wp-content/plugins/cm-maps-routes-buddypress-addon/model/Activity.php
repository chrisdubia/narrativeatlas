<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\model;

class Activity extends Model {

	static function getBuddyPressUserLink( $userId ) {
        if ( function_exists('bp_core_get_userlink') ) {
            return bp_core_get_userlink($userId);
        }
	}

    static function getBuddyPressDisplayedUserId() {
        if ( function_exists('bp_displayed_user_id') ) {
            return bp_displayed_user_id();
        }
    }

    static function addBuddyPressActivity( array $args ) {
	    if ( empty($args) ) {
            return 0;
        }
        if ( function_exists('bp_activity_add') ) {
            return bp_activity_add($args);
        }
    }

}
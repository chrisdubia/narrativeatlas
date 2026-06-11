<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\model;

class Settings extends SettingsAbstract {

    const CMMRM_BUDDYPRESS_OPTIONS_PAGE = 'bp-maps-routes';
    const OPTION_USER_PROFILE_MAPS_TAB = 'cmmrm_buddypress_user_profile_maps_tab';
    const OPTION_USER_PROFILE_MAPS_SHOW_ROUTE_PARAMS = 'cmmrm_buddypress_user_profile_maps_show_route_params';
    const OPTION_USER_PROFILE_MAPS_LAYOUT = 'cmmrm_buddypress_user_profile_maps_layout';
    const OPTION_USER_PROFILE_MAPS_FEATURED = 'cmmrm_buddypress_user_profile_maps_featured';
    const OPTION_PROFILE_MAPS_SHOW_MANAGE_SHORTCODE = 'cmmrm_buddypress_profile_maps_show_manage_shortcode';
    const OPTION_USER_PROFILE_MANAGE_MAPS_TAB = 'cmmrm_buddypress_user_profile_manage_maps_tab';
    const OPTION_USER_PUBLISHED_MAP_CREATE_ACTIVITY = 'cmmrm_buddypress_user_published_map_create_activity';
    const OPTION_FEED_USER_PUBLISHED_MAP_TEMPLATE = 'cmmrm_buddypress_feed_user_published_map_template';
	const OPTION_BP_GROUPS_ENABLE_FOR_ROUTE = 'cmmrm_buddypress_groups_enable_for_route';
	const OPTION_BP_GROUPS_PRIVACY_FOR_ROUTE = 'cmmrm_buddypress_groups_privacy_for_route';
    const INDEX_LAYOUT_LIST = 'list';
    const INDEX_LAYOUT_TILES = 'tiles';
    const FEATURED_IMAGE = 'image';
    const FEATURED_MAP = 'map';

    const CMRM_BUDDYPRESS_CATEGORY_SLUG = 'buddypress';
    const CMRM_BUDDYPRESS_CATEGORY_NAME = 'BuddyPress';

    public static $categories = array(
        'general' => 'General',
    );

    public static $subcategories = array(
        'general' => array(
            'general' => 'General',
        ),
    );

    static function getOptionsConfig() {
        $result = array(
            Settings::OPTION_USER_PROFILE_MAPS_TAB => array(
                'type' => Settings::TYPE_BOOL,
                'default' => 1,
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'user-profile-maps',
                'title' => Labels::__('Show "Maps" tab in the user\'s profile'),
                'desc' => Labels::__('If enabled users will see the "Maps" tab in each user\'s profile that displays the maps created by the specific user.'),
            ),
            Settings::OPTION_USER_PROFILE_MAPS_SHOW_ROUTE_PARAMS => array(
                'type' => Settings::TYPE_BOOL,
                'default' => 1,
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'user-profile-maps',
                'title' => Labels::__('Show route\'s parameters'),
                'desc' => Labels::__('If enabled the route\'s parameters such as distance, max elevation etc. will be visible on the routes\' snippets.'),
            ),
            Settings::OPTION_USER_PROFILE_MAPS_LAYOUT => array(
                'type' => Settings::TYPE_SELECT,
                'default' => Settings::INDEX_LAYOUT_LIST,
                'options' => array(
                    Settings::INDEX_LAYOUT_LIST  => "List",
                    Settings::INDEX_LAYOUT_TILES => "Tiles",
                ),
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'user-profile-maps',
                'title' => Labels::__('Route\'s snippets layout'),
                'desc' => Labels::__('Choose route snippets layout.'),
            ),
            Settings::OPTION_USER_PROFILE_MAPS_FEATURED => array(
                'type' => Settings::TYPE_SELECT,
                'default' => static::FEATURED_IMAGE,
                'options' => array(
                    static::FEATURED_IMAGE => "First Route Image",
                    static::FEATURED_MAP   => "Map Thumbnail",
                ),
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'user-profile-maps',
                'title' => Labels::__('Route featured image'),
                'desc' => Labels::__('Choose what kind of the route\'s featured image to display in the user\'s profile page.'),
            ),
            Settings::OPTION_PROFILE_MAPS_SHOW_MANAGE_SHORTCODE => array(
                'type' => Settings::TYPE_BOOL,
                'default' => 0,
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'user-profile-maps',
                'title' => Labels::__('Manage routes under "My Maps" tab'),
                'desc' => Labels::__('If enabled user will can manage his routes under the same tab "My Maps".'),
            ),
            Settings::OPTION_USER_PROFILE_MANAGE_MAPS_TAB => array(
                'type' => Settings::TYPE_BOOL,
                'default' => 1,
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'user-profile-manage-maps',
                'title' => Labels::__('Show separate "Manage Maps" tab in the user\'s profile'),
                'desc' => Labels::__('If enabled user will see the "Manage Maps" tab that displays his maps and allow to edit/delete it.'),
            ),
            //activity feed
            Settings::OPTION_USER_PUBLISHED_MAP_CREATE_ACTIVITY => array(
                'type' => Settings::TYPE_BOOL,
                'default' => 0,
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'activity-feed',
                'title' => Labels::__('Create BuddyPress activity when user created a map'),
                'desc' => Labels::__('The activity post with the user\'s map link will be added to his BP Activities (wall).'),
            ),
            Settings::OPTION_FEED_USER_PUBLISHED_MAP_TEMPLATE => array(
                'type' => Settings::TYPE_TEXTAREA,
                'default' => 'Published a map: [title]' . PHP_EOL . PHP_EOL . '[excerpt]'. PHP_EOL . PHP_EOL .'You can view it here: [permalink]',
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'activity-feed',
                'title' => Labels::__('Activity post template for new map'),
                'desc' => Labels::__('Template for the activity post on the user\'s activity feed added after user published a map. You can use the following shortcodes:<br>'
                    . '[fullname] - user\'s display name<br />'
                    . '[title] - route\'s title<br />'
                    . '[permalink] - permalink to the route page<br />'
                    . '[excerpt] - excerpt of the route\'s description'),
            ),
			Settings::OPTION_BP_GROUPS_ENABLE_FOR_ROUTE => array(
                'type' => Settings::TYPE_BOOL,
                'default' => 0,
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'groups',
                'title' => Labels::__('Associate groups with route'),
                'desc' => Labels::__('If enabled user able to associate groups with route.'),
            ),
			Settings::OPTION_BP_GROUPS_PRIVACY_FOR_ROUTE => array(
                'type' => Settings::TYPE_SELECT,
                'default' => 'all',
                'options' => array(
                    'all' => "Members Only",
                    'public'=> "Public",
                    'private' => "Private",
                    'hidden' => "Hidden",
                ),
                'category' => static::CMRM_BUDDYPRESS_CATEGORY_SLUG,
                'subcategory' => 'groups',
                'title' => Labels::__('Groups privacy'),
                'desc' => Labels::__('Choose groups privacy for associate with route.'),
            ),
        );
        return $result;
    }

    static function getOption($name) {
        return parent::getOption($name);
    }

    static function setOption($name, $value) {
        parent::setOption($name, $value);
    }

    static function getRouteIndexPageParamsNames() {
        return apply_filters('cmmrm_get_route_index_params_names', array());
    }

    static function showManageElements($userId, $settingName) {
         return (bool)Settings::getOption($settingName) && is_user_logged_in() &&
            $userId == get_current_user_id() && $userId > 0;
    }

}
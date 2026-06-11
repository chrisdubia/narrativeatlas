<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\model;

class Route extends PostType {

    const POST_TYPE = 'cmmrm_route';
    const RETURN_OBJ = 'obj';
    const RETURN_POST = 'post';
    const RETURN_ID = 'ids';

    static function registerPostType() {
        // don't
    }

    static function getInstance($post) {
        return parent::getInstance($post);
    }

    function getContentFragment($len = 55, $ellipsis = '...') {
        return wp_trim_words(strip_tags($this->getContent()), $len, $ellipsis);
    }

    static function getByUser($userId, $return = Route::RETURN_OBJ, $args = array()) {
        if ($return == static::RETURN_ID) {
            $args['fields'] = 'ids';
        }
        $args = array_merge(array(
            'post_status' => 'publish',
            'author' => $userId,
            'post_type' => static::POST_TYPE,
            'order' => 'DESC',
            'orderby' => 'date',
            'posts_per_page' => -1,
        ), $args);
        $posts = get_posts($args);
		
		if (function_exists('groups_get_user_groups')) {

			$collaborative_routes = get_posts(array(
				'author' => -get_current_user_id(),
				'post_type' => Route::POST_TYPE,
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'meta_key'   => '_cmmrm_use_buddypress_collaborative',
				'meta_value' => '1'
			));

			$current_user_groups_arr = groups_get_user_groups(get_current_user_id());
			$current_user_groups = $current_user_groups_arr['groups'];
			$current_user_total = $current_user_groups_arr['total'];
			
			$unique_routes = array();
			if($current_user_total > 0) {
				foreach($current_user_groups as $current_user_group_id) {
					foreach($collaborative_routes as $croute) {
						$route_bp_groups = get_post_meta($croute->ID, '_cmmrm_bp_groups', true);
						if($route_bp_groups) {
							if(is_array($route_bp_groups) && count($route_bp_groups) > 0) {
								if(in_array($current_user_group_id, $route_bp_groups)) {
									if(!in_array($croute->ID, $unique_routes)) {
										$posts[] = $croute->ID;
										$unique_routes[] = $croute->ID;
									}
								}
							}
						}
					}
				}
			}

		}

        if ($return == static::RETURN_OBJ) {
            return array_map(function($post) { return Route::getInstance($post); }, $posts);
        } else {
            return $posts;
        }
    }

}
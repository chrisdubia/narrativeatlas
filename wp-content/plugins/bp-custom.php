<?php

//// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;


// disables wp 5.9 language switcher on Login page
add_filter( 'login_display_language_dropdown', '__return_false' );

// add_action( 'template_redirect', 'buddydev_private_site', 0 );

/**
 * Filter nav items to create dynamic link.
 *
 * @param array $items nav items.
 *
 * @return array
 */
function buddydev_filter_dynamic_group_link_in_nav( $items ) {
	if ( ! function_exists( 'bp_is_group' ) || ! bp_is_group() ) {
		return $items;
	}

	$items = str_replace( '#current-group#', groups_get_current_group()->slug, $items );

	return $items;
}

add_filter( 'wp_nav_menu_items', 'buddydev_filter_dynamic_group_link_in_nav' );

/**
 * Add condition to If Menu for current group.
 *
 * @param array $conditions conditions.
 *
 * @return array
 */
function buddydev_custom_add_is_group_menu_conditions( $conditions ) {

	if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'groups' ) ) {
		return $conditions;
	}

	$conditions[] = array(
		'id'        => 'bp-is-single-group', // unique ID for the rule.
		'name'      => __( 'Is BuddyPress Group' ),
		'condition' => function ( $item ) {
			return bp_is_group();
		},
	);

	return $conditions;
}

add_filter( 'if_menu_conditions', 'buddydev_custom_add_is_group_menu_conditions' );


/**
 * Email From/Name helper.
 */
class NAtlas_Custom_Email_From {
	/**
	 * Boot.
	 */
	public static function boot() {
		static $self;
		if ( is_null( $self ) ) {
			$self = new self();
			$self->setup();
		}
	}

	/**
	 * NAtlas_Custom_Email_From constructor.
	 */
	private function __construct() {
	}

	/**
	 * Setup hooks.
	 */
	private function setup() {
		add_filter( 'wp_mail_from_name', array( $this, 'from_name' ) );
		add_filter( 'wp_mail_from', array( $this, 'from_email' ) );
	}

	/**
	 * Set From Name.
	 *
	 * @param string $name from name.
	 *
	 * @return string
	 */
	public function from_name( $name ) {
		return get_option( 'blogname' );
	}

	/**
	 * Set from email.
	 *
	 * @param string $email email.
	 *
	 * @return mixed|void
	 */
	public function from_email( $email ) {
		return get_option( 'admin_email' );
	}
}
// Boot.
NAtlas_Custom_Email_From::boot();

/**
 * Set expiration of User Invite/Password reset request to 3 days.
 */
add_filter( 'password_reset_expiration', function ( $val ) {
	return 3 * DAY_IN_SECONDS;
} );

/* end of bbpress forum pending topic edit */
// set a very small timeout for BuddyPress group subscription
// @see https://github.com/boonebgorges/buddypress-group-email-subscription/issues/175


add_filter(
	'bpges_async_request_http_request_args',
	function( $args ) {
		$args['timeout']     = 0.01;
		$args['redirection'] = 2;
		$args['blocking']    = false;
		$args['sslverify'] = false;
		return $args;
	}
);

add_action( 'bp_media_add', function () {
	if ( has_action( 'bp_activity_after_save', 'ass_group_notification_activity' ) ) {
		remove_action( 'bp_activity_after_save', 'ass_group_notification_activity', 50 );
	}
}, 2 );

/**
 * Allow duplicate for all users.
 *
 * @todo ask Chris to identify if we should only do it for group admins?
 */
add_filter( 'user_has_cap', function ( $allcaps, $cap, $args ) {

	if ( 'throttle' != $args[0] ) {
		return $allcaps;
	}

	if ( is_user_logged_in() ) {
		$allcaps[ $cap[0] ] = true;
	}

	return $allcaps;
}, 200, 3 );


add_filter( 'wp_video_extensions', function($exts ) {

	return array_merge( $exts, array('3gp', '3gpp', 'mov'));
});

function custom_as3cf_upload_object_key_as_private( $is_private, $object_key, $as3cf_item ) {
	return true;
}

add_filter( 'as3cf_upload_object_key_as_private', 'custom_as3cf_upload_object_key_as_private', 10, 3 );

// added GES customization on Dec 11, 2022.
// Disables GES option to enable/disable subscription via link in group header.
add_action( 'bp_init', function () {

	if ( ! function_exists( 'ass_group_subscribe_button' ) ) {
		return;
	}

	$action = has_action( 'bp_group_header_meta', 'ass_group_subscribe_button' );
	if ( false !== $action ) {
		remove_action( 'bp_group_header_meta', 'ass_group_subscribe_button', $action );
	}

	$action = has_action( 'bp_directory_groups_actions', 'ass_group_subscribe_button' );
	if ( false !== $action ) {
		remove_action( 'bp_directory_groups_actions', 'ass_group_subscribe_button', $action );
	}

} );
// As of December 11, 2022, BuddyPress Group Email subscription only offers this filter to update default preference
// we set the default preference for GES to 'all eails'.
add_filter( 'bp_ges_group_user_subscriptions', function ( $subscriptions, $group_id ) {
	$updated_user_ids = array_keys( $subscriptions );
	$group_member_ids = BP_Groups_Member::get_group_member_ids( $group_id );
	$not_updated      = array_diff( $group_member_ids, $updated_user_ids );

	foreach ( $not_updated as $not_updated_id ) {
		$subscriptions[ $not_updated_id ] = 'dig';////'supersub';
	}

	return $subscriptions;
}, 10, 2 );

/**
 * Forces/Ovewrited subscription to be daily.
 *
 * @param string $notification notification level.
 *
 * @return string
 */
function natlas_ges_set_default_notifications_daily( $notification ) {
	return 'dig';
}

// add_filter( 'ass_default_subscription_level', 'natlas_ges_set_default_notifications_daily' );
// add_filter( 'ass_get_default_subscription', 'natlas_ges_set_default_notifications_daily' );

/**BuddyBoss form media fix for visibility in groups*/
//// New:------
/**
 * Retrieves filter privacy args
 *
 * @param array $r Args.
 *
 * @return array
 */
function buddydev_filter_privacy_args( $r ) {

	if ( ! empty( $r['group_id'] ) ) {
		$r['privacy'] = (array) $r['privacy'];

		if ( ! in_array( 'forums', $r['privacy'] ) ) {
			$r['privacy'][] = 'forums';
		}
	}

	return $r;
}

// Filter args while fetching media, video and documents.
add_filter( 'bp_after_has_media_parse_args', 'buddydev_filter_privacy_args' );
add_filter( 'bp_after_has_video_parse_args', 'buddydev_filter_privacy_args' );
add_filter( 'bp_after_has_document_parse_args', 'buddydev_filter_privacy_args' );

/**
 * Retrieves scoped user id.
 *
 * @param array $args Args.
 *
 * @return int
 */
function buddydev_get_scoped_user_id( $args ) {
	$user_id = empty( $args['user_id'] ) ? 0 : absint( $args['user_id'] );

	if ( ! $user_id ) {
		$user_id = bp_is_user() ? bp_displayed_user_id() : bp_loggedin_user_id();
	}

	return $user_id;
}

/**
 * Retrieves filtered group ids.
 *
 * @param array $args Args.
 *
 * @return array
 */
function buddydev_get_filtered_group_ids( $args ) {
	$user_id = buddydev_get_scoped_user_id( $args );

	if ( 'groups' !== $args['scope'] ) {
		// Fetch public groups.
		$public_groups = groups_get_groups(
			array(
				'fields'   => 'ids',
				'status'   => 'public',
				'per_page' => - 1,
			)
		);
	}

	$public_groups = empty( $public_groups['groups'] ) ? array() : $public_groups['groups'];

	// Determine groups of user.
	$groups = groups_get_user_groups( $user_id );
	$groups = empty( $groups['groups'] ) ? array() : $groups['groups'];

	$group_ids = false;
	if ( ! empty( $groups ) && ! empty( $public_groups ) ) {
		$group_ids = array( array_unique( array_merge( $groups, $public_groups ) ) );
	} elseif ( empty( $groups ) && ! empty( $public_groups ) ) {
		$group_ids = array( $public_groups );
	} elseif ( ! empty( $groups ) && empty( $public_groups ) ) {
		$group_ids = array( $groups );
	}

	if ( empty( $group_ids ) ) {
		$group_ids = array( 0 );
	}

	if ( bp_is_group() ) {
		$group_ids = array( bp_get_current_group_id() );
	}

	return (array) $group_ids;
}

/**
 * Retrieves scoped group privacy args
 *
 * @param int[] $group_ids Group ids.
 *
 * @return array
 */
function buddydev_get_scoped_group_privacy_args( $group_ids ) {
	return array(
		'relation' => 'AND',
		array(
			'column'  => 'group_id',
			'compare' => 'IN',
			'value'   => $group_ids,
		),
		array(
			'column'  => 'privacy',
			'compare' => 'IN',
			'value'   => array( 'grouponly', 'forums' ),
		),
	);
}

// Update privacy in while scoping args.
add_filter( 'bp_media_set_groups_scope_args', function ( $retval, $filter ) {
	$group_ids = buddydev_get_filtered_group_ids( $filter );
	$args      = buddydev_get_scoped_group_privacy_args( $group_ids );

	if ( ! bp_is_group_albums_support_enabled() ) {
		$args[] = array(
			'column'  => 'album_id',
			'compare' => '=',
			'value'   => '0',
		);
	}

	if ( ! empty( $filter['search_terms'] ) ) {
		$args[] = array(
			'column'  => 'title',
			'compare' => 'LIKE',
			'value'   => $filter['search_terms'],
		);
	}

	$retval = array(
		'relation' => 'OR',
		$args
	);

	return $retval;
}, 15, 2 );
add_filter( 'bp_video_set_groups_scope_args', function ( $retval, $filter ) {
	$group_ids = buddydev_get_filtered_group_ids( $filter );
	$args      = buddydev_get_scoped_group_privacy_args( $group_ids );

	if ( ! bp_is_group_video_support_enabled() ) {
		$args[] = array(
			'column'  => 'album_id',
			'compare' => '=',
			'value'   => '0',
		);
	}

	if ( ! empty( $filter['search_terms'] ) ) {
		$args[] = array(
			'column'  => 'title',
			'compare' => 'LIKE',
			'value'   => $filter['search_terms'],
		);
	}

	$retval = array(
		'relation' => 'OR',
		$args,
	);

	return $retval;
}, 15, 2 );
add_filter( 'bp_document_set_document_groups_scope_args', function ( $retval, $filter ) {
	$folder_id = empty( $filter['folder_id'] ) ? 0 : absint( $filter['folder_id'] );
	$folders   = array();

	if ( ! empty( $filter['search_terms'] ) && ! empty( $folder_id ) ) {
		$folder_ids       = array();
		$fetch_folder_ids = bp_document_get_folder_children( $folder_id );

		if ( $fetch_folder_ids ) {
			foreach ( $fetch_folder_ids as $single_folder ) {
				$single_folder_ids = bp_document_get_folder_children( (int) $single_folder );

				if ( $single_folder_ids ) {
					$folder_ids = array_merge( $folder_ids, $single_folder_ids );
				}

				array_push( $folder_ids, $single_folder );
			}
		}

		$folder_ids[] = $folder_id;

		$folders      = array(
			'column'  => 'parent',
			'compare' => 'IN',
			'value'   => array_unique( $folder_ids ),
		);
	} else {

		if ( ! empty( $folder_id ) ) {
			$folders = array(
				'column'  => 'folder_id',
				'compare' => '=',
				'value'   => $folder_id,
			);
		} else {
			$folders = array(
				'column' => 'folder_id',
				'value'  => 0,
			);
		}
	}

	$group_ids = array( 0 );

	if ( bp_is_group_document_support_enabled() ) {
		$group_ids = buddydev_get_filtered_group_ids( $filter );
	}

	$args = buddydev_get_scoped_group_privacy_args( $group_ids );

	array_push( $args, $folders );

	return $args;
}, 15, 2 );

// For navigation count.
add_filter( 'bp_media_get_where_count_conditions', function ( $where_conditions, $args ) {
	global $wpdb;

	if ( ! empty( $args['group_id'] ) ) {
		$privacies = array( 'grouponly', 'forums' );
		$prepared = array();

		foreach ( $privacies as $privacy ) {
			$privacy    = trim( $privacy );
			$prepared[] = $wpdb->prepare( '%s', $privacy );
		}

		$where_conditions['group_privacy'] = sprintf( 'm.privacy IN ( %s )', implode( ',', $prepared ) );
	}

	return $where_conditions;
}, 10, 2 );
add_filter( 'bp_video_get_where_conditions', function ( $where_conditions, $args ) {
	global $wpdb;

	if ( ! empty( $args['group_id'] ) ) {
		$privacies = array( 'grouponly', 'forums' );
		$prepared = array();

		foreach ( $privacies as $privacy ) {
			$privacy    = trim( $privacy );
			$prepared[] = $wpdb->prepare( '%s', $privacy );
		}

		$where_conditions['privacy'] = sprintf( 'm.privacy IN ( %s )', implode( ',', $prepared ) );
	}

	return $where_conditions;
}, 10, 2 );

// Update privacy property on instance.
add_action( 'bp_media_before_save', function ( BP_Media $media ) {

	if ( empty( $media->id ) || empty( $media->group_id ) ) {
		return;
	}

	if ( doing_action( 'wp_ajax_media_move' ) ) {
		global $wpdb;

		$table = buddypress()->media->table_name;

		$privacy = $wpdb->get_var( $wpdb->prepare( "SELECT privacy FROM {$table} WHERE id=%d", absint( $media->id ) ) );

		if ( 'forums' == $privacy ) {
			$media->privacy = $privacy;
		}
	}
} );
add_action( 'bp_video_before_save', function ( BP_Video $video ) {

	if ( empty( $video->id ) || empty( $video->group_id ) ) {
		return;
	}

	if ( doing_action( 'wp_ajax_video_move' ) ) {
		global $wpdb;

		$table = buddypress()->media->table_name;

		$privacy = $wpdb->get_var( $wpdb->prepare( "SELECT privacy FROM {$table} WHERE id=%d", absint( $video->id ) ) );

		if ( 'forums' == $privacy ) {
			$video->privacy = $privacy;
		}
	}
} );

add_action( 'bp_document_before_save', function ( BP_Document $document ) {

	if ( empty( $document->id ) || empty( $document->group_id ) ) {
		return;
	}

	if ( doing_action( 'wp_ajax_document_move' ) ) {
		global $wpdb;

		$table = buddypress()->document->table_name;

		$privacy = $wpdb->get_var( $wpdb->prepare( "SELECT privacy FROM {$table} WHERE id=%d", absint( $document->id ) ) );

		if ( 'forums' == $privacy ) {
			$document->privacy = $privacy;
		}
	}
} );

add_filter( 'usin_field_types', function ( $field_types ) {

	$field_types['bbp_forum_users'] = array(
		'operators' => array(
			array('key' => 'bbp_replied' , 'val' => __('Replied On', 'usin')),
			array('key' => 'bbp_no_replied' , 'val' => __('Not Replied On', 'usin')),
		),
		'type' => 'option'
	);

	return $field_types;
} );

// User Insights filter to allow search by topic title.
add_filter( 'usin_fields', function ( $fields ) {

	if ( ! function_exists( 'bbpress' ) || ! USIN_Modules::get_instance()->is_module_active( 'bbpress' ) ) {
		return $fields;
	}

	if ( ! empty( $fields ) && is_array( $fields ) ) {
		$topic_search         = new USIN_Post_Option_Search( bbp_get_topic_post_type() );
		$topic_search_options = $topic_search->get_options();
		$topic_search_action  = $topic_search->get_search_action();

		$fields[] = array(
			'name'      => __( 'Forum Users', 'usin' ),
			'id'        => 'bbp_forum_users',
			'order'     => false,
			'show'      => true,
			'fieldType' => 'bbpress',
			'module'    => 'bbpress',
			'filter'    => array(
				'type'         => 'bbp_forum_users',
				'options'      => $topic_search_options,
				'searchAction' => $topic_search_action,
			),
		);
	}

	return $fields;
} );
add_filter( 'usin_custom_select', function($value, $field ) {
	global $wpdb;

	if ( 'bbp_forum_users' === $field && function_exists( 'bbpress' ) && USIN_Modules::get_instance()->is_module_active( 'bbpress' ) ) {
		//$value =  "IFNULL({$wpdb->posts}.post_parent, 0)";
	}

	return $value;
}, 10, 2);


add_filter( 'usin_db_map', function ( $db_map ) {

	if ( function_exists( 'bbpress' ) && USIN_Modules::get_instance()->is_module_active( 'bbpress' ) ) {
		global $wpdb;

		$db_map['bbp_forum_users'] = array(
			//'db_ref'       => 'post_parent',
			'db_table'     => $wpdb->posts,
			'custom_select' => true,
			'null_to_zero' => true,
			'set_alias'    => true
		);
	}

	return $db_map;
} );

/*
add_filter( 'usin_db_map', function ( $db_map ) {

	if ( function_exists( 'bbpress' ) && USIN_Modules::get_instance()->is_module_active( 'bbpress' ) ) {
		global $wpdb;

		$db_map['bbp_forum_users'] = array(
			'db_ref'       => 'post_parent',
			'db_table'     => $wpdb->posts,
			'null_to_zero' => true,
			'set_alias'    => true
		);
	}

	return $db_map;
} );
*/
add_filter( 'usin_custom_query_filter', function ( $custom_query_data, $filter ) {

	if ( 'bbp_forum_users' != $filter->by || empty( $filter->condition ) || ! function_exists( 'bbpress' ) ) {
		return $custom_query_data;
	}

	if ( USIN_Modules::get_instance()->is_module_active( 'bbpress' ) ) {
		global $wpdb;

		$operator = 'bbp_replied' == $filter->operator ? 'IN' : 'NOT IN';

		$where_query = $wpdb->prepare( "{$wpdb->users}.ID {$operator} (SELECT post_author FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %d)", bbp_get_reply_post_type(), $filter->condition  );

		$custom_query_data['where'] = " AND $where_query";
	}

	return $custom_query_data;
}, 10, 2 );

/**
 * Group members notifiers in case of group forum topic reply only.
 */
class BDev_Group_Members_Notifier {

	/**
	 * Boots class.
	 */
	public static function boot() {
		$self = new self();

		add_action( 'bp_loaded', array( $self, 'setup' ) );
	}

	/**
	 * Sets up class.
	 */
	public function setup() {

		if ( ! bp_is_active( 'groups' ) || ! bp_is_active( 'forums' ) ) {
			return;
		}

		// Just to skip the conditions in case of empty topic subscribers.
		// Assuming this filter not using anywhere.
		// @see bbp_notify_topic_subscribers
		// @todo take case of it in future.
		add_filter( 'bbp_topic_subscription_user_ids', function ( $user_ids ) {
			return empty( $user_ids ) ? array( get_current_user_id() ) : $user_ids;
		} );

		add_action( 'bbp_pre_notify_subscribers', array( $this, 'attach_filter' ) );
		add_action( 'bbp_post_notify_subscribers', array( $this, 'remove_filter' ) );
	}

	/**
	 * Attaches filter.
	 */
	public function attach_filter() {
		add_filter( 'bp_after_bb_get_subscription_users_parse_args', array( $this, 'filter_subscription_args' ) );
	}

	/**
	 * Removes filter.
	 */
	public function remove_filter() {
		remove_filter( 'bp_after_bb_get_subscription_users_parse_args', array( $this, 'filter_subscription_args' ), 10 );
	}

	/**
	 * Filters subscription users args in case of topic reply.
	 *
	 * @param array $args Retrieves subscriptions args.
	 *
	 * @return array
	 */
	public function filter_subscription_args( $args ) {

		if ( ! isset( $args['type'] ) || 'topic' !== $args['type'] ) {
			return $args;
		}

		// Retrieves forum id.
		$forum_id = empty( $args['item_id'] ) ? 0 : bbp_get_topic_forum_id( $args['item_id'] );

		if ( ! $forum_id ) {
			return $args;
		}

		// @see bbp_notify_forum_subscribers for taking group id of forum id.
		$group_ids = bp_is_active( 'groups' ) && function_exists( 'bbp_get_forum_group_ids' ) ? bbp_get_forum_group_ids( $forum_id ) : array();
		$group_id   = ( ! empty( $group_ids ) ? current( $group_ids ) : 0 );

		if ( $group_id ) {
			$args['type']    = 'group';
			$args['item_id'] = $group_id;
		}

		return $args;
	}
}

BDev_Group_Members_Notifier::boot();

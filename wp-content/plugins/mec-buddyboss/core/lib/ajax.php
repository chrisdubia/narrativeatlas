<?php

/**
 * Webnus MEC Featured, Backend ui class.
 * @author Webnus <info@webnus.biz>
 */
class BP_MEC_Ajax {

	public $main;

	public function init() {

		$this->main = new \MEC_main();

		add_action('wp_ajax_nopriv_mec_bp_event', array($this, 'event_content'));
		add_action('wp_ajax_mec_bp_event', array($this, 'event_content'));

		add_action('wp_ajax_mec_bp_assign', array($this, 'assign'));
		add_action('wp_ajax_mec_bp_event_list', array($this, 'event_list'));

	}

	public function get_event_detail( $event_id, $book_id ){

		$start_timestamp = '';
		$single_date_method = '';
		if( $book_id && $event_id !== $book_id ){

			$attention_time = get_post_meta($book_id, 'mec_attention_time', true);
			$ex = explode( ':', $attention_time );
			$start_timestamp = $ex[0];
			$end_timestamp = $ex[1];
			$single_date_method = 'single';
		}

		$event_object = new \MEC\Events\Event( $event_id );
		$timestamp = !empty( $start_timestamp ) ? $start_timestamp : '';
		$event = $event_object->get_detail('', (int)$timestamp - 86400);
		$event->permalink = $event_object->get_permalink( $timestamp, true, $single_date_method );

		$event->id2 = $book_id ? $book_id : $event_id;

		return $event;
	}

	public function event_list() {

		$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : null;
		$category = isset($_REQUEST['category']) ? $_REQUEST['category'] : null;
		$query = isset($_REQUEST['query']) ? $_REQUEST['query'] : null;
		$group_id = null;

		if (bp_mec_is_mec_groups_enabled() && bp_is_group()) {
			$group_id = bp_get_current_group_id();
		}

		$args = array(
			'post_type' => $status == 'created' ? 'mec-events' : 'mec-books',
			'numberposts' => -1,
			'post_status' => 'publish',

		);

		if ($category !== null && strlen($category) > 0) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'mec_category',
					'field' => 'term_id',
					'terms' => array($category),
				));
		}

		if ($query !== null && strlen($query) > 0) {
			$args['s'] = "{$query}";
		}

		if ($group_id !== null && $group_id > 0) {
			$args['meta_query'] = array(
				'filter_bp_group' => array(
					array(
						'key' => "mec_bp_group_{$group_id}",
						'value' => $group_id,
						'compare' => '=',
					),
				)
			);
		} else {
			$is_current_user_profile = isset( $_REQUEST['is_current_user_profile'] ) && 'yes' === $_REQUEST['is_current_user_profile'] ? true : false;
			$args['author'] = $is_current_user_profile ? bp_loggedin_user_id() : bp_displayed_user_id();
		}

		$all = get_posts($args);

		$data = new \stdClass;
		$data->created = true;
		$data->booked = false;
		$data->events = [];

		foreach ($all as $k => $post) {

			if ($status == 'created') {

				$event_id = $post->ID;
			} else {

				$event_id = get_post_meta($post->ID, 'mec_event_id', true);
			}

			$data->events[] =  $this->get_event_detail( $event_id, $post->ID );
		}

		ob_start();
		include MECBUDDYBOSSDIR . DS . 'core' . DS . 'templates' . DS . 'loop-event-list.php';
		$ret = ob_get_clean();

		return wp_send_json_success($ret);
	}

	public function event_content() {
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
		$id2 = isset($_REQUEST['id2']) ? $_REQUEST['id2'] : null;
		if (!$id) {
			return wp_send_json_error(array('status' => false, 'error' => 'bad request'));
		}

		ob_start();

		if ($id == 'create') {

			include MECBUDDYBOSSDIR . DS . 'core' . DS . 'templates' . DS . 'add_event_single.php';

		} else {

			$event = $this->get_event_detail( $id, $id2 );

			include MECBUDDYBOSSDIR . DS . 'core' . DS . 'templates' . DS . 'event_content.php';
		}
		$ret = ob_get_clean();

		return wp_send_json_success($ret);
	}

	public function assign() {

		if (!bp_mec_is_mec_groups_enabled(0, true)) {
			return wp_send_json_error(array('status' => false, 'error' => 'Failed Request, Disabled!'));
		}

		$event_id = isset($_REQUEST['event_id']) ? $_REQUEST['event_id'] : null;
		$group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : null;
		$assign_action = isset($_REQUEST['assign_action']) ? $_REQUEST['assign_action'] : null;

		if (!$event_id || !$group_id || !$assign_action) {
			return wp_send_json_error(array('status' => false, 'error' => 'bad request'));
		}

		$user_id = bp_loggedin_user_id();


		if(bp_mec_is_user_can_event_change($group_id)==false){
			return wp_send_json_error(array('status' => false, 'error' => 'Access Deny'));
		}

		if ($assign_action == 'add') {

			BP_MEC_Group_Helper::update_event_group_id( $event_id, $group_id );
		} else if ($assign_action == 'del') {

			$group_assigned_id = isset($_REQUEST['group_assigned_id']) ? $_REQUEST['group_assigned_id'] : null;

			BP_MEC_Group_Helper::remove_event_group_id( $event_id, $group_assigned_id );
		}

		return wp_send_json_success(array(
			'new_groups' => mec_bp_get_all_groups_formated($event_id)
		));
	}

}

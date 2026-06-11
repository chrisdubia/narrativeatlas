<?php

class BP_MEC_Group_Helper {

    /**
     * Return event groups ids
     *
     * @param int $event_id
     *
     * @return array
     */
    public static function get_event_groups_ids( $event_id ){

        $groups_ids = array();

        global $wpdb;
        $sql = "SELECT `meta_value` as group_id FROM {$wpdb->prefix}postmeta WHERE post_id={$event_id} AND meta_key LIKE 'mec_bp_group_%' ORDER BY meta_id ASC";
        $rows = $wpdb->get_results($sql,ARRAY_A);

        foreach( $rows as $row ){

            $group_id = $row['group_id'];
            $groups_ids[ $group_id ] = $group_id;
        }

        return $groups_ids;
    }

    /**
     * Remove event group
     *
     * @param int $event_id
     * @param int[] $groups
     * @param bool $run_hook
     *
     * @return void
     */
    public static function remove_event_group_id( $event_id, $group_id, $run_hook = true ){

        delete_post_meta($event_id, "mec_bp_group_{$group_id}");

        if( $run_hook ){

            do_action('mec_assign_event_to_group', $event_id, $group_id, 'delete' );
        }
    }

    /**
     * Update event group
     *
     * @param int $event_id
     * @param int[] $groups
     * @param bool $run_hook
     *
     * @return void
     */
    public static function update_event_group_id( $event_id, $group_id, $run_hook = true  ){

        $saved_group_id = get_post_meta($event_id, "mec_bp_group_{$group_id}", true);

        update_post_meta($event_id, "mec_bp_group_{$group_id}", $group_id);

        if( $run_hook ){

            if( $saved_group_id ){

                do_action('mec_assign_event_to_group', $event_id, $group_id, 'update' );
            }else{

                do_action('mec_assign_event_to_group', $event_id, $group_id, 'add' );
            }
        }
    }

    /**
     * Remove event groups id,name
     *
     * @param int $event_id
     *
     * @return void
     */
    public static function remove_all_event_groups( $event_id ) {

        $groups_ids = static::get_event_groups_ids( $event_id );

        foreach( $groups_ids as $group_id ){

            static::remove_event_group_id( $event_id, $group_id );
        }
    }

    /**
     * Update event groups ids
     *
     * @param int $event_id
     * @param int[] $groups
     *
     * @return void
     */
    public static function update_event_groups_ids( $event_id, $groups ){

        $saved_groups = static::get_event_groups_ids( $event_id );
        if( empty( $groups ) && empty( $saved_groups ) ) {
            return;
        }

        foreach( $groups as $group_id ){

            if( !in_array( $group_id, $saved_groups, false ) ){

                static::update_event_group_id( $event_id, $group_id );
            }else{

                unset( $saved_groups[ $group_id ] );
            }
        }

        foreach( $saved_groups as $group_id ){ // remove others

            static::remove_event_group_id( $event_id, $group_id );
        }
    }

    /**
     * Return event groups id,name
     *
     * @param int $event_id
     *
     * @return array|null
     */
    public static function get_event_groups( $event_id ) {

        $all = static::get_event_groups_ids( $event_id );

        if (!$all) {
            return null;
        }

        $ret = [];
        foreach ($all as $group_id) {

            $group = groups_get_group( $group_id );
            if (!$group) {
                continue;
            }

            $ret[] = [
                'id' => $group_id,
                'name' => $group->name,
				'status' => $group->status,
				'is_visible' => $group->is_visible,
				'link' => bp_get_group_permalink( $group ),
            ];
        }

        if (count($ret) > 0) {
            return $ret;
        }

        return null;
    }
}

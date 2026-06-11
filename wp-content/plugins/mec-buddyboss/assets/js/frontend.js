function mec_bp_Events_Load(event_id, id2) {

    var main_elem = jQuery('#bp-mec-single-events-wrapper');

    main_elem.block({
        message: null
    });


    jQuery.ajax({
            url: MEC_BUDDYBOSS_VARS.ajaxurl,
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'mec_bp_event',
                id: event_id,
                id2: id2,
            },
        })
        .done(function(data) {

            jQuery('.events-item').removeClass('current');
            jQuery('#event-show-row-' + event_id).addClass('current');


            main_elem.html(data.data);

            return false;
        })
        .fail(function() {
            console.log("error");
        })
        .always(function() {
            main_elem.unblock();
            console.log("complete");
        });
    return false;
}


function mec_bp_Events_Assign(event_id, group_assigned_id, assign_action) {
    var sel = jQuery('#bp-mec-single-events-wrapper').find('#mec-bp-groups');
    var group_id = sel.val();

    if (group_assigned_id != null) {
        group_id = group_assigned_id;
    }

    if (group_id == '' || group_id == null || typeof group_id == 'undefined') {
        console.log('group_id is null');
        return;
    }

    jQuery.ajax({
            url: MEC_BUDDYBOSS_VARS.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                'action': 'mec_bp_assign',
                'assign_action': assign_action,
                'event_id': event_id,
                'group_id': group_id,
                'group_assigned_id': group_assigned_id
            },
        })
        .done(function(data) {
            console.log("success", data);
            if (data.success == true) {
                jQuery('#mec-bp-groups-assign-list-area').html(data.data.new_groups);
            }
        })
        .fail(function(data) {
            console.log("error", data);
        })
        .always(function() {
            console.log("complete");
        });


    return false;
}


function mec_bp_Event_List(category, query) {

    var status = jQuery('#mec-bp-status_load').val();
    var cat_send = category;
    var query_send = query;
    if (category == null) {
        cat_send = jQuery('#mec-bp-events-category').val();
    }

    if (query == null) {
        query_send = jQuery('#bp_mec_events_search').val();
    }

    var main_elem = jQuery('#eventss-list');

    main_elem.block({
        message: null
    });

    jQuery.ajax({
            url: MEC_BUDDYBOSS_VARS.ajaxurl,
            type: 'GET',
            dataType: 'json',
            data: {
                'action': 'mec_bp_event_list',
                'category': cat_send,
                'query': query_send,
                'status': status,
                'is_current_user_profile' : MEC_BUDDYBOSS_VARS.is_current_user_profile,
            },
        })
        .done(function(data) {

            main_elem.html(data.data);

            if( 0 == jQuery('#bp-mec-events-container .bp-mec-events-right #bp-mec-events-content #bp-mec-single-events-wrapper').length ){

                return;
            }

            var html = jQuery('#bp-mec-events-container .bp-mec-events-right #bp-mec-events-content #bp-mec-single-events-wrapper').html().replaceAll('\n','').replaceAll('\t','').replaceAll(' ','');
            if( html.length == 0 ){

                jQuery('#eventss-list li:first-child a').trigger('click');
            }

            return false;
        })
        .fail(function() {
            console.log("error");
        })
        .always(function() {
            main_elem.unblock();
        });
    return false;
}


jQuery(document).ready(function($) {

    // Add Notification DropDown Select2
    jQuery(".mec-bp-group-dropdown-select2").select2({
        closeOnSelect: false,
        width: '33%'
    });

    mec_bp_Event_List(null, null);

    jQuery('#mec-bp-events-category').change(function(event) {
        mec_bp_Event_List(jQuery(this).val(), null);
    });

    jQuery('#bp_mec_events_search').keyup(function(event) {
        mec_bp_Event_List(null, jQuery(this).val());
    });

    jQuery('#bp_mec_events_search_form').submit(function(event) {
        mec_bp_Event_List(null, null);
        return false;
    });

    if ($(".bb-sticky-sidebar").length > 0 && jQuery("#mec_fes_form").length > 0) {
        jQuery(".mec-fes-form-cntt,.mec-fes-form-sdbr,.mec-fes-submit-wide").css("width", "100%")
        jQuery(".mec-fes-form-cntt,.mec-fes-form-sdbr,.mec-fes-submit-wide").css("padding", "0")
    }

});
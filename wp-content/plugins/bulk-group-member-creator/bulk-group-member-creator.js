jQuery(function ($) {
    var $attachedGroups = $('ul#bulk-group-member-creator-group-list');
    var listTemplate = _.template('<li class="vcard" id="group-<%= id %>"><div class="item-avatar"><%= avatar %><input type="hidden" value="<%= id %>" name="bulk-group-member-creator-selected-group-ids[]"  class="bulk-group-member-creator-selected-group-ids"/></div><div class="item-title"><a href="<%= url %>"><%= name %></a></div><span title="Remove" class="bulk-group-member-creator-remove-group">x</span></li>');
    var $status = $('#bgmu-status'),
        $log = $('#bgmu-logs'),
    $bulk_action_button = $('#bulk-group-member-creator-form-submit-btn');

    $('#bulk-group-member-creator-autocomplete').autocomplete({
        minLength: 2,
        source: function(req, add){
            // pass request to server
            $.post(
                ajaxurl,
                {
                    action:     'bulk_group_member_creator_get_groups',
                    q:           req.term,
                    _ajax_nonce: this.element.attr('data-nonce')
                },
                function(resp) {
                    add(resp.data);
                },
                'json'
            );
        },
        select: function(e, ui) {
            var groupExists = $attachedGroups.find( 'li#group-' + ui.item.id );

            if ( groupExists.length ) {
                $('#bulk-group-member-creator-autocomplete').val('');
                groupExists.animate( {borderColor: 'red',borderWidth:'5px'}, 200 );
                return false;
            }

            $attachedGroups.append(listTemplate({id: ui.item.id, name: ui.item.label, url: ui.item.url, avatar: ui.item.icon}));

            this.value='';
            return false;// do not update input box
        }
    });

    //remove
    $attachedGroups.on( 'click', 'span.bulk-group-member-creator-remove-group', function() {
        $(this).parents('li').remove();

        return false;
    });

    $bulk_action_button.click( function (e) {
        e.preventDefault();

        $log.empty();
        var $emailField = $('#bulk-group-member-creator-emails');
       var emails = $emailField.val();
        var group_ids = [];
       $('.bulk-group-member-creator-selected-group-ids').each(function() {
            group_ids.push($(this).val());
        });

        if ( ! emails.length || ! group_ids.length ) {
            alert('Emails or groups are not provided');
            return;
        }
        $(this).prop('disabled', true );
        $status.html('<strong>Status:</strong> Processing, Please wait.');
        $status.show();
        addToGroup(emails.split(','), group_ids );
        return false;
    });

    function addToGroup(emailsArr, group_ids ) {
        if( ! emailsArr.length || ! group_ids.length ) {
            return false;
        }

        var processinEmails = [];
        // get the first 5.
        if( emailsArr.length> 5 ) {
            processinEmails = emailsArr.slice(0, 5);
            emailsArr = emailsArr.slice(5);// from index 5 onwards.
        } else {
            processinEmails = emailsArr;
            emailsArr = []; // all will be processed after this.
        }

        processinEmails = processinEmails.map( function( email) {
            return email.trim();
        });

        //console.log(processinEmails, emailsArr, group_ids);
        // send an ajax request.
        $.post(ajaxurl, {
            action: 'bulk_group_member_creator_add_to_group',
            emails: processinEmails.join(','),
            group_ids: group_ids.join(',')
        }, function(resp) {
            if( resp.success ) {
                var list = resp.data;
                for( var i = 0; i < list.length; i++ ) {
                    var entry = list[i];
                    $log.append( '<li><span class="bm-email">' + entry['email'] + '</span>: ' + entry['message'] +'</li>');
                }

                if( emailsArr.length ) {
                    addToGroup(emailsArr, group_ids );
                } else {
                    // completed all, update status.
                    $status.html('<strong>Status:</strong> Completed.');
                    $bulk_action_button.prop('disabled', false);
                }
            }

        }, 'json');

    }

    function chunkArray(array, size) {
        let result = []
        for (let i = 0; i < array.length; i += size) {
            let chunk = array.slice(i, i + size)
            result.push(chunk)
        }
        return result
    }
});
jQuery(function ($) {

    var $groups_list = $('#bpdgtc-selected-groups-list');
    var $group_selector_field = $("#bpdgtc-group-selector");

    $group_selector_field.autocomplete({
        // define callback to format results, fetch data
        source: function (req, add) {
            var ids = get_included_group_ids();
            ids = ids.join(',');
            // pass request to server
            $.post(ajaxurl,
                {
                    action: 'bpdgtc_get_groups_list',
                    'q': req.term,
                    'included': ids,
                    cookie: encodeURIComponent(document.cookie)
                }, function (data) {

                    add(data);
                }, 'json');
        },
        //define select handler
        select: function (e, ui) {

            var $div = $groups_list.find('.bpdgtc-selected-group');
            $div.empty();
            $div.append("<div><a class='bpdgtc-remove-group' href='#'>X</a>" +
                "<a href='" + ui.item.url + "'>" + ui.item.label + "</a></div>");

            $('#_bpdgtc_associated_group_id').val(ui.item.id);
            this.value = "";
            return false;// do not update input box
        },
        // when a new menu is shown
        open: function (e, ui) {

        },
        // define select handler
        change: function (e, ui) {
        }
    });// end of autosuggest.


    // remove group association.
    $groups_list.on('click', '.bpdgtc-remove-group', function () {
        $(this).parent().remove();
        $('#_bpdgtc_associated_group_id').val(0);
        return false;
    });

    function get_included_group_ids() {
        var ids = [];

        $groups_list.find('li input').each(function (index, element) {
            ids.push($(element).val());
        });

        return ids;
    }
});
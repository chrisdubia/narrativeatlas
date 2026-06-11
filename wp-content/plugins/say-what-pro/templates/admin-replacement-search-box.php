<p></p>
<form id="say-what-list-table-search-form" method="get">
    <input type="hidden" name="page" value="say_what_admin"/>
    <p class="search-box">
        <label class="screen-reader-text" for="{input_id}">{text}:</label>
        <input type="search" id="{input_id}" name="s" value="<?php _admin_search_query(); ?>"/>
        {submit_button}
    </p>
</form>

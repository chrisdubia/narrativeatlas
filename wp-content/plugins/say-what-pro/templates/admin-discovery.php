<div>
    <p><?php esc_html_e( 'If you are struggling to locate the string replacement you need, you can use this tool to capture strings
        available on your site. To activate it, just click on the button below. This will set a cookie in your browser
        session asking the plugin to make a note of every string available for replacement. To use this function follow
        the instructions below.', 'say_what' ); ?></p>
    <ol>
        <li><?php esc_html_e( 'Click on the button below.', 'say_what' ); ?></li>
        <li><?php esc_html_e( 'Visit the page containing the string you want to override.', 'say_what' ); ?></li>
        <li><?php esc_html_e( 'Come back to this page, to disable the feature.', 'say_what' ); ?></li>
        <li><a href="tools.php?page=say_what_admin&amp;say_what_action=addedit"><?php esc_html_e( 'Add a new string.', 'say_what' ); ?></a> <?php esc_html_e( 'The entry boxes
            will offer autocomplete suggestions based on the strings it has captured.', 'say_what' ); ?>
        </li>
    </ol>
</div>
<form method="post" action="tools.php?page=say_what_admin&say_what_action=discovery">
	<?php wp_nonce_field( 'say_what_pro_discovery_toggle' ); ?>
    <input type="submit" name="{action}" value="{action_text}" class="button button-primary">
</form>

<p>
    <?php esc_html_e( 'Are you sure you want to delete the replacement string for &quot;{orig_string}&quot;?', 'say_what' ); ?>
</p>
<p>
	<a href="tools.php?page=say_what_admin&amp;say_what_action=delete-confirmed&amp;id={id}&amp;nonce={nonce}" class="button button-primary">
        <?php esc_html_e( 'Yes', 'say_what' ); ?>
    </a>
    <a href="tools.php?page=say_what_admin" class="button">
        <?php esc_html_e( 'No', 'say_what' ); ?>
    </a>
</p>

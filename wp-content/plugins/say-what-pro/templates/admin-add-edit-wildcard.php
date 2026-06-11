<form action="tools.php?page=say_what_admin&amp;say_what_action=addeditwildcards" method="post">
	<input type="hidden" name="say_what_save_wildcard" value="1">
	<?php wp_nonce_field( 'swaddedit', 'nonce' ); ?>
	{wildcard_id_field}
	<p>
		<label for="say_what_original"><?php esc_html_e( 'Look for', 'say_what' ); ?></label><br/>
		<textarea class="say_what_original" name="say_what_original" rows="1" cols="120">{original}</textarea><br>
		<em><?php esc_html_e( 'Note: This is case-sensitive', 'say_what' ); ?></em>
	</p>
	<p>
		<label for="say_what_replacement"><?php esc_html_e( 'Replace with', 'say_what' ); ?></label><br/>
		<textarea class="say_what_replacement" name="say_what_replacement" cols="120" rows="1">{replacement}</textarea>
	</p>
    {multilingual_section}
	<p>
		<input type="submit" class="button-primary" value="{button_text}">
	</p>
</form>

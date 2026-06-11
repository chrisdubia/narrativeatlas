<p>
	<?php printf( esc_html__( 'Fill in the details of the original translatable string, the string\'s text domain, and the string you would like to use instead. For more information check out the %1$sgetting started guide%2$s.', 'say_what' ), '<a href="https://plugins.leewillis.co.uk/doc_post/adding-string-replacement/" target="_blank" rel="noopener noreferrer">', '</a>' ); ?>
</p>
<form action="tools.php?page=say_what_admin&amp;say_what_action=addedit" method="post">
    <input type="hidden" name="say_what_save" value="1">
	<?php wp_nonce_field( 'swaddedit', 'nonce' ); ?>
    {string_id_field}
    <p>
        <label for="say_what_orig_string"><?php esc_html_e( 'Original string', 'say_what' ); ?></label><br>
        <span class="text-muted">
            <?php esc_html_e( "Enter part of the string you're trying to replace to see suggestions", 'say_what' ); ?>
        </span><br>
        <textarea class="say_what_orig_string" name="say_what_orig_string" rows="1" cols="120">{orig_string}</textarea>
    <div class="say_what_translated_string"></div>
    </p>
    <p>
        <label for="say_what_domain"><?php esc_html_e( 'Text domain', 'say_what' ); ?></label>
        <a href="http://plugins.leewillis.co.uk/doc_post/adding-string-replacement/" target="_blank"
                rel="noopener noreferrer">
            <i class="dashicons dashicons-info">&nbsp;</i>
        </a><br>
        <span class="text-muted">
            <?php esc_html_e( "Enter the plugin / theme text domain. If you selected a suggestion above this will have been filled in if required.", 'say_what' ); ?>
        </span><br>
        <input type="text" class="say_what_domain" name="say_what_domain" size="30"
               value="{domain}"><br>
    </p>
    <p>
        <label for="say_what_context"><?php esc_html_e( 'Text context', 'say_what' ); ?></label>
        <a href="http://plugins.leewillis.co.uk/doc_post/replacing-wordpress-strings-context/" target="_blank"
                rel="noopener noreferrer">
            <i class="dashicons dashicons-info">&nbsp;</i>
        </a><br>
        <span class="text-muted">
            <?php esc_html_e( 'Enter the string context. If you selected a suggestion above this will have been filled in if required.', 'say_what' ); ?>
        </span><br>
        <input type="text" class="say_what_context" name="say_what_context" size="30"
               value="{context}"><br>
    </p>
    <p>
        <label for="say_what_replacement_string">
            <?php esc_html_e( 'Replacement string', 'say_what' ); ?>
        </label><br>
        <span class="text-muted">
            <?php esc_html_e( 'Enter your replacement string.', 'say_what' ); ?>
        </span><br>
        <textarea class="say_what_replacement_string" name="say_what_replacement_string" cols="120"
                  rows="1">{replacement_string}</textarea>
    </p>
    {multilingual_section}
    <p>
        <label for="say_what_disabled">
			<?php esc_html_e( 'Active', 'say_what' ); ?>
        </label><br>
        <span class="text-muted">
            <?php esc_html_e( 'Whether this replacement is active or not.', 'say_what' ); ?>
        </span><br>
        <select name="say_what_disabled" id="say_what_disabled">
            <option value="0" {disabled_0_selected}><?php esc_html_e( 'Yes', 'say_what' ); ?></option>
            <option value="1" {disabled_1_selected}><?php esc_html_e( 'No', 'say_what' ); ?></option>
        </select>
    </p>
    <p>
        <input type="submit" class="button-primary"
               value="{button_text}">
    </p>
</form>

<div class="wrap {swp_additional_wrap_classes}">
    <h2>
        <?php esc_html_e( 'Text changes', 'say_what' ); ?>&nbsp;
        <a href="tools.php?page=say_what_admin&amp;say_what_action=addedit" class="add-new-h2">
            <?php esc_html_e( 'Add Replacement', 'say_what' ); ?>
        </a>
    </h2>
    {notices}
    <h2 class="nav-tab-wrapper">
        <a href="tools.php?page=say_what_admin" class="nav-tab {default_active}">
			<?php esc_html_e( 'String replacements', 'say_what' ); ?>
        </a>
        <a href="tools.php?page=say_what_admin&amp;say_what_action=discovery" class="nav-tab {discovery_active}">
			<?php esc_html_e( 'String discovery', 'say_what' ); ?>
        </a>
        <a href="tools.php?page=say_what_admin&amp;say_what_action=import" class="nav-tab {import_active}">
			<?php esc_html_e( 'Import replacements', 'say_what' ); ?>
        </a>
        <a href="tools.php?page=say_what_admin&amp;say_what_action=wildcards" class="nav-tab {wildcards_active}">
			<?php esc_html_e( 'Wildcard swaps', 'say_what' ); ?>
        </a>
    </h2>

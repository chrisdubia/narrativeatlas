<p>
    <?php esc_html_e( 'Wildcard swaps allow you to specify parts of strings, rather than the exact full string. The wildcard will be replaced in all strings where it occurs, unless that string has a specific replacement set up.', 'say_what' ); ?>
</p>
<p class="sw-danger">
    <?php esc_html_e( 'Caution: Wildcard swaps are inefficient, and can slow down your site. Using proper replacements is always preferred. ', 'say_what' ); ?><?php printf( esc_html__( 'Swaps may also produce undesireable results since they apply to all translatable strings, e.g. swapping %1$smanage%2$s with %1$scontrol%2$s would change %1$smanagement%2$s to %1$scontrolment%2$s', 'say_what' ), '<em>', '</em>' ); ?>
</p>
<p>
	<a href="tools.php?page=say_what_admin&amp;say_what_action=addeditwildcards" class="button button-primary"><?php esc_html_e( 'Add Wildcard Swap', 'say_what' ); ?></a>
</p>

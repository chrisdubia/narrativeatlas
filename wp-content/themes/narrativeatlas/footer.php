<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package BuddyBoss_Theme
 */

?>

<?php do_action( THEME_HOOK_PREFIX . 'end_content' ); ?>

</div><!-- .bb-grid -->
</div><!-- .container -->
</div><!-- #content -->

<?php do_action( THEME_HOOK_PREFIX . 'after_content' ); ?>

<?php do_action( THEME_HOOK_PREFIX . 'before_footer' ); ?>
<?php do_action( THEME_HOOK_PREFIX . 'footer' ); ?>
<?php do_action( THEME_HOOK_PREFIX . 'after_footer' ); ?>

</div><!-- #page -->

<?php do_action( THEME_HOOK_PREFIX . 'after_page' ); ?>

<?php wp_footer(); ?>

<script type="text/javascript">
jQuery(document).ready(function() {
	if(jQuery('nav.groups-nav').length > 0) {
		var ourmapsli = jQuery("li#nav-ourmaps-groups-li");
    	jQuery("li#nav-docs-groups-li").after(ourmapsli.clone());
        jQuery("li#nav-ourmaps-groups-li:eq(1)").remove();
	}
});
</script>

</body>
</html>
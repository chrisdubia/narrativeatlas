<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 */

if( !defined('ABSPATH') ){
	exit;
}

$message_html = $args['message_html'] ?? __('You Cannot Access to Event. Please Contact to group admin.', 'mec-buddyboss');

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main" style="text-align:center">

			<header class="page-header">
				<h1 class="page-title"><?php _e( 'Access Limited', 'twentythirteen' ); ?></h1>
			</header>

			<div class="page-wrapper">
				<div class="page-content">
					<?php echo $message_html; ?>
				</div><!-- .page-content -->
			</div><!-- .page-wrapper -->
		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>

<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Category;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\helper\RouteView;
use com\cminds\mapsroutesmanager\shortcode\RouteSnippetShortcode;
use com\cminds\mapsroutesmanager\controller\RouteController;

if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'route_save_success' ) {
?>
<div class="cmmrm-msg cmmrmmsg-info">
	<div class="cmmrm-msg-inner">
		<span>
		<?php echo Labels::getLocalized( 'route_save_success' ); ?>
		<?php
		if ( Settings::getOption( Settings::OPTION_ROUTE_MODERATION_ENABLE ) == '1' && ! current_user_can( 'manage_options' ) ) {
			echo Labels::getLocalized( 'route_save_success_pending' );
		}
		?>
		</span>
	</div>
</div>
<?php
}
?>
<div class="cmmrm-routes-archive"<?php echo RouteView::getDisplayParams( $displayParams );
?> data-ajax="<?php echo intval( $atts['ajax'] ); ?>">
	<?php if ( $atts['showfilters'] ): ?>
		<?php get_template_part( 'cmmrm', 'route-index-filter' ); ?>
	<?php endif; ?>
	<?php if ( ! App::isPro() OR ! empty( $atts['showmap'] ) ): ?>
		<?php do_action( 'cmmrm_display_index_map', $atts ); ?>
	<?php endif; ?>
	<?php if ( $atts['showlist'] ): ?>
		<div class="cmmrm-routes-archive-summary"><?php printf( Labels::getLocalized( 'routes_index_summary' ), count( $routes ), $totalRoutesNumber ); ?></div>
		<div class="cmmrm-routes-archive-<?php echo $atts['listlayout']; ?>">
			<?php foreach ( $routes as $route ):
				echo RouteSnippetShortcode::shortcode( array(
					'route'    => $route,
					'featured' => $atts['featuredimage'],
					'layout'   => $atts['listlayout'],
					'fancy'    => $atts['fancy'],
				) );
			endforeach; ?>
			<?php if ( empty( $routes ) ): ?>
				<p class="index_no_routes"><?php echo Labels::getLocalized( 'index_no_routes' ); ?></p>
			<?php endif; ?>
		</div>
		<?php get_template_part( 'cmmrm', 'route-index-bottom' ); ?>
		<?php echo RouteController::getPagination( $atts ); ?>
		<?php get_template_part( 'cmmrm', 'pagination' ); ?>
	<?php endif; ?>
</div>
<?php
$currentCategoryId   = 0;
$link_share_page_id  = false;
$currentCategorySlug = FrontendController::$query->get( Category::TAXONOMY );
if ( $term = get_term_by( 'slug', $currentCategorySlug, Category::TAXONOMY ) ) {
	$currentCategoryId = $term->term_id;
	$link_share_page_id = Settings::getOption( Settings::OPTION_LINK_SHARE_PAGE_ID );
}
if($currentCategoryId == 0 && isset($_GET['cmmrm_category']) && $_GET['cmmrm_category'] != '') {
	$route_category = get_term_by('slug', $_GET['cmmrm_category'], 'cmloc_category');
	$currentCategoryId = $route_category->term_id;
	$link_share_page_id = get_option('cmloc_link_share_page_id', '0');
}

$currentRouteTypeID   = 0;
$currentRouteTypeSlug = FrontendController::$query->get( 'cmmrm_route_type' );
if ( $rtterm = get_term_by( 'slug', $currentRouteTypeSlug, 'cmmrm_route_type' ) ) {
	$currentRouteTypeID = $rtterm->term_id;
}

$currentRouteDifficultyID   = 0;
$currentRouteDifficultySlug = FrontendController::$query->get( 'cmmrm_route_difficulty' );
if ( $rdterm = get_term_by( 'slug', $currentRouteDifficultySlug, 'cmmrm_route_difficulty' ) ) {
	$currentRouteDifficultyID = $rdterm->term_id;
}

if ( $currentCategoryId > 0 || $currentRouteTypeID > 0 || $currentRouteDifficultyID > 0) {
	$link_share_enable  = Settings::getOption( Settings::OPTION_LINK_SHARE_ENABLE );

	$route_category_link_url = '';
	if ( $link_share_page_id ) {
		$route_category_link_url = get_the_permalink( $link_share_page_id );
		if($currentCategorySlug != '') {
			if(strpos($route_category_link_url, '?') !== false) { $op = '&'; } else { $op = '?'; }
			$route_category_link_url .= $op.'category='.$currentCategorySlug;
		}
		if($currentRouteTypeSlug != '') {
			if(strpos($route_category_link_url, '?') !== false) { $op = '&'; } else { $op = '?'; }
			$route_category_link_url .= $op.'routetype='.$currentRouteTypeSlug;
		}
		if($currentRouteDifficultySlug != '') {
			if(strpos($route_category_link_url, '?') !== false) { $op = '&'; } else { $op = '?'; }
			$route_category_link_url .= $op.'routedifficulty='.$currentRouteDifficultySlug;
		}
	}

	if ( $link_share_enable == '1' ) { ?>
        <div class="route_share_link_box">
            <h6><?php echo Labels::getLocalized( 'route_link_sharing' ); ?></h6>
            <div class="route_share_link_row route_category_share_link">
                <input type="text" value="<?php echo $route_category_link_url; ?>" id="cmmrm_route_ccategory_input"
                       readonly="readonly" />
                <button onclick="cmmrm_route_ccategory_link()"
                        id="cmmrm_route_ccategory_button"><?php echo Labels::getLocalized( 'route_copy_route_category' ); ?></button>
            </div>
        </div>
        <script type="text/javascript">
            function cmmrm_route_ccategory_link() {
                var copyText = document.getElementById("cmmrm_route_ccategory_input");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                document.execCommand("copy");
                document.getElementById("cmmrm_route_ccategory_button").innerHTML = '<?php echo Labels::getLocalized( "route_copied" ); ?>';
                setTimeout(function () {
                    document.getElementById("cmmrm_route_ccategory_button").innerHTML = '<?php echo Labels::getLocalized( "route_copy_route_category" ); ?>';
                }, 3000);
            }
        </script>
		<?php
	}
}
?>
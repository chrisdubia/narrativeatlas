<?php
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Category;
use com\cminds\mapsroutesmanager\controller\FrontendController;

$lookAndFeelClass = '';
$lookAndFeel = Settings::getOption(Settings::OPTION_LOOK_AND_FEEL_CSS);
if($lookAndFeel == '2016-fancy') {
	$lookAndFeelClass = ' fancy';
} else if($lookAndFeel == '2017-june') {
	$lookAndFeelClass = ' june';
}
?>
<div class="cmmrm-route-index-filter<?php echo $lookAndFeelClass; ?>">
	<a id="cmmrmfilter"></a>
	<?php do_action('cmmrm_route_index_filter_top', $atts); ?>
	<form action="<?php echo esc_attr($atts['searchformurl']); ?>" class="cmmrm-route-index-search-form">
		<?php do_action('cmmrm_route_index_search_form_top', $atts); ?>
		<?php if (!empty($atts['categories'])): ?>
			<div class="cmmrm_categories_filter_grid cmmrm-routes-clearfix">
				<?php do_action('cmmrm_categories_filter', $atts); ?>
			</div>
		<?php endif; ?>
		<?php if (!empty($atts['searchinput'])): ?>
			<div class="cmmrm_search_grid">
				<label class="cmmrm-field-search"><input type="text" name='s' value="<?php
					echo esc_attr(strlen($atts['searchstring']) > 0 ? $atts['searchstring']: ''); ?>" placeholder="<?php echo Labels::getLocalized('search_placeholder'); ?>" />
					<button class="cmmrm-submit-btn" type="submit" title="<?php echo esc_attr(Labels::getLocalized('search_btn')); ?>">
						<?php echo Labels::getLocalized('search_form_submit_btn'); ?>
					</button>
				</label>
			</div>
		<?php endif; ?>
		<?php do_action('cmmrm_route_index_search_form_bottom', $atts); ?>
	</form>
	<?php
	if(count($_GET) > 0) {
		?>
		<script>jQuery([document.documentElement, document.body]).animate({ scrollTop: jQuery("#cmmrmfilter").offset().top }, 1000);</script>
		<?php
	}
	if(!is_admin()) {
		$currentCategoryId   = 0;
		$link_share_page_id  = false;
		if(isset(FrontendController::$query)) {
			$currentCategorySlug = FrontendController::$query->get( Category::TAXONOMY );
			if ( $term = get_term_by( 'slug', $currentCategorySlug, Category::TAXONOMY ) ) {
				$currentCategoryId = $term->term_id;
				$link_share_page_id = Settings::getOption( Settings::OPTION_LINK_SHARE_PAGE_ID );
			}
		}
		if($currentCategoryId == 0 && isset($_GET['cmmrm_category']) && $_GET['cmmrm_category'] != '') {
			$route_category = get_term_by('slug', $_GET['cmmrm_category'], 'cmloc_category');
			$currentCategoryId = $route_category->term_id;
			$link_share_page_id = get_option('cmloc_link_share_page_id', '0');
		}

		$currentRouteTypeID   = 0;
		if(isset(FrontendController::$query)) {
			$currentRouteTypeSlug = FrontendController::$query->get( 'cmmrm_route_type' );
			if ( $rtterm = get_term_by( 'slug', $currentRouteTypeSlug, 'cmmrm_route_type' ) ) {
				$currentRouteTypeID = $rtterm->term_id;
			}
		}

		$currentRouteDifficultyID   = 0;
		if(isset(FrontendController::$query)) {
			$currentRouteDifficultySlug = FrontendController::$query->get( 'cmmrm_route_difficulty' );
			if ( $rdterm = get_term_by( 'slug', $currentRouteDifficultySlug, 'cmmrm_route_difficulty' ) ) {
				$currentRouteDifficultyID = $rdterm->term_id;
			}
		}

		if ( $currentCategoryId > 0 || $currentRouteTypeID > 0 || $currentRouteDifficultyID > 0) {
			$link_share_enable  = Settings::getOption( Settings::OPTION_INDEX_SHARE_LINK_SHOW );
			
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
				<input type="text" value="<?php echo $route_category_link_url; ?>" id="cmmrm_top_route_ccategory_input" style="opacity:0; position:absolute; top:100px; left: 0;" />
				<a href="javascript:void(0);" onclick="cmmrm_top_route_ccategory_link()" class="cmmrm_top_route_ccategory_link dashicons dashicons-share" title="<?php echo Labels::getLocalized('route_copy_route_category'); ?>"></a>		
				<script type="text/javascript">
					function cmmrm_top_route_ccategory_link() {
						var copyText = document.getElementById("cmmrm_top_route_ccategory_input");
						copyText.select();
						copyText.setSelectionRange(0, 99999);
						document.execCommand("copy");
						alert('<?php echo Labels::getLocalized( "route_copied" ); ?>');
					}
				</script>
				<?php
			}
		}
	}
	?>

</div>
<div class="clerfix"></div>
<?php
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\controller\DashboardController;
use com\cminds\mapsroutesmanager\controller\FrontendController;
if(count($categories) > 0) {
	?>
	<div class="cmmrm-widget-description">
		<?php
		foreach($categories as $category) {
			//echo '<p class="cmmrm_widget_cat_name"><strong>'.$category->getName().'</strong></p>';
			echo '<p class="cmmrm_widget_cat_description">'.do_shortcode(wpautop($category->getDescription())).'</p>';
			$files_list = $category->getRouteFileList();
			if(count($files_list) > 0) {
				foreach($files_list as $file) {
					echo '<a href="'.$file['url'].'" class="cmmrm_widget_cat_description_download" download><span class="dashicons dashicons-download"></span>'.$file['title'].'</a>';
				}
			}
			if(is_user_logged_in() && current_user_can('administrator')) {
				echo '<div class="cmmrm-widget-description-edit-link"><a href="'.trailingslashit(get_site_url()).'wp-admin/term.php?taxonomy=cmmrm_category&tag_ID='.$category->getID().'&post_type=cmmrm_route" target="_blank">'.Labels::getLocalized('widget_edit_text').'</a></div>';
			}
		}
		?>
	</div>
	<?php
}
?>
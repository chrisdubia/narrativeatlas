<?php
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-routes-index-top">
	<?php echo $text; ?>
	<?php
	if(count($files_list) > 0) {
		echo '<div class="cmmrm-routes-index-top-cat-files">';
		foreach($files_list as $file) {
			echo '<a href="'.$file['url'].'" class="cmmrm_widget_cat_description_download" download><span class="dashicons dashicons-download"></span>'.$file['title'].'</a>';
		}
		echo '</div>';
	}
	if(is_user_logged_in() && current_user_can('administrator')) {
		echo '<div class="cmmrm-description-edit-link"><a href="'.trailingslashit(get_site_url()).'wp-admin/term.php?taxonomy=cmmrm_category&tag_ID='.$category_id.'&post_type=cmmrm_route" target="_blank">'.Labels::getLocalized('widget_edit_text').'</a></div>';
	}
	?>
</div>
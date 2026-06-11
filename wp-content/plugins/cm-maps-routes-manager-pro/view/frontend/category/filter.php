<?php 
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\helper\RouteView;
?>
<div class="cmmrm-filter cmmrm-categories-filter">
	<select name="cmmrm_category" class="cmmrm_category_filter">
		<option value="" data-url="<?php echo esc_attr($baseUrl); ?>"><?php echo Labels::getLocalized('filter_all_categories_opt'); ?></option>
		<?php
		if($mergecategories == '1') {
			$mergeallcategories = array();
			//echo RouteView::mergecategoriesFilter($baseUrl, $currentCategoryId, $categories);
			$cmmrm_terms = get_terms(array(
				'taxonomy' => 'cmmrm_category',
				'hide_empty' => false
			));
			if(!empty($cmmrm_terms)) {
				foreach($cmmrm_terms as $rterm) {
					$mergeallcategories[$rterm->name] = $rterm->slug;
				}
			}
			$cmloc_terms = get_terms(array(
				'taxonomy' => 'cmloc_category',
				'hide_empty' => false
			));
			if(!empty($cmloc_terms)) {
				foreach($cmloc_terms as $lterm) {
					$mergeallcategories[$lterm->name] = $lterm->slug;
				}
			}
			if(count($mergeallcategories) > 0) {
				foreach($mergeallcategories as $lrcatkey=>$lrcatval) {
					$url = add_query_arg('cmmrm_category', $lrcatval, $baseUrl);
					if(isset($_GET['cmmrm_category']) && $_GET['cmmrm_category'] != '' && $_GET['cmmrm_category'] == $lrcatval) {
						echo '<option value="'.$lrcatval.'" data-url="'.$url.'" selected="selected">'.$lrcatkey.'</option>';
					} else {
						echo '<option value="'.$lrcatval.'" data-url="'.$url.'">'.$lrcatkey.'</option>';
					}
				}
			}
		} else {
			echo RouteView::categoriesFilter($baseUrl, $currentCategoryId, $categories);
		}
		?>
	</select>
</div>
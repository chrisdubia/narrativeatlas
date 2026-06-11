<?php 
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-filter cmmrm-custom-taxonomy-filter cmmrm-custom-taxonomy-filter-<?php echo $taxonomy; ?>">
	<select name="<?php echo esc_attr($taxonomy); ?>">
		<option value="" data-url="<?php echo esc_attr(remove_query_arg($taxonomy, $baseUrl)); ?>">
			<?php printf(Labels::getLocalized('filter_option_show_all_custom_taxonomy_terms'), $tax['name_plural']); ?>
		</option>
		<?php
		if($mergetaxonomies == '1') {
			$mergeallcategories = array();
			$cmmrm_terms = get_terms(array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false
			));
			if(!empty($cmmrm_terms)) {
				foreach($cmmrm_terms as $rterm) {
					$mergeallcategories[$rterm->name] = $rterm->slug;
				}
			}
			$cmloc_terms = get_terms(array(
				'taxonomy' => str_replace('cmmrm', 'cmloc', $taxonomy),
				'hide_empty' => false
			));
			if(!empty($cmloc_terms)) {
				foreach($cmloc_terms as $lterm) {
					$mergeallcategories[$lterm->name] = $lterm->slug;
				}
			}
			if(count($mergeallcategories) > 0) {
				foreach($mergeallcategories as $lrcatkey=>$lrcatval) {
					if($lrcatkey != '') {
						$url = add_query_arg($taxonomy, $lrcatval, $baseUrl);
						if(isset($_GET[$taxonomy]) && $_GET[$taxonomy] != '' && $_GET[$taxonomy] == $lrcatval) {
							echo '<option value="'.$lrcatval.'" data-url="'.$url.'" selected="selected">'.$lrcatkey.'</option>';
						} else {
							echo '<option value="'.$lrcatval.'" data-url="'.$url.'">'.$lrcatkey.'</option>';
						}
					}
				}
			}
		} else {
			foreach ($terms as $term) {
				$url = add_query_arg($taxonomy, $term->slug, $baseUrl);
				printf('<option value="%s" data-url="%s"%s>%s</option>', esc_attr($term->slug), esc_attr($url), selected($term->slug, $current, false), $term->name);
			}
		}
		?>
	</select>
</div>
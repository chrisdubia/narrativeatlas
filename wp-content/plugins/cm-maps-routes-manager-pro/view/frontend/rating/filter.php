<?php 
use com\cminds\mapsroutesmanager\model\Labels;
?>
<div class="cmmrm-filter cmmrm-route-rating-filter">
	<select>
		<option value="<?php echo esc_attr(remove_query_arg($urlParam, $baseUrl)); ?>"><?php echo Labels::getLocalized('filter_option_show_all_rates'); ?></option>
		<?php for ($i=1; $i<=5; $i++):
			$url = add_query_arg($urlParam, $i, $baseUrl);
			printf('<option value="%s"%s>%s</option>', esc_attr($url), selected($i, $current, false), sprintf(Labels::getLocalized('filter_option_rating'), $i));
		endfor; ?>
	</select>
</div>
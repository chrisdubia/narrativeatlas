<?php
use com\cminds\mapsroutesmanager\model\Category;
use com\cminds\mapsroutesmanager\helper\FormHtml;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Settings;
$route_form_category_create = Settings::getOption(Settings::OPTION_ROUTE_FORM_CATEGORY_CREATE);
if ($route_form_category_create == '1') {
	?>
	<div class="cmmrm-field cmmrm-field-categories">
		<div class="cmmrm_categories">
			<label class="firstmain"><?php echo Labels::getLocalized('route_category'); ?></label>
			<?php echo FormHtml::checkboxTreeEdit('categories[]', $route->getCategories(Category::FIELDS_IDS), $categoriesTree); ?>
		</div>
		<div class="cmmrm_add_new_category_container">
			<a href="javascript:void(0);" class="cmmrm_add_new_category"><?php echo Labels::getLocalized('route_category_create'); ?></a>
		</div>
	</div>
	<?php
} else {
	if(count($categoriesTree) > 0) {
		?>
		<div class="cmmrm-field cmmrm-field-categories">
			<div class="cmmrm_categories">
				<label class="firstmain"><?php echo Labels::getLocalized('route_category'); ?></label>
				<?php echo FormHtml::checkboxTree('categories[]', $route->getCategories(Category::FIELDS_IDS), $categoriesTree); ?>
			</div>
		</div>
		<?php
	}
}
?>
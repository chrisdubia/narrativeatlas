<?php
use com\cminds\mapsroutesmanager\addon\customfields\helper\HtmlHelper;

$template = function($metaKey, $label, $type) use ($settingName, $fieldTypes) {
	return sprintf('<tr>
			<td class="col-label"><input type="text" name="%s" value="%s" required maxlength="1000" /></td>
			<td class="col-meta-key"><input type="text" pattern="[a-z0-9_]+" title="Only lower-case basic latin characters, digits and underscore _" required name="%s" value="%s" maxlength="100" /></td>
			<td class="col-type">%s</td>
			<td class="col-delete"><span class="dashicons dashicons-no-alt cmmrmcf-delete" title="Delete"></span></td>
		</tr>',
		esc_attr($settingName . '[label][]'),
		esc_attr($label),
		esc_attr($settingName . '[meta_key][]'),
		esc_attr($metaKey),
		HtmlHelper::renderSelect($settingName . '[type][]', $fieldTypes, $type)
	);
};
?>
<div class="cmmrm-custom-fields-settings">
	<table>
		<thead>
			<tr>
				<th>Text label</th>
				<th>Meta key (alphanumeric)</th>
				<th>Field type</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php echo $template('template', 'Template', 'string'); ?>
			<?php if (is_array($fields)) foreach ($fields as $field): ?>
				<?php echo $template($field['meta_key'], $field['label'], $field['type']); ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<a href="" class="button cmmrmcf-add-new">Add new field</a>
</div>
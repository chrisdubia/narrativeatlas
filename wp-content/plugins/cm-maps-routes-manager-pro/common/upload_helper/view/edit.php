<?php

use com\cminds\wp\common\upload_helper\v_1_0_0\controller\UploadController;


/*
 * @var $this UploadController
 */

?>

<div class="<?php echo $pluginPrefix; ?>-attachments cm-upload-helper-attachments">

	<ul class="cm-upload-helper-attachments-list"><?php
	
	// This is template
	$template = '<li data-id="%s"%s><a href="%s" target="_blank" title="%s"><img src="%s" alt="Attachment" /></a>'
		. '<span class="cm-upload-helper-attachment-delete" title="%s">&times;</span></li>';
	
	// Print one template item that will be hidden to be used by JS
	// printf($template, 0, ' style="display:none"', 'about:blank', esc_attr($this->__('View')), 'about:blank', esc_attr($this->__('Remove')));
	printf($template, 0, ' style="display:none"', '', esc_attr($this->__('View')), '', esc_attr($this->__('Remove')));
	
	foreach ($attachments as $attachment):
		printf($template,
			$attachment->getId(),
			'',
			esc_attr($attachment->getUrl()),
			esc_attr($this->__('View')),
			esc_attr($attachment->getIconUrl() ?: $defaultIcon),
			esc_attr($this->__('Remove'))
		);
	endforeach; ?></ul>
	
	<div class="cm-upload-helper-field-desc"<?php if (empty($attachmentIds)) echo ' style="display:none;"';
		?>><?php echo $this->__('Upload'); ?></div>
	
	<div class="cm-upload-helper-attachments-add">
		<input type="hidden" name="<?php echo esc_attr($fieldName); ?>" value="<?php echo esc_attr(implode(',', $attachmentIds)); ?>" />
		<span><?php echo $this->__('Upload new'); ?></span>
		<input type="file" class="cm-upload-helper-attachments-upload"<?php if ($allowedFiles ? ' accept="'. $allowedFiles .'"' : '');?> multiple>
	</div>

</div>
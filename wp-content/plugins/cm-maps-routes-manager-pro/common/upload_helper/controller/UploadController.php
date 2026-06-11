<?php

namespace com\cminds\wp\common\upload_helper\v_1_0_0\controller;

use com\cminds\wp\common\upload_helper\v_1_0_0\model\Attachment;
use com\cminds\wp\common\upload_helper\v_1_0_0\model\AttachmentImage;

/**
 * 
 *
 */
class UploadController extends BaseController {
	
	protected $uploadDir;
	protected $fieldName;
	
	
	function setUploadDir($uploadDir) {
		$this->uploadDir = $uploadDir;
		return $this;
	}
	
	
	function setFieldName($fieldName) {
		$this->fieldName = $fieldName;
		return $this;
	}
	
	
	function setupAjaxHandler($public = false) {
		add_action('wp_ajax_' . $this->getAjaxAction(), array($this, 'ajaxHandler'));
		if ($public) {
			add_action('wp_ajax_nopriv_' . $this->getAjaxAction(), array($this, 'ajaxHandler'));
		}
	}
	
	
	function ajaxHandler() {
		
		$response = array('success' => false, 'msg' => $this->__('An error occurred.'));
		$nonce = filter_input(INPUT_POST, 'nonce');
		if (wp_verify_nonce($nonce, $this->getAjaxAction())) {
				
			foreach ($_FILES as $file) {
				try {
						
					$filePath = Attachment::uploadMedia($file);
						
					$parentPostId = 0;
					$fileId = Attachment::create($filePath, $file['type'], $parentPostId);
					$attachment = Attachment::getInstance($fileId);
					
					$result = array('id' => $fileId, 'url' => $attachment->getUrl());
					if ($attachment->isImage() AND $image = AttachmentImage::getInstance($fileId)) {
						$result['thumb'] = $image->getImageUrl(Attachment::IMAGE_SIZE_THUMB);
						$result['imageUrl'] = $image->getImageUrl();
					}
					
					$response['files'][] = $result;
						
				} catch (\Exception $e) {
					$response['msg'] = $this->__($e->getMessage());
				}
			}
			
			if (!empty($response['files'])) {
				$response['msg'] = $this->__('File has been uploaded.');
				$response['success'] = true;
			}
			
		}
		
		header('content-type: application/json');
		echo json_encode($response);
		exit;
		
	}
	
	
	function __($msg) {
		return $msg;
	}
	
	
	function getAjaxAction() {
		return $this->pluginPrefix . '_' . $this->fieldName . '_upload';
	}
	
	
	
	function getEditView(array $attachmentsIds, $allowedFiles = null) {
		$this->embedAssets();
		$fieldName = $this->fieldName;
		$pluginPrefix = $this->pluginPrefix;
		$nonce = wp_create_nonce($this->getAjaxAction());
		$attachments = (!empty($attachmentsIds) ? Attachment::getAll(array('include' => $attachmentsIds)) : array());
		$defaultIcon = $this->url('asset/img/file-icon.png');
		return $this->loadView($this->getEditViewPath(),
				compact('fieldName', 'nonce', 'pluginPrefix', 'attachments', 'defaultIcon', 'attachmentIds', 'allowedFiles'));
	}
	
	
	function getEditViewPath() {
		return $this->path('view/edit.php');
	}
	
	
	function embedAssets() {
		$ver = $this->getLibraryVersion();
		wp_enqueue_script($this->prefix('-upload-helper'), $this->url('asset/js/upload-helper.js'), array('jquery'), $ver, $in_footer = true);
		wp_enqueue_style($this->prefix('-upload-helper'), $this->url('asset/css/upload-helper.css'), array(), $ver);
	}
	
		
}

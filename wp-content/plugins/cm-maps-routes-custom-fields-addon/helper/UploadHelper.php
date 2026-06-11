<?php

namespace com\cminds\mapsroutesmanager\addon\customfields\helper;

class UploadHelper {
	
	protected $field;
	
	function __construct($field) {
		$this->field = $field;
	}
	
	
	function isValid() {
		return (isset($_FILES[$this->field]) AND $this->getErrorCode() === 0 AND is_uploaded_file($this->getTmpName()));
	}
	
	
	function getErrorCode() {
		if (isset($_FILES[$this->field]['error'])) {
			return $_FILES[$this->field]['error'];
		}
	}
	
	
	function getTmpName() {
		if (isset($_FILES[$this->field]['tmp_name'])) {
			return $_FILES[$this->field]['tmp_name'];
		}
	}
	
}
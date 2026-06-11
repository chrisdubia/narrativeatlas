<?php
namespace com\cminds\mapsroutesmanager\addon\customfields\model;

class Location extends PostType {

	const POST_TYPE = 'cmmrm_location';
	
	const META_CUSTOM_FIELD_PREFIX = 'cmmrmcf_';
	

	static function registerPostType() {
		// don't
	}

	/**
	 *
	 * @param unknown $postId
	 * @return Location
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}

	
	function getCustomField($name) {
		return $this->getPostMeta(static::META_CUSTOM_FIELD_PREFIX . $name);
	}
	
	
	function setCustomField($name, $value) {
		return $this->setPostMeta(static::META_CUSTOM_FIELD_PREFIX . $name, $value);
	}
	

}

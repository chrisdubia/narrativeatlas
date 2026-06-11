<?php
namespace com\cminds\mapsroutesmanager\model;

use com\cminds\mapsroutesmanager\helper\GoogleMapsIcons;

class Category extends TaxonomyTerm {

	const TAXONOMY = 'cmmrm_category';
	const CATEGORY_PERMALINK_PART = 'category';
	const OPTION_ROUTES_DEFAULT_ICONS = 'cmmrm_category_icons';

	const META_ROUTE_FILES = 'cmmrm_route_add_files';
	
	static function init() {
		parent::init();
		
		// Register taxonomy
		$args = array(
            'hierarchical' => TRUE,
            'labels' => static::getTaxonomyLabels(),
            'show_ui' => FALSE, // to override in pro
            'query_var' => TRUE,
			'show_admin_column' => true,
			'post_types' => array(Route::POST_TYPE),
			'public' => true,
			'rewrite' => array('slug' => self::getUrlPart()),
        );
		register_taxonomy(static::TAXONOMY, $args['post_types'], apply_filters('cmmrm_category_term_args', $args));
		
		// Create General category if no categories exists
		//global $wpdb;
		//$count = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_taxonomy WHERE taxonomy = %s", static::TAXONOMY)));
		//if ($count == 0) \wp_insert_term('All Videos', static::TAXONOMY);
		
	}
	
	static function getUrlPart() {
		return Settings::getOption(Settings::OPTION_PERMALINK_PREFIX) .'-' . self::CATEGORY_PERMALINK_PART;
	}
	
	static function getTaxonomyLabels() {
		$plural = ucfirst(Labels::getLocalized('route_categories'));
		$singular = ucfirst(Labels::getLocalized('route_category'));
        return array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => 'Search ' . $plural,
            'popular_items' => 'Popular ' . $plural,
            'all_items' => 'All ' . $plural,
            'parent_item' => 'Parent ' . $singular,
            'parent_item_colon' => 'Parent ' . $singular . ':',
            'edit_item' => 'Edit ' . $singular,
            'update_item' => 'Update ' . $singular,
            'add_new_item' => 'Add New ' . $singular,
            'new_item_name' => 'New ' . $singular . ' Name',
            'menu_name' => $plural,
        );
    }
    
    /**
	 * Get instance
	 * 
	 * @param object|int $term Term object or ID
	 * @return com\cminds\mapsroutesmanager\model\Category
	 */
	static function getInstance($term) {
		return parent::getInstance($term);
	}
	
	function getEditUrl() {
		return admin_url(sprintf('edit-tags.php?action=edit&taxonomy=%s&tag_ID=%d&post_type=%s',
			Category::TAXONOMY,
			$this->getId(),
			Route::POST_TYPE
		));
	}
	
	function getIcon() {
		$options = get_option(self::OPTION_ROUTES_DEFAULT_ICONS, array());
		$id = $this->getId();
		if (isset($options[$id])) {
			return GoogleMapsIcons::fixHttps($options[$id]);
		}
	}
	
	function setIcon($icon) {
		$options = get_option(self::OPTION_ROUTES_DEFAULT_ICONS, array());
		$id = $this->getId();
		$options[$id] = $icon;
		update_option(self::OPTION_ROUTES_DEFAULT_ICONS, $options, true);
		return $this;
	}

    function getRouteFileList() {
		$fileUrlList = array();
		$allTermMeta = $this->getTermMeta('');
		foreach ($allTermMeta as $termMetaName => $termMetaValue) {
			$patternFile = '/'.static::META_ROUTE_FILES.'\d+/';
			if(preg_match($patternFile, $termMetaName)) {
				$termMetaVal = unserialize ($termMetaValue[0]);
				$fileUrlList[] = array('id' => $termMetaVal['id'], 'url' => wp_get_attachment_url( $termMetaVal['id']), 'title' => $termMetaVal['title'] );
			}
		}
		return $fileUrlList;
	}
	
	function setRouteFileList($fileIds, $filesTitle) {
		foreach ($fileIds as $fileId) {
			$this->setTermMeta(static::META_ROUTE_FILES.$fileId, array('id' => $fileId, 'title'=> strip_tags($filesTitle[$fileId])));
		}
		return true;
	}
	
	function removeRouteFileList($removeFilesID) {
		$fileIds = explode(',', $removeFilesID);
		foreach ($fileIds as $fileId) {
			if(!$this->deleteTermMeta(static::META_ROUTE_FILES.$fileId)){
				return false;
			}
		}
		return true;
	}

}
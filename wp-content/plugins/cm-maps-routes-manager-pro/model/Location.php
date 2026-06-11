<?php
namespace com\cminds\mapsroutesmanager\model;

use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\helper\RemoteConnection;
use com\cminds\mapsroutesmanager\model\Route;

class Location extends PostType {
	
	const POST_TYPE = 'cmmrm_location';
	
	const META_LAT = '_cmmrm_latitude';
	const META_LONG = '_cmmrm_longitude';
	const META_LAT_LONG = '_cmmrm_latitude_longitude';
	const META_ALTITUDE = '_cmmrm_altitude';
	const META_LOCATION_TYPE = '_cmmrm_loc_type';
	const META_ADDRESS = '_cmmrm_address';
	const META_LINKTEXT = '_cmmrm_linktext';
	const META_LINKURL = '_cmmrm_linkurl';
	const META_DISTANCE = '_cmmrm_distance';
	const META_CERTBGIMAGEID = '_cmmrm_certbgimageid';
	const META_ICON = '_cmmrm_icon';
	const META_ICON_SIZE = '_cmmrm_icon_size';
	const META_INFO_WINDOW_OPEN = '_cmmrm_info_winwo_open';
	const META_GENERATE_WAZE_BUTTON = '_cmmrm_generate_waze_button';
	
	const TYPE_LOCATION = 'location';
	const TYPE_WAYPOINT = 'waypoint';
	
	const ICON_SIZE_LARGE = 'large';
	const ICON_SIZE_NORMAL = 'normal';
	const ICON_SIZE_SMALL = 'small';
	
	static protected $postTypeOptions = array(
		'label' => 'Location',
		'public' => false,
		'show_in_rest' => true,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui' => false,
		'show_in_admin_bar' => false,
		'show_in_menu' => false,
		'hierarchical' => false,
		'supports' => array('title', 'editor', 'author', 'excerpt', 'thumbnail'),
		'has_archive' => false,
	);

	static protected function getPostTypeLabels() {
		$singular = ucfirst(Labels::getLocalized('location'));
		$plural = ucfirst(Labels::getLocalized('locations'));
		return array(
			'name' => $plural,
            'singular_name' => $singular,
            'add_new' => sprintf(__('Add %s', App::SLUG), $singular),
            'add_new_item' => sprintf(__('Add New %s', App::SLUG), $singular),
            'edit_item' => sprintf(__('Edit %s', App::SLUG), $singular),
            'new_item' => sprintf(__('New %s', App::SLUG), $singular),
            'all_items' => $plural,
            'view_item' => sprintf(__('View %s', App::SLUG), $singular),
            'search_items' => sprintf(__('Search %s', App::SLUG), $plural),
            'not_found' => sprintf(__('No %s found', App::SLUG), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', App::SLUG), $plural),
            'menu_name' => App::getPluginName()
		);
	}
	
	static function init() {
		static::$postTypeOptions['rewrite'] = array('slug' => Settings::getOption(Settings::OPTION_PERMALINK_PREFIX) . '/location');
		parent::init();
	}
	
	static function registerPostType() {
		// do not register
	}
	
	/**
	 * Get instance
	 * 
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\mapsroutesmanager\model\Location
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	function getEditUrl() {
		return admin_url(sprintf('post.php?action=edit&post=%d',
			$this->getId()
		));
	}
	
	function getUserEditUrl() {
		return RouteController::getDashboardUrl('edit', array('id' => $this->getId()));
	}
	
	function getLat() {
		return $this->getPostMeta(self::META_LAT);
	}
	
	function setLat($lat) {
		return $this->setPostMeta(self::META_LAT, $lat);
	}
	
	function getLong() {
		return $this->getPostMeta(self::META_LONG);
	}
	
	function setLong($long) {
		return $this->setPostMeta(self::META_LONG, $long);
	}

	function setLatLong($latlong) {
		$lat_long_arr = explode(',', $latlong);
		$set_of_latlong = '';
		$set_of_latlong .= $latlong.'|';
		$set_of_latlong .= $lat_long_arr[0].' '.$lat_long_arr[1].'|';
		$set_of_latlong .= $lat_long_arr[0].', '.$lat_long_arr[1].'|';
		$set_of_latlong .= $lat_long_arr[0].' , '.$lat_long_arr[1].'|';
		//$this->setPostMeta(self::META_LAT_LONG, utf8_encode($set_of_latlong));
		
		if($this->getId()) {
			$route = get_post($this->getId());
			if($route->post_parent) {
				$setoflatlong = utf8_encode($set_of_latlong);
				if(get_post_meta($route->post_parent, '_cmmrm_latitude_longitude', true)) {
					$setoflatlong = get_post_meta($route->post_parent, '_cmmrm_latitude_longitude', true).'|'.$setoflatlong;
				}
				update_post_meta($route->post_parent, '_cmmrm_latitude_longitude', $setoflatlong);
			}
		}

		return $this;
	}
	
	function getAddress() {
		return $this->getPostMeta(self::META_ADDRESS);
	}
	
	function setAddress($address) {
		return $this->setPostMeta(self::META_ADDRESS, $address);
	}

	function getLinktext() {
		return $this->getPostMeta(self::META_LINKTEXT);
	}
	
	function setLinktext($linktext) {
		return $this->setPostMeta(self::META_LINKTEXT, $linktext);
	}

	function getLinkurl() {
		return $this->getPostMeta(self::META_LINKURL);
	}
	
	function setLinkurl($linkurl) {
		return $this->setPostMeta(self::META_LINKURL, $linkurl);
	}

	function getDistance() {
		return $this->getPostMeta(self::META_DISTANCE);
	}
	
	function setDistance($distance) {
		return $this->setPostMeta(self::META_DISTANCE, $distance);
	}

	function getCertbgimageids() {
		$cert_image_id = $this->getPostMeta(self::META_CERTBGIMAGEID);
		if($cert_image_id != '') {
			$cert_image_id = substr($cert_image_id, 1);
			$cert_image_ids = explode(",", $cert_image_id);
			$array = array();
			foreach($cert_image_ids as $certimageid) {
				$array[] = Attachment::getInstance($certimageid);
			}
			return $array;
		} else {
			return array();
		}
	}
	
	function setCertbgimageid($imageid) {
		return $this->setPostMeta(self::META_CERTBGIMAGEID, $imageid);
	}
	
	function getLocationType() {
		$type = $this->getPostMeta(self::META_LOCATION_TYPE);
		if (empty($type)) $type = static::TYPE_LOCATION;
		return $type;
	}
	
	function setLocationType($type) {
		return $this->setPostMeta(self::META_LOCATION_TYPE, $type);
	}

	function getImagesIds() {
		if ($id = $this->getId()) {
			return get_posts(array(
				'posts_per_page' => -1,
				'post_type' => Attachment::POST_TYPE,
				'post_status' => 'any',
				'post_parent' => $id,
				'fields' => 'ids',
				'orderby' => 'menu_order',
				'order' => 'asc',
			));
		} else {
			return array();
		}
	}
	
	function setImages($images) {
		global $wpdb;
	
		if (!is_array($images)) {
			$images = array_filter(explode(',', $images));
		}
	
		$currentIds = $this->getImagesIds();
		$postedImagesIds = array_filter(array_map('intval', array_map('trim', $images)));
	
		$toAdd = array_diff($postedImagesIds, $currentIds);
		$toDelete = array_diff($currentIds, $postedImagesIds);
		
		$route = get_post($this->getId());
		
		if (!empty($toAdd)) {
			//$toAdd_sql = "UPDATE $wpdb->posts SET post_parent = ". intval($route->post_parent) ." WHERE ID IN (" . implode(',', $toAdd) . ")";
			$toAdd_sql = "UPDATE $wpdb->posts SET post_parent = ". intval($this->getId()) ." WHERE ID IN (" . implode(',', $toAdd) . ")";
			$wpdb->query($toAdd_sql);
		}
		if (!empty($toDelete)) {
			$toDelete_sql = "UPDATE $wpdb->posts SET post_parent = 0 WHERE ID IN (" . implode(',', $toDelete) . ")";
			$wpdb->query($toDelete_sql);
		}
		
		// Change the sorting order
		foreach ($images as $i => $id) {
			$wpdb->query("UPDATE $wpdb->posts SET menu_order = ". intval($i+1) ." WHERE ID = ". intval($id) ." LIMIT 1");
		}
	
	}

	function getImages() {
		if ($id = $this->getId()) {
			return Attachment::getForPost($id);
		} else {
			return array();
		}
	}
	
	function getAltitude() {
		return get_post_meta($this->getId(), self::META_ALTITUDE, $single = true);
	}
	
	function formatAltitude() {
		$alt = $this->getAltitude();
		if (Settings::getOption(Settings::OPTION_UNIT_LENGTH) == Settings::UNIT_FEET) {
			if($alt != '' && is_numeric($alt)) {
				$alt = $alt/Settings::FEET_TO_METER;
			} else {
				$alt = '0';
			}
			$unit = 'ft';
		} else {
			$unit = 'm';
		}
		if($alt == '') { $alt = 0; }
		return round($alt) .' '. $unit;
	}
	
	function setAltitude($alt) {
		update_post_meta($this->getId(), self::META_ALTITUDE, $alt);
		return $this;
	}
	
	static function downloadEvelations(array $coords) {
		
		if (empty($coords)) return array();
		
		$url = 'https://maps.googleapis.com/maps/api/elevation/json?sensor=false';
		
		$loc = implode('|', array_map(function($coord) {
			return implode(',', $coord);
		}, $coords));
		
		$url = add_query_arg(urlencode_deep(array(
			'locations' => $loc,
			'key' => Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY),
		)), $url);
		
		$result = RemoteConnection::getRemoteJson($url);

		if (is_array($result) AND !empty($result['results']) AND !empty($result['status']) AND $result['status'] == 'OK') {
			return $result;
		}
		
	}
	
	function getPostMetaKey($name) {
		return $name;
	}
	
	function getRoute() {
		if ($id = $this->getParentId()) {
			return Route::getInstance($id);
		}
	}
	
	function setIcon($icon) {
		return $this->setPostMeta(self::META_ICON, $icon);
	}
	
	function getIcon() {
		global $post;
		$icon = $this->getPostMeta(self::META_ICON);
		if($icon == '' && !isset($post) && !is_single()) {
			$route_id = $this->getParentId();
			$categories = get_the_terms($route_id, 'cmmrm_category');
			if(is_array($categories)) {
				if(count($categories) > 0) {
					foreach($categories as $cat) {
						$cmmrm_category_icons = get_option('cmmrm_category_icons', array());
						if(is_array($cmmrm_category_icons)) {
							if(count($cmmrm_category_icons) > 0) {
								if(isset($cmmrm_category_icons[$cat->term_id]) && $cmmrm_category_icons[$cat->term_id] != '') {
									$icon = $cmmrm_category_icons[$cat->term_id];
								}
							}
						}
					}
				}
			}
		}
		return $icon;
	}
	
	function setIconSize($size) {
		return $this->setPostMeta(self::META_ICON_SIZE, $size);
	}
	
	function getIconSize() {
		return $this->getPostMeta(self::META_ICON_SIZE);
	}
	
	function getFirstImageSrc($image_size = 'large') {
		$images = $this->getImages();
		
		if(count($images) == 0) {
			$loc_object = get_post($this->getId());
			$route_object = Route::getInstance(get_post($loc_object->post_parent));
			$images = $route_object->getImages();
		}

		if ($image = reset($images)) {
			if($image_size == 'large') {
				return $image->getImageUrl(Attachment::IMAGE_SIZE_LARGE);
			} else {
				return $image->getImageUrl(Attachment::IMAGE_SIZE_THUMB);
			}
		}
	}
	
	function getFirstImage() {
		if ($src = $this->getFirstImageSrc('thumb')) {
			return sprintf('<img src="%s" class="cmmrm-location-image-%d" />', esc_attr($src), $this->getId());
		}
	}
	
	function setInfoWindowOpen($open) {
		return $this->setPostMeta(static::META_INFO_WINDOW_OPEN, $open);
	}
	
	function getInfoWindowOpen() {
		return $this->getPostMeta(static::META_INFO_WINDOW_OPEN);
	}

	function setGenerateWazeButton($open) {
		return $this->setPostMeta(static::META_GENERATE_WAZE_BUTTON, $open);
	}
	
	function getGenerateWazeButton() {
		return $this->getPostMeta(static::META_GENERATE_WAZE_BUTTON);
	}

	function getInfoWindowContent() {
		$content = wpautop(Settings::getOption(Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_TEMPLATE));
		$tokens = static::getShortcodeTokensFuncMap();
		foreach ($tokens as $token => &$func) {
			$value = call_user_func(array($this, $func));
			$func = $value;
		}
		$content = strtr($content, $tokens);
		return $content;
	}

	static function getShortcodeTokensFuncMap() {
		return array(
			'[title]' => 'getTitle',
			'[description]' => 'getContent',
			'[permalink]' => 'getRoutePermalink',
			'[ctabutton]' => 'getCtaButton',
			'[address]' => 'getAddress',
			'[linktext]' => 'getLinktext',
			'[linkurl]' => 'getLinkurl',
			'[distance_from_start]' => 'getDistance',
			'[latitude]' => 'getLat',
			'[longitude]' => 'getLong',
			'[altitude]' => 'getAltitude',
			'[imagesrc]' => 'getFirstImageSrc',
			'[image]' => 'getFirstImage',
			'[createddate]' => 'getCreatedDate',
			'[updatedate]' => 'getUpdateDate',
			'[editlink]' => 'getEditLinkurl',
			'[deletelink]' => 'getDeleteLinkurl',
			'[closelink]' => 'getCloseLinkurl',
		);
	}

	protected function saveCreate() {
		$id = parent::saveCreate();
		if ($id) {
			$this->setLocationType(Location::TYPE_LOCATION);
		}
		return $id;
	}
	
	function getCtaButton() {
		$ctaButtonText = get_post_meta($this->getId(), '_cmmrm_cta_button_text', true);
		$ctaButtonUrl = get_post_meta($this->getId(), '_cmmrm_cta_button_url', true);
		if($ctaButtonText != '' && $ctaButtonUrl != '') {
			if($ctaButtonUrl == '#') {
				return '<a href="'.$ctaButtonUrl.'" class="cmmrm-cta-button-a-tooltip">'.$ctaButtonText.'</a>';
			} else {
				return '<a href="'.$ctaButtonUrl.'" class="cmmrm-cta-button-a-tooltip" target="_blank">'.$ctaButtonText.'</a>';
			}
		} else {
			return '';
		}
	}

	function getRoutePermalink() {
		$route = get_post($this->getId());
		return get_permalink($route->post_parent);
	}
	
	function getCreatedDate() {
		$date = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_time('U', true, $this->getId()));
		return $date;
	}

	function getUpdateDate() {
		$date = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_modified_time('U', true, $this->getId()));
		return $date;
	}

	function getEditLinkurl() {
		$editlink = '';
		if(is_user_logged_in() && current_user_can('administrator')) {
			$route = get_post($this->getId());
			$route_object = Route::getInstance(get_post($route->post_parent));
			$editlink = '<a href="'.$route_object->getUserEditUrl().'" target="_blank">'.Labels::getLocalized('dashboard_edit').'</a>';
		}
		return $editlink;
	}

	function getDeleteLinkurl() {
		$editlink = '';
		if(is_user_logged_in() && current_user_can('administrator')) {
			$route = get_post($this->getId());
			$route_object = Route::getInstance(get_post($route->post_parent));
			$editlink = '<a href="'.$route_object->getUserDeleteUrl().'" onClick="return confirm(\''.Labels::getLocalized('confirm_delete_msg').'\')" >'.Labels::getLocalized('dashboard_delete').'</a>';
		}
		return $editlink;
	}
	
	function getCloseLinkurl() {
		$closelink = '<a href="javascript:void(0);" class="rinfowindow_closelink">'.Labels::getLocalized('close').'</a>';
		return $closelink;
	}

}
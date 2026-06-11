<?php
namespace com\cminds\mapsroutesmanager\helper;

use com\cminds\mapsroutesmanager\App;

class GoogleMapsIcons {
	
	static function getAll() {
		$icons = array();
		include App::path('asset/google-maps-icons.php');
		return $icons;
	}
	
	static function fixHttps($url) {
		$find = 'http://maps.google.com/';
		if (substr($url, 0, strlen($find)) == $find) {
			$url = str_replace('http://', 'https://', $url);
		}
		return $url;
	}

}
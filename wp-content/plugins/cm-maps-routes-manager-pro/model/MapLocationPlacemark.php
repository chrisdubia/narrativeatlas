<?php
namespace com\cminds\mapsroutesmanager\model;

class MapLocationPlacemark extends PostType {
	
	const POST_TYPE = 'cmloc_location';
	
	const META_LAT = '_cmloc_latitude';
	const META_LONG = '_cmloc_longitude';
	const META_ALTITUDE = '_cmloc_altitude';
	const META_LOCATION_TYPE = '_cmloc_loc_type';
	const META_ADDRESS = '_cmloc_address';
	const META_CITY = '_cmloc_city';
	const META_POSTAL_CODE = '_cmloc_postal_code';
	const META_PHONE_NUMBER = '_cmloc_phone_number';
	const META_WEBSITE = '_cmloc_website';
	const META_EMAIL = '_cmloc_email';
	
	
	static function registerPostType() {
		// don't
	}
	
}
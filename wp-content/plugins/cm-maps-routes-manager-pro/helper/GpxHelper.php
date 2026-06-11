<?php
namespace com\cminds\mapsroutesmanager\helper;

use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\App;

class GpxHelper
{	
	const MIME_TYPE = 'application/gpx+xml';

	/**
	 * Load library files
	 */
	static function initGeoPHP() {
		if (!class_exists('\\geoPHP')) {
			require_once App::path('lib/geoPHP/geoPHP.php');
		}
	}
	
	/**
	 * Convert plain-text GPX source to plain-text KML source.
	 * 
	 * @param string $gpxSource
	 * @return string|NULL
	 */
	static function convertToKml($gpxSource) {
		self::initGeoPHP();
		$geom = \geoPHP::load($gpxSource, 'gpx');
		if ($kmlSource = $geom->out('kml')) {
			return $kmlSource;
		}
	}
	
	/**
	 * Get Simple XML object from plan-text GPX source.
	 * 
	 * @param string $gpxSource
	 * @return SimpleXMLElement
	 */
	static function getSimpleXml($gpxSource) {
		$gpxSource = str_replace('xmlns=', 'ns=', $gpxSource);
		/* @var $xml SimpleXMLElement */
		return \simplexml_load_string($gpxSource);
	}
	
	/**
	 * Get route name.
	 * 
	 * @param \SimpleXMLElement $xml
	 * @param string $fileName
	 * @return string
	 */
	static function getName(\SimpleXMLElement $xml, $fileName) {
		if ($name = (string)$xml->trk->name) {
			return $name;
		} else {
			return $fileName;
		}
	}
	
	static function export(Route $route) {
		self::initGeoPHP();
		$source = self::convertFromKml(KmlHelper::export($route));
		$source = str_replace('version="1.0"><trk>', 'version="1.0">
		<metadata>
			<time>'. date('c', strtotime($route->getCreatedDate())) .'</time>
		</metadata>
		<trk>' . PHP_EOL, $source);
		return $source;
	}
	
	static function convertFromKml($kmlSource) {
		if ($kmlSource) {
			self::initGeoPHP();
			$geom = \geoPHP::load($kmlSource, 'kml');
			if ($gpxSource = $geom->out('gpx')) {
				return $gpxSource;
			}
		}
	}
	
}
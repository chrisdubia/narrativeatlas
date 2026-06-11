<?php
namespace com\cminds\mapsroutesmanager\helper\import;

class GpxImportSource extends XmlImportSourceAbstract {
	
	function getName() {
		$xml = $this->getXml();
		if ($name = (string)$xml->trk->name) {
			return $name;
		}
	}
	
	function getPathCoords() {
		$coords = array();
		$xml = $this->getXml();
		$xpath = $xml->xpath('//trkpt');
		if(count($xpath) == 0) {
			$xpath = $xml->xpath('//rtept');
		}
		foreach ($xpath as $p => $point) {
			$lat = (float)$point['lat'];
			$long = (float)$point['lon'];
			$elevation = (float)$point->ele;
			$time = (string)$point->time;
			$coords[] = array(
				0 => $lat,
				1 => $long,
				2 => $elevation,
				3 => $time,
			);
		}
		return $coords;
	}
	
	function getLocations() {
		$locations = array();
		$xml = $this->getXml();
		foreach ($xml->wpt as $loc) {
			$locations[] = array(
				'name' => (string)$loc->name,
				'lat' => (float)$loc['lat'],
				'long' => (float)$loc['lon'],
				'elevation' => (float)$loc->ele,
			);
		}
		return $locations;
	}
	
}
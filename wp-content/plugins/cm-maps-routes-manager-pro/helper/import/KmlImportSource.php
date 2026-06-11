<?php
namespace com\cminds\mapsroutesmanager\helper\import;

class KmlImportSource extends XmlImportSourceAbstract {
	
	function getName() {
		
	}
	
	function getPathCoords() {
		$result = array();
		$xml = $this->getXml();
		$lines = $xml->xpath('//LineString');
		foreach ($lines as $line) {
			if ($line->coordinates) {
				$result = array_merge($result, array_values(array_filter(array_map(function($row) {
					$row = explode(',', $row);
					// Transpose points y-x to x-y
					$temp = $row[0];
					$row[0] = $row[1];
					$row[1] = $temp;
					if (!isset($row[2])) $row[2] = 0;
					if (!isset($row[3])) $row[3] = 0;
					return $row;
				}, array_values(array_filter(preg_split('/\s/', (string)$line->coordinates)))))));
			}
		}
		return $result;
	}
	
	
	function getLocations() {
		return array();
	}
	
}
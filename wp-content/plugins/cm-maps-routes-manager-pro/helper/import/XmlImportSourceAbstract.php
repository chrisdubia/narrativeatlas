<?php

namespace com\cminds\mapsroutesmanager\helper\import;

abstract class XmlImportSourceAbstract implements RouteImportSourceInterface {
	
	/**
	 * SimpleXMLElement object
	 * @var \SimpleXMLElement
	 */
	protected $xml;
	
	
	function __construct($kmlSource) {
		$kmlSource = str_replace('xmlns=', 'ns=', $kmlSource);
		$kmlSource = str_replace('gpxx:', '', $kmlSource);
		$kmlSource = str_replace('gpxtrkx:', '', $kmlSource);
		$this->xml = \simplexml_load_string($kmlSource);;
		if (empty($this->xml) OR !($this->xml instanceof \SimpleXMLElement)) {
			throw new \Exception('Invalid XML file.');
		}
	}
	
	
	static function createFromFile($filePath) {
		if (!file_exists($filePath)) throw new \Exception('File does not exists.');
		if (!is_readable($filePath)) throw new \Exception('Cannot read file.');
		$source = file_get_contents($filePath);
		if (strlen($source) == 0) throw new \Exception('File is empty.');
		return new static($source);
	}

	static function createFromFileCsv($filePath) {
		//if (!file_exists($filePath)) throw new \Exception('File does not exists.');
		//if (!is_readable($filePath)) throw new \Exception('Cannot read file.');
		$source = file_get_contents($filePath);
		if (strlen($source) == 0) throw new \Exception('File is empty.');
		return new static($source);
	}
	
	
	function getXml() {
		return $this->xml;
	}
	
}
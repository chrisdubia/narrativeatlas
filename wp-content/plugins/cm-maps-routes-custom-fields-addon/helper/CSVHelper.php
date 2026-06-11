<?php

namespace com\cminds\mapsroutesmanager\addon\customfields\helper;

class CSVHelper {
	
	protected $path;
	protected $fp;
	
	function __construct($path) {
		$this->path = $path;
	}
	
	
	function getAll($colNumber = null) {
		$rows = array();
		$this->fp = fopen($this->getPath(), 'r');
		while ($row = fgetcsv($this->fp)) {
			if (!empty($row)) {
				if (is_null($colNumber)) {
					$rows[] = $row;
				}
				else if (isset($row[$colNumber])) {
					$rows[] = $row[$colNumber];
				}
			}
		}
		fclose($this->fp);
		return $rows;
	}
	
	
	function getPath() {
		return $this->path;
	}
	
}
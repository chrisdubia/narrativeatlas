<?php
namespace com\cminds\mapsroutesmanager\helper\import;

interface RouteImportSourceInterface {
	
	function getName();
	function getPathCoords();
	function getLocations();
	
}
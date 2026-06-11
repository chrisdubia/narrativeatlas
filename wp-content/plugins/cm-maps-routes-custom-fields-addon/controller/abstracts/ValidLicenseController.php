<?php
namespace com\cminds\mapsroutesmanager\addon\customfields\controller\abstracts;

use com\cminds\mapsroutesmanager\addon\customfields\controller\Controller;
use com\cminds\mapsroutesmanager\addon\customfields\App;

abstract class ValidLicenseController extends Controller {

	static function addHooks() {
		if (App::isLicenseOk()) {
			parent::addHooks();
		}
	}
	
}
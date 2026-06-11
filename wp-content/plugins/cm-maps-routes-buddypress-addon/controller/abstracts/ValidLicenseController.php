<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\controller\abstracts;

use com\cminds\mapsroutesmanager\addon\buddypress\controller\Controller;
use com\cminds\mapsroutesmanager\addon\buddypress\App;

abstract class ValidLicenseController extends Controller {

	static function addHooks() {
		if (App::isLicenseOk()) {
			parent::addHooks();
		}
	}
	
}
<?php
namespace com\cminds\mapsroutesmanager\helper;

use com\cminds\mapsroutesmanager\App;

class ViewHelper {
	
	protected $controllerClass;
	protected $vars = array();
	protected $what;
	protected $viewName;
	
	static function output($controllerClass, $what, $viewName, $vars = array()) {
		$view = new static($controllerClass, $what, $viewName, $vars = array());
		return $view->getOutout();
	}
	
	function __construct($controllerClass, $what, $viewName, $vars = array()) {
		$this->controllerClass = $controllerClass;
		$this->vars = $vars;
		$this->what = $what;
		$this->viewName = $viewName;
	}
	
	function getOutout() {
		ob_start();
		include $this->getViewPathAbsolute();
		return ob_get_clean();
	}
	
	function getViewPathRelative() {
		$controllerPart = strtolower(preg_replace('/\B([A-Z])/', '_$1', call_user_func(array($this->controllerClass, 'shortClassName'))));
		return "view/{$this->what}/{$controllerPart}/{$this->viewName}.php";
	}
	
	function getViewPathAbsolute() {
		return App::path($this->getViewPathRelative());
	}
	
	function var($name) {
		if (isset($this->vars[$name])) {
			return $this->vars[$name];
		} else {
			throw new \Exception('Missing view variable: ' . $name);
		}
	}
	
	function varOpt($name) {
		if (isset($this->vars[$name])) {
			return $this->vars[$name];
		}
	}
	
}
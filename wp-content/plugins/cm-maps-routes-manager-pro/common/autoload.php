<?php

spl_autoload_register(function($name) {
	
	$prefix = 'com\cminds\wp\common';
	
	if (strpos($name, $prefix) === 0) {
		$name = preg_replace('~\\\\(v_[0-9]+_[0-9]+_[0-9]+\\\\)~', '\\', $name);
		$file = __DIR__ . str_replace('\\', '/', str_replace($prefix, '', $name)) . '.php';
		if (file_exists($file) AND is_readable($file)) {
			require_once $file;
		}
	}

});
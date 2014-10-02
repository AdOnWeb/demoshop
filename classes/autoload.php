<?php
namespace DemoShop;

spl_autoload_register(function ($className) {
	if (preg_match('/([a-z]+$)/i', $className, $match)) {
		$className = $match[1];
	} else {
		return null;
	}

	$paths = array('classes/app', 'classes/business', 'classes/actionpay');
	foreach ($paths as $path) {
		$classPath = DEMOSHOP_PATH . "/{$path}/{$className}.php";
		if (file_exists($classPath)) {
			require $classPath;
		}
	}
});
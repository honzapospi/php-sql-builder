<?php

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

// configure environment
Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

// create temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
@mkdir(TEMP_DIR); // @ - directory may already exist

$connection = new \Nette\Database\Connection('mysql:host=127.0.0.1;dbname=test', 'test', '');
$cacheStorage = new \Nette\Caching\Storages\FileStorage(TEMP_DIR);
$structure = new \Nette\Database\Structure($connection, $cacheStorage);
$conventions = new \Nette\Database\Conventions\DiscoveredConventions($structure);
$context = new \Nette\Database\Context($connection, $structure, $conventions, $cacheStorage);
return $context;
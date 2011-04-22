<?php defined('SYSPATH') or die('No direct script access.');

return array(
	// Use the cache directory on development because sessions are not important
	'save_path' => APPPATH.'cache',
	'native' => array(
		'name' => 'temp_couchdb_app',
	),
	'database' => array(
		'group' => 'default',
		'table' => 'sessions',
	),
	'cookie' => array(
		'encrypted' => TRUE,
	),
);

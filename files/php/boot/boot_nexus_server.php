<?php
$boot_path = "/var/www/boot";

// require libraries
require_once("lib/toml.php");

// define functions
function _boot_installation_not_found()
{
	// OBS! NGINX will handle 404 page
	header("HTTP/1.0 404 Not Found");
	print("<h1>Installation not found</h1>");
	exit;
}

// define functions
function _boot_installation_error($message = "Unexpected error")
{
	// OBS! NGINX will handle 500 page
	header("HTTP/1.0 500 Internal Server Error");
	print("<h1>" . $message . "</h1>");
	error_log("Nexus Boot Error: " . $message . " (" . $_SERVER["REQUEST_URI"] . ")");
	exit;
}

// get defaults
try {
	$_boot_toml_defaults = Toml::parseFile($boot_path . '/defaults.toml');
} catch (Exception $e) {
	_boot_installation_error("Could not parse default config file");
}

// get installation
if(!preg_match("@^/([a-zA-Z0-9\-\_]+)/.*@", $_SERVER["REQUEST_URI"], $arr))
	_boot_installation_not_found();
else
	$_boot_installation_name = $arr[1];

// check for aliases
if(isset($_boot_toml_defaults['aliases']) && is_array($_boot_toml_defaults['aliases']) && isset($_boot_toml_defaults['aliases'][$_boot_installation_name])) {
	$_boot_installation_name = $_boot_toml_defaults['aliases'][$_boot_installation_name];
}

// check installation config
$_boot_toml_path = $boot_path . '/configs/' . $_boot_installation_name . '.toml';
if(!is_file($_boot_toml_path)) 
	_boot_installation_not_found();

// parse config
try {
	$_boot_toml_config = Toml::parseFile($_boot_toml_path);
} catch (Exception $e) {
	_boot_installation_error("Could not parse installation config file");
}

// Merge configs
$config = (object) array_replace_recursive($_boot_toml_defaults, $_boot_toml_config);

// script filename hack so that Zend Controller baseUrl is set correctly
function _boot_replace_path($str) {
	global $_boot_installation_name;
	return str_replace('boot_nexus.php', $_boot_installation_name . '/index.php', $str);
}

// TODO this is very hackish: alternative would be to use baseUrl 
$_SERVER['SCRIPT_FILENAME'] = str_replace('boot_nexus.php', $_boot_installation_name . '/index.php', $_SERVER['SCRIPT_FILENAME']);

// System settings
$config->installation_code = isset($config->installation_code) && !empty($config->installation_code) ? $config->installation_code : $_boot_installation_name;
$config->installation_title = $config->title;
unset($config->title);

// DB config
$config->db = $config->database;
unset($config->database);
if(!isset($config->db['dbname'])) 
	$config->db['dbname'] = 'nexus_' . $config->installation_code;

// Slave DB config
if (@$config->replica_database) {
	$config->replica_db = $config->replica_database;
	unset($config->replica_database);
	if(!isset($config->replica_db['dbname']))
		$config->replica_db['dbname'] = 'nexus_' . $config->installation_code;
}

// MACS DB config
if(isset($config->macs_database) && isset($config->macs_database['dbname'])) 
	$config->macs_db = $config->macs_database;

unset($config->macs_database);

// app paths
$config->app_root = '/var/www/installations/' . $_boot_installation_name . '/';
$config->app_path = $config->app_root . 'nexus_app/';
$config->lib_path = $config->app_root . 'lib/';

// php include path
set_include_path($config->lib_path . PATH_SEPARATOR . get_include_path());

// Start initializing app
if(!isset($config->use_old_init) || !$config->use_old_init) {
	// NEW NEXUS INIT
	require_once('Nexus.php');
	Nexus::run($config);
}

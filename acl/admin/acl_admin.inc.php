<?php
require_once(dirname(__FILE__).'/../acl.class.php');
require_once(dirname(__FILE__).'/../acl_api.class.php');
require_once(dirname(__FILE__).'/acl_admin_api.class.php');

// paglacl Configuration file.
if ( !isset($config_file) ) {
#	$config_file = '../acl.ini.php';
	$config_file = dirname(__FILE__).'/../acl.ini.php';
}

//Values supplied in $acl_options array overwrite those in the config file.
if ( file_exists($config_file) ) {
	$config = parse_ini_file($config_file);

	if ( is_array($config) ) {
		if ( isset($acl_options) ) {
			$acl_options = array_merge($config, $acl_options);
		} else {
			$acl_options = $config;
		}
	}
	unset($config);
}

$acl_api = new acl_admin_api($acl_options);

$acl = &$acl_api;

$db = &$acl->db;

//Setup the Smarty Class.
require_once($acl_options['smarty_dir'].'/Smarty.class.php');

$smarty = new Smarty;
$smarty->compile_check = TRUE;
$smarty->template_dir = $acl_options['smarty_template_dir'];
$smarty->compile_dir = $acl_options['smarty_compile_dir'];

/*
 * Email address used in setup.php, please do not change.
 */
$author_email = 'ipso@snappymail.ca';

/*
 * Don't need to show notices, some of them are pretty lame and people get overly worried when they see them.
 * Mean while I will try to fix most of these. ;) Please submit patches if you find any I may have missed.
 */
error_reporting (E_ALL ^ E_NOTICE);

?>

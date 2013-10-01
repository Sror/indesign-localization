<?php
##########################################################
#die("Site Under Maintenance. Please check back later.");
error_reporting(E_ALL+E_STRICT);
ini_set("display_errors",1);
##########################################################

define("ROOT", dirname(__FILE__)."/");
date_default_timezone_set('Europe/London');

//Database Setup
define("HOST_NAME","localhost");					//database host name
define("DB_NAME","devtest");							//database name
define("DB_USER_NAME","root");						//database username
define("DB_PASSWORD","87purple87");					//database password

//Server Application Setup
define("HOST_PATH","http://localhost");				//QXPS host domain
define("PORT_NO","8090");							//QXPS port number
define("UPLOAD_DIR","E:/Server Documents/");		//QXPS upload directory KEEP TRAILING SLASH
define("IMG_LIBRARY_DIR","E:/Image Library/");		//image library directory KEEP TRAILING SLASH
define("REPOSITORY_DIR","E:/Repository/");			//file repository directory KEEP TRAILING SLASH
define("OUTPUT_DIR",UPLOAD_DIR."Output/");			//INDS output directory KEEP TRAILING SLASH

//Session Life
$max_session = ini_get('session.gc_maxlifetime');						//get php.ini session setting
define("SESSION_TIME",$max_session);									//maximum session life
if(!isset($_SESSION)) session_start();

//Load Other Configs
define("CONFIGS", ROOT."configs/");
require_once(CONFIGS.'config.site.php');
require_once(CONFIGS.'config.dir.php');
require_once(CONFIGS.'config.mail.php');
require_once(CONFIGS.'config.lang.php');
require_once(CONFIGS.'config.cons.php');
require_once(CONFIGS.'config.log.php');

//Load Other Required Classes and Modules
require_once(CLASSES.'translator.php');
$DB = new Database();
require_once(MODULES.'mod_builder.php');
require_once(MODULES.'mod_functions.php');

//overwrite ACL DB settings here if needed
$acl_options = NULL;
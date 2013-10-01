<?php
if(isset($_GET['lang'])) {
	$_SESSION['lang'] = $_GET['lang'];
	setcookie("lang", $_GET['lang'], time()+60*60*24*7);
	$langCode = $_GET['lang'];
} else {
	if(isset($_SESSION['lang'])) {
		$langCode = $_SESSION['lang'];
	} else if (isset($_COOKIE['lang'])) {
		$langCode = $_COOKIE['lang'];
	} else {
		$langCode = "gb";
	}
}

require_once(CLASSES.'language.php');
$lang = language::getInstance($langCode);
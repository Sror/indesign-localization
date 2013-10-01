<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');

$content = file_get_contents(ERROR_LOG);
if(empty($content)) {
	$output = 'Error Log Not Available';
} else {
	$output = nl2br($content);
}
echo '<div class="errorlog">'.$output.'</div>';
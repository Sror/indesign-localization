<?php
//expecting array $access if mod_authorise.php is required
$aco_section_value = $access[0];
$aco_value = $access[1];
//access check for admin and superadmin
require_once(ACL.'acl.class.php');
require_once(ACL.'acl_api.class.php');
require_once(ACL.'admin/acl_admin.inc.php');
$authorised = false;
$isadmin = false;
$issuperadmin = false;
$restrictGoTo = "index.php?layout=login";
$credits_available = 0;
if(!empty($_SESSION['userID']) && !empty($_SESSION['companyID'])) {
	$acl = new acl($acl_options);
	$authorised = $acl->acl_check($aco_section_value,$aco_value,$_SESSION['companyID'],$_SESSION['userID']);
	$isadmin = $acl->acl_check("system","admin",$_SESSION['companyID'],$_SESSION['userID']);
	$issuperadmin = $acl->acl_check("system","superadmin",$_SESSION['companyID'],$_SESSION['userID']);
	$restrictGoTo = "index.php?layout=system&id=4";
	$credits_available = $DB->count_available_credits($_SESSION['companyID'],$_SESSION['userID']);
}

if(!$authorised) {
	$userID = !empty($_SESSION['userID']) ? (int)$_SESSION['userID'] : 0;
	$DB->LogSystemEvent($userID,"failed to pass the checkpoint: $aco_section_value - $aco_value");
	$qsChar = "?";
	$referrer = $_SERVER['PHP_SELF'];
	if(strpos($restrictGoTo, "?")) $qsChar = "&";
	if(isset($_SERVER['QUERY_STRING'])) {
		$referrer .= (strpos($referrer, '?')) ? "&" : "?";
		$referrer .= $_SERVER['QUERY_STRING'];
	}
	$restrictGoTo = $restrictGoTo.$qsChar."redirect=".urlencode($referrer);
	header("Location: $restrictGoTo");
	exit;
}
<?php
/*	Skipped out on the authorisation, something to sort out after meeting deadline*/
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

require_once(ACL.'acl.class.php');
require_once(ACL.'acl_api.class.php');
require_once(ACL.'admin/acl_admin.inc.php');
$acl = new acl($acl_options);

require_once(CLASSES.'Font_Substitution.php');
/*
	Font Substitution prequisits
*/

function get(&$var,$default){return isset($var)?$var:$default;}

$result = mysql_query('SELECT allowance FROM users WHERE userID = '.$_SESSION['userID'].' AND allowance > 0') or die(mysql_error());

$companyID	= (int)get($_GET['companyID'],0);
$campaignID	= (int)get($_GET['campaignID'],0);
$artworkID	= (int)get($_GET['artworkID'],0);
$taskID		= (int)get($_GET['taskID'],0);

$service	= (int)get($_GET['service'],0);

$show		= get($_GET['show'],'All');
$keyword	= get($_GET['keyword'],'');

$limit		= get($_GET['limit'],5);
$page		= get($_GET['page'],1);

if(!empty($_GET['back'])){
	$back = urldecode($_GET['back']);
}elseif(!empty($taskID)){
	$back = "/index.php?layout=task&id=$taskID";	
}elseif(!empty($artworkID)){
	$back = "/index.php?layout=artwork&id=$artworkID";
}elseif(!empty($campaignID)){
	$back = "/index.php?layout=campaign&id=$campaignID";
}elseif(!empty($companyID)){
	$back = "/index.php?layout=campaigns";
}else{
	$back = '-';//No close
}

//"<a href=\"index.php?layout=${layout}&company=${companyID}\"></a>"

/*	Time to ensure that the user's company is the same
*/
$level = 'system';

if($taskID){
	$query = "
		SELECT artworks.artworkID,campaigns.campaignID,brands.companyID FROM tasks
			LEFT JOIN artworks ON artworks.artworkID = tasks.artworkID
			LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
			LEFT JOIN brands ON brands.brandID = campaigns.brandID
		WHERE
			tasks.taskID = ${taskID}
		LIMIT 1";
	$result = mysql_query($query) or die(mysql_error());
	if(mysql_num_rows($result)){
		list($artworkID,$campaignID,$companyID) = mysql_fetch_row($result);
		$level = 'task';
		$levelID = $taskID;
	}else
		$artworkID = $campaignID = $companyID = 0;
}

if(!$taskID && $artworkID){
	$query = "
		SELECT campaigns.campaignID,brands.companyID FROM artworks
			LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
			LEFT JOIN brands ON brands.brandID = campaigns.brandID
		WHERE
			artworks.artworkID = ${artworkID}
		LIMIT 1";
	$result = mysql_query($query) or die(mysql_error());
	if(mysql_num_rows($result)){
		list($campaignID,$companyID) = mysql_fetch_row($result);
		$level = 'artwork';
		$levelID = $artworkID;
	}else
		$campaignID = $companyID = 0;
}

if(!$artworkID && $campaignID){
	$query = "
		SELECT brands.companyID FROM campaigns
			LEFT JOIN brands ON brands.brandID = campaigns.brandID
		WHERE
			campaigns.campaignID = ${campaignID}
		LIMIT 1";
	$result = mysql_query($query) or die(mysql_error());
	if(mysql_num_rows($result)){
		list($companyID) = mysql_fetch_row($result);
		$level = 'campaign';
		$levelID = $campaignID;
	}else
		$companyID = 0;
}
if(!$campaignID){
	$level = 'company';
	$levelID = $companyID;
}
if(!$companyID){
	$level = 'system';
	$levelID = 0;
}

if(!$isadmin && $companyID != $_SESSION['companyID']){
	$taskID = $artworkID = $campaignID = 0;
	$companyID = $_SESSION['companyID'];
	$level = 'company';
}




/*
	Font Substitution processing
*/

if(!empty($_POST)) {
	$c=0;
	foreach($_POST['font'] as $i=>$font) {
		$sub = $_POST['subs'][$i];
		$c++;
		if(/*$font==$sub || */$sub==0)
			Font_Substitution::remove_font_substitution($font,$levelID,$level);
		else
			Font_Substitution::set_font_substitution($font,$sub,$levelID,$level);
	}
	if($_POST['process_only']){
		var_dump($_POST);
		echo $c;
		exit();
	}
}
?>
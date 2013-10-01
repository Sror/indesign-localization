<?php
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$userID = (isset($_GET['id'])) ? $_GET['id'] : 0;
$query_publicRs = sprintf("SELECT *, (lastActive + %d > now()) as online FROM users
						LEFT JOIN aro_groups ON users.userGroupID = aro_groups.id
						LEFT JOIN companies ON users.companyID = companies.companyID
						WHERE userID = %d
						LIMIT 1",
						SESSION_TIME,
						$userID);
$publicRs = mysql_query($query_publicRs, $conn) or die(mysql_error());
if(!mysql_num_rows($publicRs)) access_denied();
$row_publicRs = mysql_fetch_assoc($publicRs);
if(!$DB->check_company_acl($_SESSION['companyID'],$row_publicRs['companyID']) && !($row_publicRs['vtID']==0 || $row_publicRs['vtID']==$_SESSION['companyID'])  && !$issuperadmin) access_denied ();
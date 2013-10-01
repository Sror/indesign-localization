<?php
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$companyID = (isset($_GET['id'])) ? $_GET['id'] : 0;
if(!$DB->check_company_acl($_SESSION['companyID'],$companyID) && !$issuperadmin) access_denied ();
$query_company = sprintf("SELECT companies.*,
						service_packages.id AS packageID,
						service_packages.name AS packageName
						FROM companies
						LEFT JOIN service_packages ON companies.packageID = service_packages.id
						WHERE companyID = %d
						LIMIT 1",
						$companyID);
$result_company = mysql_query($query_company, $conn) or die(mysql_error());
if(!mysql_num_rows($result_company)) access_denied();
$row_company = mysql_fetch_assoc($result_company);
?>
<?php
//connect to database
$conn = mysql_connect(HOST_NAME,DB_USER_NAME,DB_PASSWORD) or die(mysql_error());
mysql_select_db(DB_NAME,$conn);
mysql_query("SET CHARACTER SET 'utf8'",$conn);
mysql_query("SET NAMES 'utf8'",$conn);

$domain = $_SERVER["HTTP_HOST"];
$wwwdomain = "www.".$domain;
$query=sprintf("SELECT systemconfig.templateName, systemconfig.siteURL, systemconfig.systemName, systemconfig.systemVersion,
				systemconfig.logoFile, systemconfig.smallLogoFile, systemconfig.currency, systemconfig.format_date, systemconfig.format_time,
				companies.companyName, companies.companyWeb,
				currencies.currencyAb, currencies.currencySymbol
				FROM systemconfig
				LEFT JOIN companies ON systemconfig.companyID = companies.companyID
				LEFT JOIN currencies ON systemconfig.currency = currencies.currencyID
				WHERE systemconfig.siteURL = '%s' OR systemconfig.siteURL = '%s'
				ORDER BY systemconfig.companyID ASC
				LIMIT 1",
				mysql_real_escape_string($domain),
				mysql_real_escape_string($wwwdomain));
$result = mysql_query($query, $conn) or die(mysql_error());
$found = mysql_num_rows($result);

if($found) {
	$row = mysql_fetch_assoc($result);
	$templateName = $row['templateName'];
	$siteURL = $row['siteURL'];
	$systemName = $row['systemName'];
	$systemVersion = $row['systemVersion'];
	$logoFile = $row['logoFile'];
	$smallLogoFile = $row['smallLogoFile'];
	$companyName = $row['companyName'];
	$companyWeb = $row['companyWeb'];
	$currency = $row['currency'];
	$currencyAb = $row['currencyAb'];
	$currencySymbol = $row['currencySymbol'];
	$format_date = $row['format_date'];
	$format_time = $row['format_time'];
} else {
	die("Unauthorised domain name. Please contact <a href=\"mailto:tech@sp-int.com\">developer</a>.");
}

//Style Setup
define("TEMPLATE_NAME",$templateName); 								//template directory name ONLY USED HERE
define("IMG_PATH","templates/".TEMPLATE_NAME."/images/");			//image path **No TRAILING SLASH
define("CSS_PATH","templates/".TEMPLATE_NAME."/css/default.css");	//css path
define("FAVICON","templates/".TEMPLATE_NAME."/favicon.ico");	    //favicon path
define("FORMAT_DATE",$format_date);									//date format
define("FORMAT_TIME",$format_time);									//time format
define("SITE_URL","http://".$siteURL."/");					//site url KEEP TRAILING SLASH
define("SYSTEM_NAME",$systemName);									//system name
define("SYSTEM_VERSION",$systemVersion);							//system version
define("SYSTEM_LOGO_PATH","systemlogos/");							//system logo path
define("SYSTEM_LOGO",SYSTEM_LOGO_PATH.$logoFile);					//small system logo
define("SYSTEM_LOGO_SMALL",SYSTEM_LOGO_PATH.$smallLogoFile);		//small system logo
define("COMPANY_NAME",$companyName);								//company name
define("COMPANY_WEB",$companyWeb);									//company weblink
define("CURRENCY",$currency);										//currency preference id
define("CURRENCY_AB",$currencyAb);									//currency preference abbreviation
define("CURRENCY_SYMBOL",$currencySymbol);							//currency preference symbol
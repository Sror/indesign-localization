<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $_SESSION['companyID']);

$by = isset($_POST['by'])?$_POST['by']:"id";
$order = isset($_POST['order'])?$_POST['order']:"DESC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(!empty($_POST['form'])) {
	if($_POST['form']=="export") {
		require_once(CLASSES.'CSVExporter.php');
		$csv = time().'_system_log_export.csv';
		$exporter = new CSVExporter(ROOT.TMP_DIR.$csv);
		//export header
		$header = array(
			"Log ID",
			"Time",
			"Username",
			"Forename",
			"Surname",
			"Campaign",
			"Artwork",
			"Task Details",
			"Action"
		);
		$exporter->export_csv($header);
		//export data
		$str_id = "";
		foreach($_POST['id'] as $id) {
			$str_id .= (int)$id.',';
		}
		$str_id = trim($str_id,',');
		$query = sprintf("SELECT systemlog.logID AS id, systemlog.action, UNIX_TIMESTAMP(systemlog.time) AS log_time,
						systemlog.campaignID, systemlog.artworkID, systemlog.taskID,
						users.username, users.forename, users.surname,
						campaigns.campaignName,
						artworks.artworkName,
						L1.languageName AS source_lang,
						L2.languageName AS target_lang
						FROM systemlog
						LEFT JOIN users ON systemlog.userID = users.userID
						LEFT JOIN campaigns ON systemlog.campaignID = campaigns.campaignID
						LEFT JOIN artworks ON systemlog.artworkID = artworks.artworkID
						LEFT JOIN tasks ON systemlog.taskID = tasks.taskID
						LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
						LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
						WHERE systemlog.logID IN (%s)
						ORDER BY `%s` %s",
						mysql_real_escape_string($str_id),
						mysql_real_escape_string($by),
						mysql_real_escape_string($order));
		$result = mysql_query($query,$conn) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$data = array(
				$row['id'],
				date(FORMAT_TIME,$row['log_time']),
				$row['username'],
				$row['forename'],
				$row['surname'],
				$row['campaignName'],
				$row['artworkName'],
				!empty($row['taskID']) ? $row['source_lang'].' -> '.$row['target_lang'] : '',
				$row['action']
			);
			$exporter->export_csv($data);
		}
		$DB->LogSystemEvent($_SESSION['userID'],"exported system log");
		header("Location: download.php?File=$csv&SaveAs=$csv&temp");
		exit();
	}
	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $id) {
			$update = sprintf("DELETE FROM systemlog
								WHERE logID = %d",
								$id);
			$result = mysql_query($update, $conn) or die(mysql_error());
		}
		header("Location: index.php?layout=$layout");
		exit();
	}
}

$query = sprintf("SELECT systemlog.logID AS id
					FROM systemlog
					LEFT JOIN users ON systemlog.userID = users.userID
					LEFT JOIN companies ON users.companyID = companies.companyID
					WHERE users.companyID = %d
					AND (users.username LIKE '%s'
					OR users.forename LIKE '%s'
					OR users.surname LIKE '%s'
					OR systemlog.action LIKE '%s')",
					$company_id,
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%");
$result = mysql_query($query, $conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit'])?(int)$_POST['limit']:RPP;
$pages = (ceil($total/$limit)==0)?1:ceil($total/$limit);
$page = isset($_POST['page'])?(int)$_POST['page']:1;
$offset = $limit*($page-1);
?>
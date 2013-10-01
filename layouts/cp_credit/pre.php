<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $_SESSION['companyID']);

$by = isset($_POST['by'])?$_POST['by']:"id";
$order = isset($_POST['order'])?$_POST['order']:"ASC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

$start_year = (isset($_POST['start_year'])) ? $_POST['start_year'] : date("Y");
$start_month = (isset($_POST['start_month'])) ? $_POST['start_month'] : date("n");
$start_day = (isset($_POST['start_day'])) ? $_POST['start_day'] : 1;
$end_year = (isset($_POST['end_year'])) ? $_POST['end_year'] : date("Y");
$end_month = (isset($_POST['end_month'])) ? $_POST['end_month'] : date("n");
$end_day = (isset($_POST['end_day'])) ? $_POST['end_day'] : date("j");

if(!empty($_POST['form'])) {
	if($_POST['form']=="refresh") {
		$DB->recalculate_company_credits($company_id);
	}
	if($_POST['form']=="export") {
		require_once(CLASSES.'CSVExporter.php');
		$csv = time().'_'.$start_day.'.'.$start_month.'.'.$start_year.'-'.$end_day.'.'.$end_month.'.'.$end_year.'_credit_transactions_export.csv';
		$exporter = new CSVExporter(ROOT.TMP_DIR.$csv);
		//export header
		$header = array(
			"Transaction ID",
			"Time",
			"Username",
			"Forename",
			"Surname",
			"Campaign",
			"Artwork",
			"Task Details",
			"Transaction",
			"Credit out",
			"Credit in",
			"Balance"
		);
		$exporter->export_csv($header);
		//export data
		$str_id = "";
		foreach($_POST['id'] as $id) {
			$str_id .= (int)$id.',';
		}
		$str_id = trim($str_id,',');
		$query = sprintf("SELECT credits.*, UNIX_TIMESTAMP(credits.time) AS trans_time,
						users.username, users.forename, users.surname,
						campaigns.campaignName,
						artworks.artworkName,
						L1.languageName AS source_lang,
						L2.languageName AS target_lang
						FROM credits
						LEFT JOIN users ON credits.user_id = users.userID
						LEFT JOIN campaigns ON credits.campaign_id = campaigns.campaignID
						LEFT JOIN artworks ON credits.artwork_id = artworks.artworkID
						LEFT JOIN tasks ON credits.task_id = tasks.taskID
						LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
						LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
						WHERE credits.id IN (%s)
						ORDER BY `%s` %s",
						mysql_real_escape_string($str_id),
						mysql_real_escape_string($by),
						mysql_real_escape_string($order));
		$result = mysql_query($query, $conn) or die(mysql_error());
		$balance = 0;
		while($row = mysql_fetch_assoc($result)) {
			$data = array(
				$row['id'],
				date(FORMAT_TIME,$row['trans_time']),
				$row['username'],
				$row['forename'],
				$row['surname'],
				$row['campaignName'],
				$row['artworkName'],
				!empty($row['task_id']) ? $row['source_lang'].' -> '.$row['target_lang'] : '',
				$row['transaction'],
				$row['credit_out'],
				$row['credit_in'],
				$row['balance']
			);
			$exporter->export_csv($data);
		}
		$DB->LogSystemEvent($_SESSION['userID'],"exported credit transactions report");
		header("Location: download.php?File=$csv&SaveAs=$csv&temp");
		exit();
	}
	if($_POST['form']=="topup") {
		$DB->log_credit_transaction($company_id,$_SESSION['userID'],0,0,0,"Top up",null,$_POST['amount']);
	}
	if($_POST['form']=="refund") {
		foreach($_POST['id'] as $id) {
			$trans_info = $DB->get_transaction_info($id);
			if($trans_info === false) continue;
			$transaction = $trans_info['transaction'];
			$refund = $trans_info['credit_out'];
			if(empty($refund)) continue;
			$DB->log_credit_transaction($company_id,$_SESSION['userID'],0,0,0,"Refund transaction ID: $id",null,$refund,false);
		}
		$DB->recalculate_company_credits($company_id);
	}
	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $id) {
			if($issuperadmin) $DB->delete_transaction($id);
		}
		$DB->recalculate_company_credits($company_id);
	}
	header("Location: index.php?layout=$layout");
	exit();
}

$query = sprintf("SELECT credits.id
				FROM credits
				WHERE company_id = %d
				AND DATE(credits.time) >= '%d-%d-%d'
				AND DATE(credits.time) <= '%d-%d-%d'",
				$company_id,
				$start_year,
				$start_month,
				$start_day,
				$end_year,
				$end_month,
				$end_day);
$result = mysql_query($query,$conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit'])?(int)$_POST['limit']:RPP;
$pages = (ceil($total/$limit)==0)?1:ceil($total/$limit);
$page = isset($_POST['page'])?(int)$_POST['page']:1;
$offset = $limit*($page-1);
?>
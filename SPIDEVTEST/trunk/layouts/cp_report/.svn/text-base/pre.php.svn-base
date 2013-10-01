<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $_SESSION['companyID']);

$start_year = (isset($_POST['start_year'])) ? $_POST['start_year'] : date("Y");
$start_month = (isset($_POST['start_month'])) ? $_POST['start_month'] : date("n");
$start_day = (isset($_POST['start_day'])) ? $_POST['start_day'] : 1;
$end_year = (isset($_POST['end_year'])) ? $_POST['end_year'] : date("Y");
$end_month = (isset($_POST['end_month'])) ? $_POST['end_month'] : date("n");
$end_day = (isset($_POST['end_day'])) ? $_POST['end_day'] : date("j");

$by = isset($_POST['by'])?$_POST['by']:"date";
$order = isset($_POST['order'])?$_POST['order']:"DESC";
$pre = ($order=="ASC")?"DESC":"ASC";

if(!empty($_POST['form'])) {
	if($_POST['form']=="refresh") {
		$DB->refresh_signoff_report($company_id, $start_year, $start_month, $start_day, $end_year, $end_month, $end_day);
	}
	if($_POST['form']=="export") {
		require_once(CLASSES.'CSVExporter.php');
		$csv = time().'_'.$start_day.'.'.$start_month.'.'.$start_year.'-'.$end_day.'.'.$end_month.'.'.$end_year.'_signoff_report_export.csv';
		$exporter = new CSVExporter(ROOT.TMP_DIR.$csv);
		//export header
		$header = array(
			"Report ID",
			"Date",
			"Pages",
			"Cost",
			"Word Count",
			"Translation Memory",
			"Usage"
		);
		$exporter->export_csv($header);
		//export data
		$str_id = "";
		foreach($_POST['id'] as $id) {
			$str_id .= (int)$id.',';
		}
		$str_id = trim($str_id,',');
		$query = sprintf("SELECT *
						FROM signoff_report_cache
						WHERE id IN (%s)
						ORDER BY `%s` %s",
						mysql_real_escape_string($str_id),
						mysql_real_escape_string($by),
						mysql_real_escape_string($order));
		$result = mysql_query($query, $conn) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$data = array(
				$row['id'],
				date(FORMAT_DATE,strtotime($row['date'])),
				$row['pages'],
				$row['cost'],
				$row['words_total'],
				$row['words_tm'],
				number_format($row['words_tm']/$row['words_total']*100,1)."%"
			);
			$exporter->export_csv($data);
		}
		$DB->LogSystemEvent($_SESSION['userID'],"exported sign-off report");
		header("Location: download.php?File=$csv&SaveAs=$csv&temp");
		exit();
	}
}

$query = sprintf("SELECT id
				FROM signoff_report_cache
				WHERE company_id = %d
				AND (date = '%d-%d-%d'
				OR date = '%d-%d-%d')",
				$company_id,
				$start_year,
				$start_month,
				$start_day,
				$end_year,
				$end_month,
				$end_day);
$result = mysql_query($query, $conn) or die(mysql_error());
if(mysql_num_rows($result)<2) $DB->refresh_signoff_report($company_id, $start_year, $start_month, $start_day, $end_year, $end_month, $end_day);

$query = sprintf("SELECT id
				FROM signoff_report_cache
				WHERE company_id = %d
				AND date >= '%d-%d-%d'
				AND date <= '%d-%d-%d'",
				$company_id,
				$start_year,
				$start_month,
				$start_day,
				$end_year,
				$end_month,
				$end_day);
$result = mysql_query($query, $conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit'])?(int)$_POST['limit']:RPP;
$pages = (ceil($total/$limit)==0)?1:ceil($total/$limit);
$page = isset($_POST['page'])?(int)$_POST['page']:1;
$offset = $limit*($page-1);
?>
<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="new") {
		header("Location: index.php?layout=$layout&task=new");
	}
	
	if($_POST['form']=="lookup") {
		header("Location: index.php?layout=$layout&task=lookup");
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=edit&id=$id");
	}

	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $id) {
			$DB->DeletePara($id,true);
		}
		header("Location: index.php?layout=$layout");
	}
	exit();
}

$DB = new Translator();
//Import
if(isset($_POST['action']) && $_POST['action']=='import' && !empty($_FILES['upload_file']['tmp_name']) && !empty($_POST['inlang_id']) && !empty($_POST['outlang_id'])){
	$temp_file = tempnam(sys_get_temp_dir(), 'csv');
	if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $temp_file)) {
		if(!file_exists($temp_file)) die("No file");
		$file_handle = fopen($temp_file, "r");
		if($file_handle!==false){
			$CompanyID = (int)$_POST['filter_company'];
			$SourceLangID = (int)$_POST['inlang_id'];
			$TargetLangID = (int)$_POST['outlang_id'];
			$para_types = (int)$_POST['filter_tm_type'];
			$update = $_POST['update_only']=='on';
			$TaskID=0;$brandID=0;$subjectID=0;
			while (!feof($file_handle) ) {
				list($sourcePara,$targetPara) =fgetcsv($file_handle, 4096);
				if(empty($sourcePara))continue;
				if(empty($targetPara))continue;
				$para_row = $DB->ParaExists($sourcePara, $SourceLangID);
				if($para_row!==false){
					//Add Translated text
					$DB->AddTranslated($targetPara, $TargetLangID, $para_row['PG'], $SourceLangID, $TaskID, $_SESSION['userID'], $para_types, $brandID, $subjectID);
				}elseif(!$update){
					//Add New Paragraph text
					$para_row = $DB->AddParagraph($sourcePara,$SourceLangID,0,$_SESSION['userID'],$para_types,$brandID,$subjectID);
					//Add Translated text
					$DB->AddTranslated($targetPara, $TargetLangID, $para_row['PG'], $SourceLangID, $TaskID, $_SESSION['userID'], $para_types, $brandID, $subjectID);
				}
			}
			fclose($file_handle);
		}
	}
	#exit();
}
				
//Export
if(isset($_POST['action']) && $_POST['action']=='export' && !empty($_POST['inlang_id']) && !empty($_POST['outlang_id'])){
	$CompanyID = (int)$_POST['filter_company'];
	$SourceLangID = (int)$_POST['inlang_id'];
	$TargetLangID = (int)$_POST['outlang_id'];
	$para_types = (int)$_POST['filter_tm_type'];
	$only_empty = ($_POST['empty_id']=='on');

	$source_query = sprintf("SELECT paragraphs.ParaText, paragraphs.uID as ParaID, languages.languageName, languages.flag, para_types.name AS paraType
							FROM paragraphs
							LEFT JOIN paraset ON paraset.ParaID = paragraphs.uID
							LEFT JOIN users ON paragraphs.user_id = users.userID
							LEFT JOIN para_types ON paragraphs.type_id = para_types.id
							LEFT JOIN languages ON languages.languageID = paragraphs.LangID
							LEFT JOIN brands ON paragraphs.brand_id = brands.brandID
							LEFT JOIN subjects ON paragraphs.subject_id = subjects.subjectID
							WHERE paragraphs.LangID = %d
							AND para_types.id=%d
							ORDER BY
							para_types.order DESC,
							paragraphs.rating DESC,
							paragraphs.timeRef DESC",
							$SourceLangID,
							$para_types);
	$export_result = mysql_query($source_query, $conn) or die(mysql_error());
	
	$Lang = $DB->get_lang_info($SourceLangID);
	$SourceLangFlag = substr($Lang['flag'], 0, 2);
	$Lang = $DB->get_lang_info($TargetLangID);
	$TargetLangFlag = substr($Lang['flag'], 0, 2);
	
	#header("Content-type: application/vnd.ms-excel");
	header("Content-type: application/force-download");
	header("Content-disposition: attachment; filename=".$SourceLangFlag."-".$TargetLangFlag."_".".csv");
	$temp_file = tempnam(sys_get_temp_dir(), 'csv');
	#$temp_file = ROOT.TMP_DIR.date("Y-m-d").".csv";
	
	//BuildTMTypeList
	
	$fp = fopen($temp_file, 'w');
	while($export_row = mysql_fetch_assoc($export_result)) {
		$Trans = $DB->TranslateText($export_row['ParaID'], $TargetLangID, $SourceLangID);
		//Skip Translated text
		if($Trans['LC'] != 0 && $only_empty) continue;
		$export = ($Trans['LC'] == 0)?
			array($export_row['ParaText'],''):
			array($export_row['ParaText'],$Trans['Para']);
		fputcsv($fp, $export);
	}
	fclose($fp);
	#$File = basename($temp_file);
	#header("Location: download.php?File=$File&SaveAs=$temp_file&temp&bin");
	#exit;
	echo "\xEF\xBB\xBF"; //BOM 
	readfile($temp_file);
	unlink($temp_file);
	exit();
}

$by = isset($_POST['by'])?$_POST['by']:"id";
$order = isset($_POST['order'])?$_POST['order']:"DESC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $_SESSION['companyID']);
if(isset($_POST['filter_tm_lang'])) $_SESSION['filter_tm_lang'] = $_POST['filter_tm_lang'];
$lang_id = (isset($_POST['filter_tm_lang'])) ? $_POST['filter_tm_lang'] : (isset($_SESSION['filter_tm_lang']) ? $_SESSION['filter_tm_lang'] : $_SESSION['filter_tm_lang']);
if(isset($_POST['filter_tm_type'])) $_SESSION['filter_tm_type'] = $_POST['filter_tm_type'];
$type_id = (isset($_POST['filter_tm_type'])) ? $_POST['filter_tm_type'] : (isset($_SESSION['filter_tm_type']) ? $_SESSION['filter_tm_type'] : $_SESSION['filter_tm_type']);

$sub = "";
$sub .= !empty($lang_id) ? " AND paragraphs.LangID = $lang_id" : "";
$sub .= !empty($type_id) ? " AND paragraphs.type_id = $type_id" : "";
$query = sprintf("SELECT paragraphs.uID
					FROM paragraphs
					LEFT JOIN languages ON paragraphs.LangID = languages.languageID
					LEFT JOIN users ON paragraphs.user_id = users.userID
					LEFT JOIN companies ON users.companyID = companies.companyID
					LEFT JOIN para_types ON paragraphs.type_id = para_types.id
					LEFT JOIN brands ON paragraphs.brand_id = brands.brandID
					LEFT JOIN subjects ON paragraphs.subject_id = subjects.subjectID
					WHERE users.companyID = %d
					%s
					AND (paragraphs.ParaText LIKE '%s'
					OR languages.languageName LIKE '%s'
					OR para_types.name LIKE '%s'
					OR users.username LIKE '%s'
					OR brands.brandName LIKE '%s'
					OR subjects.subjectTitle LIKE '%s')",
					$company_id,
					mysql_real_escape_string($sub),
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
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
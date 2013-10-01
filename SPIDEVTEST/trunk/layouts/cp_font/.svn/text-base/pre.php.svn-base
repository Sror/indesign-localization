<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(isset($_POST['filter_engine'])) $_SESSION['filter_engine'] = $_POST['filter_engine'];
$engine_id = (isset($_POST['filter_engine'])) ? $_POST['filter_engine'] : (isset($_SESSION['filter_engine']) ? $_SESSION['filter_engine'] : ENGINE_INDESIGN_ID);

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="scan") {
		require_once(CLASSES."services.php");
		$Service = new EngineService($engine_id,true);
		$IsServerRunning = $Service->IsServerRunning(10);
		if(!$IsServerRunning) server_busy();
		//generate server info for fonts
		$Service->ServerInfo();
		$InstalledFonts = $Service->GetInstalledFonts();
		foreach($InstalledFonts as $FontFamily=>$fonts) {
			foreach($fonts as $style=>$font) {
				if(is_numeric($style)) $style = "";
				if(empty($font)) continue;
				$query = sprintf("SELECT id
									FROM fonts
									WHERE name = '%s'
									AND style = '%s'
									AND engine_id = %d",
									mysql_real_escape_string($font),
									mysql_real_escape_string($style),
									$engine_id);
				$result = mysql_query($query,$conn) or die(mysql_error());
				if(mysql_num_rows($result)) {
					while($row = mysql_fetch_assoc($result)) {
						$update = sprintf("UPDATE fonts SET
											family = '%s',
											installed = 1
											WHERE id = %d",
											mysql_real_escape_string($FontFamily),
											$row['id']);
						mysql_query($update,$conn) or die(mysql_error());
					}
				} else {
					$update = sprintf("INSERT INTO fonts
										(family, name, style, engine_id, installed)
										VALUES
										('%s', '%s', '%s', %d, 1)",
										mysql_real_escape_string($FontFamily),
										mysql_real_escape_string($font),
										mysql_real_escape_string($style),
										$engine_id);
					mysql_query($update,$conn) or die(mysql_error());
				}
			}
		}
		header("Location: index.php?layout=$layout");
		exit();
	}
	
	if($_POST['form']=="install") {
		header("Location: index.php?layout=$layout&task=install");
		exit();
	}

	if($_POST['form']=="uninstall") {
		foreach($_POST['id'] as $id) {
			$update = sprintf("UPDATE fonts SET
								installed = 0
								WHERE id = %d",
								$id);
			$result = mysql_query($update, $conn) or die(mysql_error());
		}
		header("Location: index.php?layout=$layout");
		exit();
	}
	
}

$by = isset($_POST['by'])?$_POST['by']:"family";
$order = isset($_POST['order'])?$_POST['order']:"ASC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

$query = sprintf("SELECT fonts.id
					FROM fonts
					LEFT JOIN service_engines ON fonts.engine_id = service_engines.id
					WHERE fonts.engine_id = %d
					AND ( fonts.family LIKE '%s'
					OR fonts.name LIKE '%s'
					OR fonts.style LIKE '%s'
					OR service_engines.name LIKE '%s' )",
					$engine_id,
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
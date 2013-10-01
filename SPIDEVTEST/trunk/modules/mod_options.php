<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$artwork_id = !empty($_GET['artwork_id']) ? (int)$_GET['artwork_id'] : 0;
$task_id = !empty($_GET['task_id']) ? (int)$_GET['task_id'] : 0;

$file_name = $DB->GetFilenamebyArtwork($artwork_id);
if($file_name===false) die("Invalid Artwork");

require_once(CLASSES."services.php");
$Service = new EngineService($artwork_id);
if(!empty($_GET['option']) && !empty($_GET['do'])) {
	if($_GET['option']=="cache") {
		if($_GET['do']=="delete") {
			$Service->EmptyCache($file_name,$task_id);
		}
		if($_GET['do']=="enable") {
			if($_GET['target']=="artwork") {
				$DB->UpdateArtworkCache($artwork_id,1);
			}
			if($_GET['target']=="task") {
				$DB->UpdateTaskCache($task_id,1);
			}
		}
		if($_GET['do']=="disable") {
			if($_GET['target']=="artwork") {
				$DB->UpdateArtworkCache($artwork_id,0);
			}
			if($_GET['target']=="task") {
				$DB->UpdateTaskCache($task_id,0);
			}
		}
	}
}

$cache_status = $DB->GetAllCacheStatus($artwork_id,$task_id);
if($cache_status===false) die("Error Retrieving Cache Details");
$engine_cache = $cache_status['engine_cache'];
$artwork_cache = $cache_status['artwork_cache'];
$task_cache = !empty($task_id) ? $cache_status['task_cache'] : false;

echo '<div class="mainwrap">';
echo '<div class="fieldset">';
echo '<fieldset>';
echo '<legend>'.$lang->display('Cache').'</legend>';
echo '<div id="CacheDiv">';
echo '<table width="100%" cellspacing="0" cellpadding="5" border="0">';
echo '<tr>';
echo '<th width="50%">'.$lang->display('Service').'</th>';
echo '<td width="50%">';
echo '<input type="radio" name="engine_cache" value="1"';
if($engine_cache) echo ' checked="checked"';
echo ' disabled="disabled" />'.$lang->display('On');
echo '<span class="span"></span>';
echo '<input type="radio" name="engine_cache" value="0"';
if(!$engine_cache) echo ' checked="checked"';
echo ' disabled="disabled" />'.$lang->display('Off');
echo '</td>';
echo '</tr>';
if($engine_cache) {
	echo '<tr>';
	echo '<th>'.$lang->display('Artwork').'</th>';
	echo '<td>';
	echo '<input type="radio" name="artwork_cache" value="1"';
	if($artwork_cache) echo ' checked="checked"';
	if(!empty($task_id)) echo ' disabled="disabled"';
	echo ' onclick="ResetDiv(\'CacheDiv\');DoAjax(\'artwork_id='.$artwork_id.'&task_id='.$task_id.'&option=cache&do=enable&target=artwork\',\'window\',\'modules/mod_options.php\');" />'.$lang->display('On');
	echo '<span class="span"></span>';
	echo '<input type="radio" name="artwork_cache" value="0"';
	if(!$artwork_cache) echo ' checked="checked"';
	if(!empty($task_id)) echo ' disabled="disabled"';
	echo ' onclick="ResetDiv(\'CacheDiv\');DoAjax(\'artwork_id='.$artwork_id.'&task_id='.$task_id.'&option=cache&do=disable&target=artwork\',\'window\',\'modules/mod_options.php\');" />'.$lang->display('Off');
	echo '</td>';
	echo '</tr>';
	if($artwork_cache) {
		if(!empty($task_id)) {
			echo '<tr>';
			echo '<th>'.$lang->display('Task Workflow').'</th>';
			echo '<td>';
			echo '<input type="radio" name="task_cache" value="1"';
			if($task_cache) echo ' checked="checked"';
			echo ' onclick="ResetDiv(\'CacheDiv\');DoAjax(\'artwork_id='.$artwork_id.'&task_id='.$task_id.'&option=cache&do=enable&target=task\',\'window\',\'modules/mod_options.php\');" />'.$lang->display('On');
			echo '<span class="span"></span>';
			echo '<input type="radio" name="task_cache" value="0"';
			if(!$task_cache) echo ' checked="checked"';
			echo ' onclick="ResetDiv(\'CacheDiv\');DoAjax(\'artwork_id='.$artwork_id.'&task_id='.$task_id.'&option=cache&do=disable&target=task\',\'window\',\'modules/mod_options.php\');" />'.$lang->display('Off');
			echo '</td>';
			echo '</tr>';
		}
		if($DB->GetCacheStatus($artwork_id,$task_id)) {
			$iscached = $Service->isCached($file_name,$task_id);
			$cache_time = $Service->CachedTime($file_name,$task_id);
			echo '<tr>';
			echo '<th>'.$lang->display('Cached').'</th>';
			echo '<td>';
			echo '<img src="'.IMG_PATH.'ico_';
			echo $iscached ? 'enable' : 'disable';
			echo '.png" />';
			echo '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th>'.$lang->display('Last Update').'</th>';
			echo '<td>';
			echo empty($cache_time) ? '<span class="grey">'.$lang->display('N/A').'</span>' : date(FORMAT_TIME,$cache_time);
			echo '</td>';
			echo '</tr>';
			if($iscached) {
				echo '<tr>';
				echo '<th>'.$lang->display('Reset').'</th>';
				echo '<td>';
				echo '<input
						type="button"
						class="btnDo"
						onmousemove="this.className=\'btnOn\'"
						onmouseout="this.className=\'btnDo\'"
						title="'.$lang->display('Empty Cache').'"
						value="'.$lang->display('Empty Cache').'"
						onclick="if(confirm(\''.$lang->display('Are you sure you want to empty the cache?').'\')) { ResetDiv(\'CacheDiv\');DoAjax(\'artwork_id='.$artwork_id.'&task_id='.$task_id.'&option=cache&do=delete\',\'window\',\'modules/mod_options.php\'); }" />';
				echo '</td>';
				echo '</tr>';
			}
		}
	}
}
echo '</table>';
echo '</div>';
echo '</fieldset>';
echo '</div>';
echo '</div>';
<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');
$Translator = new Translator();

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$pl = isset($_GET['pl']) ? $_GET['pl'] : 0;
if(isset($_GET['ignore'])) $_SESSION['ignore'] = $_GET['ignore'];
$ignore = isset($_GET['ignore']) ? $_GET['ignore'] : ( isset($_SESSION['ignore']) ? $_SESSION['ignore'] : 0 );
if($ignore==0) {
	$Translator->RemoveParaIgnore($id,$pl);
} else {
	$Translator->AddParaIgnore($id,$pl);
}
$ignore_tick = $ignore==0 ? 1 : 0;
echo '<input type="checkbox" id="ignore_check_'.$pl.'" name="ignore_check_'.$pl.'" value="1"';
if($Translator->CheckParaIgnore($id,$pl)) echo ' checked=checked';
echo '  onclick="ResetDiv(\'ignore'.$pl.'\');DoAjax(\'id='.$id.'&pl='.$pl.'&ignore='.$ignore_tick.'\',\'ignore'.$pl.'\',\'modules/mod_task_para_ignore.php\');">';
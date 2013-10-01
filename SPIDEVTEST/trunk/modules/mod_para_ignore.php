<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');
$Translator = new Translator();

$pl = isset($_GET['pl']) ? $_GET['pl'] : 0;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$ignore = isset($_GET['ignore']) ? (int)$_GET['ignore'] : null;
if(!is_null($ignore)) $Translator->UpdateParaIgnore($pl,$id,$ignore);
$ignore_tick = empty($ignore) ? 1 : 0;
echo '<input type="checkbox" id="ignore_check_'.$pl.'" name="ignore_check_'.$pl.'" value="1"';
if($Translator->CheckParaIgnore($pl,$id)) echo ' checked=checked';
echo '  onclick="ResetDiv(\'ignore'.$pl.'\');DoAjax(\'id='.$id.'&pl='.$pl.'&ignore='.$ignore_tick.'\',\'ignore'.$pl.'\',\'modules/mod_para_ignore.php\');">';
<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$pl = isset($_GET['pl']) ? (int)$_GET['pl'] : 0;
$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;
$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;
$order = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$order = $DB->SaveStoryOrder($pl,$task_id,$order);
echo '<select
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		name="order['.$pl.']"
		id="order['.$pl.']"
		onchange="ResetDiv(\'story_order_'.$pl.'\');DoAjax(\'pl='.$pl.'&no='.$no.'&task_id='.$task_id.'&order=\'+this.value,\'story_order_'.$pl.'\',\'modules/mod_para_order.php\');">';
BuildOrders($no,$order);
echo '</select>';
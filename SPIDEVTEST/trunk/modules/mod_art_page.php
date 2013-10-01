<?php
require_once(dirname(__FILE__).'/../config.php');
$artwork_id = isset($_GET['artwork_id'])?$_GET['artwork_id']:0;
$page = isset($_GET['page'])?$_GET['page']:1;
BuildPageViewer($artwork_id,$page);
?>
<?php
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$view = (isset($_GET['view'])) ? $_GET['view'] : "intray";
$by = (isset($_POST['by'])) ? $_POST['by'] : "deadline";
$order = (isset($_POST['order'])) ? $_POST['order'] : "ASC";
$preorder = ($order == "ASC") ? "DESC" : "ASC";
?>
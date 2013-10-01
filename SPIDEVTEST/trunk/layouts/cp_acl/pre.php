<?php
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');
header("Location: acl/admin/acl_admin.php");
exit();
?>
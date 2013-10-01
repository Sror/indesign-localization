<?php
require_once(dirname(__FILE__).'/config.php');

$layout = isset($_GET['layout'])?$_GET['layout']:"login";
if(!file_exists("layouts/$layout")) access_denied();

$task = isset($_GET['task'])?$_GET['task']:"";

if(empty($task)) {
	$default = "layouts/$layout/default.php";
} else {
	$default = "layouts/$layout/$task/default.php";
}

if(empty($task)) {
	$pre = "layouts/$layout/pre.php";
} else {
	$pre = "layouts/$layout/$task/pre.php";
}

if(file_exists($pre)) {
	require_once($pre);
}

$current_layout = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
	$current_layout .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<?php
			if($layout=="system") {
				echo '<meta http-equiv="Refresh" content="'.(int)$row_code['delay'].';url='.$restrictGoTo.'">';
			}
		?>
		<link rel="shortcut icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
		<link rel="icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
		<link rel="stylesheet" type="text/css" href="<?php echo CSS_PATH; ?>" media="screen" />
		<title><?php echo SYSTEM_NAME.' '.SYSTEM_VERSION; ?></title>
		<script type="text/javascript" src="javascripts/jquery/jquery-1.7.1.js"></script>
	</head>
	<body>
		<div id="loadingme" style="display:block;">
			<div id="waiting" class="waiting" align="center"></div>
			<div id="msgbox" class="msgbox">
				<div id="processing" style="display:block">
					<div class="processing">
						<?php echo $lang->display('Processing your request. Please wait'); ?>...<br /><img src="<?php echo IMG_PATH; ?>bar_loading.gif" />
					</div>
					<div id="processlog">Initialising...</div>
				</div>
			</div>
		</div>
		<div id="wrapper">
			<?php
				if(file_exists($default)) {
					require_once($default);
				} else {
					//shouldn't get to here just in case
					echo '<div id="wrapperWhite" align="center">';
					echo '<img src="'.IMG_PATH.'img_404.jpg">';
					echo '</div>';
				}
			?>
		</div>
		<?php require_once(MODULES.'mod_footer.php'); ?>
		<div id="bottomline"></div>
	</body>
</html>
<script type="text/javascript" src="javascripts/jquery/jquery-1.6.2.js"></script>
<script type="text/javascript" src="javascripts/functions.js"></script>
<script type="text/javascript" src="javascripts/slide.js"></script>
<script type="text/javascript" src="javascripts/fade.js"></script>
<script type="text/javascript" src="javascripts/process.js"></script>
<?php
if(file_exists(ROOT."layouts/$layout/apend.js")) {
	echo '<script type="text/javascript" src="layouts/'.$layout.'/apend.js"></script>';
}
?>
<script type="text/javascript">fadeOut('loadingme');</script>
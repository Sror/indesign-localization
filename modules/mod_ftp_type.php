<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$ftp_type = isset($_GET['type']) ? $_GET['type'] : FTP_BINARY;
$_SESSION['ftp_type'] = $ftp_type;
?>
<a href="javascript:DoAjax('type=
				<?php
					switch($_SESSION['ftp_type']) {
						case FTP_BINARY:
							echo FTP_ASCII;
							break;
						case FTP_ASCII:
							echo FTP_BINARY;
							break;
					}
				?>
				','ftp_type','modules/mod_ftp_type.php');">
	<div class="ico">
	<?php
		echo '<img src="'.IMG_PATH.'toolbar/ico_';
		switch($_SESSION['ftp_type']) {
			case FTP_BINARY:
				echo 'binary';
				break;
			case FTP_ASCII:
				echo 'ascii';
				break;
		}
		echo '.png" />';
	?>
	</div>
	<div>
	<?php
		switch($_SESSION['ftp_type']) {
			case FTP_BINARY:
				echo 'BINARY';
				break;
			case FTP_ASCII:
				echo 'ASCII';
				break;
		}
	?>
	</div>
</a>
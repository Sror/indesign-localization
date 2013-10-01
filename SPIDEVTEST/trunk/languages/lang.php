<?php
if(isset($_GET['lang'])) {
	$_SESSION['lang'] = $_GET['lang'];
	setcookie("lang", $_GET['lang'], time()+60*60*24*7);
	$langCode = $_GET['lang'];
} else {
	if(isset($_SESSION['lang'])) {
		$langCode = $_SESSION['lang'];
	} else if (isset($_COOKIE['lang'])) {
		$langCode = $_COOKIE['lang'];
	} else {
		$langCode = "gb";
	}
}

function BuildLangFlags() {
	global $conn;
	if(isset($_SERVER['QUERY_STRING'])) {
		$qs = preg_replace("/&?lang=\w{2}&?/i","",$_SERVER['QUERY_STRING']);
	}
	$qs = (!empty($qs)) ? "&$qs" : "" ;
	$query = sprintf("SELECT name, acronym
						FROM language_options
						ORDER BY id ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	echo '<div id="lanBox">';
	while($row = mysql_fetch_assoc($result)) {
		echo '<span class="flag">';
		echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\''.$_SERVER['PHP_SELF'].'?lang='.$row['acronym'].$qs.'\');"><img src="images/flags/'.$row['acronym'].'.gif" title="'.$row['name'].'"></a>';
		echo '</span>';
	}
	echo '</div>';
}

function BuildLangDropdown($acronym) {
	global $conn;
	if(isset($_SERVER['QUERY_STRING'])) {
		$qs = preg_replace("/&?lang=\w{2}&?/i","",$_SERVER['QUERY_STRING']);
	}
	$qs = (!empty($qs)) ? "&$qs" : "" ;
	$query = sprintf("SELECT name, acronym
						FROM language_options
						WHERE acronym = '%s'
						LIMIT 1",
						mysql_real_escape_string($acronym));
	$result = mysql_query($query, $conn) or die(mysql_error());
	$found = mysql_num_rows($result);
	if($found) {
		$row = mysql_fetch_assoc($result);
		echo '<div class="langOff" onclick="this.className=(this.className==\'langOn\')?\'langOff\':\'langOn\';SlideDiv(\'langlist\');">';
		echo '<div class="flag"><img src="images/flags/'.$row['acronym'].'.gif" title="'.$row['name'].'"></div>';
		echo '<div class="name">'.$row['name'].'</div>';
		echo '<div class="clear"></div>';
		echo '</div>';
		echo '<div id="langlist" class="dropdown" style="display:none; overflow:hidden; height:207px;">';
		$query = sprintf("SELECT name, acronym
							FROM language_options
							ORDER BY id ASC");
		$result = mysql_query($query, $conn) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\''.$_SERVER['PHP_SELF'].'?lang='.$row['acronym'].$qs.'\');">';
			echo '<div class="lineOff" onmouseover="this.className=\'lineOn\';" onmouseout="this.className=\'lineOff\';">';
			echo '<div class="ico"><img src="'.IMG_PATH.'arrow_right.png" title="'.$row['name'].'"></div>';
			echo '<div class="ico"><img src="images/flags/'.$row['acronym'].'.gif" title="'.$row['name'].'"></div>';
			echo '<div class="txt">'.$row['name'].'</div>';
			echo '<div class="clear"></div>';
			echo '</div>';
			echo '</a>';
		}
		echo '</div>';
	}
}
?>
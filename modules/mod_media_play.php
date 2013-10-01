<?php
require_once(dirname(__FILE__).'/../config.php');

$ref = isset($_GET['ref']) ? $_GET['ref'] : "";
if(empty($ref)) die("Invalid Media");
//copy to tmp folder for preview purpose
$tmp_file = ROOT.TMP_DIR.basename($ref);
if(!file_exists($tmp_file)) {
	copy(REPOSITORY_DIR.$ref, $tmp_file);
}
$file = TMP_DIR.$ref;
?>
<OBJECT
	id="MediaPlayer1"
	classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95"
	codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"
	standby="Loading Microsoft Windows® Media Player components..."
	type="application/x-oleobject"
	width="100%"
	height="400"
>
<param name="fileName" value="<?php echo $file; ?>" />
<param name="animationatStart" value="true" />
<param name="transparentatStart" value="true" />
<param name="autoStart" value="true" />
<param name="showControls" value="true" />
<param name="Volume" value="-200" />
<EMBED
	type="application/x-mplayer2"
	pluginspage="http://www.microsoft.com/Windows/MediaPlayer/"
	src="<?php echo $file; ?>"
	name="MediaPlayer1"
	width="100%"
	height="400"
	autostart="1"
	showcontrols="1"
	volume="-200"
/>
</OBJECT>
<?php
/**
 * TODO:
 * collapse styles (moved all styles in a single node)
 * Merge Text (if all styles are the same as the previous node append text to previoue node and remove current node),
 * Cleanup (remove unneeded styles - add this into collapse)
 * Get/Set Paragraphs (this should be a simple get text from nodes once its been cleaned up (see above) )
 * remove Paragraphs (*should* be a simple getText from node)
 */
/**
 * Description of rebuilder
 *
 * @author MadTechie
 */
require_once 'nodes.php';
#require_once 'cSegments.php';
#require_once 'pSegments.php';
#require_once 'Paragraphs.php';

class rebuilder{
  private $story = '';
  private $storyObject = null;

  private $ColorTable = '';
  //Details
  private $FileFormat = '';
  private $FileVersion = '';
  private $FeatureSet = '';
  private $DefineCharStyle = array();
  private $DefineListStyle = array();
  private $DefineParaStyle = array();
  private $DefineKinsokuStyle = array();
  private $DefineMojikumiStyle = array();
  
  function  __construct($story) {
    $this->story = $story;
    $this->storyObject = new nodes();
    $this->removeHeaders();
    $this->removeDefined();

    $Story = new Section_Story();
    $section = null;

    $SkimReg = '/^<([^:]*):([^>]*)>/us';
    $TextReg = '/(^[^<]*)/usm';
    while (!empty($this->story)) {
      if (preg_match($SkimReg, $this->story, $regs)) {
	if($this->isSection($regs[1])) {
	  if(!is_null($section)){
	      if(!is_null($current)) $section->addNode($current);
	      $Story->addSection($section);
	  }
	  $current = new Section_Segments();
	  $section = "Section_{$regs[1]}";
	  $section = new $section($regs[2]);

	}elseif(!empty($regs[2])){
	  if($this->isStyle($regs[1])){
	    $style = "Style_{$regs[1]}";
	    $current->addStyle(new $style($regs[2]));
	  }else{
            $style = "Style_default";
            $default = new $style($regs[2]);
            $default->become($regs[1]);
	    $current->addStyle($default);
          }
	}else{ //Empty
	  
	}
	$this->story = preg_replace($SkimReg, '', $this->story, 1);
      }elseif(preg_match($TextReg, $this->story, $regs)){
	$this->story = preg_replace($TextReg, '', $this->story, 1);
	#echo "#TEXT {$regs[1]}\n";
	$current->setText($regs[1]);
	$section->addNode($current);
	$current = new Section_Segments();
       }else{
	die("ERROR");
      }
    }
    if(!is_null($section)) $Story->addSection($section);
    $this->storyObject = $Story;

    //NEXT read back;

  }

  function isSection($name) {
    return $this->getFile($name,'Section');
  }
  function isStyle($name) {
    return $this->getFile($name,'Style');
  }

  protected function getFile($name,$dir){
    return file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR."$name.php");
    //Will probably update to below when extra options are needed
    if($handle = opendir(dirname(__FILE__).DIRECTORY_SEPARATOR.$dir)) {
      while (false !== ($file = readdir($handle))) {
	if("$name.php" == $file) return true;
      }
    }
    return false;
  }

  function  __toString() {
    $str = '';
    $str .= $this->rebuildloader();
    $str .= $this->rebuildhead();
    $str .= (string)$this->storyObject;
    return base64_encode($str);
  }

  protected function rebuildloader() {
    $str = "";
    $str .= $this->FileFormat;
    $str .= $this->FileVersion;
    $str .= $this->FeatureSet;
    $str .= $this->ColorTable;
    return $str;
  }

  protected function rebuildhead() {
    $str = "";
    foreach ($this->DefineCharStyle as $DefineCharStyle) {
      $str .= $DefineCharStyle;
    }
    foreach ($this->DefineKinsokuStyle as $DefineKinsokuStyle) {
      if (empty($DefineKinsokuStyle))
	continue;
      $str .= $DefineKinsokuStyle;
    }
    foreach ($this->DefineMojikumiStyle as $DefineMojikumiStyle) {
      if (empty($DefineMojikumiStyle))
	continue;
      $str .= $DefineMojikumiStyle;
    }
    foreach ($this->DefineListStyle as $DefineListStyle) {
      if (empty($DefineListStyle))
	continue;
      $str .= $DefineListStyle;
    }
    foreach ($this->DefineParaStyle as $DefineParaStyle) {
      if (empty($DefineParaStyle))
	continue;
      $str .= $DefineParaStyle;
    }
    return $str;
  }

  protected function FindAndRemove($RegEx, &$Variable) {
    if (preg_match($RegEx, $this->story, $regs)) {
      $Variable = $regs[0];
      $this->story = preg_replace($RegEx, '', $this->story);
    }
    return $Variable;
  }
  
  protected function FindAndRemoveWithOptions($RegEx, &$Variable, $Head) {
    if (preg_match($RegEx, $this->story, $regs)) {
      $Values = $regs[1];
      $NewValues = "<$Head";
      $DefineReg = '/^:([^=]+)=(.*$)/i'; //Maybe * instead of + (unsure why + was used)
      if (preg_match($DefineReg, $Values, $match)) {
	$prime = $match[1];
	$options = $match[2];
	$NewValues .= ":$prime=";
	$OptionsStr = "";
	preg_match_all('/<([^:>]+):([^>]+)>/i', $options, $result, PREG_SET_ORDER);
	foreach ($result as $V) {
	  if (!empty($V[2]) || is_numeric($V[2]))
	    $OptionsStr .="<{$V[1]}:{$V[2]}>";
	}
	$NewValues .= $OptionsStr;
      }
      if(is_array($Variable)) {
	$Variable[] = $NewValues . ">\r\n";
      }else {
	$Variable = $NewValues . ">\r\n";
      }
      $this->story = preg_replace($RegEx, '', $this->story);
      return true;
    }else {
      return false;
    }
  }

  protected function removeHeaders() {
    $this->removeFileFormat();
    $this->removeFeatureSet();
    $this->removeFileVersion();
  }

  protected function removeFeatureSet() {
    $FeatureSetReg = '/<FeatureSet:([^>]*)>/ui';
    return $this->FindAndRemove($FeatureSetReg, $this->FeatureSet);
  }

  protected function removeFileVersion() {
    $FileVersionReg = '/<Version:(\d+)>/ui';
    return $this->FindAndRemove($FileVersionReg, $this->FileVersion);
  }

  protected function removeFileFormat() {
    $FileFormatReg = '/^<([^>]+)>\r\n/ui';
    return $this->FindAndRemove($FileFormatReg, $this->FileFormat);
  }

  protected function removeDefined() {
    $this->removeColorTable();
    $this->removeDefineCharStyle();
    $this->removeDefineKinsokuStyle();
    $this->removeDefineMojikumiStyle();
    $this->removeDefineListStyle();
    $this->removeDefineParaStyle();
    //$this->story = trim($this->story); //BAD!!
  }

  protected function removeColorTable() {
    $ColorTableReg = '/^<ColorTable(.*?[^\\\\](?:>)?)>\r\n/ui';
    return $this->FindAndRemoveWithOptions($ColorTableReg, $this->ColorTable, 'ColorTable');
  }

  protected function removeDefineCharStyle() {
    $DefineCharStyleReg = '/^<DefineCharStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
    while($this->FindAndRemoveWithOptions($DefineCharStyleReg, $this->DefineCharStyle, 'DefineCharStyle'));
  }

  protected function removeDefineKinsokuStyle() {
    $DefineKinsokuStyleReg = '/^<DefineKinsokuStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
    while($this->FindAndRemoveWithOptions($DefineKinsokuStyleReg, $this->DefineKinsokuStyle, 'DefineKinsokuStyle'));
  }

  protected function removeDefineMojikumiStyle() {
    $DefineMojikumiStyleReg = '/^<DefineMojikumiStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
    while($this->FindAndRemoveWithOptions($DefineMojikumiStyleReg, $this->DefineMojikumiStyle, 'DefineMojikumiStyle'));
  }

  protected function removeDefineParaStyle() {
    $DefineParaStyleReg = '/^<DefineParaStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
    while($this->FindAndRemoveWithOptions($DefineParaStyleReg, $this->DefineParaStyle, 'DefineParaStyle'));
  }

  protected function removeDefineListStyle() {
    $DefineListStyleReg = '/^<DefineListStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
    while($this->FindAndRemoveWithOptions($DefineListStyleReg, $this->DefineListStyle, 'DefineListStyle'));
  }
  
}


/*
$Story = new nodes();

$ParaStyle = new nodes();
$ParaStyle->addStyle(new Style('ParaStyle','',false));

$part = new nodes();
$part->addStyle(new Style_cCase('UPPER'));
$part->setText('h');
$ParaStyle->addNode($part);

$part = new nodes();
$part->setText('ello world');
$ParaStyle->addNode($part);

$Story->addNode($ParaStyle);

$ParaStyle = new nodes();
$ParaStyle->addStyle(new Style('ParaStyle','',false));

$part = new nodes();
$part->addStyle(new Style_cCase('UPPER'));
$part->setText('hello');
$ParaStyle->addNode($part);

$part = new nodes();
$part->setText(' world');
$ParaStyle->addNode($part);

//Table
$tlb = new nodes();
$tlb->addStyle(new Style('table','\"basic table\"'));

//row
$row = new nodes();
$row->addStyle(new Style('row','0,1'));

//cell #1
$cell = new nodes();
$cell->addStyle(new Style('cell','1,0'));

#ParaStyle + text
$part = new nodes();
$part->addStyle(new Style('ParaStyle','',false));
$part->setText('Rich');
$cell->addNode($part);
$row->addNode($cell);

//cell #2
$cell = new nodes();
$cell->addStyle(new Style('cell','2,0'));

#ParaStyle + text
$part = new nodes();
$part->addStyle(new Style('ParaStyle','',false));
$part->setText('Rick');
$cell->addNode($part);
#$row->addNode($cell);

$tlb->addNode($row); //close row
$ParaStyle->addNode($tlb); //close table
$Story->addNode($ParaStyle); //close ParaStyle
echo $Story;
var_dump($Story);
die();
//*/
//Multi-lines
$Style = base64_decode('PFVOSUNPREUtV0lOPg0KPFZlcnNpb246Nz48RmVhdHVyZVNldDpJbkRlc2lnbi1Sb21hbj48Q29sb3JUYWJsZTo9PEJsYWNrOkNPTE9SOkNNWUs6UHJvY2VzczowLDAsMCwxPj4NCjxQYXJhU3R5bGU6PlRoZSBxdWljayA8Y0NvbG9yOkNPTE9SXDpMQUJcOlByb2Nlc3NcOjU0XCw4MFwsNjk+cmVkPGNDb2xvcjo+IGZveCBqdW1wZWQgb3Zlcgp0aGUgbGF6eSBicm93biBkb2csDQo8UGFyYVN0eWxlOj5UaGUgcXVpY2sgPGNDb2xvcjpDT0xPUlw6Q01ZS1w6UHJvY2Vzc1w6MC42M1wsMFwsMVwsMD5yZWQ8Y0NvbG9yOj4gZm94IGp1bXBlZCBvdmVyCnRoZSBsYXp5IGJyb3duIGRvZywNCjxQYXJhU3R5bGU6PlRoZSBxdWljayA8Y0NvbG9yOkNPTE9SXDpDTVlLXDpQcm9jZXNzXDowLjUxXCwwXCwwLjEyXCwwPnJlZDxjQ29sb3I6PiBmb3gganVtcGVkIG92ZXIKdGhlIGxhenkgYnJvd24gZG9nLA0KPFBhcmFTdHlsZTo+VGhlIHF1aWNrIDxjQ29sb3I6Q09MT1JcOkNNWUtcOlByb2Nlc3NcOjAuNzlcLDAuMDJcLDFcLDA+cmVkPGNDb2xvcjo+IGZveCBqdW1wZWQgb3Zlcgp0aGUgbGF6eSBicm93biBkb2csDQo8UGFyYVN0eWxlOj5UaGUgcXVpY2sgPGNDb2xvcjpDT0xPUlw6Q01ZS1w6UHJvY2Vzc1w6MC44NlwsMC43N1wsMFwsMD5yZWQ8Y0NvbG9yOj4gZm94IGp1bXBlZCBvdmVyCnRoZSBsYXp5IGJyb3duIGRvZywNCg==');
//Single-lines
$Style = base64_decode('PFVOSUNPREUtV0lOPg0KPFZlcnNpb246Nz48RmVhdHVyZVNldDpJbkRlc2lnbi1Sb21hbj48Q29sb3JUYWJsZTo9PEJsYWNrOkNPTE9SOkNNWUs6UHJvY2VzczowLDAsMCwxPj4NCjxQYXJhU3R5bGU6PlRoZSBxdWljayA8Y0NvbG9yOkNPTE9SXDpMQUJcOlByb2Nlc3NcOjU0XCw4MFwsNjk+cmVkPGNDb2xvcjo+IGZveCBqdW1wZWQgb3Zlcgp0aGUgbGF6eSBicm93biBkb2cs');

//Hello world
$Style = base64_decode('PFVOSUNPREUtV0lOPg0KPFZlcnNpb246Nz48RmVhdHVyZVNldDpJbkRlc2lnbi1Sb21hbj48Q29sb3JUYWJsZTo9PEJsYWNrOkNPTE9SOkNNWUs6UHJvY2VzczowLDAsMCwxPj4NCjxQYXJhU3R5bGU6PjxjQ2FzZTpVUFBFUj5oPGNDYXNlOj5lbGxvIHdvcmxkDQo8UGFyYVN0eWxlOj48Y0Nhc2U6VVBQRVI+aGVsbG88Y0Nhc2U6PiB3b3JsZA==');

$Style = base64_decode('PFVOSUNPREUtV0lOPg0KPFZlcnNpb246Nz48RmVhdHVyZVNldDpJbkRlc2lnbi1Sb21hbj48Q29sb3JUYWJsZTo9PEJsYWNrOkNPTE9SOkNNWUs6UHJvY2VzczowLDAsMCwxPj4NCjxQYXJhU3R5bGU6PjxjQ2FzZTpVUFBFUj5oPGNDYXNlOj5lbGxvIHdvcmxkDQo8UGFyYVN0eWxlOj48Y0Nhc2U6VVBQRVI+aGVsbG88Y0Nhc2U6PiB3b3JsZA0KPHRhYmxlOlwiYmFzaWMgdGFibGVcIj48cm93OjAsMT48Y2VsbDowLDA+PFBhcmFTdHlsZTo+Umljazxyb3c6Pjx0YWJsZTo+');

$Style = base64_decode('PFVOSUNPREUtV0lOPg0KPFZlcnNpb246Nz48RmVhdHVyZVNldDpJbkRlc2lnbi1Sb21hbj48Q29sb3JUYWJsZTo9PEJsYWNrOkNPTE9SOkNNWUs6UHJvY2VzczowLDAsMCwxPj4NCjxQYXJhU3R5bGU6PjxjQ2FzZTpVUFBFUj5oPGNDYXNlOj5lbGxvIHdvcmxkDQo8UGFyYVN0eWxlOj48Y0Nhc2U6VVBQRVI+aGVsbG88Y0Nhc2U6PiB3b3JsZA0KPHRhYmxlOlwiYmFzaWMgdGFibGVcIj48cm93OjAsMT48Y2VsbDoxLDA+PFBhcmFTdHlsZTo+UmljaDxjZWxsOj48Y2VsbDoyLDA+PFBhcmFTdHlsZTo+UmljazxjZWxsOj48cm93Oj48dGFibGU6Pg==');

$Style = base64_decode('PFVOSUNPREUtV0lOPg0KPFZlcnNpb246Nz48RmVhdHVyZVNldDpJbkRlc2lnbi1Sb21hbj48Q29sb3JUYWJsZTo9PEJsYWNrOkNPTE9SOkNNWUs6UHJvY2VzczowLDAsMCwxPj4NCjxQYXJhU3R5bGU6PmhlbGxvIHdvcmxkPHRhYmxlOlwiYmFzaWMgdGFibGVcIj48UGFyYVN0eWxlOj5SaWNoPHRhYmxlOj4=');

//table only
$Style = base64_decode('PHRhYmxlOlwiYmFzaWMgdGFibGVcIj48cm93OjAsMT48Y2VsbDoxLDA+PFBhcmFTdHlsZTo+UmljaDxjZWxsOj48Y2VsbDoyLDA+PFBhcmFTdHlsZTo+UmljazxjZWxsOj48cm93Oj48dGFibGU6Pg==');

$Story = new rebuilder($Style);
echo "$Style\n\n----\n\n".base64_decode($Story)."\n\n";
var_dump($Story);
die();
/*
$cSegNull->addParagraph('Main','ParagrNull');
$cSegNull1->addParagraph('Main','ParagrOne');

#$cSegNull->addNode($cSegNull4);
//$cSegNull->addNode($text);
#$cSegNull->addNode($cSegNull3);
#$cSegNull->addNode($cSegNull2);
#$cSegNull->addNode($cSegNull1);

#
#var_dump($cSegNull);
#$node = $test->CleanNode($cSegNull);
#echo "<br />--------------------------<br />\n";
#var_dump($cSegNull);
die($cSegNull1);

if($node){
  echo $node->getText();
}else{
  //remove it
}

#echo "$test";

die();
$test_full = new nodes();
$test = textBuild2(new nodes());
$test_full->addNode($test);

$test = textBuild2(new nodes());
$test_full->addNode($test);

$test = textBuild2(new nodes());
$test_full->addNode($test);

$test = textBuild2(new nodes());
$test_full->addNode($test);

$test = textBuild2(new nodes());
$test_full->addNode($test);

die($test_full);


$test_full->addNode(clone $test);


while($test->NextItem()){
  $node = $test->Current();
  if($node instanceof pSegments){
    $styles = $node->getStyles();
    foreach($styles as $Style){
      if($Style->getKey()=="RickID") $test->removeCurrent();
    }
  }
}
while($test->NextItem()){
  $node = $test->Current();
  if($node instanceof pSegments){
    $styles = $node->getStyles();
    foreach($styles as $Style){
      if($Style->getKey()=="XXXXID") $test->removeCurrent();
    }
  }
}
$test_full->addNode($test);

$test_full2 = new nodes();
$test_full2->addNode($test_full);
#echo "<br />\n";
echo $test_full2;
//*/
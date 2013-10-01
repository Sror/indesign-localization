<?php
require_once 'rebuilder2.php';
#TODO:controls
/**
 * Push aka MoveTo
 * Pull aka MoveFrom
 */

error_reporting(E_ALL);
$str = '/c/Server%20Documents/Output/1315306702_a_n_g_l_e.INDD/XML/Stories/PAGL-201.icml';

function inddPath2PCPath($str){
	$str = urldecode($str);
	$str = str_replace('/', '\\', substr($str, 1,1).":/".substr($str, 3));
	return $str;
}


error_reporting(E_ALL);
header("Content-Type: text/html; charset=utf-8;");
#$Story = new rebuilder('C:\Users\Richard\Desktop\test.icml');
#$Story = new rebuilderControl('C:\Users\MadTechie\Desktop\InCopy - ProtoType\tester.icml');
$Story = new rebuilderControl('C:\Users\MadTechie\Desktop\InCopy - ProtoType\charstyle-test.icml');
$paras = $Story->extractPara();
$Story->reOrder();
$p=0;$s=0;
#die($Story);
#die($paras[$p]->getSegment(1));

#$paras[$p]->getSegment($s)->setContent('New text');



foreach($paras as $para){
  #var_dump($para);
  $segs =  $para->getSegments();
  var_dump($segs);
  $seg = $segs[0];
  $new = $Story->split($seg,.5);
  $new->setContent("Testing");
  //$new->getParent()->setNewOrder($seg->getParent()->getNewOrder()+.5);
  foreach($segs as $seg){
    //echo "$seg";
    #echo "~".$seg->getContent();
    #echo "~$seg";

#$cDOM = $seg->getContentDOM();
#$CharDOM = CharDOMList::getDOMElement($cDOM->getDomID());
#$Story->split($CharDOM);

    #$seg->setContent($seg->getContent()." YOU");
    #$x = rand(1,100);
    #echo "[#".$seg->getNewOrder()."#".$seg->getContent()."#$x#]";
    #$seg->setNewOrder($x);

    #echo $seg->getContent();
  }
  #echo "\n";
}
#echo "\n<br />----------------------------";
#echo "\n<br />";
#$Story->reOrder();
die($Story);
foreach($paras as $para){
  $segs =  $para->getSegments();
  foreach($segs as $seg){
	echo $seg->getContent();
  }
  echo "\n<br />";
}

echo "\n--------------------------\n";
echo "\n--------------------------\n";
echo "\n--------------------------\n";

$paras = $Story->extractPara();
foreach($paras as $para){
  $segs =  $para->getSegments();
  foreach($segs as $seg){
        $x = rand(1,100);
	echo "[#".$seg->getNewOrder()."#".$seg->getContent()."#$x#]";
        #CharDOMList::getDOMElement($seg->getContentDOM()->getDomID())->setNewOrder($x);
        $seg->getParent()->setNewOrder($x);
  }
  echo "\n";
}
$Story->reOrder();
$paras = $Story->extractPara();
foreach($paras as $para){
  $segs =  $para->getSegments();
  foreach($segs as $seg){
	echo $seg->getContent();
  }
  echo "\n";
}


die;
#$Story->save();
#die($Story);
#var_dump($paras);
$segs = $paras[2]->getSegments();
var_dump($paras);

#$seg[1]->pull($seg[2],START|END);
#$seg[2]->push($seg[1],START|END);

//Move CharStyle
#$seg = clone $segs[0];
$seg = $segs[0];
$cDOM = $seg->getContentDOM();
$CharDOM = CharDOMList::getDOMElement($cDOM->getDomID());
$CharDOM->setNewOrder(.5);

//debug
#$XML = $CharDOM->getDOM();
#$XMLParent = $XML->parentNode->parentNode->parentNode->parentNode;
#var_dump($XMLParent->saveXML($XML));

//Add and append Contents
$seg = clone $segs[0];
$seg->setNewOrder($seg->getNewOrder()+.5);
$seg->setContent($seg->getContent()." - TWO");
echo $seg->getContent()."\n";
$paras[2]->addSegment($seg);
#$Story->setPara($paras);

$Story->ReOrderCharDOMList();
$CharDOM = CharDOMList::getDOMElements();

var_dump($CharDOM);
#var_dump($paras);
#var_dump($paras);
echo "$Story";
#var_dump($Story);
die();
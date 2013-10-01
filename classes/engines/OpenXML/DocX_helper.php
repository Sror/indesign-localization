<?php
class DocX_helper{
  protected $XML;
  function  __construct(&$XML) {
    $this->XML = $XML;
  }

  function createAttribute($Attribute, $Value){
    $attr = $this->XML->createAttribute($Attribute);
    $text = $this->XML->createTextNode($Value);
    $attr->appendChild($text);
    return $attr;
  }

  function create_rPr(){
    $rPr = $this->XML->createElement("w:rPr");
    //Fonts
    $rFonts = $this->XML->createElement("w:rFonts");
    $rFonts->appendChild($this->createAttribute('w:ascii','Arial Unicode MS'));
    $rFonts->appendChild($this->createAttribute('w:hAnsi','Arial Unicode MS'));
    $rFonts->appendChild($this->createAttribute('w:cs','Arial Unicode MS'));
    $rFonts->appendChild($this->createAttribute('w:eastAsia','Arial Unicode MS'));
    $rPr->appendChild($rFonts);

    //Colour
    $color = $this->XML->createElement("w:color");
    $color->appendChild($this->createAttribute('w:val','auto'));
    $rPr->appendChild($color);

    //spacing
    $spacing = $this->XML->createElement("w:spacing");
    $spacing->appendChild($this->createAttribute('w:val','0'));
    $rPr->appendChild($spacing);

    //position
    $position = $this->XML->createElement("w:position");
    $position->appendChild($this->createAttribute('w:val','0'));
    $rPr->appendChild($position);

    //sz
    $sz = $this->XML->createElement("w:sz");
    $sz->appendChild($this->createAttribute('w:val','22'));
    $rPr->appendChild($sz);

    //shd
    $shd = $this->XML->createElement("w:shd");
    $shd->appendChild($this->createAttribute('w:fill','auto')); //Std
    //$shd->appendChild($this->createAttribute('w:fill','D9D9D9')); //Org
    //$shd->appendChild($XML->createAttribute('w:fill','F2F2F2')); //Trans
    $shd->appendChild($this->createAttribute('w:val','clear'));
    $rPr->appendChild($shd);
    return $rPr;
  }

  function CreateEmptyPara(){
    //Add Paragraph
    $p = $this->XML->createElement("w:p");
    $rPr = $this->create_rPr();
    $p->appendChild($rPr);
    return $p;
  }

  function CreatePara($ParaText, $fill='auto'){
    //Add Paragraph
    $p = $this->XML->createElement("w:p");

    $pPr = $this->XML->createElement("w:pPr");
    //spacing
    $spacing = $this->XML->createElement("w:spacing");
    $spacing->appendChild($this->createAttribute('w:before','120'));
    $spacing->appendChild($this->createAttribute('w:after','0'));
    $spacing->appendChild($this->createAttribute('w:line','240'));
    #$spacing->appendChild($this->createAttribute('w:lineRule','auto'));
    $pPr->appendChild($spacing);

    //ind
    $ind = $this->XML->createElement("w:ind");
    $ind->appendChild($this->createAttribute('w:right','0'));
    $ind->appendChild($this->createAttribute('w:left','0'));
    $ind->appendChild($this->createAttribute('w:firstLine','0'));
    $pPr->appendChild($ind);

    //jc
    $jc = $this->XML->createElement("w:jc");
    $jc->appendChild($this->createAttribute('w:val','left'));
    $pPr->appendChild($jc);

    //rPr
    $rPr = $this->create_rPr();
    $rPr->getElementsByTagName('w:shd')->item(0)->setAttribute('w:fill',$fill);
    $pPr->appendChild($rPr);

    $p->appendChild($pPr);

    //r
    $r = $this->XML->createElement("w:r");
    //rPr
    $rPr = $this->create_rPr();
    $rPr->getElementsByTagName('w:shd')->item(0)->setAttribute('w:fill',$fill);
    $r->appendChild($rPr);

    //t
    $Paras = explode("\n", $ParaText);
    #var_dump($Paras);
    foreach($Paras as $ParaKey=>$Para){
      $t = $this->XML->createElement("w:t");
      $t->appendChild($this->createAttribute('xml:space','preserve'));
      $t->nodeValue = htmlspecialchars($Para);
      if($ParaKey > 0){
	$r->appendChild($this->XML->createElement("w:br"));
      }
      $r->appendChild($t);
    }

    $p->appendChild($r);
    return $p;
  }

}
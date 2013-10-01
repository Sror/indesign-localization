<?php
require_once(CLASSES."QXP_XML_DOM.php");
/**
 * Takes a quark XML file and rebuild a new XML file only using relevant information
 * Leave Groups and linked boxes untouched
 * Only rebuild RichText sections
 *
 * @example
 * $clean = new QXP_XML_Rebuilder('in.xml','out.XML');
 * $clean->rebuild_xml();
 * $clean->save_xml('out.XML');
 * @author  Richard Thompson & Rick Xu
 * @copyright 2010 StorePOINT International
 * @version 0.0.7
 * @todo
 * Table support
 * Extends database class
 * Replace paragraphs into PG tags
 * Replace image contents with IG tags
 */
class QXP_XML_Rebuilder {
	private $xml_in;
	private $xml_out;
	private $xpath;
	private $richtext_attributs = array(
		"CHARSTYLE",
		"PLAIN",
		"MERGE",
		"BOLD",
		"ITALIC",
		"FONT",
		"SIZE",
		"COLOR",
		"SHADE",
		"OPACITY",
		"UNDERLINE",
		"WORDUNDERLINE",
		"SMALLCAPS",
		"ALLCAPS",
		"SUPERSCRIPT",
		"SUBSCRIPT",
		"SUPERIOR",
		"OUTLINE",
		"SHADOW",
		"STRIKETHRU",
		"BASELINESHIFT",
		"HORIZONTALSCALE",
		"VERTICALSCALE",
		#"TRACKAMOUNT"
		#"KERNAMOUNT",
		#"LIGATURES",
		#"OT_STANDARD_LIGATURES",
		#"OT_DISCRETIONARY_LIGATURES",
		#"OT_ORDINALS",
		#"OT_TITLING_ALTERNATES",
		#"OT_ALL_SMALL_CAPS",
		#"OT_FRACTIONS",
		#"OT_SWASHES",
		#"OT_SMALL_CAPS",
		#"OT_CONTEXTUAL_ALTERNATIVES",
		#"OT_TABULAR_FIGURES",
		#"OT_PROPORTIONAL_FIGURES",
		#"OT_LINING_FIGURES",
		#"OT_NONE",
		#"OT_SUPERSCRIPT",
		#"OT_SUBSCRIPT",
		#"OT_NUMERATOR",
		#"OT_DENOMINATOR",
		#"OT_OLDSTYLE_FIGURES",
		#"LANGUAGE",
		/*
		* new in QXPS8
		*/
		"MISSINGFONT",
		"PSFONTNAME",
		#"NONBREAKING",
		#"EMPHASISMARK",
		#"OT_SCIENTIFIC_INFERIOR_FEATURE",
		#"OT_ITALICS_FEATURE",
		#"OT_HVKANA_ALTERNATES",
		#"OT_RUBINOTATION_FORMS",
		#"OT_LOCALIZED_FORMS",
		#"OT_ALTERNATE_WIDTHS_NONE",
		#"OT_FULL_WIDTHS",
		#"OT_HALF_WIDTHS",
		#"OT_THIRD_WIDTHS",
		#"OT_QUARTER_WIDTHS",
		#"OT_PROPORTIONAL_WIDTHS",
		#"OT_ALTVERTMETRICS",
		#"OT_PROPORTIONAL_ALTVERTMETRICS",
		#"OT_ALTERNATE_HALF_METRICS",
		#"OT_ALTERNATE_FORMS_NONE",
		#"SENDING",
		#"APPLYSENDINGTONONCJK",
		#"UEGGLYPHID",
		#"OTVARIANT",
		#"OTFEATURE",
		#"SCRIPT",
		#"HALFWIDTHUPRIGHT",
	);

	/**
	 * Load QXP XML
	 *
	 * @param string $file_in
	 * @param string $file_out
	 */
	public function LoadXML($xml_in, $file_out=null){
		$valid = false;
		if($xml_in instanceof DOMDocument){
			$this->xml_in = $xml_in;
			$valid = true;
		}elseif(is_string($xml_in)){
			$this->xml_in = new DomDocument('1.0','UTF-8');
			if(file_exists($xml_in)){
				$valid = $this->xml_in->load($xml_in);
			}else{
				$valid = $this->xml_in->loadXML($xml_in);
			}
		}
		if($valid === false) return false;
		$this->xml_out = new QXPDOMDocument('1.0','UTF-8');
		$this->xml_out->xmlStandalone = false;
		if(!is_null($file_out)){
			$this->rebuild_xml();
			$this->save_xml($file_out);
		}
		return $valid;
	}

	private function CleanPara($str) {
		$replace = array(
			"\n" => "&softReturn;",
			"&" => "&amp;",
			);
		$str = str_replace(array_keys($replace),array_values($replace),$str);
		return $str;
	}

	public function getXML(){
		return $this->xml_in;
	}
	private function compareArrays($arr1, $arr2){
		if(isset($arr1['IGNORE']) || isset($arr2['IGNORE'])) return true;
		if(count($arr1) != count($arr2)) return false;
		foreach($arr1 as $key1 => $val1){
			if($key1 == 'nodeValue') continue;
			if(!(isset($arr2[$key1]) && $arr2[$key1] == $val1)){
				return false;
			}
		}
		return true;
	}

	/**
	 * Merge RICHTEXT
	 *
	 * @return DOMDocument
	 */
	public function Merge(DOMDocument $xml){
		$this->xpath = new DOMXPath($xml);
		$contents_box = $this->xpath->query('//PROJECT/LAYOUT/SPREAD/BOX');
		$contents_table = $this->xpath->query('//PROJECT/LAYOUT/SPREAD/TABLE/ROW/CELL');

		$array = array($contents_box,$contents_table);
		foreach($array as $contents) {

			foreach($contents as $box){
				$Texts = $box->getElementsByTagName('TEXT');
				if($Texts->length==0) continue;
				$Story = $Texts->item(0)->getElementsByTagName('STORY');
				if($Story->length==0) continue;
				$Paragraphs = $Story->item(0)->getElementsByTagName('PARAGRAPH');
				foreach($Paragraphs as $Paragraph){
					$RichTexts = $Paragraph->getElementsByTagName('RICHTEXT');
					$FirstRun = true;
					for($n=0;$n<$RichTexts->length;$n++){
						$RichText = $RichTexts->item($n);
					//foreach($RichTexts as $RichText){
						$Details = $this->getAttributes($RichText);
						if($FirstRun){
							$FirstRun = false;
						}else{
							$isSame = $this->compareArrays($PreviousDetails,$Details);
							if($isSame){
								//setnodeValue
								$PreviousNode->nodeValue = $this->CleanPara($PreviousNode->nodeValue).$this->CleanPara($Details['nodeValue']);

								//remove current
								$Paragraph->removeChild($RichText);
								$n--;

								continue;
							}
						}
						if(isset($Details['IGNORE'])) unset($Details['IGNORE']);
						$PreviousDetails = $Details;
						$PreviousNode = $RichText;
					}
				}
			}
		}

		return $xml;
	}

	/**
	 * Rebuild RichText section with primary attributes
	 * Diabled attributes might be useful
	 * Enable after testing if needed
	 *
	 * @param DomElement $RichText
	 * @return DomElement
	 */
	private function rebuild_richtext($RichText){
		$outRichText = $this->xml_out->createElement('RICHTEXT');
		$KeepAttrs = $this->richtext_attributs;

		foreach($KeepAttrs as $KeepAttr){
			if($RichText->hasAttribute($KeepAttr)){
				$outRichText->setAttribute($KeepAttr,$RichText->getAttribute($KeepAttr));
			}
		}
		$outRichText->nodeValue = $this->CleanPara($RichText->nodeValue);
		return $outRichText;
	}


	private function getAttributes(DOMElement $RichText){
		$ret = array('nodeValue' => $RichText->nodeValue);
		$KeepAttrs = $this->richtext_attributs;
		foreach($KeepAttrs as $KeepAttr){
			if($RichText->hasAttribute($KeepAttr)){
				$ret[$KeepAttr] = $RichText->getAttribute($KeepAttr);
			}
		}
		$str = trim((string)$RichText->nodeValue);
		$ignore_chars = array("&softReturn;");
		if(empty($str) || in_array($str,$ignore_chars)) $ret['IGNORE'] = true;
		return $ret;
	}

	/**
	 * Take the xml DomDocument and rebuild it
	 * Return new xml DomDocument
	 *
	 * @return DomDocument
	 */
	function rebuild_xml(){
		//could potentially reduce the number of RICHTEXT
		#$this->xml_in = $this->Merge($this->xml_in);

		$Project = $this->xml_in->getElementsByTagName('PROJECT')->item(0);

		$outProject = $this->xml_out->createElement('PROJECT');
		$outProject->setAttribute('JOBJACKET',$Project->getAttribute('JOBJACKET'));
		$outProject->setAttribute('JOBTICKET',$Project->getAttribute('JOBTICKET'));
		$outProject->setAttribute('PROJECTNAME',$Project->getAttribute('PROJECTNAME'));
		$outProject->setAttribute('XMLVERSION',$Project->getAttribute('XMLVERSION'));

		$Layout = $this->xml_in->getElementsByTagName('PROJECT')->item(0)->getElementsByTagName('LAYOUT')->item(0);
		$LayoutID = $Layout->getElementsByTagName('ID')->item(0);
		$outLayout = $this->xml_out->createElement('LAYOUT');
		$outLayoutID = $this->xml_out->createElement('ID');
		$outLayoutID->setAttribute('NAME',$LayoutID->getAttribute('NAME'));
		$outLayoutID->setAttribute('UID',$LayoutID->getAttribute('UID'));
		$outLayout->appendChild($outLayoutID);

		$Layers = $Layout->getElementsByTagName('LAYER');
		$invalid_layers = array();
		foreach($Layers as $Layer) {
			$LayerLocked = $Layer->getAttribute('LOCKED');
			$LayerVisible = $Layer->getAttribute('VISIBLE');
			$LayerName = $Layer->getElementsByTagName('ID')->item(0)->getAttribute('NAME');
			$LayerUID = $Layer->getElementsByTagName('ID')->item(0)->getAttribute('UID');
			$LayerRed = $Layer->getElementsByTagName('RGBCOLOR')->item(0)->getAttribute('RED');
			$LayerGreen = $Layer->getElementsByTagName('RGBCOLOR')->item(0)->getAttribute('GREEN');
			$LayerBlue = $Layer->getElementsByTagName('RGBCOLOR')->item(0)->getAttribute('BLUE');
			if($LayerLocked=="true" || $LayerVisible=="false") $invalid_layers[] = $LayerName;
			$outLayer = $this->xml_out->createElement('LAYER');
			$outLayer->setAttribute('LOCKED',$LayerLocked);
			$outLayer->setAttribute('VISIBLE',$LayerVisible);
			$outLayerID = $this->xml_out->createElement('ID');
			$outLayerID->setAttribute('NAME',$LayerName);
			$outLayerID->setAttribute('UID',$LayerUID);
			$outLayer->appendChild($outLayerID);
			$outLayerRGBCOLOR = $this->xml_out->createElement('RGBCOLOR');
			$outLayerRGBCOLOR->setAttribute('RED',$LayerRed);
			$outLayerRGBCOLOR->setAttribute('GREEN',$LayerGreen);
			$outLayerRGBCOLOR->setAttribute('BLUE',$LayerBlue);
			$outLayer->appendChild($outLayerRGBCOLOR);
			$outLayout->appendChild($outLayer);
		}

		$Spreads = $Layout->getElementsByTagName('SPREAD');
		foreach($Spreads as $Spread) {
			$SpreadID = $Spread->getElementsByTagName('ID')->item(0)->getAttribute('UID');
			$outSpread = $this->xml_out->createElement('SPREAD');
			$outSpreadID = $this->xml_out->createElement('ID');
			$outSpreadID->setAttribute('UID',$SpreadID);
			$outSpread->appendChild($outSpreadID);

			$Pages = $Spread->getElementsByTagName('PAGE');
			foreach($Pages as $Page) {
				$outPage = $this->xml_out->createElement('PAGE');
				$outPage->setAttribute('MASTER',$Page->getAttribute('MASTER'));
				$outPage->setAttribute('POSITION',$Page->getAttribute('POSITION'));
				$outPageID = $this->xml_out->createElement('ID');
				$outPageID->setAttribute('UID',$Page->getElementsByTagName('ID')->item(0)->getAttribute('UID'));
				$outPage->appendChild($outPageID);
				$outSpread->appendChild($outPage);
			}

			$Boxes = $Spread->getElementsByTagName('BOX');
			foreach($Boxes as $Box) {
				$BoxID = $Box->getElementsByTagName('ID')->item(0)->getAttribute('UID');
				$outBox = $this->xml_out->createElement('BOX');
				$outBox->setAttribute('BLENDSTYLE',$Box->getAttribute('BLENDSTYLE'));
				$outBox->setAttribute('BOXTYPE',$Box->getAttribute('BOXTYPE'));
				$outBox->setAttribute('COLOR',$Box->getAttribute('COLOR'));
				$outBoxID = $this->xml_out->createElement('ID');
				$outBoxID->setAttribute('UID',$BoxID);
				$outBox->appendChild($outBoxID);
				//GEOMETRY
				$GeoOut = $this->GeometryParsa($Box, $outBox, $invalid_layers);
				//FRAME
				$Frames = $Box->getElementsByTagName('FRAME');
				if($Frames->length == 1) {
					$Frame = $Frames->item(0);
					$outFrame = $this->xml_out->createElement('FRAME');
					$outFrame->setAttribute('COLOR',$Frame->getAttribute('COLOR'));
					$outFrame->setAttribute('GAPCOLOR',$Frame->getAttribute('GAPCOLOR'));
					$outFrame->setAttribute('GAPOPACITY',$Frame->getAttribute('GAPOPACITY'));
					$outFrame->setAttribute('GAPSHADE',$Frame->getAttribute('GAPSHADE'));
					$outFrame->setAttribute('OPACITY',$Frame->getAttribute('OPACITY'));
					$outFrame->setAttribute('SHADE',$Frame->getAttribute('SHADE'));
					$outFrame->setAttribute('STYLE',$Frame->getAttribute('STYLE'));
					$outFrame->setAttribute('WIDTH',$Frame->getAttribute('WIDTH'));
					$outBox->appendChild($outFrame);
				}
				if($GeoOut instanceof DOMElement){
					$outBox = $GeoOut;
				}else{
					continue; //Don't run TextElementParsa
				}
				unset($GeoOut);
				$outBox = $this->TextElementParsa($Box,$outBox);
				if($outBox instanceof DOMElement) $outSpread->appendChild($outBox);
			}

			$Tables = $Spread->getElementsByTagName('TABLE');
			foreach($Tables as $Table) {
				$outTable = $this->xml_out->createElement('TABLE');
				$KeepAttrs = array(
									"OPERATION",
									"COLUMNS",
									"ROWS",
									"MAINTAINGEOMETRY",
									"COLOR",
									"SHADE",
									"OPACITY",
									"ANCHOREDIN"
									);
				foreach($KeepAttrs as $KeepAttr){
					if($Table->hasAttribute($KeepAttr)){
						$outTable->setAttribute($KeepAttr,$Table->getAttribute($KeepAttr));
					}
				}
				$outTableID = $this->xml_out->createElement('ID');
				$outTableID->setAttribute('UID',$Table->getElementsByTagName('ID')->item(0)->getAttribute('UID'));
				$outTable->appendChild($outTableID);

				$Rows = $Table->getElementsByTagName('ROW');
				foreach($Rows as $Row) {
					$outRow = $this->xml_out->createElement('ROW');
					$KeepAttrs = array(
										"ROWCOUNT",
										"ROWHEIGHT",
										"COLOR",
										"SHADE",
										"OPACITY"
										);
					foreach($KeepAttrs as $KeepAttr){
						if($Row->hasAttribute($KeepAttr)){
							$outRow->setAttribute($KeepAttr,$Row->getAttribute($KeepAttr));
						}
					}

					$Cells = $Row->getElementsByTagName('CELL');
					foreach($Cells as $Cell) {
						$outCell = $this->xml_out->createElement('CELL');
						$KeepAttrs = array(
											"COLUMNCOUNT",
											"BOXTYPE",
											"COLOR",
											"SHADE",
											"OPACITY"
											);
						foreach($KeepAttrs as $KeepAttr){
							if($Cell->hasAttribute($KeepAttr)){
								$outCell->setAttribute($KeepAttr,$Cell->getAttribute($KeepAttr));
							}
						}

						$outCell = $this->TextElementParsa($Cell,$outCell);
						if($outCell instanceof DOMElement) $outRow->appendChild($outCell);
					}
					$outTable->appendChild($outRow);
				}

				$GeoOut = $this->GeometryParsa($Table, $outTable, $invalid_layers);
				if($GeoOut instanceof DOMElement){
					$outTable = $GeoOut;
				}else{
					continue; //Don't run TextElementParsa
				}
				unset($GeoOut);

				//COLSPEC
				$ColSpecs = $Table->getElementsByTagName('COLSPEC');
				if($ColSpecs->length > 0) {
					$ColSpec = $ColSpecs->item(0);
					$outColSpec = $this->xml_out->createElement('COLSPEC');
					$TableColumns = $ColSpec->getElementsByTagName('COLUMN');
					foreach($TableColumns as $TableColumn) {
						$outTableColumn = $this->xml_out->createElement('COLUMN');
						$KeepAttrs = array(
											"COLUMNCOUNT",
											"COLUMNWIDTH",
											"COLOR",
											"SHADE",
											"OPACITY"
											);
						foreach($KeepAttrs as $KeepAttr){
							if($TableColumn->hasAttribute($KeepAttr)){
								$outTableColumn->setAttribute($KeepAttr,$TableColumn->getAttribute($KeepAttr));
							}
						}
						$outColSpec->appendChild($outTableColumn);
					}
					$outTable->appendChild($outColSpec);
				}
				$outSpread->appendChild($outTable);
			}

			$outLayout->appendChild($outSpread);
		}
		$outProject->appendChild($outLayout);
		$this->xml_out->appendChild($outProject);
		$this->xml_out = $this->Merge($this->xml_out);
		return $this->xml_out;
	}

	/**
	 * Save xml DomDocument into file
	 *
	 * @param string $file
	 */
	public function save_xml($file){
		$fh = fopen($file,"w");
		$data = "\xEF\xBB\xBF";
		$data .= $this->xml_out->saveXML();
		fwrite($fh,$data);
		fclose($fh);
		#$this->xml_out->save($file);
	}

	private function GeometryParsa(DomElement $Box, DomElement $outBox, array $invalid_layers) {
		//GEOMETRY
		$BoxGeo = $Box->getElementsByTagName('GEOMETRY')->item(0);

		//Remove GEOMETRY with * in PAGE Attribute or items on locked or invisible layers
		if(!is_numeric($BoxGeo->getAttribute('PAGE')) || in_array($BoxGeo->getAttribute('LAYER'),$invalid_layers)) return false;

		$outBoxGeo = $this->xml_out->createElement('GEOMETRY');

		$BoxPos = $BoxGeo->getElementsByTagName('POSITION')->item(0);
		$outBoxPos = $this->xml_out->createElement('POSITION');
		//TOP
		$BoxTop = $BoxPos->getElementsByTagName('TOP')->item(0);
		$outBoxTop = $this->xml_out->createElement('TOP');
		$outBoxTop->nodeValue = $BoxTop->nodeValue;
		$outBoxPos->appendChild($outBoxTop);
		//LEFT
		$BoxLeft = $BoxPos->getElementsByTagName('LEFT')->item(0);
		$outBoxLeft = $this->xml_out->createElement('LEFT');
		$outBoxLeft->nodeValue = $BoxLeft->nodeValue;
		$outBoxPos->appendChild($outBoxLeft);
		//BOTTOM
		$BoxBottom = $BoxPos->getElementsByTagName('BOTTOM')->item(0);
		$outBoxBottom = $this->xml_out->createElement('BOTTOM');
		$outBoxBottom->nodeValue = $BoxBottom->nodeValue;
		$outBoxPos->appendChild($outBoxBottom);
		//RIGHT
		$BoxRight = $BoxPos->getElementsByTagName('RIGHT')->item(0);
		$outBoxRight = $this->xml_out->createElement('RIGHT');
		$outBoxRight->nodeValue = $BoxRight->nodeValue;
		$outBoxPos->appendChild($outBoxRight);

		$outBoxGeo->setAttribute('CORNERRADIUS',$BoxGeo->getAttribute('CORNERRADIUS'));
		$outBoxGeo->setAttribute('LAYER',$BoxGeo->getAttribute('LAYER'));
		$outBoxGeo->setAttribute('PAGE',$BoxGeo->getAttribute('PAGE'));
		$outBoxGeo->setAttribute('SHAPE',$BoxGeo->getAttribute('SHAPE'));
		$BoxAngle = $BoxGeo->getAttribute('ANGLE');
		if(!empty($BoxAngle)) $outBoxGeo->setAttribute('ANGLE',$BoxAngle);

		$outBoxGeo->appendChild($outBoxPos);

		//SPLINESHAPE
		$SplineShapes = $Box->getElementsByTagName('SPLINESHAPE');
		if($SplineShapes->length == 1) {
			$SplineShape = $SplineShapes->item(0);
			$outSplineShape = $this->xml_out->createElement('SPLINESHAPE');
			$KeepAttrs = array(
				"RECTSHAPE",
				"INVERTEDSHAPE",
				"HASSPLINES",
				"HASHOLES",
				"NEWFORMAT",
				"MORETHANONETOPLEVELCONTOUR",
				"CLOSEDSHAPE",
				"WELLFORMED",
				"TAGSALLOCATED",
				"INCOMPLETE",
				"VERTSELECTED"
			);
			foreach($KeepAttrs as $KeepAttr){
				if($SplineShape->hasAttribute($KeepAttr)){
					$outSplineShape->setAttribute($KeepAttr,$SplineShape->getAttribute($KeepAttr));
				}
			}
			//CONTOURS
			$Contours = $SplineShape->getElementsByTagName('CONTOURS');
			if($Contours->length == 1) {
				$Contours = $Contours->item(0);
				$outContours = $this->xml_out->createElement('CONTOURS');
				$Contours = $Contours->getElementsByTagName('CONTOUR');
				//CONTOUR
				foreach($Contours as $Contour) {
					$outContour = $this->xml_out->createElement('CONTOUR');
					$KeepAttrs = array(
						"CURVEDEDGES",
						"RECTCONTOUR",
						"INVERTEDCONTOUR",
						"TOPLEVEL",
						"SELFINTERSECTED",
						"POLYCONTOUR",
						"VERTEXTAGEXISTS"
					);
					foreach($KeepAttrs as $KeepAttr){
						if($Contour->hasAttribute($KeepAttr)){
							$outContour->setAttribute($KeepAttr,$Contour->getAttribute($KeepAttr));
						}
					}
					//VERTICES
					$Vertices = $Contour->getElementsByTagName('VERTICES');
					if($Vertices->length == 1) {
						$Vertices = $Vertices->item(0);
						$outVertices = $this->xml_out->createElement('VERTICES');
						$Vertexs = $Vertices->getElementsByTagName('VERTEX');
						foreach($Vertexs as $Vertex) {
							$outVertex = $this->xml_out->createElement('VERTEX');
							$KeepAttrs = array(
								"SMOOTHVERTEX",
								"STRAIGHTEDGE",
								"SYMMVERTEX",
								"CUSPVERTEX",
								"TWISTED",
								"VERTEXSELECTED"
							);
							foreach($KeepAttrs as $KeepAttr){
								if($Vertex->hasAttribute($KeepAttr)){
									$outVertex->setAttribute($KeepAttr,$Vertex->getAttribute($KeepAttr));
								}
							}
							//LEFTCONTROLPOINT
							$LeftVertexPoints = $Vertex->getElementsByTagName('LEFTCONTROLPOINT');
							if($LeftVertexPoints->length == 1) {
								$LeftVertexPoint = $LeftVertexPoints->item(0);
								$outLeftVertexPoint = $this->xml_out->createElement('LEFTCONTROLPOINT');
								$outLeftVertexPoint->setAttribute('X',$LeftVertexPoint->getAttribute('X'));
								$outLeftVertexPoint->setAttribute('Y',$LeftVertexPoint->getAttribute('Y'));
								$outVertex->appendChild($outLeftVertexPoint);
							}
							//VERTEXPOINT
							$VertexPoints = $Vertex->getElementsByTagName('VERTEXPOINT');
							if($VertexPoints->length == 1) {
								$VertexPoint = $VertexPoints->item(0);
								$outVertexPoint = $this->xml_out->createElement('VERTEXPOINT');
								$outVertexPoint->setAttribute('X',$VertexPoint->getAttribute('X'));
								$outVertexPoint->setAttribute('Y',$VertexPoint->getAttribute('Y'));
								$outVertexPoint->setAttribute('TAG',$VertexPoint->getAttribute('TAG'));
								$outVertex->appendChild($outVertexPoint);
							}
							//RIGHTCONTROLPOINT
							$RightVertexPoints = $Vertex->getElementsByTagName('RIGHTCONTROLPOINT');
							if($RightVertexPoints->length == 1) {
								$RightVertexPoint = $RightVertexPoints->item(0);
								$outRightVertexPoint = $this->xml_out->createElement('RIGHTCONTROLPOINT');
								$outRightVertexPoint->setAttribute('X',$RightVertexPoint->getAttribute('X'));
								$outRightVertexPoint->setAttribute('Y',$RightVertexPoint->getAttribute('Y'));
								$outVertex->appendChild($outRightVertexPoint);
							}
							$outVertices->appendChild($outVertex);
						}
						$outContour->appendChild($outVertices);
					}
					$outContours->appendChild($outContour);
				}
				$outSplineShape->appendChild($outContours);
			}
			$outBoxGeo->appendChild($outSplineShape);
		}

		//SUPPRESSOUTPUT
		$BoxSups = $Box->getElementsByTagName('SUPPRESSOUTPUT');
		if($BoxSups->length == 1) {
			$BoxSup = $BoxSups->item(0);
			$outBoxSup = $this->xml_out->createElement('SUPPRESSOUTPUT');
			$outBoxSup->nodeValue = $BoxSup->nodeValue;
			$outBoxGeo->appendChild($outBoxSup);
		}

		//RUNAROUND
		$BoxRuns = $Box->getElementsByTagName('RUNAROUND');
		if($BoxRuns->length == 1) {
			$BoxRun = $BoxRuns->item(0);
			$outBoxRun = $this->xml_out->createElement('RUNAROUND');
			$outBoxRun->setAttribute('TYPE',$BoxRun->getAttribute('TYPE'));
			$outBoxGeo->appendChild($outBoxRun);
		}

		$outBox->appendChild($outBoxGeo);
		return $outBox;
	}

	private function TextElementParsa(DOMElement $Box, DOMElement $outBox){
		//Validate box usage (on page)
		$valid = false;
		//Loop TEXT
		$Texts = $Box->getElementsByTagName('TEXT');
		//Removed Empty Boxes
		if($Texts->length > 0) $valid = true;
		$Overmatters = $Box->getElementsByTagName('OVERMATTER');
		foreach($Texts as $Text) {
			$outText = $this->xml_out->createElement('TEXT');
			//LOOP STORY
			$Stories = $Text->getElementsByTagName('STORY');
			foreach($Stories as $Story) {
				$outStory = $this->xml_out->createElement('STORY');
				$outStory->setAttribute('STORYDIRECTION',$Story->getAttribute('STORYDIRECTION'));
				$outStory->setAttribute('CLEAROLDTEXT','true');
				//LINKEDBOX
				$Linkedboxes = $Story->getElementsByTagName('LINKEDBOX');
				if($Linkedboxes->length == 1) {
					$Linkedbox = $Linkedboxes->item(0);
					$outLinkedbox = $this->xml_out->createElement('LINKEDBOX');
					$outLinkedbox->setAttribute('STARTOFFSET',$Linkedbox->getAttribute('STARTOFFSET'));
					$outLinkedbox->setAttribute('ENDOFFSET',$Linkedbox->getAttribute('ENDOFFSET'));
					$LinkedboxID = $Linkedbox->getElementsByTagName('ID')->item(0);
					$outLinkedboxID = $this->xml_out->createElement('ID');
					$outLinkedboxID->setAttribute('NAME',$LinkedboxID->getAttribute('NAME'));
					$outLinkedboxID->setAttribute('UID',$LinkedboxID->getAttribute('UID'));
					$outLinkedbox->appendChild($outLinkedboxID);
					$outStory->appendChild($outLinkedbox);
				}
				//LOOP PARAGRAPH
				$Paras = $Story->getElementsByTagName('PARAGRAPH');
				$ParasLength = $Paras->length;
				foreach($Paras as $Key => $Para) {
					if($Overmatters->length == 1) {
						$Overmatter = $Overmatters->item(0);
						if($Para->parentNode === $Overmatter) continue;
					}
					$outPara = $this->xml_out->createElement('PARAGRAPH');
					$KeepAttrs = array(
										"PARASTYLE",
										"MERGE"
										);
					foreach($KeepAttrs as $KeepAttr){
						if($Para->hasAttribute($KeepAttr)){
							$outPara->setAttribute($KeepAttr,$Para->getAttribute($KeepAttr));
						}
					}
					//FORMAT
					$Format = $Para->getElementsByTagName('FORMAT');
					if($Format->length) {
						$Format = $Format->item(0);
						$outFormat = $this->xml_out->createElement('FORMAT');
						$KeepAttrs = array(
											"SPACEBEFORE",
											"SPACEAFTER",
											"LEFTINDENT",
											"RIGHTINDENT",
											"FIRSTLINE",
											#"LEADING", // for some textbox QXPS returns Error Code: 10109 - sub-renderer could not process the request.
											"ALIGNMENT",
											"LOCKTOGRID",
											"HANDJ",
											"KEEPWITHNEXT"
											);
						foreach($KeepAttrs as $KeepAttr){
							if($Format->hasAttribute($KeepAttr)){
								$outFormat->setAttribute($KeepAttr,$Format->getAttribute($KeepAttr));
							}
						}
						$outPara->appendChild($outFormat);
					}
					if($Para->parentNode === $Story) {
						//TABSPEC
						$TabSpecs = $Para->getElementsByTagName('TABSPEC');
						if($TabSpecs->length == 1) {
							$outTabSpec = $this->xml_out->createElement('TABSPEC');
							$TabSpec = $TabSpecs->item(0);
							$Tabs = $TabSpec->getElementsByTagName('TAB');
							$counter = 0;
							foreach($Tabs as $Tab) {
								if($this->validate_tabspec($Box,$Tab)) {
									$counter++;
									$outTab= $this->xml_out->createElement('TAB');
									$KeepAttrs = array(
														"POSITION",
														"FILL",
														"ALIGNMENT",
														"ALIGNON"
														);
									foreach($KeepAttrs as $KeepAttr){
										if($Tab->hasAttribute($KeepAttr)){
											$outTab->setAttribute($KeepAttr,$Tab->getAttribute($KeepAttr));
										}
									}
									$outTabSpec->appendChild($outTab);
								}
							}
							if($counter) $outPara->appendChild($outTabSpec);
						}

						//RULE
						$Rules = $Para->getElementsByTagName('RULE');
						if($Rules->length == 1) {
							$Rule = $Rules->item(0);
							$outRule = $this->xml_out->createElement('RULE');
							$outRule->setAttribute('ENABLED',$Rule->getAttribute('ENABLED'));
							$outRule->setAttribute('OFFSET',$Rule->getAttribute('OFFSET'));
							$outRule->setAttribute('POSITION',$Rule->getAttribute('POSITION'));
							$outRule->setAttribute('SHADE',$Rule->getAttribute('SHADE'));
							$outRule->setAttribute('WIDTH',$Rule->getAttribute('WIDTH'));
							$outPara->appendChild($outRule);
						}

						//LOOP RICHTEXT
						$RichTexts = $Para->getElementsByTagName('RICHTEXT');
						foreach($RichTexts as $RichText) {
							if(empty($RichText->nodeValue)) continue;
							$outRichText = $this->rebuild_richtext($RichText);
							$outPara->appendChild($outRichText);
						}
					}
					if($Key != ($ParasLength - 1)) {
						$outStory->appendChild($outPara);
					}
				}

				if(!isset($outPara)){
					$outPara = $this->xml_out->createElement('PARAGRAPH');
				}
				//OVERMATTER
				if($Overmatters->length == 1) {
					$Overmatter = $Overmatters->item(0);
					//OVERMATTER RICHTEXT
					$RichTexts = $Overmatter->getElementsByTagName('RICHTEXT');
					foreach($RichTexts as $RichText){
						if($RichText->parentNode === $Overmatter){
							if(empty($RichText->nodeValue)) continue;
							$outRichText = $this->rebuild_richtext($RichText);
							$outPara->appendChild($outRichText);
						}
					}
					$outStory->appendChild($outPara);
					//OVERMATTER PARAGRAPH
					$OverParas = $Overmatter->getElementsByTagName('PARAGRAPH');
					foreach($OverParas as $OverPara){
						$outPara= $this->xml_out->createElement('PARAGRAPH');
						$RichTexts = $OverPara->getElementsByTagName('RICHTEXT');
						foreach($RichTexts as $RichText){
							if(empty($RichText->nodeValue)) continue;
							$outRichText = $this->rebuild_richtext($RichText);
							$outPara->appendChild($outRichText);
						}
						$outStory->appendChild($outPara);
					}
				} else {
					$outStory->appendChild($outPara);
				}
				$outText->appendChild($outStory);
			}
			$outBox->appendChild($outText);
		}

		//Loop PICTURE
		$Picts = $Box->getElementsByTagName('PICTURE');
		if($Picts->length == 1) {
			//Empty Pictures are INVALID
			$valid = true;
			$Pict = $Picts->item(0);
			$outPict = $this->xml_out->createElement('PICTURE');
			$KeepAttrs = array(
				"FIT",
				"SCALEACROSS",
				"SCALEDOWN",
				#"OFFSETACROSS",
				#"OFFSETDOWN",
				"ANGLE",
				"SKEW",
				"PICCOLOR",
				"SHADE",
				"OPACITY",
				"FLIPVERTICAL",
				"FLIPHORIZONTAL",
				"SUPRESSPICT",
				"FULLRES",
				"MASK"
			);
			foreach($KeepAttrs as $KeepAttr){
				if($Pict->hasAttribute($KeepAttr)){
					$outPict->setAttribute($KeepAttr,$Pict->getAttribute($KeepAttr));
				}
			}
			//CLIPPING
			$clippings = $Box->getElementsByTagName('CLIPPING');
			if($clippings->length == 1) {
				$clipping = $clippings->item(0);
				$outClipping = $this->xml_out->createElement('CLIPPING');
				$KeepAttrs = array(
					#"TYPE", //TYPE default is ITEM Quark doesn't like empty EMBEDDEDPATH
					"TOP",
					"RIGHT",
					"LEFT",
					"BOTTOM",
					"PATHNAME",
					"OUTSET",
					"NOISE",
					"THRESHOLD",
					"SMOOTHNESS",
					"OUTSIDEONLY",
					"RESTRICTTOBOX",
					"INVERT",
					"EDITED"
				);
				foreach($KeepAttrs as $KeepAttr){
					if($clipping->hasAttribute($KeepAttr)){
						$outClipping->setAttribute($KeepAttr,$clipping->getAttribute($KeepAttr));
					}
				}
				$outPict->appendChild($outClipping);
			}
			$outBox->appendChild($outPict);
			$contents = $Box->getElementsByTagName('CONTENT');
			$outContent = $this->xml_out->createElement('CONTENT');
			if($contents->length == 0) return false;
			$outContent->nodeValue = $this->CleanPara($contents->item(0)->nodeValue);
			$outBox->appendChild($outContent);
		}
		return ($valid)?$outBox:false;
	}

	/**
	 * Check if a tab in tabspec is valid
	 *
	 * @param DomElement $Box
	 * @param DomElement $Tab
	 * @return boolean
	 */
	private function validate_tabspec($Box, $Tab) {
		$BoxPos = $Box->getElementsByTagName('GEOMETRY')->item(0);
		if(is_null($BoxPos)) return true;
		$Box_Position = $Box->getElementsByTagName('GEOMETRY')->item(0)->getElementsByTagName('POSITION')->item(0);
		$Box_Left = $Box_Position->getElementsByTagName('LEFT')->item(0)->nodeValue;
		$Box_Right = $Box_Position->getElementsByTagName('RIGHT')->item(0)->nodeValue;
		$Tab_Position = $Tab->getAttribute('POSITION');
		return !($Tab_Position > ($Box_Right - $Box_Left));
	}
}
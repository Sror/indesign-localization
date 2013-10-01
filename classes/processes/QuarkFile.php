<?php
require_once(ENGINES."Quark.php");
require_once(PROCESSES."BaseProcess.php");
require_once(CLASSES."QXP_XML_Rebuilder.php");

class QuarkProcess extends Process {
	protected $QuarkEngine;
	Protected $link;
	function __construct() {
		$this->QuarkEngine = new QuarkEngine();
		$this->link = $this->QuarkEngine->GetDBLink();
	}
	function GetPDFDefault(){
		return 0;
	}
	function GetPDFOptions() {
		return array(
			'Low Quality',
			'High Quality'
		);
	}
	function setPDFOption($opt){
		parent::setPDFOption($opt);
		$this->QuarkEngine->setPDFProfile($this->getPDFOption());
	}
}

class OriginalQuark extends QuarkProcess {
	//Original File Access
	public function UploadFile($aID, $filein) {
		if(empty($aID)) return false;
		$row = $this->get_artwork_info($aID);
		if($row === false) return false;
		$parse_type = $row['parse_type'];
		$SourceLangID = $row['sourceLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];
		
		if(!$SourceLangID) return false;
		if(!file_exists($this->QuarkEngine->GetStorage().$filein) || is_dir($this->QuarkEngine->GetStorage().$filein)) return false;
		
		$tFile = BareFilename($filein);
		#$URL = $this->QuarkEngine->GetQuarkLowPDF().urlencode($filein).$this->QuarkEngine->GetQuarkLowPDFSettings();
		//$URL = $this->QuarkEngine->GetQuarkHighPDF().urlencode($filein).$this->QuarkEngine->GetQuarkHighPDFSettings();
		$URL = $this->QuarkEngine->GetQuarkURL($filein);
		file_put_contents($this->QuarkEngine->GetStorage()."$tFile.pdf",@$this->QuarkEngine->curl_get_file_contents($URL));
		
		$URL = $this->QuarkEngine->GetQuarkXML().urlencode($filein);
		$XMLData = @$this->QuarkEngine->curl_get_file_contents($URL);
		file_put_contents("{$this->QuarkEngine->GetStorage()}{$tFile}.base.raw",$XMLData);

		//Capture Grouped Boxes in RAW file NOT needed for base file
		$XML = new DOMDocument('1.0','UTF-8');
		$loaded = $XML->loadXML($XMLData);
		if($loaded === false) return false;
		$xPath = new DOMXpath($XML);
		$GroupedBoxID = array();
		$groupedboxes = $xPath->query("//PROJECT/LAYOUT/SPREAD/GROUP/BOX/ID");
		foreach($groupedboxes as $groupedbox) {
			$GroupedBoxID[] = $groupedbox->getAttribute('UID');
		}
		unset($xPath,$XML);
		
		//Remove odd char's
		#$XMLData = $this->QuarkEngine->CleanXML($XMLData);
		$clean = new QXP_XML_Rebuilder();
		$loaded = $clean->LoadXML($XMLData);
		if($loaded === false) return false;
		$XML = $clean->rebuild_xml();
		$xPath = new DOMXpath($XML);
		
		//Capture overflowed boxIDs
		$OverFlow = array();
		$overmatters = $xPath->query("//PROJECT/LAYOUT/SPREAD/BOX/TEXT/STORY/OVERMATTER");
		foreach($overmatters as $overmatter) {
			$OverFlow[] = $overmatter->parentNode->parentNode->parentNode->getElementsByTagName('ID')->item(0)->getAttribute('UID');
		}
		
		$overmatters = $xPath->query("//PROJECT/LAYOUT/SPREAD/TABLE/ROW/CELL/TEXT/STORY/OVERMATTER");
		foreach($overmatters as $overmatter) {
			$OverFlow[] = $overmatter->parentNode->parentNode->parentNode->parentNode->parentNode->getElementsByTagName('ID')->item(0)->getAttribute('UID');
		}
		array_unique($OverFlow);
		
		//build layers
		$layers = $xPath->query("//PROJECT/LAYOUT/LAYER");
		$layers_array = array();
		foreach($layers as $layer) {
			$LayerKRA = $layer->getAttribute('KEEPRUNAROUND');
			$LayerLocked = $layer->getAttribute('LOCKED');
			$LayerSuppress = $layer->getAttribute('SUPPRESS');
			$LayerVisible = $layer->getAttribute('VISIBLE');
			$ID = $layer->getElementsByTagName('ID')->item(0);
			$LayerID = $ID->getAttribute('UID');
			$LayerName = $ID->getAttribute('NAME');
			$RGBCOLOR = $layer->getElementsByTagName('RGBCOLOR')->item(0);
			$LayerRed = (int)$RGBCOLOR->getAttribute('RED');
			$LayerGreen = (int)$RGBCOLOR->getAttribute('GREEN');
			$LayerBlue = (int)$RGBCOLOR->getAttribute('BLUE');
			$layer_colour = strtoupper(dechex($LayerRed).dechex($LayerGreen).dechex($LayerBlue));
			$layer_visible = strtolower((string)$LayerVisible)=="true" ? 1 : 0;
			$layer_locked = strtolower((string)$LayerLocked)=="true" ? 1 : 0;
			$layer_id = $this->QuarkEngine->AddLayer($aID,$LayerID,$LayerName,$layer_colour,$layer_visible,$layer_locked);
			
			$layers_array[$LayerName] = array("ID"=>$layer_id,
										"LOCKED"=>$layer_locked,
										"VISIBLE"=>$layer_visible);
		}
		$page_count = 0;
		$total_word_count = 0;
		$links = array();
		//build spreads
		$spreads = $xPath->query("//PROJECT/LAYOUT/SPREAD");
		foreach($spreads as $spread) {
			//pages
			$QPages = array();
			$pages = $spread->getElementsByTagName('PAGE');
			foreach($pages as $page) {
				$page_count++;
				$page_uid = $page->getElementsByTagName('ID')->item(0)->getAttribute('UID');
				$Preview = $this->QuarkEngine->RebuildFile($aID,0,$page_count,ROOT.$this->QuarkEngine->GetPreviewOutputPath(),"JPG");
				$PageID = $this->QuarkEngine->AddPage($aID,$page_count,$Preview,$page_uid);
				if($PageID === false) return false;
				$QPages[$page_count] = $PageID;
			}
			
			// tables
			$tables = $spread->getElementsByTagName('CELL');
			// boxes
			$boxes = $spread->getElementsByTagName('BOX');
			// combine tables and boxes
			$Elements = array("BOX" => $boxes, "CELL" => $tables);
			foreach($Elements as $K=>$Element){
				$boxes = $Element;
				foreach($boxes as $box) {
					$box_type = $this->QuarkEngine->convert_box_type($box->getAttribute('BOXTYPE'));
					switch($K){
						case "BOX":
							$box_uid = $box->getElementsByTagName('ID')->item(0)->getAttribute('UID');
							$grouped = in_array($box_uid,$GroupedBoxID) ? 1 : 0;
							$box_name= "Box$box_uid";
							$box_geo = $box->getElementsByTagName('GEOMETRY')->item(0);
							break;
						case "CELL":
							$table = $box->parentNode->parentNode;
							$box_uid = $table->getElementsByTagName('ID')->item(0)->getAttribute('UID');
							$grouped = 0;
							$cell_id = $box->getAttribute('COLUMNCOUNT');
							$row_id = $box->parentNode->getAttribute('ROWCOUNT');
							$box_name= "Box$box_uid-$row_id-$cell_id";
							$box_geo = $table->getElementsByTagName('GEOMETRY')->item(0);
							break;
					}
					$box_angle = $box_geo->getAttribute('ANGLE');
					$box_layer = $box_geo->getAttribute('LAYER');
					$box_page = $box_geo->getAttribute('PAGE');
					$box_shape = $box_geo->getAttribute('SHAPE');
					$box_pos = $box_geo->getElementsByTagName('POSITION')->item(0);
					$box_top = $box_pos->getElementsByTagName('TOP')->item(0)->nodeValue;
					$box_left = $box_pos->getElementsByTagName('LEFT')->item(0)->nodeValue;
					$box_bottom = $box_pos->getElementsByTagName('BOTTOM')->item(0)->nodeValue;
					$box_right = $box_pos->getElementsByTagName('RIGHT')->item(0)->nodeValue;

					//ignore invisible or locked boxes
					if($layers_array[$box_layer]['LOCKED'] || !$layers_array[$box_layer]['VISIBLE']) continue;
					
					//insert box to db
					$BoxID = @$this->QuarkEngine->InsertBox($box_name,$QPages[$box_page],$box_uid,$box_top,$box_left,$box_right,$box_bottom,$layers_array[$box_layer]['ID'],$box_type,$box_angle,$grouped);
					
					//check linked box
					$linkedboxes = $box->getElementsByTagName('LINKEDBOX');
					if($linkedboxes->length == 1) {
						$linkedbox = $linkedboxes->item(0);
						$linkedbox_uid = $linkedbox->getElementsByTagName('ID')->item(0)->getAttribute('UID');
						$links[$BoxID] = $linkedbox_uid;
					}

					//check overflow
					if(in_array($box_uid,$OverFlow)) {
						$this->QuarkEngine->SaveBoxOverflows($aID,$BoxID);
					}
					
					if($box_type == "TEXT") {
						$total_word_count = $this->BuildTexts($box,(int)$BoxID,$aID,$parse_type,$SourceLangID,$brandID,$subjectID,$total_word_count);
					}
					
					if($box_type == "PICT") {
						$this->BuildPicts($box,(int)$BoxID,$aID,$SourceLangID,$brandID,$subjectID);
					}
				}
			}
		}

		//update linked boxes
		foreach($links as $k=>$v) {
			$this->QuarkEngine->UpdateLinkedBoxes($aID,$k,$v);
		}

		$clean->save_xml("{$this->QuarkEngine->GetStorage()}{$tFile}.base");
		$this->QuarkEngine->UpdateQuarkArtwork($aID, array("wordCount" => $total_word_count));
		return true;
	}
	
	public function RebuildBase($aID,$filein) {
		if(empty($aID)) return false;
		$row = $this->get_artwork_info($aID);
		if($row === false) return false;
		$parse_type = $row['parse_type'];
		$SourceLangID = $row['sourceLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];
		
		if(!$SourceLangID) return false;
		if(!file_exists($this->QuarkEngine->GetStorage().$filein) || is_dir($this->QuarkEngine->GetStorage().$filein)) return false;
		
		$tFile = BareFilename($filein);
		$XMLFile = "{$this->QuarkEngine->GetStorage()}{$tFile}.base";
		//overwrite base xml with raw
		copy("$XMLFile.raw",$XMLFile);
		
		$clean = new QXP_XML_Rebuilder();
		$loaded = $clean->LoadXML($XMLFile);
		if($loaded === false) return false;
		
		//reset all the paralinks and img_links rebuilt to 0
		$this->QuarkEngine->ResetPLRebuilt($aID);
		$this->QuarkEngine->ResetILRebuilt($aID);
		
		$XML = $clean->rebuild_xml();
		$xPath = new DOMXpath($XML);
		
		$tables = $xPath->query("//PROJECT/LAYOUT/SPREAD/CELL");
		$boxes = $xPath->query("//PROJECT/LAYOUT/SPREAD/BOX");
		$Elements = array("BOX" => $boxes, "CELL" => $tables);
		$total_word_count = 0;
		foreach($Elements as $K=>$Element){
			$boxes = $Element;
			foreach($boxes as $box) {
				$box_type = $this->QuarkEngine->convert_box_type($box->getAttribute('BOXTYPE'));
				$BoxID = null;
				if($box_type == "TEXT") {
					$total_word_count = $this->BuildTexts($box,$BoxID,$aID,$parse_type,$SourceLangID,$brandID,$subjectID,$total_word_count);
				}
				if($box_type == "PICT") {
					$this->BuildPicts($box,$BoxID,$aID,$SourceLangID,$brandID,$subjectID);
				}
			}
		}
		$clean->save_xml($XMLFile);
		$this->QuarkEngine->UpdateQuarkArtwork($aID, array("wordCount" => $total_word_count));
		return true;
	}
	
	private function BuildTexts(DOMElement $box, $BoxID, $aID, $parse_type, $SourceLangID, $brandID, $subjectID, $total_word_count){
		$texts = $box->getElementsByTagName('TEXT');
		if($texts->length==0) return $total_word_count;
		$stories = $texts->item(0)->getElementsByTagName('STORY');
		if($stories->length==0) return $total_word_count;
		$paragraphs = $stories->item(0)->getElementsByTagName('PARAGRAPH');
		foreach($paragraphs as $paragraph) {
			$SG = $this->QuarkEngine->AddStoryGroup();
			$SO = 0;
			$richtexts = $paragraph->getElementsByTagName('RICHTEXT');
			$keep_text_style = TRUE;
			if($keep_text_style) {
				foreach($richtexts as $richtext) {
					$para = $richtext->nodeValue;
					$para = $this->QuarkEngine->PreParsaPara($para);
					//start to replace para with tags
					/*
					 * Handle InDesign special characters, also refer to DBParsaPara() in translator class
					 * 03 ETX / end of text / End Nested Style Here
					 * 07 BEL / bell / Indent to Here
					 * 08 BS / backspace / Right Indent Tab
					 * 09 TAB / tab / Normal Tab
					 * 10 LF
					 * 18 CAN / cancel / *
					 */
					$chars = array(
						#'\x03',
						#'\x07',
						#'\x08',
						'\x09',
						#'\x13',
					);
					if($this->getConfig('softreturn')===true) $chars[] = "\x0A";
					$regStr = implode("|",$chars);
					$regex_pattern = '/(?:'.$regStr.')/';
					if (preg_match_all($regex_pattern, $para, $obj, PREG_PATTERN_ORDER)) {
						$paras = preg_split($regex_pattern, $para);
						$obj = $obj[0];
					} else {
						$paras = array($para);
						$obj = array();
					}
					$Tags = array();
					foreach($paras as $n=>$para) {
						$Tags[$n] = "";
						if($parse_type==PARSE_BY_PARAGRAPH) {
							$SO++;
							if(!is_null($BoxID)){
								$para_row = $this->QuarkEngine->AddParagraph($para,$SourceLangID,$BoxID,$_SESSION['userID'],PARA_UPLOAD,$brandID,$subjectID,$SG);
								if($para_row === false) {
									$Tags[$n] = $para;
									$SO--;
									continue;
								}
								$total_word_count += $para_row['Words'];
								$PL = $para_row['PL'];
							} else {
								$ids = $box->getElementsByTagName('ID');
								if($ids->length==0) {
									$Tags[$n] = $para;
									$SO--;
									continue;
								}
								$BoxRef = $ids->item(0)->getAttribute('UID');
								$para_row = $this->QuarkEngine->ParaExists($para,$SourceLangID);
								if($para_row===false) {
									$Tags[$n] = $para;
									$SO--;
									continue;
								}
								$total_word_count += $para_row['Words'];
								$ParaID = $para_row['ParaID'];
								$PL = $this->QuarkEngine->GetPLByPara($aID,$BoxRef,$ParaID);
								if($PL === false) {
									$Tags[$n] = $para;
									$SO--;
									continue;
								}
								$this->QuarkEngine->MarkPLRebuilt($PL);
							}
							$Tags[$n] = "[PL:$PL]";
						}

						if($parse_type==PARSE_BY_SENTENCE) {
							switch($SourceLangID) {
								case 8:
									preg_match_all('/[^。�?？]+[。�?？]+/iu', $para, $match, PREG_SET_ORDER);
									break;
								default:
									preg_match_all('/.*?(?:[.?!]+\s*(?=[A-Z0-9])|$)/', $para, $match, PREG_SET_ORDER);
									break;
							}
							foreach($match as $text) {
								$SO++;
								$sentence = $text[0];
								if(!is_null($BoxID)){
									$para_row = $this->QuarkEngine->AddParagraph($sentence,$SourceLangID,$BoxID,$_SESSION['userID'],PARA_UPLOAD,$brandID,$subjectID,$SG);
									if($para_row === false) {
										$Tags[$n] .= $sentence;
										$SO--;
										continue;
									}
									$total_word_count += $para_row['Words'];
									$PL = $para_row['PL'];
								} else {
									$ids = $box->getElementsByTagName('ID');
									if($ids->length==0) {
										$Tags[$n] .= $sentence;
										$SO--;
										continue;
									}
									$BoxRef = $ids->item(0)->getAttribute('UID');
									$para_row = $this->QuarkEngine->ParaExists($sentence,$SourceLangID);
									if($para_row===false) {
										$Tags[$n] .= $sentence;
										$SO--;
										continue;
									}
									$total_word_count += $para_row['Words'];
									$ParaID = $para_row['ParaID'];
									$PL = $this->QuarkEngine->GetPLByPara($aID,$BoxRef,$ParaID);
									if($PL === false) {
										$Tags[$n] .= $sentence;
										$SO--;
										continue;
									}
									$this->QuarkEngine->MarkPLRebuilt($PL);
								}
								$Tags[$n] .= "[PL:$PL] ";
							}
						}
					}
					$Tag = "";
					foreach($Tags as $n=>$T) {
						$ob = (isset($obj[$n])) ? $obj[$n] : "";
						$Tag .= $T.$ob;
					}
					$Tag = $this->QuarkEngine->PostParsaPara($Tag);
					$richtext->nodeValue = $Tag;
				}
			} else {
				//advanced tagging parser routine
			}
		}
		return $total_word_count;
	}
	
	private function BuildPicts(DOMElement $box, $BoxID, $aID, $SourceLangID, $brandID, $subjectID) {
		$content = $box->getElementsByTagName('CONTENT')->item(0)->nodeValue;
		if(!is_null($BoxID)){
			$IL = $this->QuarkEngine->AddImage($BoxID,$_SESSION['userID'],$SourceLangID,$content,$brandID,$subjectID);
		} else {
			$ids = $box->getElementsByTagName('ID');
			if($ids->length==0) return false;
			$box_ref = $ids->item(0)->getAttribute('UID');
			$img_row = $this->QuarkEngine->ImageExists($content,IMG_UPLOAD);
			if($img_row === false) return false;
			$img_id = $img_row['img_id'];
			$img_link_row = $this->QuarkEngine->GetILByImg($aID,$box_ref,$img_id);
			if($img_link_row === false) return false;
			$IL = $img_link_row['PL'];
			$this->QuarkEngine->MarkILRebuilt($IL);
		}
		$Tag = "[IL:$IL]";
		$box->getElementsByTagName('CONTENT')->item(0)->nodeValue = $Tag;
	}
	
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(empty($ArtworkID) || !empty($record_id)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$filename = $row['artworkName']."_".substr($row['flag'],0,2).".qxp";
		copy(UPLOAD_DIR.$row['fileName'], ROOT.TMP_DIR.$filename);
		if(!file_exists(ROOT.TMP_DIR.$filename)) return false;
		return $filename;
	}
}

class OriginalPDF extends QuarkProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(empty($ArtworkID) || !empty($record_id)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$filename = $row['artworkName']."_".substr($row['flag'],0,2).".pdf";
		copy(UPLOAD_DIR.BareFilename($row['fileName']).".pdf", ROOT.TMP_DIR.$filename);
		if(!file_exists(ROOT.TMP_DIR.$filename)) return false;
		return $filename;
	}
}

class TemplatedQXP extends QuarkProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(!$record_id) return false;
		if(!$this->QuarkEngine->IsServerRunning()) return false;
		return $this->QuarkEngine->RebuildFileTemp($ArtworkID, $record_id, 0, ROOT.TMP_DIR, "QXP");
	}
}

class TemplatedPDF extends QuarkProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(empty($record_id)) return false;
		if(!$this->QuarkEngine->IsServerRunning()) return false;
		return $this->QuarkEngine->RebuildFileTemp($ArtworkID, $record_id, 0, ROOT.TMP_DIR, "PDF");
	}
}

class AmendedQuark extends QuarkProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->QuarkEngine->IsServerRunning()) return false;
		$file = $this->QuarkEngine->RebuildFile($ArtworkID, 0, 0, ROOT.TMP_DIR, "QXP");
		if($file === false) return false;
		if($packed === false) return $file;
		$zip = new ZipArchive();
		$filename = ROOT.TMP_DIR.basename($file).".zip";
		if ($zip->open($filename,ZIPARCHIVE::CREATE)===TRUE) {
			//add quark file
			$zip->addFile($file,basename($file));
			//add used images
			$query = sprintf("SELECT images.content 
							FROM img_usage
							LEFT JOIN images ON images.id = img_usage.img_id
							WHERE img_usage.artwork_id = %d
							AND img_usage.task_id = 0",
							$ArtworkID);
			$result = mysql_query($query) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$img_content = $row['content'];
				$zip->addFile($img_content,basename($img_content));
			}
			$zip->close();
		}
		@unlink($file);
		if(!file_exists($filename)) return false;
		return $filename;
	}
}

class AmendedPDF extends QuarkProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->QuarkEngine->IsServerRunning()) return false;
		return $this->QuarkEngine->RebuildFile($ArtworkID, 0, 0, ROOT.TMP_DIR, "PDF");
	}
}

class TranslatedQuark extends QuarkProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->QuarkEngine->IsServerRunning()) return false;
		$file = $this->QuarkEngine->RebuildFile($ArtworkID, $TaskID, 0, ROOT.TMP_DIR, "QXP");
		if($file === false) return false;
		if($packed === false) return $file;
		$zip = new ZipArchive();
		$filename = ROOT.TMP_DIR.basename($file).".zip";
		if ($zip->open($filename,ZIPARCHIVE::CREATE)===TRUE) {
			//add quark file
			$zip->addFile($file,basename($file));
			//add used images
			$query = sprintf("SELECT images.content 
							FROM img_usage
							LEFT JOIN images ON images.id = img_usage.img_id
							WHERE img_usage.artwork_id = %d
							AND img_usage.task_id IN (0,%d)",
							$ArtworkID,
							$TaskID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$img_content = $row['content'];
				$zip->addFile($img_content,basename($img_content));
			}
			$zip->close();
		}
		@unlink($file);
		if(!file_exists($filename)) return false;
		return $filename;
	}
}

class TranslatedPDF extends QuarkProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->QuarkEngine->IsServerRunning()) return false;
		return $this->QuarkEngine->RebuildFile($ArtworkID, $TaskID, 0, ROOT.TMP_DIR, "PDF");
	}
}

class TweakQuark extends QuarkProcess {
	public function TweakFile($ArtworkID, $TaskID, $filein) {
		$FilePath = $this->QuarkEngine->GetStorage().$filein;
		if(!file_exists($FilePath) || is_dir($FilePath)) return false;
		$URL = $this->QuarkEngine->GetQuarkXML().urlencode($filein);
		$XMLData = @$this->QuarkEngine->curl_get_file_contents($URL);
		//just keep a copy in tmp folder for debug
		file_put_contents(ROOT.TMP_DIR.BareFilename($filein).".tweak",$XMLData);
		$XML = new DOMDocument('1.0','UTF-8');
		$loaded = $XML->loadXML($XMLData);
		if($loaded === false) return false;
		$xPath = new DOMXpath($XML);
		$spreads = $xPath->query("//PROJECT/LAYOUT/SPREAD");
		foreach($spreads as $spread) {
			$pages = $spread->getElementsByTagName('PAGE');
			foreach($pages as $page) {
				$tables = $spread->getElementsByTagName('TABLE');
				$boxes = $spread->getElementsByTagName('BOX');
				$elements = array($boxes,$tables);
				foreach($elements as $boxes){
					foreach($boxes as $box) {
						$box_uid = $box->getElementsByTagName('ID')->item(0)->getAttribute('UID');
						$box_geo = $box->getElementsByTagName('GEOMETRY')->item(0);
						$box_angle = $box_geo->getAttribute('ANGLE');
						$box_pos = $box_geo->getElementsByTagName('POSITION')->item(0);
						$box_top = $box_pos->getElementsByTagName('TOP')->item(0)->nodeValue;
						$box_left = $box_pos->getElementsByTagName('LEFT')->item(0)->nodeValue;
						$box_bottom = $box_pos->getElementsByTagName('BOTTOM')->item(0)->nodeValue;
						$box_right = $box_pos->getElementsByTagName('RIGHT')->item(0)->nodeValue;
						$BoxID = $this->GetBoxIDByRef($ArtworkID,$box_uid);
						if($BoxID === false) continue;
						$this->QuarkEngine->SaveBoxProperties($ArtworkID,$BoxID,$TaskID);
						$this->QuarkEngine->SaveBoxMoves($ArtworkID,$BoxID,$TaskID,$box_left,$box_right,$box_top,$box_bottom,$box_angle);
						$this->QuarkEngine->CheckOverflow($ArtworkID,$TaskID);
					}
				}
			}
		}
		@unlink($FilePath);
	}
}
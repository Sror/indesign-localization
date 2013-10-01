<?php
require_once(ENGINES."InDesign.php");
require_once(PROCESSES."BaseProcess.php");
require_once(CLASSES."INDD_Story_Rebuilder.php");

class INDDProcess extends Process {
	protected $InDesignEngine;
	protected $XML;
	Protected $link;
	function __construct() {
		$this->InDesignEngine = new InDesignEngine();
		$this->link = $this->InDesignEngine->GetDBLink();
		$this->XML = new DOMDocument('1.0','UTF-8');
	}
	function GetPDFDefault(){
		return 'Smallest File Size';
	}
	
	protected $PDFOptions = Array(
			//'PAGL PDF'=>NULL,
			'High Quality Print',
			'PDFX1a 2001'=> 'PDF/X-1a:2001',
			'PDFX3 2002' => 'PDF/X-3:2002',
			'PDFX4 2008' => 'PDF/X-4:2008',
			'Press Quality' => 'Press Quality',
			'Smallest File Size' => 'Smallest File Size'
			);
			
	function AddPDFOption($Key, $File){
		$this->PDFOptions[$Key] = $File;
	}
	function GetPDFOptions(){
		return $this->PDFOptions;
	}
	function getPDFOption(){
		return parent::getPDFOption();
	  //$pdfOpts = $this->GetPDFOptions();
	  //return $pdfOpts[parent::getPDFOption()];
	}
	function setPDFOption($opt){
		parent::setPDFOption($opt);
		$this->InDesignEngine->setPDFProfile($this->getPDFOption());
	}
        
        public function getInDesignEngine() {
            return $this->InDesignEngine;
        }
}

class OriginalINDD extends INDDProcess {
	public function UploadFile($aID, $filein) {
		if(empty($aID)) return false;
		$row = $this->get_artwork_info($aID);
		if($row === false) return false;
		$parse_type = $row['parse_type'];
		$SourceLangID = $row['sourceLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		if(!$SourceLangID) return false;
		if(!file_exists($this->InDesignEngine->GetStorage().$filein) || is_dir($this->InDesignEngine->GetStorage().$filein)) return false;
		
		//create original PDF
		$tFile = BareFilename($filein);
		if(file_exists(OUTPUT_DIR.$filein."/Original/$tFile.pdf") && !file_exists($this->InDesignEngine->GetStorage()."$tFile.pdf")) {
			rename(OUTPUT_DIR.$filein."/Original/$tFile.pdf", $this->InDesignEngine->GetStorage()."$tFile.pdf");
		}
		if(file_exists(OUTPUT_DIR.$filein."/Original/$tFile.idml") && !file_exists($this->InDesignEngine->GetStorage()."$tFile.idml")) {
			rename(OUTPUT_DIR.$filein."/Original/$tFile.idml", $this->InDesignEngine->GetStorage()."$tFile.idml");
		}
		
		//read the generated XML
		$XMLFile = OUTPUT_DIR.$filein."/XML/BASE.XML";
		copy($XMLFile,OUTPUT_DIR.$filein."/XML/base.raw");
		
		$loaded = $this->XML->load($XMLFile);
		if($loaded === false) return false;
		$this->XML->formatOutput = true;
		$this->XML->preserveWhiteSpace = false;
                $IgnoreStories = $this->getConfig('UploadSettings_IgnoreStories');
		$xPath = new DOMXpath($this->XML);
		$page_count = 0;
		$total_word_count = 0;
		#$ProcessedStories = array();
		$links = array();
		
		//layers
		$layers = $this->XML->getElementsByTagName('Layer');
		$layers_array = array();
		foreach($layers as $layer) {
			$layer_ref = (int)$layer->getAttribute('ID');
			$layer_name = $layer->getAttribute('Name');
			$layer_visible = strtolower((string)$layer->getAttribute('Visible'))=="true" ? 1 : 0;
			$layer_locked = strtolower((string)$layer->getAttribute('Locked'))=="true" ? 1 : 0;
			$layer_colour = $this->InDesignEngine->html2hex($layer->getAttribute('Colour'));
			$layer_id = $this->InDesignEngine->AddLayer($aID,$layer_ref,$layer_name,$layer_colour,$layer_visible,$layer_locked);
			$layers_array[$layer_ref] = $layer_id;
		}

		//master spread pages
		$MasterSpreads = $this->XML->getElementsByTagName('MasterSpreads');
		//spread pages
		$NormalSpreads = $this->XML->getElementsByTagName('Spreads');
		$SpreadLoop = array(1=>$MasterSpreads,0=>$NormalSpreads);
		
		//Find used Master Pages
		$UsedMasters = array();
		foreach($SpreadLoop as $Master => $spreads){
			if($spreads->length==0) continue;
			$pages = $spreads->item(0)->getElementsByTagName('Page');
			foreach($pages as $page) {
				$UsedMasters[] = $page->getAttribute('MasterPageID');;
			}
		}
		$UsedMasters = array_unique($UsedMasters);
		foreach($SpreadLoop as $Master => $spreads){
			if($spreads->length==0) continue;
			$pages = $spreads->item(0)->getElementsByTagName('Page');
			foreach($pages as $page) {
				$page_id = $page->getAttribute('ID');
				$page_name = $page->getAttribute('PageName');
				$page_prefix = $page->getAttribute('PagePrefix');
				$page_ref = $page_prefix.$page_name;
				$MasterPageID = $page->getAttribute('MasterPageID');
				if($Master && !in_array($page_id, $UsedMasters)) continue;
				if(!$Master){
					$page_count++;
					$PagePreview = "$tFile-$page_count.jpg";
					if(file_exists(OUTPUT_DIR.$filein."/Original/$PagePreview") && is_file(OUTPUT_DIR.$filein."/Original/$PagePreview") ){
						rename(OUTPUT_DIR.$filein."/Original/$PagePreview", ROOT.PREVIEW_DIR.$PagePreview);
					}
					$PageNumber = $page_count;
				}else{
					$PageNumber = 0;
					$PagePreview = "";
				}
				$MasterPage = $xPath->query(sprintf("//Document/MasterSpreads/Spread/Pages/Page[@ID=%d]",$MasterPageID));
				if($MasterPage->length == 0) {
					$master_page_id = 0;
				} else {
					$master_page_id = $this->InDesignEngine->CheckPage($aID,$MasterPage->item(0)->getAttribute('PageName'));
					if($master_page_id === false) $master_page_id = 0;
				}
				$PageID = $this->InDesignEngine->AddPage($aID,$PageNumber,$PagePreview,$page_ref,$Master,$master_page_id);
				if($PageID === false) return false;
				//boxes=items
				$items = $page->getElementsByTagName('Item');

				foreach($items as $i => $item) {
					$item_id = $item->getAttribute('ID');
					$item_type = $item->getAttribute('Type');
					$parent_id = $item->getAttribute('parentID');
					$overflow = $item->getAttribute('overflow');
					$locked = $item->getAttribute('locked');
					$layer_ref = $item->getAttribute('LayerID');
					switch(strtoupper($item_type)) {
						case "GRAPHIC_TYPE":
							$BoxType = "PICT";
						break;
						case "TEXT_TYPE":
							$BoxType = "TEXT";
						break;
						default:
							$BoxType = "NONE";
						break;
					}

					$geo_bounds = $item->getElementsByTagName('visibleBounds')->item(0);
					$Top = MMtoPX($geo_bounds->getAttribute('Y1'));
					$Left = MMtoPX($geo_bounds->getAttribute('X1'));
					$Bottom = MMtoPX($geo_bounds->getAttribute('Y2'));
					$Right = MMtoPX($geo_bounds->getAttribute('X2'));
					$Angle = $item->getAttribute('Angle');
					$Grouped = strtolower((string)$item->getAttribute('Grouped'))=="true" ? 1 : 0;
					$BoxID = @$this->InDesignEngine->InsertBox($item_id,$PageID,$item_id,$Top,$Left,$Right,$Bottom,$layers_array[$layer_ref],$BoxType,$Angle,$Grouped,$parent_id);

					//check linked box
					$LinkedFrom = $item->getAttribute('LinkedFrom');
					$LinkedTo = $item->getAttribute('LinkedTo');
					if($LinkedTo > 0) $links[$BoxID] = $LinkedTo;

					//check overflow
					if(strtoupper($overflow)=="TRUE") {
						$this->InDesignEngine->SaveBoxOverflows($aID,$BoxID);
					}

					$islocked = (strtoupper($locked)=="TRUE") ? 1 : 0;
					$this->InDesignEngine->SaveBoxProperties($aID,$BoxID,0,$islocked,0);

					if($BoxType == "TEXT") {
						if($LinkedFrom != 0) continue;
						#if(in_array($parent_id,$ProcessedStories)) continue;
						#$total_word_count = $this->BuildStories($xPath,(int)$parent_id,$BoxID,$aID,$parse_type,$SourceLangID,$brandID,$subjectID,$total_word_count);
						#
                                                #$total_word_count = $this->NewBuildStories($filein,$total_word_count,$BoxID,$parent_id,$SourceLangID,$brandID,$subjectID);
                                                $total_word_count = $this->NewBuildStories($filein,$total_word_count,$BoxID,$parent_id,$SourceLangID,$brandID,$subjectID,$aID);
						#$ProcessedStories[] = $parent_id;
					}

					if($BoxType == "PICT") {
						$this->BuildLinks($xPath,(int)$parent_id,$BoxID,$aID,$SourceLangID,$brandID,$subjectID);
					}
				}
			}
		}
		rmdir(OUTPUT_DIR.$filein."/Original/");

		//update linked boxes
		foreach($links as $k=>$v) {
			$this->InDesignEngine->UpdateLinkedBoxes($aID,$k,$v);
		}
		
		$this->XML->save($XMLFile);
		$this->InDesignEngine->UpdateINDDArtwork($aID, array("wordCount" =>$total_word_count));
		return true;
	}
	
	public function RebuildBase($aID,$filein){
		//force rebuild BASE.XML
		if(empty($aID)) return false;
		$rebuild = $this->InDesignEngine->IDSR->IDSUpload($this->InDesignEngine->GetStorage().$filein, ".");
		if($rebuild!==true) return false;
		$row = $this->get_artwork_info($aID);
		if($row === false) return false;
		$parse_type = $row['parse_type'];
		$SourceLangID = $row['sourceLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];
		
		if(!$SourceLangID) return false;
		if(!file_exists($this->InDesignEngine->GetStorage().$filein) || is_dir($this->InDesignEngine->GetStorage().$filein)) return false;
		
		$XMLFile = OUTPUT_DIR.$filein."/XML/BASE.XML";
		@copy($XMLFile,OUTPUT_DIR.$filein."/XML/base.raw");

		//reset all the paralinks rebuilt to 0
		$this->InDesignEngine->ResetPLRebuilt($aID);
		$this->InDesignEngine->ResetILRebuilt($aID);
		
		$this->XML->load($XMLFile);
		$xPath = new DOMXpath($this->XML);
		
		$parent_id = null;
		$BoxID = null;
		$total_word_count = $this->BuildStories($xPath,$parent_id,$BoxID,$aID,$parse_type,$SourceLangID,$brandID,$subjectID,$total_word_count);
		$this->BuildLinks($xPath,$parent_id,$BoxID,$aID,$SourceLangID,$brandID,$subjectID);

		$this->XML->save($XMLFile);
		$this->InDesignEngine->UpdateINDDArtwork($aID, array("wordCount" =>$total_word_count));
		return true;
	}

	private function NewBuildStories($filein,$total_word_count,$BoxID,$story_ref,$SourceLangID,$brandID,$subjectID,$artwork_id) {
		$story_file = OUTPUT_DIR . $filein . "/XML/Stories/PAGL-$story_ref.icml";
		$rebuilder = new rebuilderControl($story_file);
		$paras = $rebuilder->getParas();
		
		$story_file = sprintf('INSERT INTO `story_files` (`artwork_id`, `story_ref`) VALUES ("%d", "%d")',$artwork_id,$story_ref);
		$result = mysql_query($story_file) or die( $story_file.mysql_error() );
		$story_id = mysql_insert_id();
		
		foreach ($paras as $paraRef => $para) {
			$segs = $para->getSegments();
			$SG = $this->InDesignEngine->AddStoryGroup();
                        //Add SG to Story_file
			$SO = 0;
			foreach ($segs as $segRef => $seg) {
				$SO++;
				if(!$seg->HasText()) continue;
				
				$SFG = sprintf('INSERT INTO `story_files_groups` (`story_file_id` ,`story_group_id`) VALUES (%d, %d)',$story_id,$SG);
				$result = mysql_query($SFG) or die( $SFG.mysql_error() );

				$content = $seg->getContent();
				#echo $seg->getContent();
				#$seg->setContent($seg->getContent() . " YOU");
				$para_row = $this->InDesignEngine->AddParagraph($content,$SourceLangID,$BoxID,$_SESSION['userID'],PARA_UPLOAD,$brandID,$subjectID,$SG,$SO,$paraRef,$segRef);
				if($para_row === false) {
					$SO--;
					continue;
				}
				list($Col,$Row) = explode(":",$seg->getName());
				$this->InDesignEngine->AddParaExtra(0,$para_row['PL'],"$Col:$Row",'table');	
				$total_word_count += $para_row['Words'];
			}
		}
		return $total_word_count;
	}

	function PathConverter($path){
		$path = urldecode($path);
		$path = str_replace('/', '\\', substr($path, 1,1).":/".substr($path, 3));
		return $path;
	}
	
	private function BuildStories($xPath, $parent_id, $BoxID, $aID, $parse_type, $SourceLangID, $brandID, $subjectID, $total_word_count){
		//call instance of INDD_Story_Rebuilder and set attributes
		$rebuilder = new INDD_Story_Rebuilder();
		$rebuilder->setKeeps(
			array(
				'cFont',
				'cSize',
				'cTypeface',
				'cColor',
				'cColorTint',
				'cStrikethru',
				'cUnderline',
				'cCase',
				'cLeading',
				'cNextXChars',
				'CharStyle',
				'cPosition',
				'cHorizontalScale',
				'cVerticalScale',
				'cBaselineShift',
				'cSkew',
				#'cStrokeWeight',
				#'cStrokeAlign',
				#'cMiterLimit',
				#'cKentenSize',
			)
		);
		
		$stories = $xPath->query(sprintf("//Document/Stories/Story%s",(!is_null($parent_id))?"[@ID=$parent_id]":""));
		foreach($stories as $story){
			$StoryID = $story->getAttribute('ID');
			$StoryData = base64_decode($story->nodeValue);
			
			//escape '\'
			#$StoryData = preg_replace('/\\\\([\S]{1})/sim', '\1', $StoryData);
			//fliter \< and \>
			$StoryData = $this->InDesignEngine->PreParsaStory($StoryData);
			//rebuild story data
			$parsed = $rebuilder->Parsa($StoryData);
			if(!$parsed) {
				log_error("Artwork[$aID] Box[$BoxID] StoryID[$StoryID] falied to be parsed.","INDD_Story_Rebuilder");
				continue;
			}
			while($paragraph = $rebuilder->NextItem()){
				while($pSeg = $paragraph->NextItem()){
					$pSeg->merge();
					$SG = $this->InDesignEngine->AddStoryGroup();
					$SO = 0;
					while($cSeg = $pSeg->NextItem()){
						$para = $cSeg->getText();
						if(preg_match('/\[Table:(\d+)\]/sim',$para)) continue;
						//strip out the last LF of last segement before insert into db
						if(!$pSeg->hasNextItem() && substr($para,-1,1)=="\n"){
							$para = substr($para,0,-1);
						}
						//start to replace para with tags
						/*
						 * Handle InDesign special characters, also refer to DBParsaPara() in translator class
						 * 03 ETX / end of text / End Nested Style Here
						 * 07 BEL / bell / Indent to Here
						 * 08 BS / backspace / Right Indent Tab
						 * 09 TAB / tab / Normal Tab
						 * 10 LF / soft return
						 * 18 CAN / cancel / *
						 */
						$chars = array(
							'\[OBJ:\d+\]',
							'\x03',
							'\x07',
							'\x08',
							'\x09',
							'\x13',
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
								if(!is_null($BoxID)) {
									$para_row = $this->InDesignEngine->AddParagraph($para,$SourceLangID,$BoxID,$_SESSION['userID'],PARA_UPLOAD,$brandID,$subjectID,$SG,$SO);
									if($para_row === false) {
										$Tags[$n] = $para;
										$SO--;
										continue;
									}
									$total_word_count += $para_row['Words'];
									$PL = $para_row['PL'];
								} else {
									$items = $xPath->query(sprintf("//Document/Spreads/Spread/Pages/Page/Item[@parentID=%d]",$StoryID));
									if($items->length==0) {
										$Tags[$n] = $para;
										$SO--;
										continue;
									}
									$BoxRef = $items->item(0)->getAttribute('ID');
									$para_row = $this->InDesignEngine->ParaExists($para,$SourceLangID);
									if($para_row===false) {
										$Tags[$n] = $para;
										$SO--;
										continue;
									}
									$total_word_count += $para_row['Words'];
									$ParaID = $para_row['ParaID'];
									$PL = $this->InDesignEngine->GetPLByPara($aID,$BoxRef,$ParaID);
									if($PL === false) {
										$Tags[$n] = $para;
										$SO--;
										continue;
									}
									$this->InDesignEngine->MarkPLRebuilt($PL);
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
										$para_row = $this->InDesignEngine->AddParagraph($sentence,$SourceLangID,$BoxID,$_SESSION['userID'],PARA_UPLOAD,$brandID,$subjectID,$SG,$SO);
										if($para_row === false) {
											$Tags[$n] .= $sentence;
											$SO--;
											continue;
										}
										$total_word_count += $para_row['Words'];
										$PL = $para_row['PL'];
									} else {
										$items = $xPath->query(sprintf("//Document/Spreads/Spread/Pages/Page/Item[@parentID=%d]",$StoryID));
										if($items->length==0) {
											$Tags[$n] .= $sentence;
											$SO--;
											continue;
										}
										$BoxRef = $items->item(0)->getAttribute('ID');
										$para_row = $this->InDesignEngine->ParaExists($para,$SourceLangID);
										if($para_row===false) {
											$Tags[$n] .= $sentence;
											$SO--;
											continue;
										}
										$total_word_count += $para_row['Words'];
										$ParaID = $para_row['ParaID'];
										$PL = $this->InDesignEngine->GetPLByPara($aID,$BoxRef,$ParaID);
										if($PL === false) {
											$Tags[$n] .= $sentence;
											$SO--;
											continue;
										}
										$this->InDesignEngine->MarkPLRebuilt($PL);
									}
									$Tags[$n] .= "[PL:$PL] ";
								}
							}
						}
						$Tag = "";
						foreach($Tags as $n=>$T) {
							$ob = (isset($obj[$n])) ? $obj[$n] : "";
							//NO carriage returns in the paragraphs
							if($T == "\r") continue;
							$Tag .= $T.$ob;
						}
						if(!$pSeg->hasNextItem()) {
							$Tag .= "\r\n";
						}
						$cSeg->setText($Tag);
						#$cSeg->addText($Tag);
						#echo "$para => $Tag\n";
						$pSeg->setCurrent($cSeg);
					}
					$paragraph->setCurrent($pSeg);
				}
				$rebuilder->setCurrent($paragraph);
			}
			/*
			$rebuilder->reset();
			while($paragraph = $rebuilder->NextItem()){
				$tmp = $paragraph->getSegment(0);
				$tmp = $tmp->getSegment(0);
				echo $tmp->getText();
			}
			//*/
			#echo $rebuilder->debugTable();
			$StoryData = $rebuilder->rebuild();
			#echo "\n\n$StoryData\n\n";
			$StoryData = $this->InDesignEngine->PostParsaStory($StoryData);
			$story->nodeValue = base64_encode($StoryData);
		}
		return $total_word_count;
	}
	
	private function BuildLinks($xPath, $parent_id, $BoxID, $aID, $SourceLangID, $brandID, $subjectID){
		//grab image links
		$links = $xPath->query(sprintf("//Document/Links/Link%s",(!is_null($parent_id))?"[@ObjectID=$parent_id]":""));
		foreach($links as $link) {
			$LinkID = $link->getAttribute('ObjectID');
			$filePath = $link->getAttribute('filePath');
			if(!is_null($BoxID)) {
				$IL = $this->InDesignEngine->AddImage($BoxID,$_SESSION['userID'],$SourceLangID,$filePath,$brandID,$subjectID);
				if($IL === false) continue;
			} else {
				$items = $xPath->query(sprintf("//Document/Spreads/Spread/Pages/Page/Item[@parentID=%d]",$LinkID));
				if($items->length==0) continue;
				$box_ref = $items->item(0)->getAttribute('ID');
				$img_row = $this->InDesignEngine->ImageExists($filePath,IMG_UPLOAD);
				if($img_row===false) return false;
				$img_id = $img_row['img_id'];
				$img_link_row = $this->InDesignEngine->GetILByImg($aID,$box_ref,$img_id);
				if($img_link_row === false) return false;
				$IL = $img_link_row['PL'];
				$this->InDesignEngine->MarkILRebuilt($IL);
			}
			$Tag = "[IL:$IL]";
			$link->setAttribute('filePath',$Tag);
		}
	}
	
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(empty($ArtworkID) || !empty($record_id)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$filename = $row['artworkName']."_".substr($row['flag'],0,2).".INDD";
		copy(UPLOAD_DIR.$row['fileName'], ROOT.TMP_DIR.$filename);
		if(!file_exists(ROOT.TMP_DIR.$fileName)) return false;
		return $filename;
	}
}

class OriginalIDML extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(empty($ArtworkID) || !empty($record_id)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$filename = $row['artworkName']."_".substr($row['flag'],0,2).".idml";
		copy(UPLOAD_DIR.BareFilename($row['fileName']).".idml", ROOT.TMP_DIR.$filename);
		if(!file_exists(ROOT.TMP_DIR.$filename)) return false;
		return $filename;
	}
}

class OriginalPDF extends INDDProcess {
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

class TemplatedINDD extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(!$record_id) return false;
		if(!$this->InDesignEngine->IsServerRunning()) return false;
		return $this->InDesignEngine->RebuildFileTemp($ArtworkID, $record_id, 0, ROOT.TMP_DIR, "INDD");
	}
}

class TemplatedIDML extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(!$record_id) return false;
		if(!$this->InDesignEngine->IsServerRunning()) return false;
		return $this->InDesignEngine->RebuildFileTemp($ArtworkID, $record_id, 0, ROOT.TMP_DIR, "IDML");
	}
}

class TemplatedPDF extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(!$record_id) return false;
		if(!$this->InDesignEngine->IsServerRunning()) return false;
		return $this->InDesignEngine->RebuildFileTemp($ArtworkID, $record_id, 0, ROOT.TMP_DIR, "PDF");
	}
}

class AmendedINDD extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->InDesignEngine->IsServerRunning()) return false;
		$file = $this->InDesignEngine->RebuildFile($ArtworkID,0,0,ROOT.TMP_DIR,"INDD");
		if($file === false) return false;
		if($packed === false) return $file;
		$zip = new ZipArchive();
		$filename = ROOT.TMP_DIR."$file.zip";
		if ($zip->open($filename,ZIPARCHIVE::CREATE)===TRUE) {
			//add indd file
			$zip->addFile(ROOT.TMP_DIR.$file,$file);
			//add used images
			$query = sprintf("SELECT images.content 
							FROM img_usage
							LEFT JOIN images ON images.id = img_usage.img_id
							WHERE img_usage.artwork_id = %d
							AND img_usage.task_id = 0",
							$ArtworkID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$img_content = $row['content'];
				$zip->addFile($img_content,basename($img_content));
			}
			$zip->close();
		}
		if(!file_exists($filename)) return false;
		return $filename;
	}
}

class AmendedIDML extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->InDesignEngine->IsServerRunning()) return false;
		$file = $this->InDesignEngine->RebuildFile($ArtworkID,0,0,ROOT.TMP_DIR,"IDML");
		if($file === false) return false;
		if($packed === false) return $file;
		$zip = new ZipArchive();
		$filename = ROOT.TMP_DIR."$file.zip";
		if ($zip->open($filename,ZIPARCHIVE::CREATE)===TRUE) {
			//add indd file
			$zip->addFile(ROOT.TMP_DIR.$file,$file);
			//add used images
			$query = sprintf("SELECT images.content 
							FROM img_usage
							LEFT JOIN images ON images.id = img_usage.img_id
							WHERE img_usage.artwork_id = %d
							AND img_usage.task_id = 0",
							$ArtworkID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$img_content = $row['content'];
				$zip->addFile($img_content,basename($img_content));
			}
			$zip->close();
		}
		if(!file_exists($filename)) return false;
		return $filename;
	}
}

class AmendedPDF extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->InDesignEngine->IsServerRunning()) return false;
		return $this->InDesignEngine->RebuildFile($ArtworkID,0,0,ROOT.TMP_DIR,"PDF");
	}
}

class TranslatedINDD extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->InDesignEngine->IsServerRunning()) return false;
		$file = $this->InDesignEngine->RebuildFile($ArtworkID,$TaskID,0,ROOT.TMP_DIR,"INDD");
		if($file === false) return false;
		if($packed === false) return $file;
		$zip = new ZipArchive();
		$filename = ROOT.TMP_DIR."$file.zip";
		if ($zip->open($filename,ZIPARCHIVE::CREATE)===TRUE) {
			//add indd file
			$zip->addFile(ROOT.TMP_DIR.$file,$file);
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
		if(!file_exists($filename)) return false;
		return $filename;
	}
}

class TranslatedIDML extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->InDesignEngine->IsServerRunning()) return false;
		$file = $this->InDesignEngine->RebuildFile($ArtworkID,$TaskID,0,ROOT.TMP_DIR,"IDML");
		if($file === false) return false;
		if($packed === false) return $file;
		$zip = new ZipArchive();
		$filename = ROOT.TMP_DIR."$file.zip";
		if ($zip->open($filename,ZIPARCHIVE::CREATE)===TRUE) {
			//add indd file
			$zip->addFile(ROOT.TMP_DIR.$file,$file);
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
		if(!file_exists($filename)) return false;
		return $filename;
	}
}

class TranslatedPDF extends INDDProcess {
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->InDesignEngine->IsServerRunning()) return false;
		return $this->InDesignEngine->RebuildFile($ArtworkID,$TaskID,0,ROOT.TMP_DIR,"PDF");
	}
}

class TweakINDD extends INDDProcess {
	public function TweakFile($ArtworkID, $TaskID, $filein) {
		$FilePath = $this->InDesignEngine->GetStorage().$filein;
		if(!file_exists($FilePath) || is_dir($FilePath)) return false;
		$XMLFile = OUTPUT_DIR.$filein."/XML/BASE.XML";
		//just keep a copy in tmp folder for debug
		@copy($XMLFile,ROOT.TMP_DIR.BareFilename($filein).".tweak");
		$XML = new DOMDocument('1.0','UTF-8');
		$loaded = $XML->load($XMLFile);
		if($loaded === false) return false;
		$xpath = new DOMXPath($XML);
		$master_page_items = $xpath->query('//Document/MasterSpreads/Spread/Pages/Page/Item');
		$normal_page_items = $xpath->query('//Document/Spreads/Spread/Pages/Page/Item');
		$all_items = array($master_page_items,$normal_page_items);
		foreach($all_items as $items) {
			foreach($items as $item) {
				$item_id = $item->getAttribute('ID');
				$locked = $item->getAttribute('locked');
				$geo_bounds = $item->getElementsByTagName('visibleBounds')->item(0);
				$Top = MMtoPX($geo_bounds->getAttribute('Y1'));
				$Left = MMtoPX($geo_bounds->getAttribute('X1'));
				$Bottom = MMtoPX($geo_bounds->getAttribute('Y2'));
				$Right = MMtoPX($geo_bounds->getAttribute('X2'));
				$Angle = $item->getAttribute('Angle');
				$BoxID = $this->GetBoxIDByRef($ArtworkID,$item_id);
				if($BoxID === false) continue;
				$this->InDesignEngine->SaveBoxProperties($ArtworkID,$BoxID,$TaskID,$locked);
				$this->InDesignEngine->SaveBoxMoves($ArtworkID,$BoxID,$TaskID,$Left,$Right,$Top,$Bottom,$Angle);
				$this->InDesignEngine->CheckOverflow($ArtworkID,$TaskID);
			}
		}
		@unlink($FilePath);
		if($filein!="") @do_rmdir(OUTPUT_DIR.$filein);
	}
}
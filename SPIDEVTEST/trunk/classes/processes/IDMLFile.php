<?php
require_once(ENGINES."IDML.php");
require_once(PROCESSES."BaseProcess.php");

class IDMLProcess extends Process {
	protected $IDMLEngine;
	function __construct() {
		$this->IDMLEngine = new IDMLEngine();
	}
}

class OriginalIDML extends IDMLProcess {
	//Original File Access
	public function UploadFile($aID, $filein) {
		if(empty($aID)) return false;
		$row = $this->get_artwork_info($aID);
		if($row === false) return false;
		$parse_type = $row['parse_type'];
		$SourceLangID = $row['sourceLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		if(!$SourceLangID>0) return false;

		//$filein = $DocInfo->Name;
		if(!(file_exists($this->IDMLEngine->GetStorage().$filein) && !is_dir($this->IDMLEngine->GetStorage().$filein))) {
			return false;
		}
		$tFile = BareFilename($filein);
		@file_put_contents($this->IDMLEngine->GetStorage()."$tFile.pdf",@file_get_contents($this->IDMLEngine->GetStorage()."DUMMY.PDF"));
		
		$input = $this->IDMLEngine->GetStorage().$filein;
		$path_parts = pathinfo($input);
		$output = $path_parts['dirname']."/".$path_parts['filename'].".base";
		
		$IDML = new IDML($input);
		$spreads = $IDML->getSpreads()->getSpreads();
		$PageNum=0;
		$boxes = array();
		//prep boxes
		foreach($spreads as $S => $spread) {
			$PageNum++;
			$Preview = "";
			$PageID = $this->IDMLEngine->AddPage($aID,$PageNum,$Preview,$S);
			if($PageID === false) return false;
			
			//$spread->getReferences() returns the references of all textBoxes used by this Spread
			foreach($spread->getReferences() as $Ref) {
				$RefID = (string)$Ref->attributes()->ParentStory;
				//exclude linked boxes
				if(!array_key_exists($RefID,$boxes)) {
					$boxes[$RefID] = $PageID;
				}
			}
		}
		//insert boxes
		$BoxNum = 0;
		$total_word_count = 0;
		foreach($boxes as $box => $page) {
			$BoxNum++;
			$Top = 0;
			$Left = 0;
			$Right = 0;
			$Bottom = 0;
			$BoxType = "TEXT";
			
			#$Dimensions = $spread->getDimensions($box);
			$story = $IDML->getStories()->getStorie($box);
			$BoxID = @$this->IDMLEngine->InsertBox($box, $page, $BoxNum, $Top, $Left, $Right, $Bottom, $BoxType);
			//paragraphs
			$contents = $story->getContents();
			$SG = $this->IDMLEngine->AddStoryGroup();
			$SO = 0;
			foreach($contents as $K=> $paragraph) {
				$SO++;
				set_time_limit(0);
				$StyleText = (string)$paragraph[0];
				$para_row = $this->IDMLEngine->AddParagraph($StyleText,$SourceLangID,$BoxID,$_SESSION['userID'],PARA_UPLOAD,$brandID,$subjectID,$SG,$SO);
				if($para_row === false) {
					$SO--;
					continue;
				}
				$PL = $para_row['PL'];
				$total_word_count += $para_row['Words'];
				$paragraph[0] = "[PL:$PL]";
			}
		}
			
		$IDML->reBuild($output);
		$this->IDMLEngine->UpdateIDMLArtwork($aID, array("wordCount" =>$total_word_count));
		return true;
	}
	
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(empty($ArtworkID) || !empty($record_id)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$filename = $row['artworkName']."_".substr($row['flag'],0,2).".IDML";
		copy(UPLOAD_DIR.$row['fileName'], ROOT.TMP_DIR.$filename);
		if(!file_exists(ROOT.TMP_DIR.$filename)) return false;
		return $filename;
	}
}

class TranslatedIDML extends IDMLProcess {
	//Translated File Access
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->IDMLEngine->IsServerRunning()) return false;
		return $this->IDMLEngine->RebuildFile($ArtworkID, $TaskID, 0, ROOT.TMP_DIR, "IDML");
	}
}

class OriginalPDF extends IDMLProcess {
	//Original File Access
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if(empty($ArtworkID) || !empty($record_id)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$filename = $row['artworkName']."_".substr($row['flag'],0,2).".pdf";
		copy(UPLOAD_DIR.BareFilename($row['fileName']).".pdf", TMP_DIR.$filename);
		if(!file_exists(ROOT.TMP_DIR.$filename)) return false;
		return $filename;
	}
}

class TranslatedPDF extends IDMLProcess {
	//Translated File Access
	public function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		if($record_id) return false;
		if(!$this->IDMLEngine->IsServerRunning()) return false;
		return $this->IDMLEngine->RebuildFile($ArtworkID, $TaskID, 0, ROOT.TMP_DIR, "PDF");
	}
}
?>
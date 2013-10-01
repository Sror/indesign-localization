<?php
require_once(CLASSES."imagemanager.php");
Class Translator extends ImageManager {
	/*
	protected $link;
	function __construct()
	{
		parent::__construct();
	}
	*/
	public function AddTranslatedPara($TranslatedParagraph, $TargetLangID, $ParaGroup, $ParalinkID, $taskID, $userID=0, $type=0, $brandID=0, $subjectID=0) {
		if(empty($ParalinkID)) return false;
		$TranslatedParagraph = preg_replace('/%u([\dA-F]{4})/im', '&#x\1;', $TranslatedParagraph);
		$TranslatedParagraph = $this->html_entity_decode_utf8($TranslatedParagraph);
		if(empty($TranslatedParagraph) && strlen($TranslatedParagraph)==0) return false;
		
		$query = sprintf("SELECT paragraphs.LangID
						FROM paralinks
						LEFT JOIN paragraphs ON paragraphs.uID = paralinks.ParaID
						WHERE paralinks.uID = %d
						LIMIT 1",
						$ParalinkID);
    	$result = mysql_query($query) or die(mysql_error());
    	if(!mysql_num_rows($result)) return false;
    	$row = mysql_fetch_assoc($result);
    	$LangID = $row['LangID'];
		return $this->AddTranslated($TranslatedParagraph, $TargetLangID, $ParaGroup, $LangID, $taskID, $userID, $type, $brandID, $subjectID, $ParalinkID);
	}
	
	public function AddTranslated($TranslatedParagraph="", $TargetLangID, $ParaGroup, $sourceLanguageID, $taskID=0, $userID=0, $type=0, $brandID=0, $subjectID=0, $ParalinkID=0) {
		$TranslatedParagraph = $this->DBParsaPara($TranslatedParagraph);
    	$para_row = $this->ParaExists($TranslatedParagraph, $TargetLangID);
    	if($para_row === false) {
			$word_count =str_word_count($TranslatedParagraph);
			$query = sprintf("INSERT INTO `paragraphs`
							(`LangID`, `ParaText`, `Words`, `user_id`, `type_id`, `brand_id`, `subject_id`)
							VALUES
							(%d, '%s', %d, %d, %d, %d, %d)",
							$TargetLangID,
							mysql_real_escape_string($TranslatedParagraph),
							$word_count,
							$userID,
							$type,
							$brandID,
							$subjectID);
			$result = mysql_query($query) or die(mysql_error());
			$ParaID = mysql_insert_id();
		} else {
			$ParaID = $para_row['ParaID'];
    	}
		$query = sprintf("SELECT * FROM `paraset` WHERE ParaID = %d AND ParaGroup = %d", $ParaID, $ParaGroup);
		$result = mysql_query($query) or die(mysql_error());
		if(!mysql_num_rows($result)) {
			$query = sprintf("INSERT INTO `paraset` (`ParaID`, `ParaGroup`) VALUES (%d, %d)", $ParaID, $ParaGroup);
			$result = mysql_query($query);
		}
		
		//add records to paratrans table
		if(!empty($taskID)) {
			if(!empty($ParalinkID)) {
				$this->AddParatrans($ParalinkID,$taskID,$ParaID,$userID);
			} else {
				/**
				 * codes below helps to insert records into prartrans. Technically not needed.
				 */
				$Para_result = $this->GetAllParaByPG($ParaGroup, $sourceLanguageID);
	    		if($Para_result === false) return false;
				while($Para = mysql_fetch_assoc($Para_result)) {
					$SourceParaID = $Para['ParaID'];

					//check if para has been edited in prework if so we have the PL
					$query = sprintf("SELECT paraedit.pl_id
									FROM paraedit
									LEFT JOIN paralinks ON paraedit.pl_id = paralinks.uID
									LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
									LEFT JOIN pages ON boxes.PageID = pages.uID
									LEFT JOIN tasks ON pages.ArtworkID = tasks.artworkID
									WHERE paraedit.para_id = %d
									AND paraedit.task_id = 0
									AND tasks.taskID = %d",
									$SourceParaID,
									$taskID);
					$result = mysql_query($query) or die(mysql_error());
					while($row = mysql_fetch_assoc($result)) {
						$this->AddParatrans($row['pl_id'],$taskID,$ParaID,$userID);
					}
					//for the rest of those that we don't have PL, try to work out their PL by looking through the paralink table
					$query = sprintf("SELECT paralinks.uID AS ParalinkID
									FROM paralinks
									LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
									LEFT JOIN pages ON boxes.PageID = pages.uID
									LEFT JOIN tasks ON pages.ArtworkID = tasks.artworkID
									WHERE paralinks.ParaID = %d
									AND tasks.taskID = %d",
									$SourceParaID,
									$taskID);
					$result = mysql_query($query) or die(mysql_error());
					while($row = mysql_fetch_assoc($result)) {
						$query_check = sprintf("SELECT id
										FROM paraedit
										WHERE pl_id = %d
										AND task_id = 0",
										$row['ParalinkID']);
						$result_check = mysql_query($query_check) or die(mysql_error());
						//continue in case overwrite the edited para that has been done in the previous loop
						if(mysql_num_rows($result_check)) continue;
						$this->AddParatrans($row['ParalinkID'],$taskID,$ParaID,$userID);
					}
				}
			}
		}
		return true;
	}
	
	public function AddParatrans($ParalinkID, $taskID, $ParaID, $userID) {
		$this->AddChangedItem($this->GetArtworkIDbyTask($taskID),$this->GetBoxIDbyPL($ParalinkID),$taskID);
		//add to paraedit to save as proofreading history if at proofreading stage
		$query = sprintf("SELECT taskStatus
						FROM tasks
						WHERE taskID = %d
						LIMIT 1",
						$taskID);
		$result = mysql_query($query,$this->link);
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		if($row['taskStatus'] > 7) {
			$this->AmendPara($ParalinkID,$ParaID,$userID,$taskID);
		}
		//add to paratrans table
		$query = sprintf("SELECT *
						FROM paratrans
						WHERE taskID = %d
						AND ParalinkID = %d",
						$taskID,
						$ParalinkID);
		$result = mysql_query($query,$this->link);
		if(!mysql_num_rows($result)) {
			$query = sprintf("INSERT INTO paratrans
							(taskID, ParalinkID, transParaID)
							VALUES
							(%d, %d, %d)",
							$taskID,
							$ParalinkID,
							$ParaID);
	       	$result = mysql_query($query,$this->link);
		} else {
			$query = sprintf("UPDATE paratrans SET
							transParaID = %d
							WHERE taskID = %d
							AND ParalinkID = %d",
							$ParaID,
							$taskID,
							$ParalinkID);
	       	$result = mysql_query($query,$this->link);
		}
		return true;
	}

	private function GetAllBoxes($ArtworkID, $PL) {
		$BoxIDs = array();
		$query = sprintf("SELECT ParaID
						FROM paralinks
						WHERE uID = %d
						LIMIT 1",
						$PL);
		$result = mysql_query($query,$this->link);
		if(!mysql_num_rows($result)) return $BoxIDs;
		$row = mysql_fetch_assoc($result);
		$query = sprintf("SELECT paralinks.BoxID
						FROM paralinks
						LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE paralinks.ParaID = %d
						AND pages.ArtworkID = %d
						ORDER BY paralinks.BoxID ASC",
						$row['ParaID'],
						$ArtworkID);
		$result = mysql_query($query,$this->link);
		while($row = mysql_fetch_assoc($result)) {
			$BoxIDs[] = $row['BoxID'];
		}
		return array_unique($BoxIDs);
	}

	public function RemoveParatrans($PL, $task_id=0) {
		$condition = !empty($task_id) ? sprintf("AND taskID = %d",$task_id) : "";
		$query = sprintf("DELETE FROM paratrans
						WHERE ParalinkID = %d",
						$PL,
						$condition);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}
	
	public function GetTMPara($TaskID, $PG) {
		$query = sprintf("SELECT paragraphs.ParaText
						FROM paraset
						LEFT JOIN paragraphs ON paraset.ParaID = paragraphs.uID
						LEFT JOIN para_types ON paragraphs.type_id = para_types.id
						LEFT JOIN tasks ON paragraphs.LangID = tasks.desiredLanguageID
						WHERE paraset.ParaGroup = %d
						AND tasks.taskID = %d
						ORDER BY
						para_types.order DESC,
						paragraphs.rating DESC,
						paragraphs.timeRef DESC",
						$PG,
						$TaskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['ParaText'];
	}

	public function UpdateParaIgnore($PL, $TaskID=0, $ignore=0) {
		$this->AddChangedItem($this->GetArtworkIDbyTask($TaskID),$this->GetBoxIDbyPL($PL),$TaskID);
		$ignored = $this->CheckParaIgnore($PL,$TaskID);
		if($ignored === false) {
			$query = sprintf("INSERT INTO para_ignore
							(pl_id, task_id, ignored)
							VALUES
							(%d, %d, %d)",
							$PL,
							$TaskID,
							$ignore);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			return mysql_insert_id($this->link);
		} else {
			$query = sprintf("UPDATE para_ignore SET
							ignored = %d
							WHERE pl_id = %d
							AND task_id = %d",
							$ignore,
							$PL,
							$TaskID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			return mysql_insert_id($this->link);
		}
	}

	public function CheckParaIgnore($PL, $TaskID=0) {
		$query = sprintf("SELECT ignored
						FROM para_ignore
						WHERE pl_id = %d
						AND task_id = %d
						LIMIT 1",
						$PL,
						$TaskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['ignored'];
	}

	public function FinalCheckParaIgnore($PL, $TaskID=0) {
		$query = sprintf("SELECT ignored
						FROM para_ignore
						WHERE pl_id = %d
						AND task_id IN (0,%d)
						LIMIT 1",
						$PL,
						$TaskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['ignored'];
	}
	
	public function GetTrans($TaskID, $PL=0) {
		$str = !empty($PL) ? sprintf(" AND paratrans.ParalinkID = %d",$PL) : "" ;
		$query = sprintf("SELECT paratrans.ParalinkID AS PL, UNIX_TIMESTAMP(paratrans.time) AS time, paragraphs.ParaText
						FROM paratrans
						LEFT JOIN paragraphs ON paratrans.transParaID = paragraphs.uID
						LEFT JOIN paralinks ON paralinks.uID = paratrans.ParalinkID
						WHERE paratrans.taskID = %d
						%s",
						$TaskID,
						mysql_real_escape_string($str));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return $result;
	}

	public function GetTransPara($TaskID, $PL) {
		$result = $this->GetTrans($TaskID,$PL);
		if($result === false) return false;
		$row = mysql_fetch_assoc($result);
		return $row['ParaText'];
	}
	
	protected function GetParaByGroup($PG, $LangID, $SourceID=0) {
		$query =sprintf("SELECT paragraphs.ParaText AS Para, paraset.ParaGroup AS PG, (languages.languageID = %d) as Target
						FROM paragraphs
						LEFT JOIN paraset ON ( paraset.ParaID = paragraphs.uID )
						LEFT JOIN languages ON ( languages.languageID = paragraphs.LangID )
						WHERE paraset.ParaGroup = %d and (languages.languageID = %d)
						ORDER BY Target, paragraphs.uID DESC",$LangID,$PG,$LangID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		
		if( mysql_num_rows($result) > 0)
		{
			$row = mysql_fetch_assoc($result);
			return array('Para' => $row['Para'], 'Target' => $row['Target']);
		}else{
			return false;
		}
	}
	
	/**
	 * Global subsititute for engines
	 * 
	 * @param string $Para
	 * @return string
	 */
	public function ParsaPara($Para) {
		return $Para;
	}
	
	/**
	 * Cleans Para before inserting into database
	 * only accept characters that can be input into browser by user
	 * Also refer to INDDFile.php for InDesign special character filter
	 *
	 * @param string $Para
	 * @return string
	 */
	private final function DBParsaPara($Para) {
		$Para = $this->ParsaPara($Para);
		$Keys = array(
			"\r\n" => "\n",
			"\r" => "\n",
			"&amp;" => "&",
			"&gt;" => ">",
			"&lt;" => "<",
			);
		for($n=0;$n<=31;$n++){
			//allow tabs and line feeds
			if($n==9 || $n==10) continue;
			$Keys[chr($n)] = "";
		}
		$Para = str_ireplace(array_keys($Keys),array_values($Keys),$Para);
		return $Para;
	}

	/**
	 * TranslateText
	 *	Returns an Array Para as translated Text
	 *  flag = Target Flag
	 *  PG = ParaGroup (a ParaGroup Already exists)
	 * 	LC = Translated to Target
	 *  Bool False Means No Group Exixts
	 * @param String $ParaText
	 * @param Int $TargetLangID
	 * @param Int $SourceLangID
	 * @return mixed
	 */
	public function TranslateText($ParaID=0, $TargetLangID=0, $SourceLangID=0)
	{
		$ForceSource = ($SourceLangID==0)?"":sprintf(" AND paragraphs.LangID = %d",$SourceLangID);
		$query = sprintf("SELECT paragraphs.ParaText AS Para, paragraphs.timeRef, languages.flag, paraset.ParaGroup AS PG, languages.languageID = %d AS LC
						FROM paragraphs
						LEFT JOIN para_types ON paragraphs.type_id = para_types.id
						LEFT JOIN paraset ON ( paraset.ParaID = paragraphs.uID )
						LEFT JOIN languages ON ( languages.languageID = paragraphs.LangID )
						WHERE paraset.paragroup = (
							SELECT paraset.paragroup
							FROM paragraphs
							LEFT JOIN paraset ON ( paraset.ParaID = paragraphs.uID )
							WHERE ParaID = %d $ForceSource
							LIMIT 1 
						)
						ORDER BY
						LC DESC,
						para_types.order DESC,
						paragraphs.rating DESC,
						paragraphs.timeRef DESC",
						$TargetLangID,
						$ParaID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			return $row;
		} else {
			return false;
		}
	}
	
	public function ResetPLRebuilt($ArtworkID) {
		$query = sprintf("SELECT paralinks.uID AS PL
						FROM paralinks
						LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						ORDER BY paralinks.uID ASC",
						$ArtworkID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$pls = "";
		while($row = mysql_fetch_assoc($result)) {
			$pls .= $row['PL'].",";
		}
		$pls = trim($pls,",");
		if(empty($pls)) return false;
		$query = sprintf("UPDATE paralinks SET
						Rebuilt = 0
						WHERE uID IN (%s)",
						mysql_real_escape_string($pls));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}
	
	public function MarkPLRebuilt($PL) {
		$query = sprintf("UPDATE paralinks SET
						Rebuilt = 1
						WHERE uID = %d",
						$PL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}
	
	public function GetPLByPara($ArtworkID,$BoxRef,$ParaID) {
		$query = sprintf("SELECT paralinks.uID AS PL
						FROM paralinks
						LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						AND boxes.BoxUID = %d
						AND paralinks.ParaID = %d
						AND paralinks.Rebuilt = 0
						ORDER BY paralinks.uID ASC
						LIMIT 1",
						$ArtworkID,
						$BoxRef,
						$ParaID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['PL'];
	}

	public function GetParaByPL($PL, $prework=true) {
		$query = sprintf("SELECT ParaID
						FROM paralinks
						WHERE uID = %d
						LIMIT 1",
						$PL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$para_id = $row['ParaID'];
		if($prework==true) {
			// check if para has been edited during prework
			$query = sprintf("SELECT para_id
							FROM paraedit
							WHERE pl_id = %d
							AND task_id = 0
							ORDER BY time DESC
							LIMIT 1",
							$PL);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				$para_id = $row['para_id'];
			}
		}
		$query = sprintf("SELECT paragraphs.uID AS ParaID, paragraphs.ParaText, paragraphs.Words, paragraphs.timeRef, paraset.ParaGroup
						FROM paragraphs
						LEFT JOIN paraset ON paragraphs.uID = paraset.ParaID
						WHERE paragraphs.uID = %d
						LIMIT 1",
						$para_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function GetParaTypeByID($ParaID) {
		$query =sprintf("SELECT para_types.*
						FROM paragraphs
						LEFT JOIN para_types ON paragraphs.type_id = para_types.id
						WHERE paragraphs.uID = %d
						LIMIT 1",
						$ParaID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function GetParaByPG($PG, $LangID) {
		$query =sprintf("SELECT paragraphs.uID AS ParaID, paragraphs.ParaText, paragraphs.Words
						FROM paragraphs
						LEFT JOIN para_types ON paragraphs.type_id = para_types.id
						LEFT JOIN paraset ON paraset.ParaID = paragraphs.uID
						LEFT JOIN languages ON languages.languageID = paragraphs.LangID
						WHERE paraset.ParaGroup = %d
						AND paragraphs.LangID = %d
						ORDER BY
						para_types.order DESC,
						paragraphs.rating DESC,
						paragraphs.timeRef DESC
						LIMIT 1",
						$PG,
						$LangID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	protected function GetAllParaByPG($PG, $LangID) {
		$query =sprintf("SELECT paragraphs.uID AS ParaID, paragraphs.ParaText, paragraphs.Words
						FROM paragraphs
						LEFT JOIN para_types ON paragraphs.type_id = para_types.id
						LEFT JOIN paraset ON paraset.ParaID = paragraphs.uID
						LEFT JOIN languages ON languages.languageID = paragraphs.LangID
						WHERE paraset.ParaGroup = %d
						AND paragraphs.LangID = %d
						ORDER BY
						para_types.order DESC,
						paragraphs.rating DESC,
						paragraphs.timeRef DESC",
						$PG,
						$LangID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return $result;
	}

	public function AddStoryGroup() {
		$query = "INSERT INTO `story_groups` (`id`) VALUES (NULL);";
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}

	public function GetStoryInfoByPL($PL) {
		$query = sprintf("SELECT *
						FROM paralinks
						WHERE uID = %d
						LIMIT 1",
						$PL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function AddParagraph($Para, $SourceLangID, $BoxID, $userID, $type, $brandID=0, $subjectID=0, $StoryGroup=0, $StoryOrder=0, $ParaRef=0, $SegRef=0) {
		$Para = $this->DBParsaPara($Para);
		$ParaTrim = trim($Para," ");
		if(empty($ParaTrim) && !is_numeric($ParaTrim)) return false;
		//check if paragraph exists (case sensitive) to allow more formats of same paragraph to be added
		$para_row = $this->ParaExists($Para,$SourceLangID,true);
		if($para_row === false) {
			$WordCount = str_word_count($Para);
			$query = sprintf("INSERT INTO `paragraphs`
							(`LangID`, `ParaText`, `Words`, `user_id`, `type_id`, `brand_id`, `subject_id`)
							VALUES
							(%d, '%s', %d, %d, %d, %d, %d);",
							$SourceLangID,
							mysql_real_escape_string($Para, $this->link),
							$WordCount,
							$userID,
							$type,
							$brandID,
							$subjectID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			$ParaID = mysql_insert_id($this->link);
			//check if paragraph exists (case insensitive) to assign same PG to different formats of same paragraph
			$para_ci_row = $this->ParaExists($Para,$SourceLangID,false);
			if($para_ci_row === false) {
				$query = "INSERT INTO `paragroups` (`uID`) VALUES (NULL);";
				$result = mysql_query($query,$this->link) or die(mysql_error());
				$PG = mysql_insert_id($this->link);
			} else {
				$PG = $para_ci_row['PG'];
			}
		} else {
			$ParaID = $para_row['ParaID'];
			$WordCount = $para_row['Words'];
			$PG = $para_row['PG'];
		}
		$query = sprintf("SELECT * FROM `paraset` WHERE `ParaID` = %d  AND `ParaGroup` = %d", $ParaID, $PG);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) {
			$query = sprintf("INSERT INTO `paraset` (`ParaID` ,`ParaGroup`) VALUES (%d, %d);", $ParaID, $PG);
			$result = mysql_query($query,$this->link) or die(mysql_error());
		}
		if(empty($BoxID)) {
			$PL = 0;
		} else {
			$query = sprintf("INSERT INTO `paralinks`
							(`ParaID` ,`BoxID`, `StoryGroup`, `order`, `ParaRef`, `SegRef`)
							VALUES (%d, %d, %d, %d, %d, %d)",
							$ParaID, $BoxID, $StoryGroup, $StoryOrder, $ParaRef, $SegRef);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			$PL = mysql_insert_id($this->link);
		}
		return array("ParaID"=>$ParaID, "Words"=>$WordCount, "PG"=>$PG, "PL"=>$PL);
	}
	
	public function ParaExists($Para="", $LangID=0, $CS=true) {
		$Para = $this->DBParsaPara($Para);
		$ParaTrim = trim($Para);
		if(empty($ParaTrim)) return false;
		$lang = (!empty($LangID)) ? sprintf(" AND paragraphs.LangID = %d",$LangID) : "";
		$strict = ($CS===true) ? "BINARY" : "";
		$query =sprintf("SELECT paragraphs.uID AS ParaID, paragraphs.Words, paraset.ParaGroup AS PG
						FROM paragraphs
						LEFT JOIN paraset on paraset.paraid = paragraphs.uID
						WHERE paraset.ParaGroup IS NOT NULL
						AND ParaText = $strict '%s'
						$lang
						LIMIT 1",
						mysql_real_escape_string($Para, $this->link));
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function GetClosePara($Para="", $LangID=0) {
		$Para = $this->DBParsaPara($Para);
		$ParaTrim = trim($Para);
		if(empty($ParaTrim)) return false;
		$lang = (!empty($LangID)) ? sprintf(" AND paragraphs.LangID = %d",$LangID) : "";
		$query =sprintf("SELECT paragraphs.uID AS ParaID, paragraphs.ParaText, paragraphs.Words, paraset.ParaGroup AS PG
						FROM paragraphs
						LEFT JOIN paraset on paraset.paraid = paragraphs.uID
						WHERE paraset.ParaGroup IS NOT NULL
						AND ParaText = '%s'
						$lang",
						mysql_real_escape_string($Para, $this->link));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return $result;
	}

	public function get_all_paralinks($artwork_id) {
		$query =sprintf("SELECT paralinks.uID AS PL, paralinks.ParaRef, paralinks.SegRef, paralinks.active, paralinks.type, boxes.StoryRef, paralinks.StoryGroup, 
						IF(para_orders.order IS NOT NULL, para_orders.order, paralinks.order) AS StoryOrder
						FROM paralinks
						LEFT JOIN boxes on paralinks.BoxID = boxes.uID
						LEFT JOIN pages on boxes.PageID = pages.uID
						LEFT JOIN para_orders ON ( para_orders.pl_id = paralinks.uID AND para_orders.task_id = 0 )
						WHERE pages.ArtworkID  = %d
						ORDER BY
							paralinks.StoryGroup ASC,
							paralinks.order ASC,
							paralinks.uID ASC",
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return $result;
	}

	public function get_all_paras($artwork_id, $task_id=0, $box_id=0, $PL=0, $conditions="") {
		$str = "";
		$str .= !empty($box_id) ? sprintf(" AND boxes.uID = %d",$box_id) : "";
		$str .= !empty($PL) ? sprintf(" AND paralinks.uID = %d",$PL) : "";
		// check if task is trial run
		$str .= $this->get_task_trial_status($task_id) ? " AND boxes.heading = 1" : "";
		$str .= mysql_real_escape_string($conditions);
		$query = sprintf("SELECT paralinks.uID AS PL, paralinks.ParaID, paralinks.BoxID, paralinks.StoryGroup,
						IF(para_orders.order IS NOT NULL, para_orders.order, paralinks.order) AS StoryOrder,
						paraset.ParaGroup,
						paragraphs.ParaText, paragraphs.timeRef, paragraphs.Words,
						para_types.name AS paraType, para_types.icon,
						pages.Page
						FROM paralinks
						LEFT JOIN paraset ON paralinks.ParaID = paraset.ParaID
						LEFT JOIN paragraphs ON paralinks.ParaID = paragraphs.uID
						LEFT JOIN para_types ON paragraphs.type_id = para_types.id
						LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						LEFT JOIN box_properties ON ( box_properties.box_id = paralinks.BoxID AND box_properties.task_id IN (0,%d) )
						LEFT JOIN para_ignore ON ( paralinks.uID = para_ignore.pl_id AND para_ignore.task_id = 0 )
						LEFT JOIN para_orders ON ( para_orders.pl_id = paralinks.uID AND para_orders.task_id = 0 )
						WHERE pages.ArtworkID = %d
						AND paralinks.active = 1
						AND (box_properties.lock IS NULL OR box_properties.lock = 0)
						AND (para_ignore.ignored IS NULL OR para_ignore.ignored = 0)
						$str
						GROUP BY paralinks.uID
						ORDER BY
							boxes.order ASC,
							paralinks.StoryGroup ASC,
							StoryOrder ASC,
							paralinks.uID ASC",
						$task_id,
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return $result;
	}
	
	function ExportPara($ArtworkID, $TaskID, $prework=true) {
		$result = $this->get_all_paras($ArtworkID,$TaskID);
		$rows = array();
		while($row = mysql_fetch_assoc($result)) {
			$PL = $row['PL'];
			$para_row = $this->GetParaByPL($PL,$prework);
			if($para_row === false) {
				$ParaID = $row['ParaID'];
				$ParaText = $row['ParaText'];
			} else {
				$ParaID = $para_row['ParaID'];
				$ParaText = $para_row['ParaText'];
			}
			
			$rows[] = array("PL"=>$PL,"ParaID"=>$ParaID,"ParaText"=>$ParaText);
		}
		return $rows;
	}

	public function AmendPara($PL, $para_id, $user_id, $task_id=0) {
		$query = sprintf("INSERT INTO paraedit
						(pl_id, task_id, para_id, user_id, time)
						VALUES
						(%d, %d, %d, %d, UNIX_TIMESTAMP(NOW()))",
						$PL,
						$task_id,
						$para_id,
						$user_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		$id = mysql_insert_id($this->link);
		//remove possible paratrans records if tasks are started
		$this->RemoveParatrans($PL);
		return $id;
	}
	
	public function GetAmendedPara($PL, $task_id=0) {
		$query = sprintf("SELECT paraedit.para_id AS ParaID, paragraphs.ParaText, paraedit.time
						FROM paraedit
						LEFT JOIN paragraphs ON paraedit.para_id = paragraphs.uID
						WHERE paraedit.pl_id = %d
						AND task_id = %d
						ORDER BY paraedit.time DESC
						LIMIT 1",
						$PL,
						$task_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}
	
	Public function GetParaAmends($PL, $task_id=0) {
		$query = sprintf("SELECT paraedit.user_id, paraedit.time,
						paragraphs.ParaText,
						users.username
						FROM paraedit
						LEFT JOIN paragraphs ON paraedit.para_id = paragraphs.uID
						LEFT JOIN users ON users.userID = paraedit.user_id
						WHERE paraedit.pl_id = %d
						AND task_id = %d
						ORDER BY paraedit.time DESC",
						$PL,
						$task_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return $result;
	}

	public function DeleteAmended($id) {
		//delete possible paratrans record but keep TM
		$query = sprintf("SELECT pl_id, task_id
						FROM paraedit
						WHERE id = %d
						LIMIT 1",
						$id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$this->RemoveParatrans($row['pl_id']);
		//delete paraedit record
		$query = sprintf("DELETE FROM paraedit WHERE id = %d", $id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}
	
	public function GetPGbyPara($para_id) {
		$query = sprintf("SELECT ParaGroup
						FROM paraset
						WHERE ParaID = %d
						LIMIT 1",
						$para_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['ParaGroup'];
	}
	
	public function CleanXMLfile($file) {
		$DATA = file_get_contents($file);
		$DATA = $this->CleanXML($DATA);
		file_put_contents($file,$DATA);
		return true;
	}
		
	public function AddParaExtra($ArtworkID, $ParaID, $extra='', $Ref='') {
		$query = sprintf("INSERT INTO `para_extra` (`artwork_id`, `para_link_id` ,`extra`, `ref`) VALUES (%d, %d, '%s', '%s')", 
			$ArtworkID, $ParaID, $extra, $Ref);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		
	}
	
	public function CleanXML($DATA, $chunkSize=1048) {
		//get rid of (C) (R) etc.
		$chunks = str_split($DATA,$chunkSize);
		$DATA = "";
		foreach($chunks as $chunk){
			#$DATA .= preg_replace('/(?:\xA9|\xAE|\x1F|\xA8\xA6\x20\x64)/u', '', $chunk);
		}
		return $DATA;
	}

	public function CheckProgress($task_id) {
		if(empty($task_id)) return false;
		$task_id = (int)$task_id;
		$artwork_id = $this->GetArtworkIDbyTask($task_id);
		if($artwork_id === false) return false;
		$user_words = 0;
		$tm_words = 0;
		$missing_words = 0;
		$result = $this->get_all_paras($artwork_id,$task_id);
		while($row = mysql_fetch_assoc($result)) {
			$PL = $row['PL'];
			$SourcePara = $this->GetParaByPL($PL,true);
			if($SourcePara === false) continue;
			$SourceParaWords = $SourcePara['Words'];
			$SourceParaGroup = $SourcePara['ParaGroup'];
			//check if ignored
			$ignored = $this->CheckParaIgnore($PL,$task_id);
			if($ignored) {
				$user_words += $SourceParaWords;
			} else {
				//check if user has picked any translation
				$TransPara = $this->GetTransPara($task_id,$PL);
				if($TransPara === false) {
					$TMPara = $this->GetTMPara($task_id,$SourceParaGroup);
					if($TMPara === false) {
						//use the origial
						$missing_words += $SourceParaWords;
					} else {
						//user the latest translation
						$tm_words += $SourceParaWords;
					}
				} else {
					//use user picked translation
					$user_words += $SourceParaWords;
				}
			}
		}
		$this->UpdateProgress($task_id,$user_words,$tm_words,$missing_words);
		return $missing_words;
	}
	
	private function UpdateProgress($task_id, $user_words=0, $tm_words=0, $missing_words=0) {
		if(empty($task_id)) return false;
		$query = sprintf("UPDATE tasks SET
						userWords = %d,
						tmWords = %d,
						missingWords = %d
						WHERE taskID = %d",
						$user_words,
						$tm_words,
						$missing_words,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
	}

	public function RemoveTM($para_id, $PL=0, $task_id=0) {
		$str = "";
		$str .= !empty($PL) ? sprintf(" AND `ParalinkID` = %d", $PL) : "";
		$str .= !empty($task_id) ? sprintf(" AND `taskID` = %d", $task_id) : "";
		// mark associated boxes as changed for cache function
		$update = sprintf("SELECT taskID, ParalinkID
						FROM `paratrans`
						WHERE `transParaID` = %d
						$str",
						$para_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$this->AddChangedItem($this->GetArtworkIDbyTask($row['taskID']),$this->GetBoxIDbyPL($row['ParalinkID']),$row['taskID']);
		}
		// start to delete paratrans
		$update = sprintf("DELETE FROM `paratrans`
						WHERE `transParaID` = %d
						$str",
						$para_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());

		$query = sprintf("SELECT * FROM `paratrans`
						WHERE `transParaID` = %d",
						$para_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) {
			$PG = $this->GetPGbyPara($para_id);
			if($PG !== false) {
				$update = sprintf("DELETE FROM `paraset`
								WHERE `ParaID` = %d
								AND `ParaGroup` = %d",
								$para_id,
								$PG);
				$result = mysql_query($update,$this->link) or die(mysql_error());
			}
		}
		$query = sprintf("SELECT * FROM `paraset`
						WHERE `ParaID` = %d",
						$para_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) {
			$update = sprintf("DELETE FROM `paragraphs`
							WHERE `uID` = %d",
							$para_id);
			$result = mysql_query($update,$this->link) or die(mysql_error());
		}
		return true;
	}

	public function UpdateParaNotes($para_id, $notes) {
		if(empty($para_id)) return false;
		$query = sprintf("UPDATE paragraphs SET
						notes = '%s'
						WHERE uID = %d",
						mysql_real_escape_string($notes),
						$para_id);
		$result = mysql_query($query,$this->link);
		return mysql_affected_rows($this->link);
	}

	/**
	 *
	 * @uses code2utf
	 */
	public function html_entity_decode_utf8($string) {
	    static $trans_tbl;
	    // replace numeric entities
	    $string = preg_replace('/\\\\\'([a-f\d]{2})/e', 'code2utf(hexdec("\\1"))', $string);
	    $string = preg_replace('~&#x([0-9a-f]+);~ei', '$this->code2utf(hexdec("\\1"))', $string);
	    $string = preg_replace('~&#([0-9]+);~e', '$this->code2utf(\\1)', $string);
	    // replace literal entities
	    if (!isset($trans_tbl))
	    {
	        $trans_tbl = array();

	        foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key)
	            $trans_tbl[$key] = utf8_encode($val);
	    }

	    return strtr($string, $trans_tbl);
	}

	// Returns the utf string corresponding to the unicode value
	public function code2utf($num) {
		#if ($num = 128) $num=8364;
	    if ($num < 128) return chr($num);
	    if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
	    if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	    if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	    return '';
	}

	public function utf8ToUnicode($str) {
		$str = (string)$str;
		$unicode = array();
		$values = array();
		$lookingFor = 1;

		for ($i = 0; $i < strlen($str); $i++ )
		{
			$thisValue = ord($str[$i]);

			if ($thisValue < 128) {
				$unicode[] = $thisValue;
			} else {
				if ( count( $values ) == 0 )
				{
					$lookingFor = ( $thisValue < 224 ) ? 2 : 3;
				}

				$values[] = $thisValue;

				if ( count( $values ) == $lookingFor )
				{
					$number = ( $lookingFor == 3 )?
						( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
						( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );

					$unicode[] = $number;
					$values = array();
					$lookingFor = 1;
				}
			}
		}

		return $unicode;
	}

	public function escapeUnicode($var) {
	  if(!empty($var[2]) && is_numeric($var[2])){
	    $uchr = (float)$var[2];
	    return sprintf("&#%d;",($uchr < 0)?$uchr+65536:$uchr);
	  }elseif(!empty($var[3]) && is_numeric($var[3])){
	    $uchr = (float)$var[3];
	    return sprintf("&#%d;",($uchr < 0)?$uchr+65536:$uchr);
	  }
	  if(!empty($var[1]) && preg_match('/[a-f\d]{2}/i',$var[1])) return sprintf("&#%d;",hexdec($var[1]));
	  return "";
	}
	
	function str_word_count_utf8($string, $format = 0){
		switch ($format)
		{
			case 1:
				$matches = array();
				preg_match_all(WORD_COUNT_MASK, $string, $matches);
				return $matches[0];
			case 2:
				preg_match_all(WORD_COUNT_MASK, $string, $matches, PREG_OFFSET_CAPTURE);
				$result = array();
				foreach ($matches[0] as $match)
				{
					$result[$match[1]] = $match[0];
				}
				return $result;
		}
		return preg_match_all(WORD_COUNT_MASK, $string, $matches);
	}
        
        function mergedTasks($TaskID,$StoryRef){
            $query = sprintf('SELECT `story_files_task`.`id`,`story_files_task`.`parent_task_id`,`story_files`.`artwork_id`, `story_files`.`story_ref`
                FROM `story_files_task`
                LEFT JOIN `story_files` ON `story_files`.`id`=`story_files_task`.`story_file_id`
                LEFT JOIN `tasks` ON (`tasks`.`artworkID` = `story_files`.`artwork_id` AND `tasks`.`taskID` = `story_files_task`.`task_id`)
                WHERE `tasks`.`desiredLanguageID` = 0
                AND `tasks`.`translatorID` IS NULL
                AND `tasks`.`taskID`=%d
                AND `story_files`.`story_ref`=%d
                LIMIT 1',$TaskID,$StoryRef);
            $result = mysql_query($query,$this->link);
            if(mysql_num_rows($result)) {
                $row = mysql_fetch_assoc($result);
                return $row['parent_task_id'];
            }else{
                return $TaskID;
            }
        }
}

class MachineTranslation {
	private $TransCodes = array();
	function __construct() {
		$this->TransCodes = array(
			"ar" => "Arabic",
			"bg" => "Bulgarian",
			"ca" => "Catalan",
			"zh-CN" => "Chinese (simplified)",
			"zh-TW" => "Chinese (traditional)",
			"hr" => "Croatian",
			"cs" => "Czech",
			"da" => "Danish",
			"nl" => "Dutch",
			"en" => "English",
			"et" => "Estonian",
			"fi" => "Finnish",
			"fr" => "French",
			"de" => "German",
			"el" => "Greek",
			"iw" => "Hebrew",
			"hi" => "Hindi",
			"hu" => "Hungarian",
			"is" => "Icelandic",
			"id" => "Indonesian",
			"it" => "Italian",
			"ja" => "Japanese",
			"ko" => "Korean",
			"lv" => "Latvian",
			"lt" => "Lithuanian",
			"no" => "Norwegian",
			"pl" => "Polish",
			"pt" => "Portuguese",
			"ro" => "Romanian",
			"ru" => "Russian",
			"sr" => "Serbian",
			"sk" => "Slovak",
			"sl" => "Slovenian",
			"es" => "Spanish",
			"sv" => "Swedish",
			"tl" => "Tagalog",
			"th" => "Thai",
			"tr" => "Turkish",
			"uk" => "Ukrainian",
			"ur" => "Urdu",
			"vi" => "Vietnamese"
		);
		
	}
	
	//convert flag acronym to work with google
	function LangCodesConvert($lang = 'en') {
		return (isset($this->TransCodes[$lang]))?$this->TransCodes[$lang]:$lang;
	}
	
	function Translation($textSource, $langSource, $langTarget) {
		$langSource = $this->LangCodesConvert($langSource);
		$langTarget = $this->LangCodesConvert($langTarget);
		
		return false;
	}
	
	//get machine translation
	function GetMT($textSource, $langSource, $langTarget) {
		
		$trans = $this->GetMTCache($textSource, $langSource, $langTarget);
		if($trans === false) {
			$trans = $this->Translation($textSource, $langSource, $langTarget);
			//InsertMTCache
			$this->InsertMTCache($textSource, $langSource, $langTarget,$trans);
		}
		$langSource = $this->LangCodesConvert($langSource);
		$langTarget = $this->LangCodesConvert($langTarget);
		if($trans === false) {
			//echo 'Translation failed.';
			return "<span style=\"color: red;\" >[".$this->TransCodes[$langSource]."]</span> ".nl2br($this->fixUnicode(htmlspecialchars($textSource)));
		}
		return "<span style=\"color: blue;\" >[".$this->TransCodes[$langTarget]."]</span> ".nl2br($this->fixUnicode(htmlspecialchars($trans)));
	}
	
	function fixUnicode($str) {
		$replace = array(
			"&amp;#" => "&#"
			);
		$str = str_replace(array_keys($replace),array_values($replace),$str);
		return $str;
	}
	
	function GetPureMT($textSource, $langSource, $langTarget) {
		
		$trans = $this->GetMTCache($textSource, $langSource, $langTarget);
		if($trans === false)
		{
			$trans = $this->Translation($textSource, $langSource, $langTarget);
			//InsertMTCache
			$this->InsertMTCache($textSource, $langSource, $langTarget,$trans);
		}
		$langSource = $this->LangCodesConvert($langSource);
		$langTarget = $this->LangCodesConvert($langTarget);
		if($trans === false)
		{
			//echo 'Translation failed.';
			return $textSource;
		}
		return $trans;
	}
	
	function MassMT($paras, $langSource, $langTarget)
	{
		if(!is_array($paras)) return false;
	
		foreach($paras as $K=> $para)
		{
			$query = sprintf("SELECT *
							FROM `mt_cache`
							WHERE sourceText = '%s'
							AND souceLang = '%s'
							AND targetLang = '%s'
							LIMIT 1;",
							mysql_real_escape_string($para),
							$langSource,
							$langTarget);
			$result = mysql_query($query) or die(mysql_error());
			$found = mysql_num_rows($result);
			if($found) unset($paras[$K]);
		}
		if(empty($paras)) return false;
		$toTrans = implode(chr(13),$paras);
		$afterTrans = $this->Translation($toTrans, $langSource, $langTarget);
		$afterTrans = explode("\n",$afterTrans);
		if(!is_array($afterTrans)) return false;
		return $afterTrans;
	}
	
	function InsertMTCache($paras, $langSource, $langTarget, $afterTrans, $trim=false) {
		
		if(!is_array($paras)) $paras = array($paras);
		if(!is_array($afterTrans)) $afterTrans = array($afterTrans);
		if(count($paras)!= count($afterTrans)) return false;
		foreach ($paras as $K=>$para) {
			$para = htmlspecialchars_decode($para);
			$trans = $afterTrans[$K];
			if($trim === true) {
				$para = trim($para);
				$trans = trim($trans);
			}
			if(empty($para) || empty($trans)) continue;
			$query = sprintf("INSERT INTO `mt_cache`
							(sourceText,souceLang,targetLang,targetText)
							VALUES
							('%s', '%s', '%s', '%s')",
							mysql_real_escape_string($para),
							mysql_real_escape_string($langSource),
							mysql_real_escape_string($langTarget),
							mysql_real_escape_string($trans));
			$result = mysql_query($query) or die(mysql_error());
		}
	}
	
	function GetMTCache($para, $langSource, $langTarget) {
		$query = sprintf("SELECT targetText
							FROM `mt_cache`
							WHERE sourceText='%s'
							AND souceLang = '%s'
							AND targetLang = '%s'
							LIMIT 1",
			mysql_real_escape_string($para),
			mysql_real_escape_string($langSource), 
			mysql_real_escape_string($langTarget));
		$result = mysql_query($query)or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$count = mysql_num_rows($result);
		if($count) return $row['targetText'];
		return false;
	}
}

class Google extends MachineTranslation {
	function LangCodesConvert($lang = 'en') {
		$TransCodes = array(
			"ae" => "ar",
			"cn" => "zh-CN",
			"cz" => "cs",
			"dk" => "da",
			"gb" => "en",
			"us" => "en",
			"gr" => "el",
			"il" => "iw",
			"jp" => "ja",
			"kr" => "ko",
			"pk" => "ur",
			"se" => "sv",
			"tw" => "zh-TW",
			"vn" => "vi",
			"in" => "hi"
		);
		return (isset($TransCodes[$lang]))?$TransCodes[$lang]:$lang;
	}
	
	function Translation($textSource, $langSource, $langTarget) {
		$textSource = nl2br($textSource);
		$langSource = $this->LangCodesConvert($langSource);
		$langTarget = $this->LangCodesConvert($langTarget);
		
		$ch = curl_init();
		#$url = 'http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&langpair='.urlencode($langSource.'|'.$langTarget);
		// google translation api v2
		$url = "https://www.googleapis.com/language/translate/v2?key=AIzaSyAz4HzSV8USK-odTrfUVIs6A6zrU88s3eQ&source=$langSource&target=$langTarget";
		curl_setopt_array($ch, 
			array(
				CURLOPT_URL => $url,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_NOBODY => false,
				CURLOPT_POSTFIELDS => array('q'=>$textSource),
				CURLOPT_HTTPHEADER => array("User-Agent: Mozilla/5.0 (WindowsCURLOPT_HTTPHEADER; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15","X-HTTP-Method-Override: GET"),
				CURLOPT_CONNECTTIMEOUT => false
			)
		);
	   $c = curl_exec($ch);
	   if(empty($c)) return false;
	   $ret = json_decode($c, true);
	   curl_close($ch);
	   $response = $ret['data']['translations'][0]['translatedText'];
	   $response = html_entity_decode($response);
	   $response = preg_replace("%<BR[ /]*>[\r|\n]*%im", "\n", $response);
	   //remove the very first space that google returns
	   $response = preg_replace('/^\s/m', '', $response);
	   return $response;
	}
}
?>
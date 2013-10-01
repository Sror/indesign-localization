<?php
class Database {
	protected $link;
	protected $DBServer;
	protected $DBName;
	protected $DBUser;
	protected $DBPass;
	
	function __construct() {
		$this->db_connect();
	}
	
	function __destruct() {
		#mysql_close($this->link);
	}
        
        public function getLink() {
            return $this->link;
        }

	public function db_connect($DBServer=HOST_NAME, $DBName=DB_NAME, $DBUser=DB_USER_NAME, $DBPass=DB_PASSWORD) {
		$this->DBServer = $DBServer;
		$this->DBName = $DBName;
		$this->DBUser = $DBUser;
		$this->DBPass = $DBPass;
		if(!isset($this->link)) {
			$this->link = mysql_connect($this->DBServer,$this->DBUser,$this->DBPass);
			mysql_selectdb($this->DBName);
			mysql_query("SET CHARACTER SET utf8", $this->link);
			mysql_query("SET NAMES 'utf8'", $this->link);
		}
	}
	
	protected function AddLayer($artwork_id, $ref, $name, $colour="", $visible=1, $locked=0) {
		$query = sprintf("INSERT INTO artwork_layers
						(artwork_id, ref, name, colour, visible, locked)
						VALUES
						(%d, %d, '%s', '%s', %d, %d)",
						$artwork_id,
						$ref,
						mysql_real_escape_string($name),
						mysql_real_escape_string($colour),
						$visible,
						$locked);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}
	
	protected function AddPage($ArtworkID, $Page, $PreviewFile="", $PageRef="", $Master=0, $MasterPageID=0) {
		if(empty($ArtworkID)) return false;
		$query = sprintf("SELECT width, height
						FROM artworks
						WHERE artworkID = %d
						LIMIT 1",
						$ArtworkID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$preview = ROOT.PREVIEW_DIR.$PreviewFile;
		if(file_exists($preview) && is_file($preview)) {
			list($width,$height) = getimagesize($preview);
			if($width > $height){
				$PageScale = $width / $row['width'];
			}else{
				$PageScale = $height / $row['height'];
			}
		} else {
			$PageScale = 0;
		}
		$PageID = $this->CheckPageExists($ArtworkID,$Page);
		if($PageID !== false && !$Master) {
			log_error("Page Number Conflict - Artwork:$PreviewFile, PageName:$PageRef, Page:$Page, Master:$Master","INDD");
			die("ERROR:~Page Number Conflict - Artwork:$PreviewFile, PageName:$PageRef, Page:$Page, Master:$Master");
			return false;
		}
		$query = sprintf("INSERT INTO pages
						(ArtworkID, Page, PreviewFile, PreviewTimestamp, PageRef, PageScale, Master, MasterPageID)
						VALUES
						(%d, %d, '%s', NOW(), '%s', %f, %d, %d)",
						$ArtworkID,
						$Page,
						mysql_real_escape_string($PreviewFile),
						$PageRef,
						$PageScale,
						$Master,
						$MasterPageID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}

	protected function CheckPage($ArtworkID, $PageRef) {
		if(empty($ArtworkID)) return false;
		$query = sprintf("SELECT uID
						FROM pages
						WHERE ArtworkID = %d
						AND PageRef = '%s'
						LIMIT 1",
						$ArtworkID,
						mysql_real_escape_string($PageRef));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['uID'];
	}
	
	protected function CheckPageExists($ArtworkID, $Page) {
		if(empty($ArtworkID)) return false;
		if(empty($Page)) return false;
		$query = sprintf("SELECT uID
						FROM pages
						WHERE ArtworkID = %d
						AND Page = %d
						LIMIT 1",
						$ArtworkID,
						$Page);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return (mysql_num_rows($result)!==0);
	}

	public function GetAllPages($ArtworkID, $PageNo) {
		if(empty($ArtworkID) || empty($PageNo)) return false;
		$query = sprintf("SELECT uID, MasterPageID
						FROM pages
						WHERE ArtworkID = %d
						AND Page = %d
						LIMIT 1",
						$ArtworkID,
						$PageNo);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$PageStr = $row['uID'];
		if(empty($row['MasterPageID'])) {
			return $PageStr;
		} else {
			return $this->GetRelatedPages($row['MasterPageID'],$PageStr);
		}
	}

	protected function GetRelatedPages($PageID, $PageStr) {
		$query = sprintf("SELECT uID, MasterPageID
						FROM pages
						WHERE uID = %d
						LIMIT 1",
						$PageID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return $PageStr;
		$row = mysql_fetch_assoc($result);
		$PageStr .= ','.$row['uID'];
		if(empty($row['MasterPageID'])) {
			return $PageStr;
		} else {
			return $this->GetRelatedPages($row['MasterPageID'],$PageStr);
		}
	}

	protected function UpdatePage($ArtworkID, $PageRef, $PreviewFile="", $PageScale=1, $Master=0, $MasterPageID=0) {
		if(empty($ArtworkID)) return false;
		$query = sprintf("UPDATE pages SET
						PreviewFile = '%s',
						PreviewTimestamp = NOW(),
						PageScale = %f,
						Master = %d,
						MasterPageID = %d
						WHERE ArtworkID = %d
						AND PageRef = '%s'
						LIMIT 1",
						mysql_real_escape_string($PreviewFile),
						$PageScale,
						$Master,
						$MasterPageID,
						$ArtworkID,
						mysql_real_escape_string($PageRef));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}


	protected function InsertBox($Name, $PageID, $BoxUID, $Top, $Left, $Right, $Bottom, $LayerID, $Type="TEXT", $Angle=0, $Grouped=0, $StoryRef=0) {
		$query =sprintf("INSERT INTO `boxes`
						(`Name`, `PageID`, `BoxUID`, `Top`, `Left`, `Right`, `Bottom`, `LayerID`, `Type`, `Angle`, `Grouped`, `StoryRef`)
						VALUES
						('%s', %d, %d, %d, %d, %d, %d, %d, '%s', %d, %d, %d)",
						mysql_real_escape_string($Name),
						$PageID,
						$BoxUID,
						round($Top),
						round($Left),
						round($Right),
						round($Bottom), 
						$LayerID,
						mysql_real_escape_string($Type),
						$Angle,
						$Grouped,
						$StoryRef);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}

	protected function UpdateLinkedBoxes($artworkID, $boxID, $linkedBoxUID) {
		$query = sprintf("SELECT boxes.uID
						FROM boxes
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE boxes.BoxUID = %d
						AND pages.ArtworkID = %d
						LIMIT 1",
						$linkedBoxUID,
						$artworkID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$query = sprintf("UPDATE boxes SET
						LinkedBoxID = %d
						WHERE uID = %d",
						$row['uID'],
						$boxID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function GetTheBox($boxID) {
		$query = sprintf("SELECT uID
						FROM boxes
						WHERE LinkedBoxID = %d
						LIMIT 1",
						$boxID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return $boxID;
		$row = mysql_fetch_assoc($result);
		return $this->GetTheBox($row['uID']);
	}

	public function check_box_overflow($artwork_id, $box_id=0, $task_id=0) {
		$str = !empty($box_id) ? sprintf("AND box_id = %d",$box_id) : "";
		$query = sprintf("SELECT id
						FROM box_overflows
						WHERE artwork_id = %d
						AND task_id = %d
						%s",
						$artwork_id,
						$task_id,
						$str);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_num_rows($result);
	}

	public function check_page_box_overflow($artwork_id, $page_no, $task_id=0) {
		$query = sprintf("SELECT id
						FROM box_overflows
						LEFT JOIN boxes ON box_overflows.box_id = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE box_overflows.artwork_id = %d
						AND pages.ArtworkID = %d
						AND pages.Page = %d
						AND box_overflows.task_id = %d",
						$artwork_id,
						$artwork_id,
						$page_no,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_num_rows($result);
	}
	
	protected function SaveBoxOverflows($artworkID, $boxID, $taskID=0, $overflow=1) {
		$query =sprintf("INSERT INTO `box_overflows`
						(`artwork_id`, `box_id`, `task_id`, `overflow`)
						VALUES
						(%d, %d, %d, %d)",
						$artworkID,
						$boxID,
						$taskID,
						$overflow);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}
	
	public function EditArtworkDetails($artwork=0, $Extra=array()) {
		$ExtraSQL = "";
		if(is_array($Extra) && !empty($Extra))
		{
			foreach($Extra as $K => $V)
			{
				if(is_numeric($V))
				{
					if(is_float($V))
					{
						$ExtraSQL .= sprintf("`$K` = %f, ",(float)$V);
					} else {
						$ExtraSQL .= sprintf("`$K` = %d, ",(int)$V);
					}
				}elseif(is_string($V)){
					$ExtraSQL .=sprintf("`$K` = '%s', ",mysql_real_escape_string($V));
				}
			}
		} else {
			if(!empty($artwork)) return false;
		}
		$INSERT = is_a($artwork,"DocInfo");
		
		if($INSERT) {
			$Type = "INSERT INTO";
			$ExtraSQL .= sprintf("`lastUpdate` = NOW(), `pageCount` = '%d', `width` = '%f', `height` = '%f', `fileName`='%s', `time`=UNIX_TIMESTAMP(NOW()) ",
				$artwork->getPages(), $artwork->getWidth(), $artwork->getHeight(), mysql_real_escape_string($artwork->getName()));
		} else {
			$Type = "UPDATE";
			$ExtraSQL = trim($ExtraSQL,", ")." ";
			$ExtraSQL .= sprintf("WHERE `artworkID` = %d LIMIT 1",$artwork);
		}
		
		$query = "$Type `artworks` SET $ExtraSQL";
		$result = mysql_query($query,$this->link) or die(mysql_error());
		
		if($INSERT){
			return (mysql_insert_id($this->link));
		}else{
			return (mysql_affected_rows($this->link) == 1);
		}
	}
	
	public function AddFileFonts($artworkID, array $fileFontIDs) {
		$font_id_str = "";
		if(empty($fileFontIDs)) return false;
		foreach($fileFontIDs as $font_id) {
			$font_id_str .= sprintf("(%d,%d),",$artworkID,$font_id);
		}
		$update = sprintf("INSERT INTO artwork_fonts
							(artwork_id, font_id)
							VALUES
							%s",
							trim($font_id_str,","));
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return true;
	}
	
	public function InitialiseBoxOrders($artwork_id) {
		$query = sprintf("SELECT paralinks.BoxID
						FROM paralinks
						LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						GROUP BY paralinks.BoxID
						ORDER BY pages.Page ASC, pages.PageRef ASC, boxes.Left ASC, boxes.Top ASC",
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$order = 1;
		while($row = mysql_fetch_assoc($result)) {
			$update = sprintf("UPDATE boxes
							SET `order` = %d
							WHERE uID = %d",
							$order,
							$row['BoxID']);
			$do = mysql_query($update,$this->link) or die(mysql_error());
			$order++;
		}
	}

	public function GetBoxIDByRef($artwork_id,$box_uid) {
		$query = sprintf("SELECT boxes.uID
						FROM boxes
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						AND boxes.BoxUID = %d
						LIMIT 1",
						$artwork_id,
						$box_uid);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['uID'];
	}
	
	public function SaveBoxConfigs($box_id, $order=0, $dynamic=0, $heading=0) {
		$query = sprintf("UPDATE boxes SET
						`order` = %d,
						`dynamic` = %d,
						`heading` = %d
						WHERE uID = %d",
						$order,
						$dynamic,
						$heading,
						$box_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function SaveStoryOrder($PL, $task_id=0, $order=0) {
		if(empty($order)) return $this->RestoreStoryOrder($PL,$task_id);
		$query = sprintf("SELECT `order`
						FROM paralinks
						WHERE uID = %d
						LIMIT 1",
						$PL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$default_order = $row['order'];
		$query = sprintf("SELECT id
						FROM para_orders
						WHERE pl_id = %d
						AND task_id = %d
						LIMIT 1",
						$PL,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			if(empty($task_id) && $order==$default_order) {
				$query = sprintf("DELETE FROM para_orders
								WHERE id = %d",
								$row['id']);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			} else {
				$query = sprintf("UPDATE para_orders
								SET `order` = %d
								WHERE id = %d",
								$order,
								$row['id']);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		} else {
			if(empty($task_id) && $order==$default_order) {
				// do nothing
			} else {
				$query = sprintf("INSERT INTO para_orders
								(`pl_id`, `task_id`, `order`)
								VALUES
								(%d, %d, %d)",
								$PL,
								$task_id,
								$order);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		}
		return $order;
	}

	public function GetStoryOrder($PL, $task_id=0) {
		$by_str = empty($task_id) ? "ASC" : "DESC";
		$query = sprintf("SELECT `order`
						FROM para_orders
						WHERE pl_id = %d
						AND task_id IN (0,%d)
						ORDER BY task_id %s
						LIMIT 1",
						$PL,
						$task_id,
						mysql_real_escape_string($by_str));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			return $row['order'];
		} else {
			$query = sprintf("SELECT `order`
							FROM paralinks
							WHERE uID = %d
							LIMIT 1",
							$PL);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				return $row['order'];
			} else {
				return 0;
			}
		}
	}

	public function RestoreStoryOrder($PL, $task_id=0) {
		$query = sprintf("DELETE FROM para_orders
						WHERE pl_id = %d
						AND task_id = %d",
						$PL,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return $this->GetStoryOrder($PL,$task_id);
	}

	public function ActivatePL($PL) {
		$query = sprintf("UPDATE paralinks
						SET active = 1
						WHERE uID = %d",
						$PL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function DeactivatePL($PL) {
		$query = sprintf("UPDATE paralinks
						SET active = 0
						WHERE uID = %d",
						$PL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function MarkPLType($PL, $type) {
		$query = sprintf("UPDATE paralinks
						SET type = %d
						WHERE uID = %d",
						$type,
						$PL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function CleanupBox($box_id) {
		$query = sprintf("DELETE FROM paralinks
						WHERE BoxID = %d
						AND type > 0
						AND active = 0",
						$box_id);
		mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function RestoreBoxChange($box_id) {
		//delete changed
		$query = sprintf("DELETE FROM paralinks
						WHERE BoxID = %d
						AND type > 0",
						$box_id);
		mysql_query($query,$this->link) or die(mysql_error());
		//activate original
		$query = sprintf("UPDATE paralinks
						SET active = 1
						WHERE BoxID = %d
						AND active = 0",
						$box_id);
		mysql_query($query,$this->link) or die(mysql_error());
		//reset orders
		$query = sprintf("SELECT StoryGroup
						FROM paralinks
						WHERE BoxID = %d
						ORDER BY uID ASC",
						$box_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$this->ResetStoryOrder($row['StoryGroup']);
		}
		return true;
	}

	public function GetStorySum($SG) {
		$result = $this->GetStoryInfo($SG);
		return mysql_num_rows($result);
	}

	private function GetStoryInfo($SG) {
		$query = sprintf("SELECT uID
						FROM paralinks
						WHERE StoryGroup = %d
						AND active = 1
						ORDER BY
						`order` ASC,
						uID ASC",
						$SG);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return $result;
	}

	public function ResetStoryOrder($SG) {
		$result = $this->GetStoryInfo($SG);
		$order = 0;
		while($row = mysql_fetch_assoc($result)) {
			$order++;
			$query = sprintf("UPDATE paralinks
						SET `order` = %d
						WHERE uID = %d",
						$order,
						$row['uID']);
			mysql_query($query,$this->link) or die(mysql_error());
		}
		return $order;
	}
	
	public function SaveBoxProperties($artwork_id, $box_id, $task_id=0, $lock=0, $resize=0) {
		if($lock==0 && $resize==0) {
			$query = sprintf("SELECT id
							FROM box_properties
							WHERE artwork_id = %d
							AND box_id = %d
							AND task_id = %d
							LIMIT 1",
							$artwork_id,
							$box_id,
							$task_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			$found = mysql_num_rows($result);
			if($found) {
				$row = mysql_fetch_assoc($result);
				$query = sprintf("DELETE FROM box_properties
								WHERE id = %d",
								$row['id']);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		} else {
			$query = sprintf("SELECT id
							FROM box_properties
							WHERE artwork_id = %d
							AND box_id = %d
							AND task_id = %d
							LIMIT 1",
							$artwork_id,
							$box_id,
							$task_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			$found = mysql_num_rows($result);
			if($found) {
				$row = mysql_fetch_assoc($result);
				$query = sprintf("UPDATE box_properties
								SET `lock` = %d,
								`resize` = %d,
								user_id = %d,
								time = NOW()
								WHERE id = %d",
								$lock,
								$resize,
								$_SESSION['userID'],
								$row['id']);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			} else {
				$query = sprintf("INSERT INTO box_properties
								SET artwork_id = %d,
								box_id = %d,
								task_id = %d,
								`lock` = %d,
								`resize` = %d,
								user_id = %d,
								time = NOW()",
								$artwork_id,
								$box_id,
								$task_id,
								$lock,
								$resize,
								$_SESSION['userID']);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		}
	}
        
        public function getArtworkVersionID($ArtworkID,$TaskID=0){
            $query = sprintf("SELECT id FROM `artwork_versions` WHERE `artwork_id` =%d AND `task_id` =%d AND `active` =1 LIMIT 1", $ArtworkID,$TaskID);
            $result = mysql_query($query,$this->link) or die(mysql_error());
            if(!mysql_num_rows($result)){//No task found
                $query = sprintf("SELECT id FROM `artwork_versions` WHERE `artwork_id` =%d AND `active` =1 LIMIT 1", $ArtworkID);
                $result = mysql_query($query,$this->link) or die(mysql_error());
                if(!mysql_num_rows($result)) return null;
                return null; //ONLY USED FOR TASKS NOW ?!?
            }
            $row = mysql_fetch_assoc($result);
            return $row['id'];
        }
	
	public function SaveBoxMoves($artwork_id, $box_id, $task_id, $left, $right, $top, $bottom, $angle=0) {
		$query = sprintf("SELECT `Left`, `Right`, `Top`, `Bottom`, `Angle`
							FROM boxes
							WHERE uID = %d
							LIMIT 1",
							$box_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$default_left = $row['Left'];
		$default_right = $row['Right'];
		$default_top = $row['Top'];
		$default_bottom = $row['Bottom'];
		$default_angle = $row['Angle'];
		if($left==$default_left && $right==$default_right && $top==$default_top && $bottom==$default_bottom && $angle==$default_angle) {
			$query = sprintf("SELECT id
							FROM box_moves
							WHERE artwork_id = %d
							AND box_id = %d
							AND task_id = %d
							LIMIT 1",
							$artwork_id,
							$box_id,
							$task_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				$query = sprintf("DELETE FROM box_moves
								WHERE id = %d",
								$row['id']);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		} else {
			$query = sprintf("SELECT id
							FROM box_moves
							WHERE artwork_id = %d
							AND box_id = %d
							AND task_id = %d
							LIMIT 1",
							$artwork_id,
							$box_id,
							$task_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				$query = sprintf("UPDATE box_moves
								SET `left` = %d,
								`right` = %d,
								`top` = %d,
								`bottom` = %d,
								`angle` = %d,
								user_id = %d,
								time = NOW()
								WHERE id = %d",
								$left,
								$right,
								$top,
								$bottom,
								$angle,
								$_SESSION['userID'],
								$row['id']);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			} else {
				$query = sprintf("INSERT INTO box_moves
								SET artwork_id = %d,
								box_id = %d,
								task_id = %d,
								`left` = %d,
								`right` = %d,
								`top` = %d,
								`bottom` = %d,
								`angle` = %d,
								user_id = %d,
								time = NOW()",
								$artwork_id,
								$box_id,
								$task_id,
								$left,
								$right,
								$top,
								$bottom,
								$angle,
								$_SESSION['userID']);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		}
	}
	
	public function GetBoxMoves($artwork_id, $box_id, $task_id=0) {
		$by_str = empty($task_id) ? "ASC" : "DESC";
		$query = sprintf("SELECT `left`, `right`, `top`, `bottom`, `angle`
						FROM box_moves
						WHERE artwork_id = %d
						AND box_id = %d
						AND task_id IN (0,%d)
						ORDER BY task_id %s
						LIMIT 1",
						$artwork_id,
						$box_id,
						$task_id,
						mysql_real_escape_string($by_str));
                
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row;
	}
	
	public function RestoreBoxMoves($artwork_id, $box_id, $task_id=0) {
		$query = sprintf("DELETE FROM box_moves
						WHERE artwork_id = %d
						AND box_id = %d
						AND task_id = %d",
						$artwork_id,
						$box_id,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
	}
	
	function RebuildPageThumbnail($dir, $artwork_id, $page=0, $task_id=0) {
		if(!empty($page)) {
			$query = sprintf("SELECT PreviewFile
							FROM pages
							WHERE ArtworkID = %d
							AND Page = %d
							LIMIT 1",
							$artwork_id,
							$page);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(!mysql_num_rows($result)) return false;
			$row = mysql_fetch_assoc($result);
			$preview = $row['PreviewFile'];
			if(!empty($task_id)) $preview = BareFilename($preview)."-$task_id.jpg";
			if(!file_exists(ROOT.$dir.$preview)) @copy(ROOT.PREVIEW_DIR.$row['PreviewFile'],ROOT.$dir.$preview);
			$preview_img = @imagecreatefromjpeg(ROOT.$dir.$preview);
			list($width,$height) = @getimagesize(ROOT.$dir.$preview);
			if($width >= $height) {
				$w = THUMBNAIL_MAX_WIDTH;
				$h = $height * $w / $width;
			} else {
				$h = THUMBNAIL_MAX_HEIGHT;
				$w = $width * $h / $height;
			}
			$img2 = @imagecreatetruecolor($w,$h);
			@imagecopyresized($img2,$preview_img,0,0,0,0,$w,$h,$width,$height);
			@imagejpeg($img2,ROOT.$dir.THUMBNAILS_DIR.$preview);
			@imagedestroy($preview_img);
			@imagedestroy($img2);
			return $dir.THUMBNAILS_DIR.$preview;
		} else {
			$query = sprintf("SELECT Page
							FROM pages
							WHERE ArtworkID = %d
							AND Master = 0",
							$artwork_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$this->RebuildPageThumbnail($dir,$artwork_id,$row['Page'],$task_id);
			}
		}
	}
	
	function RebuildBoxPreview($artwork_id, $box_id=0, $task_id=0) {
		if(!empty($box_id)) {
			$query = sprintf("SELECT pages.uID, pages.PreviewFile, pages.PageScale,
							boxes.Left, boxes.Right, boxes.Top, boxes.Bottom, boxes.Angle
							FROM boxes
							LEFT JOIN pages ON boxes.PageID = pages.uID
							WHERE pages.ArtworkID = %d
							AND boxes.uID = %d
							LIMIT 1",
							$artwork_id,
							$box_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(!mysql_num_rows($result)) return false;
			$row = mysql_fetch_assoc($result);
			$PreviewFile = $row['PreviewFile'];
			$PageScale = $row['PageScale'];
			$left = $row['Left'];
			$right = $row['Right'];
			$top = $row['Top'];
			$bottom = $row['Bottom'];
			$angle = $row['Angle'];
			if(empty($PreviewFile) && empty($PageScale)) {
				$row = $this->GetMasterPagePreview($row['uID']);
				if($row !== false) {
					$PreviewFile = $row['PreviewFile'];
					$PageScale = $row['PageScale'];
				}
			}
			$page_path = empty($task_id) ? PREVIEW_DIR.$PreviewFile : POSTVIEW_DIR.BareFilename($PreviewFile)."-$task_id.jpg";
			if(!file_exists(ROOT.$page_path)) return false;
			$box_path = empty($task_id) ? PREVIEW_DIR.TEXTBOXES_DIR.BareFilename($PreviewFile)."-$box_id.jpg" : POSTVIEW_DIR.TEXTBOXES_DIR.BareFilename($PreviewFile)."-$box_id-$task_id.jpg";
			//get updated geometry info
			$geo = $this->GetBoxMoves($artwork_id,$box_id,$task_id);
			if($geo) {
				$left = $geo['left'];
				$right = $geo['right'];
				$top = $geo['top'];
				$bottom = $geo['bottom'];
				$angle = $geo['angle'];
			}
			$points = get_points($left*$PageScale,$top*$PageScale,$right*$PageScale,$bottom*$PageScale,$angle);
			$w = $points[4] - $points[0];
			if($w < 0) $w = 0 - $w;
			$h = $points[3] - $points[7];
			if($h < 0) $h = 0 - $h;
			$img1 = @imagecreatefromjpeg(ROOT.$page_path);
			#$w = ($right-$left)*$PageScale;
			#$h = ($bottom-$top)*$PageScale;
			$img2 = @imagecreatetruecolor($w,$h);
			@imagecopy($img2, $img1, 0, 0, $left*$PageScale, $top*$PageScale, $w, $h);
			@imagejpeg($img2, ROOT.$box_path);
			@imagedestroy($img2);
			return $box_path;
		} else {
			$query = sprintf("SELECT boxes.uID
							FROM boxes
							LEFT JOIN pages ON boxes.PageID = pages.uID
							WHERE pages.ArtworkID = %d
							ORDER BY boxes.uID ASC",
							$artwork_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$this->RebuildBoxPreview($artwork_id,$row['uID'],$task_id);
			}
		}
	}

	public function GetMasterPagePreview($master_page_id) {
		$query = sprintf("SELECT uID, PreviewFile, PageScale
						FROM pages
						WHERE MasterPageID = %d
						ORDER BY Page ASC, PageRef ASC
						LIMIT 1",
						$master_page_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		if(empty($row['PreviewFile']) && empty($row['PageScale'])) {
			return $this->GetMasterPagePreview($row['uID']);
		} else {
			return $row;
		}
	}
	
	public function AddComment($artwork_id, $page, $user_id, $comment, $attachment="", $box_id=0, $task_id=0, $is_guest=0, $name="") {
		if(empty($artwork_id) || empty($page) || empty($comment)) return false;
		if($is_guest && !empty($name)) {
			$query = sprintf("UPDATE artwork_guests
							SET name = '%s'
							WHERE id = %d",
							$name,
							$user_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
		}
		$query = sprintf("INSERT INTO comments
						(artwork_id, page, task_id, box_id, user_id, is_guest, comment, attachment, time)
						VALUES
						(%d, %d, %d, %d, %d, %d, '%s', '%s', NOW())",
						$artwork_id,
						$page,
						$task_id,
						$box_id,
						$user_id,
						$is_guest,
						mysql_real_escape_string($comment),
						mysql_real_escape_string($attachment));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}
	
	public function RemoveComment($comment_id) {
		$query = sprintf("SELECT attachment
						FROM comments
						WHERE id = %d
						LIMIT 1",
						$comment_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			@unlink(REPOSITORY_DIR.$row['attachment']);
			$query = sprintf("DELETE FROM comments
							WHERE id = %d",
							$comment_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
		}
	}
	
	public function GetBoxComments($artwork_id, $box_id, $task_id=0) {
		$query = sprintf("SELECT id
						FROM comments
						WHERE artwork_id = %d
						AND box_id = %d
						AND task_id = %d",
						$artwork_id,
						$box_id,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_num_rows($result);
	}
	
	public function AddGuest($artwork_id, $user_id, $email, $name="") {
		if(empty($email)) return false;
		$query = sprintf("SELECT token
						FROM artwork_guests
						WHERE artwork_id = %d
						AND email = '%s'",
						$artwork_id,
						$email);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			return $row['token'];
		} else {
			$token = md5(time().rand().$email);
			$query = sprintf("INSERT INTO artwork_guests
							(artwork_id, user_id, email, name, token)
							VALUES
							(%d, %d, '%s', '%s', '%s')",
							$artwork_id,
							$user_id,
							mysql_real_escape_string($email),
							mysql_real_escape_string($name),
							mysql_real_escape_string($token));
			$result = mysql_query($query,$this->link) or die(mysql_error());
			return $token;
		}
	}
	
	public function RemoveGuest($guest_id) {
		$query = sprintf("SELECT id
						FROM comments
						WHERE is_guest = 1
						AND user_id = %d",
						$guest_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$this->RemoveComment($row['id']);
		}
		$query = sprintf("DELETE FROM artwork_guests
						WHERE id = %d",
						$guest_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
	}
	
	public function GetImportColname($artwork_id, $PL) {
		$query = sprintf("SELECT import_map.colname
						FROM import_map_para
						LEFT JOIN import_map ON import_map_para.import_map_id = import_map.id
						WHERE import_map_para.artwork_id = %d
						AND import_map_para.pl_id = %d
						LIMIT 1",
						$artwork_id,
						$PL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['colname'];
	}
	
	public function GetImportData($col_name,$record_id) {
		$query = sprintf("SELECT %s
						FROM import_rows
						WHERE id = %d
						LIMIT 1",
						mysql_real_escape_string($col_name),
						$record_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row[$col_name];
	}
	
	protected function ImportStart($artwork_id, $task_id, $file_type, $option=1, $loose=0) {
		$query = sprintf("INSERT INTO task_imports
						(`artwork_id`, `task_id`, `file_type`, `option`, `loose`, `time_start`)
						VALUES
						(%d, %d, '%s', %d, %d, NOW())",
						$artwork_id,
						$task_id,
						$file_type,
						$option,
						$loose);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}
	
	protected function AddImportRow($import_id, $source, $target, $imported=0) {
		$query = sprintf("INSERT INTO task_import_rows
						(import_id, source, target, imported)
						VALUES
						(%d, '%s', '%s', %d)",
						$import_id,
						mysql_real_escape_string($source),
						mysql_real_escape_string($target),
						$imported);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}
	
	protected function ImportEnd($import_id) {
		$query = sprintf("UPDATE task_imports SET
						time_end = NOW()
						WHERE id = %d",
						$import_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function get_users($company_id, $source_lang_id, $target_lang_id) {
		$query = sprintf("SELECT users.userID, users.forename, users.surname
						FROM users
						LEFT JOIN userlanguages L1 ON users.userID = L1.userID
						LEFT JOIN userlanguages L2 ON users.userID = L2.userID
						WHERE users.companyID IN (%s)
						AND users.vtID IN (0,%d)
						AND ( (L1.languageID=%d AND L2.languageID=%d) OR users.userGroupID IN (33,34) )
						GROUP BY users.userID
						ORDER BY L2.proID DESC,
						users.forename ASC,
						users.surname ASC",
						mysql_real_escape_string($this->get_company_list($company_id)),
						$company_id,
						$source_lang_id,
						$target_lang_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return $result;
	}

	public function get_user_info($user_id) {
		$query = sprintf("SELECT userID, forename, surname
						FROM users
						WHERE userID = %d",
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_fetch_assoc($result);
	}

	public function get_company_list($company_id) {
		$str = "$company_id";
		$query = sprintf("SELECT partner_company_id
						FROM companies_acl
						WHERE company_id = %d
						ORDER BY partner_company_id ASC",
						$company_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$str .= ",".$row['partner_company_id'];
		}
		return $str;
	}

	public function check_company_acl($company_id, $partner_company_id) {
		$query = sprintf("SELECT id
						FROM companies_acl
						WHERE company_id = %d
						AND partner_company_id = %d
						LIMIT 1",
						$company_id,
						$partner_company_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_num_rows($result);
	}

	public function update_company_acl($company_id, array $acls) {
		if(empty($company_id) || !is_array($acls)) return false;
		$query = sprintf("DELETE FROM companies_acl
						WHERE company_id = %d",
						$company_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		foreach($acls as $partner_company_id=>$acl) {
			if($company_id==$partner_company_id) continue;
			$query = sprintf("INSERT INTO companies_acl
							(company_id, partner_company_id)
							VALUES
							(%d, %d)",
							$company_id,
							$partner_company_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
		}
	}

	public function check_campaign_acl($campaign_id, $company_id=0, $user_id=0) {
		$query = sprintf("SELECT allow
						FROM campaigns_acl
						WHERE campaign_id = %d
						AND company_id IN (0,%d)
						AND user_id IN (0,%d)
						ORDER BY
						user_id DESC,
						company_id DESC
						LIMIT 1",
						$campaign_id,
						$company_id,
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return ($row['allow']==1);
	}

	public function update_campaign_acl($campagn_id, array $acls) {
		if(empty($campagn_id) || !is_array($acls)) return false;
		$query = sprintf("DELETE FROM campaigns_acl
						WHERE campaign_id = %d",
						$campagn_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		foreach($acls as $company_id=>$acl) {
			$users = array();
			$query = sprintf("SELECT userID
							FROM users
							WHERE companyID = %d
							ORDER BY userID ASC",
							$company_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$users[$row['userID']] = $row['userID'];
			}
			ksort($acl);
			foreach($acl as $user_id=>$allow) {
				unset($users[$user_id]);
				$query = sprintf("SELECT id
								FROM campaigns_acl
								WHERE campaign_id = %d
								AND company_id = %d
								AND user_id = 0
								AND allow = %d",
								$campagn_id,
								$company_id,
								$allow);
				$result = mysql_query($query,$this->link) or die(mysql_error());
				if(mysql_num_rows($result)) continue;
				$query = sprintf("INSERT INTO campaigns_acl
								(campaign_id, company_id, user_id, allow)
								VALUES
								(%d, %d, %d, %d)",
								$campagn_id,
								$company_id,
								$user_id,
								$allow);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
			foreach($users as $user) {
				$query = sprintf("INSERT INTO campaigns_acl
								(campaign_id, company_id, user_id, allow)
								VALUES
								(%d, %d, %d, 0)",
								$campagn_id,
								$company_id,
								$user);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		}
	}

	public function get_service_process_id($service_id, $transaction_id, $type_id=0) {
		$query = sprintf("SELECT id
						FROM service_transaction_process
						WHERE serviceID = %d
						AND transactionID = %d
						AND type_id = %d
						LIMIT 1",
						$service_id,
						$transaction_id,
						$type_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['id'];
	}

	public function get_country_code($ArtworkID,$TaskID) {
		$code = "";
		if(!empty($TaskID)) {
			$query = sprintf("SELECT languages.flag
							FROM tasks
							LEFT JOIN languages ON tasks.desiredLanguageID = languages.languageID
							WHERE tasks.taskID = %d
							LIMIT 1",
							$TaskID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				$code = substr($row['flag'],0,2);
			}
		} else {
			$query = sprintf("SELECT languages.flag
							FROM artworks
							LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
							LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
							WHERE artworks.artworkID = %d
							LIMIT 1",
							$ArtworkID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				$code = substr($row['flag'],0,2);
			}
		}
		return $code;
	}

	public function get_font_info($font_id) {
		$query = sprintf("SELECT *
						FROM fonts
						WHERE id = %d
						LIMIT 1",
						$font_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row;
	}

	public function get_font_id($font_family, $font_name, $font_style, $engine_id) {
		$query = sprintf("SELECT id
						FROM fonts
						WHERE family = '%s'
						AND name = '%s'
						AND style = '%s'
						AND engine_id = %d
						LIMIT 1",
						mysql_real_escape_string($font_family),
						mysql_real_escape_string($font_name),
						mysql_real_escape_string($font_style),
						$engine_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		/*
		//update font family name
		if(empty($row['family']) && !empty($font_family)) {
			$query = sprintf("UPDATE fonts SET
							family = '%s'
							WHERE id = %d
							LIMIT 1",
							mysql_real_escape_string($font_family),
							$row['id']);
			$result = mysql_query($query,$this->link) or die(mysql_error());
		}
		//*/
		return $row['id'];
	}

	public function get_sub_font_id($artwork_id, $font_id, $task_id=0) {
		if(empty($task_id)) {
			return $this->get_artwork_sub_font_id($artwork_id,$font_id);
		} else {
			$query = sprintf("SELECT sub_font_id
							FROM task_font_subs
							WHERE task_id = %d
							AND font_id = %d
							LIMIT 1",
							$task_id,
							$font_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				return $row['sub_font_id'];
			} else {
				return $this->get_artwork_sub_font_id($artwork_id,$font_id);
			}
		}
	}

	public function get_artwork_sub_font_id($artwork_id, $font_id) {
		$query = sprintf("SELECT artwork_fonts.font_id, artwork_fonts.sub_font_id,
						fonts.installed
						FROM artwork_fonts
						LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
						WHERE artwork_fonts.artwork_id = %d
						AND artwork_fonts.font_id = %d
						LIMIT 1",
						$artwork_id,
						$font_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		if($row['installed'] && empty($row['sub_font_id'])) {
			return $row['font_id'];
		} else {
			if(!empty($row['sub_font_id'])) {
				return $row['sub_font_id'];
			} else {
				$query = sprintf("SELECT artworks.default_sub_font_id AS artwork_default_sub_font_id,
								campaigns.default_sub_font_id AS campaign_default_sub_font_id,
								systemconfig.default_sub_font_id AS company_default_sub_font_id
								FROM artworks
								LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
								LEFT JOIN users ON users.userID = campaigns.ownerID
								LEFT JOIN systemconfig ON systemconfig.companyID = users.companyID
								WHERE artworks.artworkID = %d
								LIMIT 1",
								$artwork_id);
				$result = mysql_query($query,$this->link) or die(mysql_error());
				if(!mysql_num_rows($result)) return false;
				$row = mysql_fetch_assoc($result);
				if(!empty($row['artwork_default_sub_font_id'])) {
					return $row['artwork_default_sub_font_id'];
				} else {
					if(!empty($row['campaign_default_sub_font_id'])) {
						return $row['campaign_default_sub_font_id'];
					} else {
						if(!empty($row['company_default_sub_font_id'])) {
							return $row['company_default_sub_font_id'];
						} else {
							return DEFAULT_SUB_FONT_ID;
						}
					}
				}
			}
		}
	}

	function get_user_rate($user_id, $source_lang_id, $target_lang_id) {
		$query = sprintf("SELECT userrates.rate, userrates.currencyID, currencies.currencySymbol AS symbol
						FROM userrates
						LEFT JOIN currencies ON userrates.currencyID = currencies.currencyID
						WHERE (
						(userrates.sourceLangID = %d AND userrates.targetLangID = %d)
						OR (userrates.sourceLangID = 0 AND userrates.targetLangID = %d)
						OR (userrates.sourceLangID = %d AND userrates.targetLangID = 0)
						OR (userrates.sourceLangID = 0 AND userrates.targetLangID = 0)
						)
						AND userrates.userID = %d
						ORDER BY
						userrates.targetLangID DESC,
						userrates.sourceLangID DESC",
						$source_lang_id,
						$target_lang_id,
						$target_lang_id,
						$source_lang_id,
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	function get_page_rate($agency_id, $company_id) {
		//Allow Agency to be the Agent.
		#if($agency_id == $company_id) return false;
		$query = sprintf("SELECT currency_id, rate
						FROM page_rates
						WHERE agency_id = %d AND client_id IN (0,%d)
						ORDER BY client_id DESC",
						$agency_id,
						$company_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	function update_task_cost($task_id, $currency_id, $cost) {
		$update = sprintf("UPDATE tasks SET
						currencyID = %d,
						cost = %f
						WHERE taskID = %d",
						$currency_id,
						(float)$cost,
						$task_id);
		mysql_query($update,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	function get_next_proofreaders($task_id, $order=0) {
		if(empty($task_id)) return false;
		$task_info = $this->get_task_info($task_id);
		if($task_info === false) return false;
        
        //insert manager as proofreader if there's no proofreader assigned until this point
        $query = sprintf("SELECT id
						FROM task_proofreaders
						WHERE task_id = %d",
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) {
            $query = sprintf("INSERT INTO task_proofreaders
                            (task_id, user_id, deadline)
                            VALUES
                            (%d, %d, '%s')",
                            $task_id,
                            $task_info['creatorID'],
                            $task_info['deadline']);
            $result = mysql_query($query,$this->link) or die(mysql_error());
        }
        $array = array();
		//get next order
		$query = sprintf("SELECT `order`
						FROM task_proofreaders
						WHERE task_id = %d
						AND `order` >= %d
						AND done = 0
						GROUP BY `order`
						ORDER BY `order` ASC
						LIMIT 1",
						$task_id,
						$order);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return $array;
		$row = mysql_fetch_assoc($result);
		$next_order = $row['order'];
		$query = sprintf("SELECT user_id, deadline
						FROM task_proofreaders
						WHERE task_id = %d
						AND `order` = %d
						AND done = 0
						ORDER BY deadline ASC",
						$task_id,
						$next_order);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return $array;
		while($row = mysql_fetch_assoc($result)) {
			$array[$row['user_id']] = $row['deadline'];
		}
		return $array;
	}

	protected function GetPLByRef($artwork_id, $box_ref, $para_id) {
		$query = sprintf("SELECT paralinks.uID AS PL
						FROM paralinks
						LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						AND boxes.BoxUID = %d
						AND paralinks.ParaID = %d
						LIMIT 1",
						$artwork_id,
						$box_ref,
						$para_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['PL'];
	}

	public function GetPLsByBox($box_id) {
		$PLs = array();
		$query = sprintf("SELECT paralinks.uID
						FROM paralinks
						WHERE BoxID = %d
						AND active = 1
						ORDER BY
						StoryGroup ASC,
						`order` ASC,
						uID ASC",
						$box_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$PLs[] = $row['uID'];
		}
		return $PLs;
	}

	public function UpdatePL($artwork_id, $new_artwork_id, $task_id=0) {
		$query = sprintf("SELECT paralinks.uID, paralinks.ParaID, boxes.BoxUID
						FROM paralinks
						LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						ORDER BY paralinks.uID ASC",
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$PL = $row['uID'];
			$box_ref = $row['BoxUID'];
			$para_id = $row['ParaID'];
			$new_PL = $this->GetPLByRef($new_artwork_id,$box_ref,$para_id);
			if($new_PL===false) continue;
			//update paraedit
			$update = sprintf("UPDATE paraedit SET
							pl_id = %d
							WHERE pl_id = %d
							AND task_id = %d",
							$new_PL,
							$PL,
							$task_id);
			mysql_query($update,$this->link) or die(mysql_error());
			//update paratrans
			$update = sprintf("UPDATE paratrans SET
							ParalinkID = %d
							WHERE ParalinkID = %d
							AND taskID = %d",
							$new_PL,
							$PL,
							$task_id);
			mysql_query($update,$this->link) or die(mysql_error());
			//update comments
			$update = sprintf("UPDATE comments SET
							artwork_id = %d
							WHERE artwork_id = %d
							AND task_id = %d",
							$new_artwork_id,
							$artwork_id,
							$task_id);
			mysql_query($update,$this->link) or die(mysql_error());
			//update img_usage
			$update = sprintf("UPDATE img_usage SET
							artwork_id = %d
							WHERE artwork_id = %d
							AND task_id = %d",
							$new_artwork_id,
							$artwork_id,
							$task_id);
			mysql_query($update,$this->link) or die(mysql_error());
		}
		return true;
	}

	public function LastUpdateCampaign($campaign_id) {
		$update = sprintf("UPDATE campaigns SET lastEdit = NOW() WHERE campaignID = %d",$campaign_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function LastUpdateArtwork($artwork_id) {
		$query = sprintf("SELECT campaignID FROM artworks WHERE artworkID = %d",$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$campaign_id = $row['campaignID'];
		$update = sprintf("UPDATE artworks SET lastUpdate = NOW() WHERE artworkID = %d",$artwork_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return $this->LastUpdateCampaign($campaign_id);
	}

	public function reset_page($artwork_id, $page_id) {
		$query = sprintf("SELECT uID
						FROM pages
						WHERE uID = %d
						AND ArtworkID = %d
						LIMIT 1",
						$page_id,
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(mysql_num_rows($result)) {
			return $page_id;
		} else {
			return 0;
		}
	}

	public function reset_layer($artwork_id, $layer_id) {
		$query = sprintf("SELECT id
						FROM artwork_layers
						WHERE id = %d
						AND artwork_id = %d
						LIMIT 1",
						$layer_id,
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(mysql_num_rows($result)) {
			return $layer_id;
		} else {
			return 0;
		}
	}

	public function edit_user_account($acl_api, $user_id, $username, $password, $company_id, $ug_id, $active, $agent, $global, $allowance) {
		$global = isset($global) ? $global : $company_id;
		$agent = empty($agent) ? 0 : $agent;
		$active = empty($active) ? 0 : $active;
		$update = sprintf("UPDATE users SET
						password = '%s',
						companyID = %d,
						userGroupID = %d,
						vtID = %d,
						agent = %d,
						allowance = %d,
						active = %d
						WHERE userID = %d",
						mysql_real_escape_string($password),
						$company_id,
						$ug_id,
						$global,
						$agent,
						$allowance,
						$active,
						$user_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//prep for 'aro' update
		$query = sprintf("SELECT id, order_value
							FROM aro
							WHERE value = %d
							LIMIT 1",
							$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		//update companyID - `aro` table
		$acl_api->edit_object($row['id'], $company_id, $username, $user_id, $row['order_value'], 0, 'ARO');
		//update usergroup - `groups_aro_map` table
		$update = sprintf("UPDATE groups_aro_map SET
						group_id = %d
						WHERE aro_id = %d",
						$ug_id,
						$row['id']);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return true;
	}

	public function edit_user_profile($user_id, $forename, $surname, $email, $telephone, $fax, $mobile, $lang_id, $defaultLangID) {
		$query = sprintf("UPDATE users
						SET forename = '%s',
						surname = '%s',
						email = '%s',
						telephone = '%s',
						fax = '%s',
						mobile = '%s',
						langID = %d,
						defaultLangID = %d
						WHERE userID = %d",
						mysql_real_escape_string($forename),
						mysql_real_escape_string($surname),
						mysql_real_escape_string($email),
						mysql_real_escape_string($telephone),
						mysql_real_escape_string($fax),
						mysql_real_escape_string($mobile),
						$lang_id,
						$defaultLangID,
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function edit_user_lang($user_id, $langs, $pros, $deletes, $new_langs, $new_pros) {
		if(!empty($langs)) {
			foreach($langs as $id=>$lang_id) {
				if(!empty($deletes) && array_key_exists($id,$deletes)) {
					$query = sprintf("DELETE FROM userlanguages
									WHERE indexID = %d",
									$id);
				} else {
					$query = sprintf("UPDATE userlanguages SET
									languageID = %d,
									proID = %d
									WHERE indexID = %d",
									$lang_id,
									$pros[$id],
									$id);
				}
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		}
		if(!empty($new_langs)) {
			foreach($new_langs as $k=>$v) {
				if(empty($v) || empty($new_pros[$k]) || in_array($v,$langs)) continue;
				$query = sprintf("INSERT INTO userlanguages
								(userID, languageID, proID)
								VALUES
								(%d, %d, %d)",
								$user_id,
								$v,
								$new_pros[$k]);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		}
	}

	public function edit_user_rate($user_id, $source_langs, $target_langs, $currencies, $rates, $preferences, $deletes, $new_source_langs, $new_target_langs, $new_currencies, $new_rates, $new_preferences) {
		if(!empty($source_langs)) {
			foreach($source_langs as $id=>$source_lang) {
				if(!empty($deletes) && array_key_exists($id,$deletes)) {
					$query = sprintf("DELETE FROM userrates
									WHERE indexID = %d",
									$id);
				} else {
					$query = sprintf("UPDATE userrates SET
									sourceLangID = %d,
									targetLangID = %d,
									currencyID = %d,
									rate = %f,
									preference = %d
									WHERE indexID = %d",
									$source_lang,
									$target_langs[$id],
									$currencies[$id],
									$rates[$id],
									$preferences[$id],
									$id);
				}
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		}
		if(!empty($new_source_langs)) {
			foreach($new_source_langs as $k=>$v) {
				if(empty($new_rates[$k]) || (in_array($v,$source_langs) && in_array($new_target_langs[$k],$target_langs))) continue;
				$query = sprintf("INSERT INTO userrates
								(userID, sourceLangID, targetLangID, currencyID, rate, preference)
								VALUES
								(%d, %d, %d, %d, %f, %d)",
								$user_id,
								$v,
								$new_target_langs[$k],
								$new_currencies[$k],
								$new_rates[$k],
								$new_preferences[$k]);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		}
	}

	public function edit_user_spec($user_id, $subject_ids) {
		$query = sprintf("DELETE FROM userspecs
						WHERE userID = %d",
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!empty($subject_ids)) {
			foreach($subject_ids as $subject_id) {
				$query = sprintf("INSERT INTO userspecs
								(userID, subjectID)
								VALUES
								(%d, %d)",
								$user_id,
								$subject_id);
				$result = mysql_query($query,$this->link) or die(mysql_error());
			}
		}
	}

	public function edit_user_acl($acl_api, $user_id, $company_id, $aco_ids, $reset = false) {
		$query = sprintf("SELECT id, section_value, value
						FROM aco
						WHERE hidden = 0
						ORDER BY
						section_value ASC,
						order_value ASC");
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$aco_array = array($row['section_value'] => array($row['value']));
			$aro_array = array($company_id => array($user_id));
			$aro_group_ids = NULL;
			$axo_array = NULL;
			$axo_group_ids = NULL;
			if(!empty($aco_ids) && in_array($row['id'],$aco_ids)) {
				$allow = 1;
			} else {
				$allow = 0;
			}
			$enabled = 1;
			$return_value = NULL;
			$note = NULL;
			$section_value = $company_id;
			$acl_ids = $acl_api->search_acl($row['section_value'],$row['value'],$company_id,$user_id);
			if(count($acl_ids)) {
				foreach($acl_ids as $acl_id) {
					//just in case there are multiple acl entries
					$acl_api->edit_acl($acl_id, $aco_array, $aro_array, $aro_group_ids, $axo_array, $axo_group_ids, $allow, $enabled, $return_value, $note, $section_value);
				}
			} else {
				$acl_api->add_acl($aco_array, $aro_array, $aro_group_ids, $axo_array, $axo_group_ids, $allow, $enabled, $return_value, $note, $section_value);
			}
		}
		if($reset) {
			$acl_ids = $acl_api->search_acl(FALSE,FALSE,$company_id,$user_id);
			if(count($acl_ids)) {
				foreach($acl_ids as $acl_id) {
					$acl_api->del_acl($acl_id);
				}
			}
		}
	}

	public function refresh_signoff_report($company_id, $start_year, $start_month, $start_day, $end_year, $end_month, $end_day) {
		//delete cache
		$query = sprintf("DELETE FROM signoff_report_cache
						WHERE company_id = %d
						AND date >= '%d-%d-%d'
						AND date <= '%d-%d-%d'",
						$company_id,
						$start_year,
						$start_month,
						$start_day,
						$end_year,
						$end_month,
						$end_day);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		//rebuild cache
		$query = sprintf("SELECT signoff_reports.time, signoff_reports.pages,
						tasks.tmWords, (tasks.userWords+tasks.tmWords+tasks.missingWords) AS totalWords
						FROM signoff_reports
						LEFT JOIN users ON signoff_reports.user_id = users.userID
						LEFT JOIN tasks ON signoff_reports.task_id = tasks.taskID
						WHERE users.companyID = %d
						AND UNIX_TIMESTAMP(signoff_reports.time) >= %d
						AND UNIX_TIMESTAMP(signoff_reports.time) <= %d",
						$company_id,
						mktime(0,0,0,$start_month,$start_day,$start_year),
						mktime(23,59,59,$end_month,$end_day,$end_year));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			//get cost per page based on date
			$cost = 0;
			$query_cost = sprintf("SELECT page_rates.rate,
									currencies.currencyAb
									FROM page_rates
									LEFT JOIN currencies ON page_rates.currency_id = currencies.currencyID
									WHERE page_rates.agency_id = %d
									AND page_rates.client_id IN (0,%d)
									AND UNIX_TIMESTAMP(page_rates.date) <= %d
									ORDER BY
									page_rates.client_id DESC,
									page_rates.date DESC
									LIMIT 1",
									ADMIN_COMPANYID,
									$company_id,
									strtotime($row['time']));
			$result_cost = mysql_query($query_cost,$this->link) or die(mysql_error());
			if(mysql_num_rows($result_cost)) {
				$row_cost = mysql_fetch_assoc($result_cost);
				$cost = $row_cost['currencyAb']==CURRENCY_AB ? $row_cost['rate']*$row['pages'] : XeConvert($row_cost['rate']*$row['pages'], $row_cost['currencyAb'], CURRENCY_AB);
			}

			//update cache
			$year = date("Y",strtotime($row['time']));
			$month = date("n",strtotime($row['time']));
			$day = date("j",strtotime($row['time']));
			$query_search = sprintf("SELECT id
									FROM signoff_report_cache
									WHERE company_id = %d
									AND date = '%d-%d-%d'",
									$company_id,
									$year,
									$month,
									$day);
			$result_search = mysql_query($query_search,$this->link) or die(mysql_error());
			if(mysql_num_rows($result_search)==1) {
				$row_search = mysql_fetch_assoc($result_search);
				$query_update = sprintf("UPDATE signoff_report_cache
										SET pages = pages + %d,
										words_tm = words_tm + %d,
										words_total = words_total + %d,
										cost = cost + %d
										WHERE id = %d",
										$row['pages'],
										$row['tmWords'],
										$row['totalWords'],
										$cost,
										$row_search['id']);
				$result_update = mysql_query($query_update,$this->link) or die(mysql_error());
			} else {
				$query_update = sprintf("INSERT INTO signoff_report_cache
										(company_id, date, pages, words_tm, words_total, cost)
										VALUES
										(%d, '%d-%d-%d', %d, %d, %d, %d)",
										$company_id,
										$year,
										$month,
										$day,
										$row['pages'],
										$row['tmWords'],
										$row['totalWords'],
										$cost);
				$result_update = mysql_query($query_update,$this->link) or die(mysql_error());
			}
		}
	}

	public function log_signoff_report($user_id, $pages, $task_id) {
		$update = sprintf("INSERT INTO signoff_reports (user_id, time, pages, task_id)
							VALUES (%d, NOW(), %d, %d)",
							$user_id,
							$pages,
							$task_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}

	public function LogTaskAction($taskID, $userID, $action="", $ref="") {
		$update = sprintf("INSERT INTO tasklog (taskID, userID, action, time, reference)
							VALUES (%d, %d, '%s', NOW(), '%s')",
							$taskID,
							$userID,
							mysql_real_escape_string($action),
							mysql_real_escape_string($ref));
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return $this->LogSystemEvent($userID,$action,0,0,$taskID);
	}

	public function LogSystemEvent($userID, $action="", $campaignID=0, $artworkID=0, $taskID=0) {
		if(!empty($taskID)) {
			$task_info = $this->get_task_info($taskID);
			if($task_info !== false) {
				$artworkID = $task_info['artworkID'];
				$campaignID = $task_info['campaignID'];
			}
		}
		if(!empty($artworkID)) {
			$artwork_info = $this->get_artwork_info($artworkID);
			if($artwork_info !== false) {
				$campaignID = $artwork_info['campaignID'];
			}
		}
		$update = sprintf("INSERT INTO systemlog
						(userID, campaignID, artworkID, taskID, action, time)
						VALUES
						(%d, %d, %d, %d, '%s', NOW())",
						$userID,
						$campaignID,
						$artworkID,
						$taskID,
						mysql_real_escape_string($action));
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}

	public function TrashMsg($msgID) {
		$query = sprintf("SELECT senderID, receiverID
							FROM messages
							WHERE messageID = %d
							LIMIT 1",
							$msgID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			if($row['senderID']==$_SESSION['userID']) {
				$update = sprintf("UPDATE messages
									SET senderSideStatus = 0
									WHERE messageID = %d",
									$msgID);
				$result = mysql_query($update,$this->link) or die(mysql_error());
			}
			if($row['receiverID']==$_SESSION['userID']) {
				$update = sprintf("UPDATE messages
									SET receiverSideStatus = 0
									WHERE messageID = %d",
									$msgID);
				$result = mysql_query($update,$this->link) or die(mysql_error());
			}
		}
	}

	public function RestoreMsg($msgID) {
		$query = sprintf("SELECT senderID, receiverID
							FROM messages
							WHERE messageID = %d
							LIMIT 1",
							$msgID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			if($row['senderID']==$_SESSION['userID']) {
				$update = sprintf("UPDATE messages
									SET senderSideStatus = 1
									WHERE messageID = %d",
									$msgID);
				$result = mysql_query($update,$this->link) or die(mysql_error());
			}
			if($row['receiverID']==$_SESSION['userID']) {
				$update = sprintf("UPDATE messages
									SET receiverSideStatus = 1
									WHERE messageID = %d",
									$msgID);
				$result = mysql_query($update,$this->link) or die(mysql_error());
			}
		}
	}

	public function DeleteFTP($ftp_id) {
		$update = sprintf("DELETE FROM ftps WHERE id = %d", $ftp_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$this->LogSystemEvent($_SESSION['userID'],"deleted ftp [$ftp_id]");
	}

	public function DeletePara($paraID, $force=false) {
		if($force==true) {
			$confirm = true;
		} else {
			$query = sprintf("SELECT BoxID
							FROM paralinks
							WHERE ParaID = %d",
							$paraID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			$paralink_safe = (mysql_num_rows($result)==1);
			//need to review and restrict paraset more
			$query = sprintf("SELECT ParaGroup
							FROM paraset
							WHERE ParaID = %d",
							$paraID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			$paraset_safe = (mysql_num_rows($result)==1);
			$confirm = $paralink_safe && $paraset_safe;
		}
		if($confirm) {
			//delete paratrans
			$update = sprintf("DELETE FROM paratrans WHERE transParaID = %d", $paraID);
			$result = mysql_query($update,$this->link) or die(mysql_error());
			//delete paraset
			$update = sprintf("DELETE FROM paraset WHERE ParaID = %d", $paraID);
			$result = mysql_query($update,$this->link) or die(mysql_error());
			//paralinks records are deleted while deleting boxes
			//delete paragraphs
			$update = sprintf("DELETE FROM paragraphs WHERE uID = %d", $paraID);
			$result = mysql_query($update,$this->link) or die(mysql_error());
			$this->LogSystemEvent($_SESSION['userID'],"deleted paragraph [$paraID]");
		}
	}

	public function TenderTask($taskTypeID, $artworkID, $paraID="", $targetLangID, $translatorIDs, $proofreaderIDs, $deadline, $brief="", $attachment="", $trial=0, $start=true, $notes="",$storyGroup=0) {
		$art_query = sprintf("SELECT artworks.wordCount, campaigns.sourceLanguageID
								FROM artworks
								LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
								WHERE artworks.artworkID = %d
								LIMIT 1",
								$artworkID);
		$art_result = mysql_query($art_query,$this->link) or die(mysql_error());
		if(mysql_num_rows($art_result)) {
			$art_row = mysql_fetch_assoc($art_result);
			$souceLangID = $art_row['sourceLanguageID'];
			$has_sample = false;
			foreach($translatorIDs as $translatorID=>$tdeadline) {
				$vCode = md5(rand(0,9)).md5(rand(0,99)).md5(rand(0,999));
				$update = sprintf("INSERT INTO tasks
                                        (taskTypeID, desiredLanguageID, translatorID, tdeadline, attachment, artworkID, paraID, creatorID, lastUpdate,
                                        deadline, taskStatus, vCode, brief, serviceCurrencyID, serviceCharge, trial, notes, artwork_storygroup_id)
                                        VALUES
                                        (%d, %d, %d, %d, '%s', '%s', %d, %d, NOW(), '%s', 7, '%s', '%s', %d, %f, %d, '%s',%d)",
                                        $taskTypeID,
                                        $targetLangID,
                                        $translatorID,
                                        !empty($tdeadline) ? "'".mysql_real_escape_string($tdeadline)."'" : "NULL",
                                        mysql_real_escape_string($attachment),
                                        $artworkID,
                                        $paraID,
                                        $_SESSION['userID'],
                                        mysql_real_escape_string($deadline),
                                        $vCode,
                                        mysql_real_escape_string($brief),
                                        CURRENCY,
                                        0,
                                        $trial,
                                        mysql_real_escape_string($notes),
                                        $storyGroup
                                        );
                                        //die($update);
				$result = mysql_query($update,$this->link);
				$taskID = mysql_insert_id($this->link);
				
				require_once(CLASSES."translator.php");
				$Translator = new Translator();
				$missing_words = $Translator->CheckProgress($taskID);
				$rate = 0;
				$currencyID = CURRENCY;
				$rate_info = $this->get_user_rate($translatorID,$souceLangID,$targetLangID);
				if($rate_info !== false) {
					$rate = $rate_info['rate'];
					$currencyID = $rate_info['currencyID'];
				}
				$cost = $rate * $missing_words;
				$this->update_task_cost($taskID,$currencyID,$cost);
				
				$proofs = "";
				foreach($proofreaderIDs as $proofreaderID=>$details) {
					$proofs .= sprintf("(%d, %d, %d, %s),",
                                                    $taskID,
                                                    $proofreaderID,
                                                    $details['order'],
                                                    !empty($details['deadline']) ? "'".mysql_real_escape_string($details['deadline'])."'" : "NULL");
				}
				$proofs = trim($proofs,",");
				if(!empty($proofs)) {
					$update = sprintf("INSERT INTO task_proofreaders (task_id, user_id, `order`, deadline) VALUES %s", $proofs);
					$result = mysql_query($update,$this->link) or die(mysql_error());
				}

				if($has_sample === false) {
					require_once('download.php');
					$sample = GetExportFile($artworkID,$taskID,MAIL_ATTCHMENT_PROCESS,MAIL_ATTCHMENT_SAMPLE_LINES);
					$sample = ROOT.TMP_DIR.$sample;
					$has_sample = true;
				}

				$this->LogTaskAction($taskID,$_SESSION['userID'],"Started Tendering Translators");
				$this->RebuildPageThumbnail(POSTVIEW_DIR,$artworkID,0,$taskID);
				
				if($start) {
					$this->StartTask($taskID,$Service);
				} else {
					$query = sprintf("SELECT tasks.brief, tasks.notes, tasks.tdeadline, tasks.vCode, tasks.attachment, tasks.tdeadline,
									artworks.artworkName, artworks.fileName, artworks.wordCount,
									L1.languageName AS sourceLanguage,
									L2.languageName AS desiredLanguage,
									users.forename, users.surname, users.email
									FROM tasks
									LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
									LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
									LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
									LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
									LEFT JOIN users ON tasks.translatorID = users.userID
									WHERE taskID = %d
									LIMIT 1",
									$taskID);
					$result = mysql_query($query,$this->link) or die(mysql_error());
					if(mysql_num_rows($result)) {
						$row = mysql_fetch_assoc($result);
						$notes = empty($row['notes']) ? "Not Specified" : $row['notes'] ;
						$brief = empty($row['brief']) ? "Not Specified" : $row['brief'] ;
						$body = "******FOR TENDERING PURPOSE ONLY******";
						$body .= "\n\n\nDear ".$row['forename']." ".$row['surname'].",";
						$body .= "\n\nThis is a potential job opportunity to take the translation task at ".SYSTEM_NAME.". We are currently farming translators for the following job:";
						$body .= "\n\nArtwork Title: ".$row['artworkName'];
						$body .= "\nLanguages: ".$row['sourceLanguage']." -> ".$row['desiredLanguage'];
						$body .= "\nTotal Word Count: ".$row['wordCount'];
						$body .= "\nWords to be Translated: $missing_words";
						$body .= "\nProjected Price: $cost";
						$body .= "\nDeadline: ".$row['tdeadline'];
                        $body .= "\n\nNotes:";
						$body .= "\n---------------------------------------------------------------------------------------------------------";
						$body .= "\n".$notes;
						$body .= "\n---------------------------------------------------------------------------------------------------------";
						$body .= "\n\nJob Brief:";
						$body .= "\n---------------------------------------------------------------------------------------------------------";
						$body .= "\n".$brief;
						$body .= "\n---------------------------------------------------------------------------------------------------------";
						$body .= "\n\nPlease find the attached file for translation samples. A copy of the PDF version of the artwork can be downloaded at:";
						$body .= "\n\n".SITE_URL."download.php?File=".BareFilename($row['fileName']).".pdf&SaveAs=".$row['artworkName'].".pdf";
						$body .= "\n\nIf you are any interested in doing this job, please click on the link below to be shortlisted and send us back with the translated sample ASAP. Once qualified, we will contact you soon in writing with further notice.";
						$body .= "\n\n".SITE_URL."shortlist.php?vCode=".$row['vCode'];
						$body .= "\n\nAlternatively, you may decline this job by clicking on the link below:";
						$body .= "\n\n".SITE_URL."decline.php?vCode=".$row['vCode'];
						$body .= "\n\nKind Regards,";
						$body .= "\n\n".COMPANY_NAME;
						$name = $row['forename']." ".$row['surname'];
						$address = $row['email'];
						$subject = SYSTEM_NAME.": New Task Tendering";
						$attachments = array();
						$attachments[] = $sample;
						if(!empty($attachment)) {
							$attachments[] = REPOSITORY_DIR.$attachment;
						}
						require_once(CLASSES.'Mailer.php');
						$mailer = new Mailer();
						$mailer->send_mail($name,$address,$subject,$body,array(),$attachments);
					}
				}
			}
			@unlink($sample);
		}
	}

	public function TenderAgency($artworkID, $targetLangID, $agencyID, $agentID, $deadline, $brief, $attachment="", $trial=0, $mailagent=1) {
		$art_query = sprintf("SELECT pageCount
								FROM artworks
								WHERE artworkID = %d
								LIMIT 1",
								$artworkID);
		$art_result = mysql_query($art_query,$this->link) or die(mysql_error());
		if(mysql_num_rows($art_result)) {
			$art_row = mysql_fetch_assoc($art_result);
			$pages = $art_row['pageCount'];
			$serviceCurrencyID = CURRENCY;
			$serviceCharge = 0;
			$rate_info = $this->get_page_rate($agencyID,$_SESSION['companyID']);
			if($rate_info !== false) {
				$serviceCurrencyID = $rate_info['currency_id'];
				$serviceCharge = $rate_info['rate'];
			}
			$update = sprintf("INSERT INTO tasks
							(desiredLanguageID, artworkID, creatorID, agentID, lastUpdate, deadline, taskStatus, brief, attachment, serviceCurrencyID, serviceCharge, trial)
							VALUES
							(%d, %d, %d, %d, NOW(), '%s', 7, '%s', '%s', %d, %f, %d)",
							$targetLangID,
							$artworkID,
							$_SESSION['userID'],
							$agentID,
							mysql_real_escape_string($deadline),
							mysql_real_escape_string($brief),
							mysql_real_escape_string($attachment),
							$serviceCurrencyID,
							$serviceCharge*$pages,
							$trial);
			$result = mysql_query($update,$this->link) or die(mysql_error());
			$taskID = mysql_insert_id($this->link);

			$this->LogTaskAction($taskID,$_SESSION['userID'],"Started Tendering Agency");

			require_once(CLASSES."translator.php");
			$Translator = new Translator();
			$Translator->CheckProgress($taskID);
			$this->RebuildPageThumbnail(POSTVIEW_DIR,$artworkID,0,$taskID);

			if($mailagent) {
				$query_farmRs = sprintf("SELECT *, L1.languageName AS sourceLanguage, L2.languageName AS desiredLanguage
										FROM tasks
										LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
										LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
										LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
										LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
										LEFT JOIN users ON tasks.agentID = users.userID
										WHERE taskID = %d
										LIMIT 1",
										$taskID);
				$farmRs = mysql_query($query_farmRs,$this->link) or die(mysql_error());
				if(mysql_num_rows($farmRs)) {
					$row_farmRs = mysql_fetch_assoc($farmRs);
					$brief = empty($row_farmRs['brief']) ? "Not Specified" : $row_farmRs['brief'] ;
					$body = "******TASK DELEGATION******";
					$body .= "\n\n\nDear ".$row_farmRs['forename']." ".$row_farmRs['surname'].",";
					$body .= "\n\nYou have been delegated the following translation job at ".SYSTEM_NAME.":";
					$body .= "\n\nArtwork Title: ".$row_farmRs['artworkName'];
					$body .= "\nLanguages: ".$row_farmRs['sourceLanguage']." -> ".$row_farmRs['desiredLanguage'];
					$body .= "\nWord Count: ".$row_farmRs['wordCount'];
					$body .= "\nDeadline: ".$row_farmRs['deadline'];
					$body .= "\n\nJob Brief:";
					$body .= "\n---------------------------------------------------------------------------------------------------------";
					$body .= "\n".$brief;
					$body .= "\n---------------------------------------------------------------------------------------------------------";
					$body .= "\n\nTo access your task online, please click on the link below:";
					$body .= "\n\n".SITE_URL."index.php?layout=task&id=".$taskID;
					$body .= "\n\nAlternatively, you may decline this job by clicking on the link below:";
					$body .= "\n\n".SITE_URL."decline.php?vCode=".$row_farmRs['vCode'];
					$body .= "\n\nKind Regards,";
					$body .= "\n\n".COMPANY_NAME;
					$name = $row_farmRs['forename']." ".$row_farmRs['surname'];
					$address = $row_farmRs['email'];
					$subject = SYSTEM_NAME.": New Task Delegation";
					require_once('download.php');
					$file = GetExportFile($artworkID,$taskID,MAIL_ATTCHMENT_PROCESS);
					$file = ROOT.TMP_DIR.$file;
					$attachments = array($file);
					if(!empty($attachment)) {
						$attachments[] = REPOSITORY_DIR.$attachment;
					}
					require_once(CLASSES.'Mailer.php');
					$mailer = new Mailer();
					$mailer->send_mail($name,$address,$subject,$body,array(),$attachments);
					@unlink($file);
				}
			}
		}
	}

	public function EditTask($taskID, $tdeadline, $proofreaders_info=array(), $deadline="", $brief="", $attachment="", $trial=0) {
		$sub = sprintf("`trial` = %d", $trial);
		$sub .= !empty($tdeadline) ? sprintf(", `tdeadline` = '%s'",mysql_real_escape_string($tdeadline)) : "";
		$sub .= !empty($deadline) ? sprintf(", `deadline` = '%s'",mysql_real_escape_string($deadline)) : "";
		$sub .= !empty($brief) ? sprintf(", `brief` = '%s'",mysql_real_escape_string($brief)) : "";
		$sub .= !empty($attachment) ? sprintf(", `attachment` = '%s'",mysql_real_escape_string($attachment)) : "";
		$update = sprintf("UPDATE `tasks` SET
						%s
						WHERE taskID = %d",
						$sub,
						$taskID);
		$result = mysql_query($update,$this->link) or die($update.mysql_error());
		foreach($proofreaders_info as $id=>$info) {
			$update = sprintf("UPDATE `task_proofreaders` SET
							`order` = %d,
							`deadline` = %s,
							`done` = %d
							WHERE id = %d",
							$info['order'],
							!empty($info['deadline']) ? "'".mysql_real_escape_string($info['deadline'])."'" : "NULL",
							$info['done'],
							$id);
			$result = mysql_query($update,$this->link) or die($update.mysql_error());
		}
		$this->StartTask($taskID);
	}
	
	public function StartTask($taskID, $Service=NULL) {
		$query = sprintf("SELECT tasks.artworkID, tasks.taskStatus, tasks.deadline, tasks.translatorID, tasks.tdeadline, tasks.vCode,
							tasks.desiredLanguageID, tasks.notes, tasks.brief, tasks.attachment, tasks.missingWords, tasks.cost,
							artworks.artworkName, artworks.wordCount,
							L1.languageName AS sourceLanguage, L1.flag AS sourceFlag,
							L2.languageName AS desiredLanguage, L2.flag AS desiredFlag,
							users.forename, users.surname, users.email
							FROM tasks
							LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
							LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
							LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
							LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
							LEFT JOIN users ON tasks.translatorID = users.userID
							WHERE tasks.taskID = %d
							LIMIT 1",
							$taskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			$artworkID = $row['artworkID'];
			$notes = empty($row['notes']) ? "Not Specified" : $row['notes'] ;
			$brief = empty($row['brief']) ? "Not Specified" : $row['brief'] ;
			if($row['taskStatus']<8) {
				//use google bot
				if($row['translatorID']==GOOGLE_BOT_ID) {
					$Translator = new Translator();
					//prep paragraphs
					$para_result = $Translator->get_all_paras($artworkID,$taskID);
					while($para_row = mysql_fetch_assoc($para_result)){
						$para = $Translator->GetParaByPL($para_row['PL']);
						if($para === false) continue;
						$paras[] = $para['ParaText'];
					}

					//fetch google translation and store in TM cache
					$MT = new Google();
					$trans = $MT->MassMT($paras,BareFilename($row['sourceFlag']),BareFilename($row['desiredFlag']));
					if(!empty($trans)) {
						$MT->InsertMTCache($paras,BareFilename($row['sourceFlag']),BareFilename($row['desiredFlag']),$trans);
					}

					//assign cache to TM
					$para_result = $Translator->get_all_paras($artworkID,$taskID);
					$MT = new Google();
					while($para_row = mysql_fetch_assoc($para_result)){
						$para = $Translator->GetParaByPL($para_row['PL']);
						if($para === false) continue;
						$paras[] = $para['ParaText'];

						$translatedText = $MT->GetPureMT($para_row['ParaText'],BareFilename($row['sourceFlag']),BareFilename($row['desiredFlag']));
						if(!empty($translatedText)) {
							$Translator->AddTranslatedPara($translatedText,$row['desiredLanguageID'],$para_row['ParaGroup'],$para_row['PL'],$taskID,$_SESSION['userID'],PARA_GOOGLE,0,0);
						}
					}
				} else {
					//send email
					$body = "Dear ".$row['forename']." ".$row['surname'].",";
					$body .= "\n\nYou have been assigned a new translation task at ".SYSTEM_NAME.".";
					$body .= "\n\nArtwork Title: ".$row['artworkName'];
					$body .= "\nLanguages: ".$row['sourceLanguage']." -> ".$row['desiredLanguage'];
					$body .= "\nWord Count: ".$row['wordCount'];
					$body .= "\nWords to be Translated: ".$row['missingWords'];
					$body .= "\nProjected Price: ".$row['cost'];
					$body .= "\nDeadline: ".$row['tdeadline'];
                    $body .= "\n\nNotes:";
					$body .= "\n---------------------------------------------------------------------------------------------------------";
					$body .= "\n".$notes;
					$body .= "\n---------------------------------------------------------------------------------------------------------";
					$body .= "\n\nJob Brief:";
					$body .= "\n---------------------------------------------------------------------------------------------------------";
					$body .= "\n".$brief;
					$body .= "\n---------------------------------------------------------------------------------------------------------";
					$body .= "\n\nPlease find the attached file for offline translation. To access your task online, please click on the link below:";
					$body .= "\n\n".SITE_URL."index.php?layout=task&id=".$taskID;
					$body .= "\n\nAlternatively, you may decline this job by clicking on the link below:";
					$body .= "\n\n".SITE_URL."decline.php?vCode=".$row['vCode'];
					$body .= "\n\nKind Regards,";
					$body .= "\n\n".COMPANY_NAME;
					$name = $row['forename']." ".$row['surname'];
					$address = $row['email'];
					$subject = SYSTEM_NAME.": New Translation Task Notification";
					require_once('download.php');
					$file = GetExportFile($artworkID,$taskID,MAIL_ATTCHMENT_PROCESS);
					$attachments = array();
					$attachments[] = ROOT.TMP_DIR.$file;
					if(!empty($row['attachment'])) {
						$attachments[] = REPOSITORY_DIR.$row['attachment'];
					}
					require_once(CLASSES.'Mailer.php');
					$mailer = new Mailer();
					$mailer->send_mail($name,$address,$subject,$body,array(),$attachments);
					@unlink($attachment);
				}
                
                //rebuild page preview
                if(is_null($Service)) {
                    require_once(CLASSES."services.php");
                    $Service = new EngineService($artworkID);
                }
                if($Service->IsServerRunning(10)) $Service->RebuildFile($artworkID,$taskID,0,ROOT.POSTVIEW_DIR, "JPG",0);
                
				$update = sprintf("UPDATE tasks SET
									taskStatus = 6
									WHERE taskID = %d",
									$taskID);
				$result = mysql_query($update,$this->link) or die(mysql_error());
				$this->LogTaskAction($taskID,$_SESSION['userID'],"Assigned Task","Start Task");
			}
		}
	}

	public function PauseTask($taskID) {
		$query = sprintf("SELECT tasks.artworkID, tasks.taskStatus, tasks.tdeadline, tasks.vCode, 
							artworks.artworkName, artworks.wordCount,
							L1.languageName AS sourceLanguage,
							L2.languageName AS desiredLanguage,
							users.forename, users.surname, users.email
							FROM tasks
							LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
							LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
							LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
							LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
							LEFT JOIN users ON tasks.translatorID = users.userID
							WHERE tasks.taskID = %d
							LIMIT 1",
							$taskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			$artworkID = $row['artworkID'];
			if($row['taskStatus']!=7) {
				$update = sprintf("UPDATE tasks SET
									taskStatus = 7
									WHERE taskID = %d",
									$taskID);
				$result = mysql_query($update,$this->link) or die(mysql_error());
				//send email
				$body = "Dear ".$row['forename']." ".$row['surname'].",";
				$body .= "\n\nThe following translation task at ".SYSTEM_NAME." have been paused.";
				$body .= "\n\nArtwork Title: ".$row['artworkName'];
				$body .= "\nLanguages: ".$row['sourceLanguage']." -> ".$row['desiredLanguage'];
				$body .= "\nWord Count: ".$row['wordCount'];
				$body .= "\nDeadline: ".$row['tdeadline'];
				$body .= "\n\nAlternatively, you may decline this job by clicking on the link below:";
				$body .= "\n\n".SITE_URL."decline.php?vCode=".$row['vCode'];
				$body .= "\n\nKind Regards,";
				$body .= "\n\n".COMPANY_NAME;
				$name = $row['forename']." ".$row['surname'];
				$address = $row['email'];
				$subject = SYSTEM_NAME.": Translation Task Pause Notification";
				require_once(CLASSES.'Mailer.php');
				$mailer = new Mailer();
				$mailer->send_mail($name,$address,$subject,$body);
				$this->LogTaskAction($taskID,$_SESSION['userID'],"Paused Task","Pause Task");
			}
		}
	}

	public function send_exported_file($sender, $emails, $brief, $file) {
		$emails = explode(",",$emails);
		foreach($emails as $email) {
			$body = "FAO: $email";
			$body .= "\n\n$sender has sent a file from ".SYSTEM_NAME.". Please find the attached.";
			$body .= "\n\nJob Brief:";
			$body .= "\n---------------------------------------------------------------------------------------------------------";
			$body .= "\n".$brief;
			$body .= "\n---------------------------------------------------------------------------------------------------------";
			$body .= "\n\nKind Regards,";
			$body .= "\n\n".COMPANY_NAME;
			$subject = SYSTEM_NAME.": File Notification";
			require_once(CLASSES.'Mailer.php');
			$mailer = new Mailer();
			$mailer->send_mail($email,$email,$subject,$body,array(),array($file));
		}
	}

	public function DeleteTask($taskID) {
		//delete postviews
		$query = sprintf("SELECT pages.PreviewFile, artworks.fileName
							FROM pages
							LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
							INNER JOIN tasks ON artworks.artworkID = tasks.artworkID
							WHERE tasks.taskID = %d",
							$taskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			@unlink(UPLOAD_DIR.BareFilename($row['fileName'])."-".$taskID.".xml");
			@unlink(ROOT.POSTVIEW_DIR.BareFilename($row['PreviewFile'])."-".$taskID.".jpg");
			@unlink(ROOT.POSTVIEW_DIR.THUMBNAILS_DIR.BareFilename($row['PreviewFile'])."-".$taskID.".jpg");
			@unlink(ROOT.POSTVIEW_DIR.EDITS_DIR.BareFilename($row['PreviewFile'])."-".$taskID.".jpg");
		}
		//delete comments
		$update = sprintf("DELETE FROM comments WHERE task_id = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete paratrans records
		$update = sprintf("DELETE FROM paratrans WHERE taskID = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete paraproof records
		$update = sprintf("DELETE FROM paraedit WHERE task_id = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete task logs
		$update = sprintf("DELETE FROM tasklog WHERE taskID = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete signoff reports if any
		$update = sprintf("DELETE FROM signoff_reports WHERE task_id = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete box properties
		$update = sprintf("DELETE FROM box_properties WHERE task_id = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete box moves
		$update = sprintf("DELETE FROM box_moves WHERE task_id = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete box overflows
		$update = sprintf("DELETE FROM box_overflows WHERE task_id = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//send email
		$query = sprintf("SELECT tasks.artworkID, tasks.taskStatus, tasks.tdeadline,
							artworks.artworkName, artworks.wordCount,
							L1.languageName AS sourceLanguage,
							L2.languageName AS desiredLanguage,
							users.forename, users.surname, users.email
							FROM tasks
							LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
							LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
							LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
							LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
							LEFT JOIN users ON tasks.translatorID = users.userID
							WHERE tasks.taskID = %d
							LIMIT 1",
							$taskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			$artworkID = $row['artworkID'];
			$body = "Dear ".$row['forename']." ".$row['surname'].",";
			$body .= "\n\nWe are sorry to inform you that the following task has been cancelled:";
			$body .= "\n\nArtwork Title: ".$row['artworkName'];
			$body .= "\nLanguages: ".$row['sourceLanguage']." -> ".$row['desiredLanguage'];
			$body .= "\nWord Count: ".$row['wordCount'];
			$body .= "\nDeadline: ".$row['tdeadline'];
			$body .= "\n\nPlease do check back later and we are looking forward to working with you soon.";
			$body .= "\n\nKind Regards,";
			$body .= "\n\n".COMPANY_NAME;
			$name = $row['forename']." ".$row['surname'];
			$address = $row['email'];
			$subject = SYSTEM_NAME.": Task Cancellation";
			require_once(CLASSES.'Mailer.php');
			$mailer = new Mailer();
			$mailer->send_mail($name,$address,$subject,$body);
		}
		//delete task imports
		$query = sprintf("SELECT id FROM task_imports WHERE task_id = %d", $taskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$str = "0";
		while($row = mysql_fetch_assoc($result)) {
			$str .= ",".$row['id'];
		}
		$update = sprintf("DELETE FROM task_import_rows WHERE import_id IN (%s)", mysql_real_escape_string($str));
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$update = sprintf("DELETE FROM task_imports WHERE task_id = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete proofreaders
		$update = sprintf("DELETE FROM task_proofreaders WHERE task_id = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete task
		$update = sprintf("DELETE FROM tasks WHERE taskID = %d", $taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		// log
		return $this->LogSystemEvent($_SESSION['userID'],"deleted task [$taskID]",0,0,$taskID);
	}

	public function SignoffTask($taskID) {
		$update = sprintf("UPDATE tasks SET
						taskStatus = 10,
						lastUpdate = NOW()
						WHERE taskID = %d",
						$taskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$this->LogTaskAction($taskID, $_SESSION['userID'], "Signed off Translation Task", "Sign-off");
		$query = sprintf("SELECT artworks.pageCount
						FROM tasks
						LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
						WHERE tasks.taskID = %d
						LIMIT 1",
						$taskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$pages = $row['pageCount'];
		// update rating if it's trial run
		if($this->get_task_trial_status($taskID)) {
			$query = sprintf("SELECT transParaID
							FROM paratrans
							WHERE taskID = %d
							ORDER BY ParalinkID ASC",
							$taskID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$this->update_para_rating($row['transParaID'],PARA_GLOSSARY,5);
			}
		}
		return $this->log_signoff_report($_SESSION['userID'],$pages,$taskID);
	}

	public function update_para_rating($para_id, $type_id, $rating) {
		$update = sprintf("UPDATE paragraphs SET
						type_id = %d,
						rating = %d
						WHERE uID = %d",
						$type_id,
						$rating,
						$para_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function TrashArtwork($artworkID) {
		$update = sprintf("UPDATE artworks
						SET live = %d
						WHERE artworkID = %d",
						STATUS_TRASHED,
						$artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return $this->LogSystemEvent($_SESSION['userID'],"trashed artwork [$artworkID]",0,$artworkID);
	}

	public function RestoreArtwork($artworkID) {
		$update = sprintf("UPDATE artworks
						SET live = %d
						WHERE artworkID = %d",
						STATUS_ACTIVE,
						$artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$this->LogSystemEvent($_SESSION['userID'],"restored artwork [$artworkID]",0,$artworkID);
	}

	public function DeleteArtwork($artworkID, $log=true) {
		//get artwork info
		$query_artworkRs = sprintf("SELECT artworkType, artworkName, fileName, campaignID
									FROM artworks
									WHERE artworkID = %d",
									$artworkID);
		$artworkRs = mysql_query($query_artworkRs,$this->link) or die(mysql_error());
		$row_artworkRs = mysql_fetch_assoc($artworkRs);

		//get pageIDs
		$query_pageRs = sprintf("SELECT uID,PreviewFile FROM pages WHERE ArtworkID = %d", $artworkID);
		$pageRs = mysql_query($query_pageRs,$this->link) or die(mysql_error());
		while($row_pageRs = mysql_fetch_assoc($pageRs)) {
			$pageID = $row_pageRs['uID'];
			@unlink(ROOT.PREVIEW_DIR.$row_pageRs['PreviewFile']);
			@unlink(ROOT.PREVIEW_DIR.THUMBNAILS_DIR.$row_pageRs['PreviewFile']);
			@unlink(ROOT.PREVIEW_DIR.EDITS_DIR.$row_pageRs['PreviewFile']);
			//for template based only
			@unlink(ROOT.POSTVIEW_DIR.$row_pageRs['PreviewFile']);
			@unlink(ROOT.POSTVIEW_DIR.THUMBNAILS_DIR.$row_pageRs['PreviewFile']);
			@unlink(ROOT.POSTVIEW_DIR.EDITS_DIR.$row_pageRs['PreviewFile']);
			//get boxIDs
			$query_box = sprintf("SELECT uID FROM boxes WHERE PageID = %d", $pageID);
			$result_box = mysql_query($query_box,$this->link) or die(mysql_error());
			while($row_box = mysql_fetch_assoc($result_box)) {
				$boxID = $row_box['uID'];
				@unlink(ROOT.PREVIEW_DIR.TEXTBOXES_DIR.BareFilename($row_pageRs['PreviewFile'])."-".$boxID.".jpg");

				//Remove paragraphs when artwork deleted
				/*
				//get ParaIDs from paralinks
				$query_paraRs = sprintf("SELECT ParaID
										FROM paralinks
										WHERE BoxID = %d",
										$boxID);
				$paraRs = mysql_query($query_paraRs,$this->link) or die(mysql_error());
				while($row_paraRs = mysql_fetch_assoc($paraRs)) {
					$this->DeletePara($paraID);
				}
				*/
				//delete tasks
				$query_task = sprintf("SELECT taskID
									FROM tasks
									WHERE artworkID = %d",
									$artworkID);
				$result_task = mysql_query($query_task,$this->link) or die(mysql_error());
				while($row_task = mysql_fetch_assoc($result_task)) {
					$this->DeleteTask($row_task['taskID']);
				}
				//delete paraedit records
				$query_paralink = sprintf("SELECT uID
										FROM paralinks
										WHERE BoxID = %d",
										$boxID);
				$result_paralink = mysql_query($query_paralink,$this->link) or die(mysql_error());
				while($row_paralink = mysql_fetch_assoc($result_paralink)) {
					$update = sprintf("DELETE FROM paraedit WHERE pl_id = %d", $row_paralink['uID']);
					$result = mysql_query($update,$this->link) or die(mysql_error());
				}
				//delete paralinks
				$update = sprintf("DELETE FROM paralinks WHERE BoxID = %d", $boxID);
				$result = mysql_query($update,$this->link) or die(mysql_error());
			}

			//delete boxes records
			$update = sprintf("DELETE FROM boxes WHERE PageID = %d", $pageID);
			$result = mysql_query($update,$this->link) or die(mysql_error());
		}
		//delete pages layers
		$update = sprintf("DELETE FROM artwork_layers WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete pages recoreds
		$update = sprintf("DELETE FROM pages WHERE ArtworkID = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete box properties
		$update = sprintf("DELETE FROM box_properties WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete artwork box moves
		$update = sprintf("DELETE FROM box_moves WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete box overflows
		$update = sprintf("DELETE FROM box_overflows WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete artwork image usage
		$update = sprintf("DELETE FROM img_usage WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete artwork comments
		$update = sprintf("DELETE FROM comments WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete artwork fonts
		$update = sprintf("DELETE FROM artwork_fonts WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete artwork pg maps
		$update = sprintf("DELETE FROM import_map_para WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete artwork guests
		$update = sprintf("DELETE FROM artwork_guests WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete files
		if($row_artworkRs['fileName'] != ""){
			@unlink(UPLOAD_DIR.$row_artworkRs['fileName']);
			@unlink(UPLOAD_DIR.BareFilename($row_artworkRs['fileName']).".pdf");
			@unlink(UPLOAD_DIR.BareFilename($row_artworkRs['fileName']).".idml");
			@unlink(UPLOAD_DIR.BareFilename($row_artworkRs['fileName']).".base.raw");
			@unlink(UPLOAD_DIR.BareFilename($row_artworkRs['fileName']).".base");
			@unlink(UPLOAD_DIR.BareFilename($row_artworkRs['fileName']).".xml");
			@unlink(UPLOAD_DIR.BareFilename($row_artworkRs['fileName'])."-0.xml");
			do_rmdir(OUTPUT_DIR.$row_artworkRs['fileName']);//for indd file only
		}
		//delete imports
		$query = sprintf("SELECT id FROM task_imports WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$str = "0";
		while($row = mysql_fetch_assoc($result)) {
			$str .= ",".$row['id'];
		}
		$update = sprintf("DELETE FROM task_import_rows WHERE import_id IN (%s)", mysql_real_escape_string($str));
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$update = sprintf("DELETE FROM task_imports WHERE artwork_id = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//delete artworks records
		$update = sprintf("DELETE FROM artworks WHERE artworkID = %d", $artworkID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$this->LastUpdateCampaign($row_artworkRs['campaignID']);
		if($log === true) $this->LogSystemEvent($_SESSION['userID'],"deleted artwork: {$row_artworkRs['artworkName']}",0,$artworkID);
		return true;
	}

	public function CompleteCampaign($campaignID) {
		$update = sprintf("UPDATE campaigns
						SET campaignStatus = %d
						WHERE campaignID = %d",
						STATUS_COMPLETE,
						$campaignID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$this->LastUpdateCampaign($campaignID);
		$this->LogSystemEvent($_SESSION['userID'],"completed campaign [$campaignID]",$campaignID);
	}

	public function ArchiveCampaign($campaignID) {
		$update = sprintf("UPDATE campaigns
						SET campaignStatus = %d
						WHERE campaignID = %d",
						STATUS_ARCHIVED,
						$campaignID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$this->LastUpdateCampaign($campaignID);
		$this->LogSystemEvent($_SESSION['userID'],"archived campaign [$campaignID]",$campaignID);
	}

	public function UnarchiveCampaign($campaignID) {
		$update = sprintf("UPDATE campaigns
						SET campaignStatus = %d
						WHERE campaignID = %d",
						STATUS_COMPLETE,
						$campaignID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$this->LastUpdateCampaign($campaignID);
		$this->LogSystemEvent($_SESSION['userID'],"unarchived campaign [$campaignID]",$campaignID);
	}

	public function TrashCampaign($campaignID) {
		$update = sprintf("UPDATE campaigns
						SET campaignStatus = %d
						WHERE campaignID = %d",
						STATUS_TRASHED,
						$campaignID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$this->LastUpdateCampaign($campaignID);
		$this->LogSystemEvent($_SESSION['userID'],"trashed campaign [$campaignID]",$campaignID);
	}

	public function RestoreCampaign($campaignID) {
		$update = sprintf("UPDATE campaigns
						SET campaignStatus = %d
						WHERE campaignID = %d",
						STATUS_ACTIVE,
						$campaignID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$this->LastUpdateCampaign($campaignID);
		$this->LogSystemEvent($_SESSION['userID'],"restored campaign [$campaignID]",$campaignID);
	}

	public function DeleteCampaign($campaignID) {
		$query = sprintf("SELECT campaignName, campaignStatus FROM campaigns WHERE campaignID = %d LIMIT 1", $campaignID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$campaign_name = $row['campaignName'];
		$campaign_status = $row['campaignStatus'];
		// archived campaigns cannot be deleted
		if($campaign_status == STATUS_ARCHIVED) return false;
		$query = sprintf("SELECT artworkID FROM artworks WHERE campaignID = %d", $campaignID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$this->DeleteArtwork($row['artworkID']);
		}
		// delete upload log
		$query = sprintf("SELECT id FROM artwork_uploads WHERE campaign_id = %d", $campaignID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$update = sprintf("DELETE FROM artwork_upload_log WHERE upload_id = %d", $row['id']);
			mysql_query($update,$this->link) or die(mysql_error());
		}
		$update = sprintf("DELETE FROM artwork_uploads WHERE campaign_id = %d", $campaignID);
		mysql_query($update,$this->link) or die(mysql_error());
		// delete campaign acl
		$update = sprintf("DELETE FROM campaigns_acl WHERE campaign_id = %d", $campaignID);
		mysql_query($update,$this->link) or die(mysql_error());
		// delete campaign
		$update = sprintf("DELETE FROM campaigns WHERE campaignID = %d", $campaignID);
		mysql_query($update,$this->link) or die(mysql_error());
		$this->LogSystemEvent($_SESSION['userID'],"deleted campaign: $campaign_name",$campaignID);
	}

	public function DeleteUser($acl_api, $userID) {
		$update = sprintf("DELETE FROM userlanguages WHERE userID = %d", $userID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$update = sprintf("DELETE FROM userrates WHERE userID = %d", $userID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$update = sprintf("DELETE FROM userspecs WHERE userID = %d", $userID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$update = sprintf("DELETE FROM users WHERE userID = %d", $userID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		$query = sprintf("SELECT id, name FROM aro WHERE value = %d LIMIT 1", $userID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			$acl_api->del_object($row['id'], 'ARO', TRUE);
		}
		$this->LogSystemEvent($_SESSION['userID'],"deleted user: {$row['name']}");
	}

	public function DeleteCompany($acl_api, $companyID) {
		$query_campaignRs = sprintf("SELECT campaigns.campaignID
									FROM campaigns
									LEFT JOIN users ON campaigns.ownerID = users.userID
									LEFT JOIN companies ON users.companyID = companies.companyID
									WHERE companies.companyID = %d", $companyID);
		$campaignRs = mysql_query($query_campaignRs,$this->link) or die(mysql_error());
		while($row_campaignRs = mysql_fetch_assoc($campaignRs)) {
			$this->DeleteCampaign($row_campaignRs['campaignID']);
		}
		$query_userRs = sprintf("SELECT users.userID
								FROM users
								LEFT JOIN companies ON users.companyID = companies.companyID
								WHERE companies.companyID = %d", $companyID);
		$userRs = mysql_query($query_userRs,$this->link) or die(mysql_error());
		while($row_userRs = mysql_fetch_assoc($userRs)) {
			$this->DeleteUser($acl_api,$row_userRs['userID']);
		}

		$query = sprintf("SELECT systemName, companyLogo FROM companies WHERE companyID = %d", $companyID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		if($row['systemName']!="") do_rmdir(ROOT.FTP_DIR.$row['systemName']);
		if($row['companyLogo'] != "default.jpg") @unlink(ROOT.LOGO_PATH.$row['companyLogo']);

		$query = sprintf("SELECT logoFile, smallLogoFile FROM systemconfig WHERE companyID = %d", $companyID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		if($row['logoFile'] != "pagl.gif") @unlink(ROOT.SYSTEM_LOGO_PATH.$row['logoFile']);
		if($row['smallLogoFile'] != "s_pagl.gif") @unlink(ROOT.SYSTEM_LOGO_PATH.$row['smallLogoFile']);

		$update = sprintf("DELETE FROM brands WHERE companyID = %d", $companyID);
		$result = mysql_query($update,$this->link) or die(mysql_error());

		$update = sprintf("DELETE FROM systemconfig WHERE companyID = %d", $companyID);
		$result = mysql_query($update,$this->link) or die(mysql_error());

		$update = sprintf("DELETE FROM companies_acl WHERE company_id = %d", $companyID);
		$result = mysql_query($update,$this->link) or die(mysql_error());

		$update = sprintf("DELETE FROM companies WHERE companyID = %d", $companyID);
		$result = mysql_query($update,$this->link) or die(mysql_error());

		$query_arosRs = sprintf("SELECT id, name FROM aro_sections WHERE value = %d LIMIT 1", $companyID);
		$arosRs = mysql_query($query_arosRs,$this->link) or die(mysql_error());
		if(mysql_num_rows($arosRs)) {
			$row_arosRs = mysql_fetch_assoc($arosRs);
			$acl_api->del_object_section($row_arosRs['id'], 'ARO', TRUE);
		}
		$query_aclsRs = sprintf("SELECT id FROM acl_sections WHERE value = %d LIMIT 1", $companyID);
		$aclsRs = mysql_query($query_aclsRs,$this->link) or die(mysql_error());
		if(mysql_num_rows($aclsRs)) {
			$row_aclsRs = mysql_fetch_assoc($aclsRs);
			$acl_api->del_object_section($row_aclsRs['id'], 'ACL', TRUE);
		}
		$this->LogSystemEvent($_SESSION['userID'],"deleted company: {$row_arosRs['name']}");
	}

	public function ClearPrework($artwork_id) {
		//DELETE comments
		$update = sprintf("DELETE FROM comments WHERE artwork_id = %d AND task_id = 0", $artwork_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//DELETE paraedit
		$query = sprintf("SELECT paralinks.uID
						FROM paralinks
						LEFT JOIN boxes ON boxes.uID = paralinks.BoxID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						ORDER BY paralinks.uID ASC",
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return true;
		$str_pl = "0";
		while($row = mysql_fetch_assoc($result)) {
			$str_pl .= ",{$row['uID']}";
		}
		$update = sprintf("DELETE FROM paraedit WHERE pl_id IN (%s) AND task_id = 0", mysql_real_escape_string($str_pl));
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//DELETE box_moves
		$update = sprintf("DELETE FROM box_moves WHERE artwork_id = %d AND task_id = 0", $artwork_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//DELETE box_overflows
		$update = sprintf("DELETE FROM box_overflows WHERE artwork_id = %d AND task_id = 0", $artwork_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//DELETE box_properties
		$update = sprintf("DELETE FROM box_properties WHERE artwork_id = %d AND task_id = 0", $artwork_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		//DELETE img_usage
		$update = sprintf("DELETE FROM img_usage WHERE artwork_id = %d AND task_id = 0", $artwork_id);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		return true;
	}

	function AddChangedItem($artwork_id, $box_id, $task_id=0) {
		if(empty($artwork_id) || empty($box_id)) return false;
		$query = sprintf("SELECT id
						FROM doc_cache
						WHERE artwork_id = %d
						AND box_id = %d
						AND task_id = %d
						LIMIT 1",
						$artwork_id,
						$box_id,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(mysql_num_rows($result)) return true;
		$query = sprintf("INSERT INTO doc_cache
						(artwork_id, task_id, box_id)
						VALUES
						(%d, %d, %d)",
						$artwork_id,
						$task_id,
						$box_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	function GetChangedItems($artwork_id, $task_id) {
		$query = sprintf("SELECT boxes.BoxUID
						FROM doc_cache
						LEFT JOIN boxes ON doc_cache.box_id = boxes.uID
						WHERE doc_cache.artwork_id = %d
						AND doc_cache.task_id = %d
						ORDER BY doc_cache.box_id ASC",
						$artwork_id,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$box_ids = array();
		while($row = mysql_fetch_assoc($result)) {
			$box_ids[] = $row['BoxUID'];
		}
		return $box_ids;
	}

	function ClearItemChanges($artwork_id, $task_id, $page_ref=0) {
		$box_ids = "0";
		$query = sprintf("SELECT boxes.uID
						FROM boxes
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						AND pages.PageRef = '%s'
						ORDER BY boxes.uID ASC",
						$artwork_id,
						mysql_real_escape_string($page_ref));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$box_ids .= ",".$row['uID'];
		}
		$limit = $page_ref==0 ? "" : "AND box_id IN ($box_ids)";
		$query = sprintf("DELETE FROM doc_cache
						WHERE artwork_id = %d
						AND task_id = %d
						$limit",
						$artwork_id,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	function GetArtworkIDbyTask($task_id) {
		$query = sprintf("SELECT artworkID
						FROM tasks
						WHERE taskID = %d
						LIMIT 1",
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['artworkID'];
	}

	function GetBoxIDbyPL($PL) {
		$query = sprintf("SELECT BoxID
						FROM paralinks
						WHERE uID = %d
						LIMIT 1",
						$PL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['BoxID'];
	}

	public function GetPageRef($ArtworkID,$Page) {
		$query = sprintf("SELECT pages.PageRef
						FROM pages
						LEFT JOIN tasks ON pages.ArtworkID = tasks.artworkID
						WHERE pages.ArtworkID = %d
						AND pages.Page = %d
						LIMIT 1",
						$ArtworkID,
						$Page);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['PageRef'];
	}

	public function GetFilenamebyArtwork($artwork_id) {
		$query = sprintf("SELECT fileName
						FROM artworks
						WHERE artworkID = %d
						LIMIT 1",
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['fileName'];
	}

	public function GetCacheStatus($artwork_id, $task_id=0) {
		$row = $this->GetAllCacheStatus($artwork_id,$task_id);
		if($row === false) return false;
		if(empty($task_id)) {
			return $row['engine_cache'] ? $row['artwork_cache'] : false;
		} else {
			return $row['engine_cache'] ? ($row['artwork_cache']?$row['task_cache']:false) : false;
		}
	}

	public function GetAllCacheStatus($artwork_id, $task_id=0) {
		if(empty($task_id)) {
			$query = sprintf("SELECT artworks.cache AS artwork_cache, service_engines.cache AS engine_cache
							FROM artworks
							LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
							WHERE artworks.artworkID = %d
							LIMIT 1",
							$artwork_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(!mysql_num_rows($result)) return false;
			return mysql_fetch_assoc($result);
		} else {
			$query = sprintf("SELECT tasks.cache AS task_cache, artworks.cache AS artwork_cache, service_engines.cache AS engine_cache
							FROM tasks
							LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
							LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
							WHERE tasks.taskID = %d
							LIMIT 1",
							$task_id);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			if(!mysql_num_rows($result)) return false;
			return mysql_fetch_assoc($result);
		}
	}

	public function UpdateArtworkCache($artwork_id, $cache=1) {
		$query = sprintf("UPDATE artworks SET
						cache = %d
						WHERE artworkID = %d",
						$cache,
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function UpdateTaskCache($task_id, $cache=1) {
		$query = sprintf("UPDATE tasks SET
						cache = %d
						WHERE taskID = %d",
						$cache,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function AssignTranslator($task_id, $translator_id) {
		$query = sprintf("UPDATE tasks SET
						translatorID = %d,
						tdeadline = NULL
						WHERE taskID = %d",
						$translator_id,
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function ResetTranslator($task_id, $translator_id) {
		$query = sprintf("UPDATE tasks SET
						translatorID = 0,
						tdeadline = NULL
						WHERE taskID = %d
						AND translatorID = %d",
						$task_id,
						$translator_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function AssignProofreader($task_id, $proofreader_id) {
		$query = sprintf("SELECT id
						FROM task_proofreaders
						WHERE task_id = %d
						AND user_id = %d
						LIMIT 1",
						$task_id,
						$proofreader_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			return $row['id'];
		}
		$query = sprintf("INSERT INTO task_proofreaders
						(task_id, user_id) VALUES (%d, %d)",
						$task_id,
						$proofreader_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}

	public function DeleteProofreader($task_id, $proofreader_id) {
		$query = sprintf("DELETE FROM task_proofreaders
						WHERE task_id = %d
						AND user_id = %d",
						$task_id,
						$proofreader_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function get_proofreaders($task_id) {
		$query = sprintf("SELECT task_proofreaders.*, users.forename, users.surname
						FROM task_proofreaders
						LEFT JOIN users ON task_proofreaders.user_id = users.userID
						WHERE task_proofreaders.task_id = %d
						ORDER BY
						task_proofreaders.order ASC,
						task_proofreaders.deadline ASC,
						users.forename ASC,
						users.surname ASC",
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return $result;
	}

	public function get_task_acl_user_ids($task_id) {
		$acl_user_ids = array();
		$query = sprintf("SELECT creatorID, agentID, translatorID
						FROM tasks
						WHERE taskID = %d
						LIMIT 1",
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return $acl_user_ids;
		$row = mysql_fetch_assoc($result);
		if(!empty($row['creatorID'])) $acl_user_ids[] = $row['creatorID'];
		if(!empty($row['agentID'])) $acl_user_ids[] = $row['agentID'];
		if(!empty($row['translatorID'])) $acl_user_ids[] = $row['translatorID'];
		$result = $this->get_proofreaders($task_id);
		while($row = mysql_fetch_assoc($result)) {
			$acl_user_ids[] = $row['user_id'];
		}
		return array_unique($acl_user_ids);
	}

	public function get_upload_info($upload_id) {
		$query = sprintf("SELECT *
						FROM artwork_uploads
						LEFT JOIN campaigns ON artwork_uploads.campaign_id = campaigns.campaignID
						LEFT JOIN users ON artwork_uploads.user_id = users.userID
						WHERE artwork_uploads.id = %d
						LIMIT 1",
						$upload_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function delete_campaign_upload($upload_id) {
		$query = sprintf("DELETE FROM artwork_upload_log
						WHERE upload_id = %d",
						$upload_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$query = sprintf("DELETE FROM artwork_uploads
						WHERE id = %d",
						$upload_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return true;
	}

	public function start_upload($campaign_id, $user_id) {
		$query = sprintf("INSERT INTO artwork_uploads
						(campaign_id, user_id, time_start)
						VALUES (%d, %d, UNIX_TIMESTAMP(NOW()))",
						$campaign_id,
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}

	public function end_upload($upload_id) {
		$query = sprintf("UPDATE artwork_uploads SET
						time_end = UNIX_TIMESTAMP(NOW())
						WHERE id = %d",
						$upload_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function add_upload_log($upload_id, $filename) {
		$query = sprintf("INSERT INTO artwork_upload_log
						(upload_id, filename, time_start)
						VALUES (%d, '%s', UNIX_TIMESTAMP(NOW()))",
						$upload_id,
						$filename);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_insert_id($this->link);
	}

	public function update_upload_log($log_id, $nvp=array()) {
		$str = "";
		foreach($nvp as $n=>$v) {
			$str .= sprintf("`%s` = %d,", mysql_real_escape_string($n), $v);
		}
		$str = trim($str,',');
		if(empty($str)) return false;
		$query = sprintf("UPDATE artwork_upload_log SET $str WHERE id = %d", $log_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function end_upload_log($log_id) {
		$query = sprintf("UPDATE artwork_upload_log SET
						time_end = UNIX_TIMESTAMP(NOW()),
						progress = 100
						WHERE id = %d",
						$log_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function get_campaign_artworks($campaign_id) {
		$query = sprintf("SELECT artworkID, fileName
						FROM artworks
						WHERE campaignID = %d
						ORDER BY artworkID ASC",
						$campaign_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return $result;
	}
	public function get_artwork_tasks($artwork_id) {
		$query = sprintf("SELECT taskID
						FROM tasks
						WHERE artworkID = %d
						ORDER BY taskID ASC",
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return $result;
	}

	public function get_artwork_info($artwork_id) {
		$query = sprintf("SELECT artworks.*,
						campaigns.campaignName, campaigns.sourceLanguageID, campaigns.brandID,
						languages.flag
						FROM artworks
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
						LEFT JOIN languages ON languages.languageID = campaigns.sourceLanguageID
						WHERE artworks.artworkID = %d
						LIMIT 1",
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function get_task_info($task_id) {
		$query = sprintf("SELECT tasks.*,
						artworks.subjectID, artworks.artworkName,
						campaigns.campaignID, campaigns.brandID, campaigns.sourceLanguageID,
						L1.flag AS TargetLangFlag,
						L2.flag AS SourceLangFlag
						FROM tasks
						LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
						LEFT JOIN languages L1 ON tasks.desiredLanguageID = L1.languageID
						LEFT JOIN languages L2 ON campaigns.sourceLanguageID = L2.languageID
						WHERE tasks.taskID = %d
						LIMIT 1",
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function get_task_trial_status($task_id) {
		$query = sprintf("SELECT trial
						FROM tasks
						WHERE taskID = %d
						LIMIT 1",
						$task_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['trial'];
	}

	public function create_service_package($user_id, $name, $items) {
		if(empty($user_id) || empty($name) || !is_array($items)) return false;
		// check service package name
		$query = sprintf("SELECT id
						FROM service_packages
						WHERE name='%s'
						LIMIT 1",
						mysql_real_escape_string($name));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(mysql_num_rows($result)) return false;
		// insert service package
		$update = sprintf("INSERT INTO service_packages
						(name)
						VALUES
						('%s')",
						mysql_real_escape_string($name));
		mysql_query($update,$this->link) or die(mysql_error());
		$service_package_id = mysql_insert_id($this->link);
		// add new service package items
		foreach($items as $item_id => $credits) {
			$update = sprintf("INSERT INTO service_package_items
							(packageID, service_tID, credits)
							VALUES
							(%d, %d, %d)",
							$service_package_id,
							$item_id,
							$credits);
			mysql_query($update,$this->link) or die(mysql_error());
		}
		return $this->LogSystemEvent($user_id,"created new service package: $name");
	}

	public function update_service_package($user_id, $service_package_id, $name, $items) {
		if(empty($user_id) || empty($service_package_id) || empty($name) || !is_array($items)) return false;
		// update service package name
		$update = sprintf("UPDATE service_packages SET
						name = '%s'
						WHERE id = %d",
						mysql_real_escape_string($name),
						$service_package_id);
		mysql_query($update,$this->link) or die(mysql_error());
		// reset service package items
		$update = sprintf("DELETE FROM service_package_items
						WHERE packageID = %d",
						$service_package_id);
		mysql_query($update,$this->link) or die(mysql_error());
		// add new service package items
		foreach($items as $item_id => $credits) {
			$update = sprintf("INSERT INTO service_package_items
							(packageID, service_tID, credits)
							VALUES
							(%d, %d, %d)",
							$service_package_id,
							$item_id,
							$credits);
			mysql_query($update,$this->link) or die(mysql_error());
		}
		return $this->LogSystemEvent($user_id,"edited service package: $name");
	}

	public function delete_service_package($user_id, $service_package_id) {
		$update = sprintf("DELETE FROM service_package_items
						WHERE packageID = %d",
						$service_package_id);
		mysql_query($update,$this->link) or die(mysql_error());
		$update = sprintf("DELETE FROM service_packages
						WHERE id = %d",
						$service_package_id);
		mysql_query($update,$this->link) or die(mysql_error());
		return $this->LogSystemEvent($user_id,"deleted service package [$service_package_id]");
	}

	public function get_service_package_item_info($service_package_id, $item_id) {
		$query = sprintf("SELECT *
						FROM service_package_items
						WHERE packageID = %d
						AND service_tID = %d",
						$service_package_id,
						$item_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function check_credit_allowance($user_id) {
		$query = sprintf("SELECT allowance
						FROM users
						WHERE userID = %d
						LIMIT 1",
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['allowance'];
	}

	public function get_company_credit_balance($company_id) {
		$query = sprintf("SELECT credits
						FROM companies
						WHERE companyID = %d
						LIMIT 1",
						$company_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['credits'];
	}

	public function update_company_credit_balance($company_id, $balance) {
		$query = sprintf("UPDATE companies SET
						credits = %d
						WHERE companyID = %d",
						$balance,
						$company_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function count_used_credits($company_id, $user_id) {
		$query = sprintf("SELECT SUM(credit_out) AS used
						FROM credits
						WHERE company_id = %d
						AND user_id = %d
						AND DATE(time) = DATE(NOW())
						ORDER BY time ASC",
						$company_id,
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return 0;
		$row = mysql_fetch_assoc($result);
		return !empty($row['used']) ? (int)$row['used'] : 0;
	}

	public function count_available_credits($company_id, $user_id) {
		$credits_company = $this->get_company_credit_balance($company_id);
		if(empty($credits_company)) return 0;
		$credits_allowance = $this->check_credit_allowance($user_id);
		if(empty($credits_allowance)) return 0;
		if($credits_allowance==-1) return $credits_company;
		$credits_left = $credits_allowance - $this->count_used_credits($company_id,$user_id);
		return $credits_left<$credits_company ? $credits_left: $credits_company;
	}

	public function get_transaction_info($transaction_id) {
		$query = sprintf("SELECT *
						FROM credits
						WHERE id = %d
						LIMIT 1",
						$transaction_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function log_credit_transaction($company_id, $user_id, $campaign_id=0, $artwork_id=0, $task_id=0, $transaction="", $credit_out=null, $credit_in=null, $refresh=true) {
		if(empty($company_id) || empty($user_id)) return false;
		$balance = $this->recalculate_company_credits($company_id);
		if(is_null($credit_out)) {
			$credit_out = 'NULL';
		} else {
			$credit_out = (int)$credit_out;
			$balance -= $credit_out;
		}
		if(is_null($credit_in)) {
			$credit_in = 'NULL';
		} else {
			$credit_in = (int)$credit_in;
			$balance += $credit_in;
		}
		$query = sprintf("INSERT INTO credits
						(company_id, user_id, campaign_id, artwork_id, task_id, transaction, credit_out, credit_in, balance)
						VALUES
						(%d, %d, %d, %d, %d, '%s', $credit_out, $credit_in, %d)",
						$company_id,
						$user_id,
						$campaign_id,
						$artwork_id,
						$task_id,
						mysql_real_escape_string($transaction),
						$balance);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$trans_id = mysql_insert_id($this->link);
		if($refresh === true) $this->recalculate_company_credits($company_id);
		return $this->LogSystemEvent($user_id,$transaction,$campaign_id,$artwork_id,$task_id);
	}

	public function delete_transaction($transaction_id) {
		$query = sprintf("DELETE FROM credits
						WHERE id = %d",
						$transaction_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function recalculate_company_credits($company_id) {
		$query = sprintf("SELECT credit_out, credit_in
						FROM credits
						WHERE company_id = %d
						ORDER BY id ASC",
						$company_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$balance = 0;
		while($row = mysql_fetch_assoc($result)) {
			$balance = $balance + $row['credit_in'] - $row['credit_out'];
		}
		$this->update_company_credit_balance($company_id,$balance);
		return $balance;
	}

	public function get_credit_usage($company_id, $user_id) {
		$query = sprintf("SELECT credits.*, UNIX_TIMESTAMP(credits.time) AS trans_time,
						campaigns.campaignName,
						artworks.artworkName,
						L1.languageName AS source_lang, L1.flag AS source_flag,
						L2.languageName AS target_lang, L2.flag AS target_flag
						FROM credits
						LEFT JOIN campaigns ON credits.campaign_id = campaigns.campaignID
						LEFT JOIN artworks ON credits.artwork_id = artworks.artworkID
						LEFT JOIN tasks ON credits.task_id = tasks.taskID
						LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
						LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
						WHERE credits.company_id = %d
						AND credits.user_id = %d
						AND DATE(credits.time) = DATE(NOW())
						AND credits.credit_out IS NOT NULL
						ORDER BY credits.time ASC",
						$company_id,
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return $result;
	}

	public function get_credit_config($service_package_id, $item_id) {
		$query = sprintf("SELECT credits
						FROM service_package_items
						WHERE packageID = %d
						AND service_tID = %d
						LIMIT 1",
						$service_package_id,
						$item_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return 0;
		$row = mysql_fetch_assoc($result);
		return $row['credits'];
	}

	public function get_service_process_transaction($trans_id) {
		$query = sprintf("SELECT service_transaction_process.notes, service_engines.ext
						FROM service_transaction_process
						LEFT JOIN service_engines ON service_transaction_process.serviceID = service_engines.id
						WHERE service_transaction_process.id = %d
						LIMIT 1",
						$trans_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function get_user_company_id($user_id) {
		$query = sprintf("SELECT companyID
						FROM users
						WHERE userID = %d
						LIMIT 1",
						$user_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['companyID'];
	}

	public function count_campaign_artworks($campaign_id) {
		$query = sprintf("SELECT artworkID
						FROM artworks
						WHERE campaignID = %d",
						$campaign_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_num_rows($result);
	}

	public function get_lang_info($lang_id) {
		$query = sprintf("SELECT *
						FROM languages
						WHERE languageID = %d
						LIMIT 1",
						$lang_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}

	public function get_lang_by_id($lang_id) {
		$query = sprintf("SELECT acronym
						FROM language_options
						WHERE id = %d
						LIMIT 1",
						$lang_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return 'gb';
		$row = mysql_fetch_assoc($result);
		return $row['acronym'];
	}

	public function get_system_name($company_id) {
		$query = sprintf("SELECT systemName
						FROM companies
						WHERE companyID = %d",
						$company_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['systemName'];
	}
	
	public function get_company_groups($company_id) {
		$str = "$company_id";
		$query = sprintf("SELECT companyID
						FROM companies
						WHERE parentCompanyID = %d",
						$company_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return $str;
		while($row = mysql_fetch_assoc($result)) {
			$str .= ",".$row['companyID'];
		}
		return $str;
	}
    function artwork_story_groups($id) {
        $query = sprintf('SELECT name FROM `artwork_story_groups` WHERE id=%d LIMIT 1', $id);
        $result = mysql_query($query, $this->link);
        if(mysql_num_rows($result)){
            $row = mysql_fetch_assoc($result);
            return $row['name'];
        }else{
            return "None";
        }
    }
}
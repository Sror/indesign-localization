<?php
require_once(CLASSES."FTP_Local.php");
require_once(CLASSES."services.php");
class ImageManager extends FTP_Local {
	
	public function AddImage($box_id, $user_id, $lang_id, $content="", $brand_id=0, $subject_id=0, $type_id=IMG_UPLOAD, $hash="") {
		$found = $this->ImageExists($content,$type_id,$user_id);
		if($found) {
			$img_id = $found['img_id'];
			$img_group_id = $found['img_group_id'];
		} else {
			$query = sprintf("INSERT INTO images
							(content, user_id, time, lang_id, type_id, brand_id, subject_id, hash)
							VALUES
							('%s', %d, NOW(), %d, %d, %d, %d, '%s')",
							mysql_real_escape_string($content),
							$user_id,
							$lang_id,
							$type_id,
							$brand_id,
							$subject_id,
							mysql_real_escape_string($hash));
			$result = mysql_query($query, $this->link) or die(mysql_error());
			$img_id = mysql_insert_id($this->link);
			$query = "INSERT INTO img_groups (id) VALUES (NULL)";
			$result = mysql_query($query, $this->link) or die(mysql_error());
			$img_group_id = mysql_insert_id($this->link);
		}
		//add to imgae links
		$query = sprintf("INSERT INTO img_links
						(img_id, box_id)
						VALUES (%d, %d)",
						$img_id,
						$box_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		$img_link_id = mysql_insert_id($this->link);
		//add to image sets
		$query = sprintf("SELECT id
						FROM img_sets
						WHERE img_id = %d
						AND img_group_id = %d",
						$img_id,
						$img_group_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) {
			$query = sprintf("INSERT INTO img_sets
							(img_id, img_group_id)
							VALUES
							(%d, %d)",
							$img_id,
							$img_group_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
		}
		return $img_link_id;
	}
	
	protected function ImageExists($check, $type_id, $user_id=0) {
		switch($type_id) {
			case IMG_UPLOAD:
				if(file_exists($check)) {
					$check = md5_file($check);
					$field = "hash";
				} else {
					$field = "content";
				}
				break;
			case IMG_LIBRARY:
				$field = "hash";
				break;
			default:
				$field = "hash";
		}
		$condition = !empty($user_id) ? sprintf("AND user_id = %d",$user_id) : "";
		$query =sprintf("SELECT images.id AS img_id, img_sets.img_group_id
						FROM images
						LEFT JOIN img_sets on img_sets.img_id = images.id
						WHERE images.`%s` = '%s'
						%s
						LIMIT 1",
						mysql_real_escape_string($field),
						mysql_real_escape_string($check),
						$condition);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			$query = sprintf("UPDATE images
							SET time = NOW()
							WHERE id = %d",
							$row['img_id']);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			return $row;
		} else {
			return false;
		}
	}
	
	public function GetIGbyBox($box_id) {
		$query = sprintf("SELECT img_sets.img_group_id
						FROM img_links
						LEFT JOIN img_sets ON img_sets.img_id = img_links.img_id
						WHERE img_links.box_id = %d
						LIMIT 1",
						$box_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return $row['img_group_id'];
	}
	
	public function ReplaceImage($user_id, $lang_id, $content, $artwork_id, $box_id, $task_id=0, $type_id=IMG_LIBRARY) {
		$img_group_id = $this->GetIGbyBox($box_id);
		if($img_group_id === false) return false;
		$hash = md5_file($content);
		$found = $this->ImageExists($hash,$type_id,$user_id);
		if($found) {
			$img_id = $found['img_id'];
		} else {
			$query = sprintf("SELECT artworks.subjectID, campaigns.brandID
							FROM artworks
							LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
							WHERE artworks.artworkID = %d
							LIMIT 1",
							$artwork_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			if(!mysql_num_rows($result)) return false;
			$row = mysql_fetch_assoc($result);
			$subject_id = $row['subjectID'];
			$brand_id = $row['brandID'];
			$query = sprintf("INSERT INTO images
							(content, user_id, time, lang_id, type_id, brand_id, subject_id, hash)
							VALUES
							('%s', %d, NOW(), %d, %d, %d, %d, '%s')",
							mysql_real_escape_string($content),
							$user_id,
							$lang_id,
							$type_id,
							$brand_id,
							$subject_id,
							mysql_real_escape_string($hash));
			$result = mysql_query($query, $this->link) or die(mysql_error());
			$img_id = mysql_insert_id($this->link);
		}
		
		$query = sprintf("SELECT id
						FROM img_sets
						WHERE img_id = %d
						AND img_group_id = %d",
						$img_id,
						$img_group_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) {
			$query = sprintf("INSERT INTO img_sets
							(img_id, img_group_id)
							VALUES
							(%d, %d)",
							$img_id,
							$img_group_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
		}
		
		$query = sprintf("SELECT id
						FROM img_usage
						WHERE artwork_id = %d
						AND box_id = %d
						AND task_id = %d
						LIMIT 1",
						$artwork_id,
						$box_id,
						$task_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			$id = $row['id'];
			$query = sprintf("UPDATE img_usage
							SET img_id = %d
							WHERE id = %d",
							$img_id,
							$id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
		} else {
			$query = sprintf("INSERT INTO img_usage
							(img_id, artwork_id, box_id, task_id)
							VALUES
							(%d, %d, %d, %d)",
							$img_id,
							$artwork_id,
							$box_id,
							$task_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			$id = mysql_insert_id($this->link);
		}

		/*/* force empty cache for image restore
		# have to keep this in for image rebuild
		# need to remove this block once image update is fixed in INDS
		# then only updated image will appear as Link
		$Service = new EngineService($artwork_id);
		$DB = new Database();
		$file_name = $DB->GetFilenamebyArtwork($artwork_id);
		$Service->EmptyCache($file_name, $task_id);
		 * 
		 */

		return $id;
	}
	
	public function RestoreImage($artwork_id, $box_id=0, $task_id=0) {
		if(empty($box_id)) {
			$query = sprintf("SELECT boxes.uID
							FROM boxes
							LEFT JOIN pages ON boxes.PageID = pages.uID
							WHERE pages.ArtworkID = %d
							ORDER BY boxes.uID ASC",
							$artwork_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$this->RestoreImage($artwork_id,$row['uID'],$task_id);
			}
		} else {
			// force empty cache for image restore
			$Service = new EngineService($artwork_id);
			$DB = new Database();
			$file_name = $DB->GetFilenamebyArtwork($artwork_id);
			$Service->EmptyCache($file_name, $task_id);

			$query = sprintf("DELETE FROM img_usage
							WHERE artwork_id = %d
							AND box_id = %d
							AND task_id = %d",
							$artwork_id,
							$box_id,
							$task_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			return true;
		}
	}
	
	public function GetImageContent($artwork_id, $box_id, $task_id=0) {
		$query = sprintf("SELECT images.content
						FROM img_usage
						LEFT JOIN images ON images.id = img_usage.img_id
						WHERE img_usage.artwork_id = %d
						AND img_usage.box_id = %d
						AND img_usage.task_id IN (0,%d)
						ORDER BY img_usage.task_id DESC
						LIMIT 1",
						$artwork_id,
						$box_id,
						$task_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			return $row['content'];
		} else {
			return "";
		}
	}
	
	public function UploadImage($user_id, $lang_id, $content, $brand_id=0, $subject_id=0, $type_id=IMG_LIBRARY) {
		$hash = md5_file($content);
		$found = $this->ImageExists($hash,$type_id,$user_id);
		if($found) {
			$img_id = $found['img_id'];
		} else {
			$query = sprintf("INSERT INTO images
							(content, user_id, time, lang_id, type_id, brand_id, subject_id, hash)
							VALUES
							('%s', %d, NOW(), %d, %d, %d, %d, '%s')",
							mysql_real_escape_string($content),
							$user_id,
							$lang_id,
							$type_id,
							$brand_id,
							$subject_id,
							mysql_real_escape_string($hash));
			$result = mysql_query($query, $this->link) or die(mysql_error());
			$img_id = mysql_insert_id($this->link);
		}
		return $img_id;
	}
	
	public function EditImage($img_id, $user_id, $lang_id, $content="", $brand_id=0, $subject_id=0, $type_id=IMG_LIBRARY) {
		if(empty($content)) {
			$query = sprintf("UPDATE images SET
							user_id = %d,
							time = NOW(),
							lang_id = %d,
							type_id = %d,
							brand_id = %d,
							subject_id = %d,
							hash = '%s'
							WHERE id = %d",
							$user_id,
							$lang_id,
							$type_id,
							$brand_id,
							$subject_id,
							mysql_real_escape_string($hash),
							$img_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			return true;
		} else {
			$hash = md5_file($content);
			$query = sprintf("SELECT content, hash
							FROM images
							WHERE id = %d
							LIMIT 1",
							$img_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			$found = mysql_num_rows($result);
			if($found) {
				$row = mysql_fetch_assoc($result);
				$img_content = $row['content'];
				if($row['hash'] != $hash) {
					@unlink($img_content);
					$tmp_file = ROOT.TMP_DIR.basename($img_content);
					if(file_exists($tmp_file)) {
						@unlink($tmp_file);
					}
				}
			}
			$query = sprintf("UPDATE images SET
							content = '%s',
							user_id = %d,
							time = NOW(),
							lang_id = %d,
							type_id = %d,
							brand_id = %d,
							subject_id = %d,
							hash = '%s'
							WHERE id = %d",
							mysql_real_escape_string($content),
							$user_id,
							$lang_id,
							$type_id,
							$brand_id,
							$subject_id,
							mysql_real_escape_string($hash),
							$img_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			return true;
		}
	}
	
	public function DeleteImage($img_id) {
		$query = sprintf("DELETE FROM img_usage
						WHERE img_id = %d",
						$img_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		
		$query = sprintf("DELETE FROM img_sets
						WHERE img_id = %d",
						$img_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		
		$query = sprintf("SELECT content
						FROM images
						WHERE id = %d
						LIMIT 1",
						$img_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		$found = mysql_num_rows($result);
		if($found) {
			$row = mysql_fetch_assoc($result);
			$img_content = $row['content'];
			@unlink($img_content);
			$tmp_file = ROOT.TMP_DIR.basename($img_content);
			if(file_exists($tmp_file)) {
				@unlink($tmp_file);
			}
		}
		
		$query = sprintf("DELETE FROM images
						WHERE id = %d",
						$img_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		
		return true;
	}
	
	public function CheckImageStatus($artwork_id, $img_link_id) {
		$query = sprintf("SELECT images.hash, images.id
						FROM img_links
						LEFT JOIN boxes ON img_links.box_id = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						LEFT JOIN images ON img_links.img_id = images.id
						WHERE img_links.id = %d
						AND pages.ArtworkID = %d
						LIMIT 1",
						$img_link_id,
						$artwork_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		if(!empty($row['hash'])) return true;
		$query = sprintf("SELECT images.hash, images.id
						FROM img_usage
						LEFT JOIN img_links ON img_usage.box_id = img_links.box_id
						LEFT JOIN boxes ON img_links.box_id = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						LEFT JOIN images ON img_usage.img_id = images.id
						WHERE img_links.id = %d
						AND pages.ArtworkID = %d
						LIMIT 1",
						$img_link_id,
						$artwork_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		return !empty($row['hash']);
	}
	
	public function get_img_basename($img_path) {
		//mac file path may contain colons
		$img_path = basename($img_path);
		return stripos($img_path,":") ? substr($img_path,strripos($img_path,":")+1) : $img_path;
	}
	
	public function get_default_img_dir($artwork_id) {
		$query = sprintf("SELECT artworks.default_img_dir AS artwork_default_img_dir,
						campaigns.default_img_dir AS campaign_default_img_dir,
						systemconfig.default_img_dir AS company_default_img_dir
						FROM artworks
						LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
						LEFT JOIN users ON users.userID = campaigns.ownerID
						LEFT JOIN systemconfig ON systemconfig.companyID = users.companyID
						WHERE artworks.artworkID = %d
						LIMIT 1",
						$artwork_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		if(!empty($row['artwork_default_img_dir'])) {
			return $row['artwork_default_img_dir'];
		} else {
			if(!empty($row['campaign_default_img_dir'])) {
				return $row['campaign_default_img_dir'];
			} else {
				if(!empty($row['company_default_img_dir'])) {
					return $row['company_default_img_dir'];
				} else {
					return DEFAULT_IMG_DIR;
				}
			}
		}
	}
	
	public function AutoLookup($user_id, $artwork_id, $img_link_id=0) {
		$query = sprintf("SELECT users.companyID
						FROM artworks
						LEFT JOIN users ON artworks.uploaderID = users.userID
						WHERE artworks.artworkID = %d
						LIMIT 1",
						$artwork_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$company_id = $row['companyID'];

		if(empty($img_link_id)) {
			$query = sprintf("SELECT img_links.id
							FROM img_links
							LEFT JOIN images ON img_links.img_id = images.id
							LEFT JOIN boxes ON img_links.box_id = boxes.uID
							LEFT JOIN pages ON boxes.PageID = pages.uID
							WHERE pages.ArtworkID = %d
							ORDER BY img_links.id ASC",
							$artwork_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			while($row = mysql_fetch_assoc($result)) {
				$this->AutoLookup($user_id, $artwork_id, $row['id']);
			}
		} else {
			if($this->CheckImageStatus($artwork_id,$img_link_id)) return true;
			$query = sprintf("SELECT img_links.box_id, images.content, campaigns.sourceLanguageID
							FROM img_links
							LEFT JOIN images ON img_links.img_id = images.id
							LEFT JOIN boxes ON img_links.box_id = boxes.uID
							LEFT JOIN pages ON boxes.PageID = pages.uID
							LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
							LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
							WHERE img_links.id = %d
							AND pages.ArtworkID = %d
							LIMIT 1",
							$img_link_id,
							$artwork_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			if(!mysql_num_rows($result)) return false;
			$row = mysql_fetch_assoc($result);
			$box_id = $row['box_id'];
			$img_name = $this->get_img_basename($row['content']);
			$lang_id = $row['sourceLanguageID'];
			
			$default_img_dir = $this->get_default_img_dir($artwork_id);
			$local_ftp_dir = $this->format_ftp_dir($default_img_dir);
			$query = sprintf("SELECT systemName
							FROM companies
							WHERE companyID = %d
							LIMIT 1",
							$company_id);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			if(!mysql_num_rows($result)) return false;
			$row = mysql_fetch_assoc($result);
			$system_name = $row['systemName'];
			$local_path_to_ftp = ROOT.FTP_DIR.$system_name;
			if(!$this->is_local_ftp_cache_usable($company_id,$local_ftp_dir)) {
				$this->rebuild_local_ftp_cache($company_id,$local_ftp_dir,$this->local_list_dir_contents($local_path_to_ftp.$local_ftp_dir));
			}
			
			$query = sprintf("SELECT ftp_cache_local.name, ftp_cache_local_dir.dir
							FROM ftp_cache_local
							LEFT JOIN ftp_cache_local_dir ON ftp_cache_local_dir.id = ftp_cache_local.dir_id
							WHERE ftp_cache_local_dir.company_id = %d
							AND ftp_cache_local_dir.dir = '%s'
							AND ftp_cache_local.name = '%s'
							AND ftp_cache_local.type = 'file'
							LIMIT 1",
							$company_id,
							$local_ftp_dir,
							$img_name);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			if(!mysql_num_rows($result)) return false;
			
			$this->ReplaceImage($user_id, $lang_id, $local_path_to_ftp.$local_ftp_dir.$img_name, $artwork_id, $box_id);
			return true;
		}
	}

	public function ResetILRebuilt($artwork_id) {
		$query = sprintf("SELECT img_links.id AS IL
						FROM img_links
						LEFT JOIN boxes ON img_links.box_id = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						ORDER BY img_links.id ASC",
						$artwork_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$ils = "";
		while($row = mysql_fetch_assoc($result)) {
			$ils .= $row['IL'].",";
		}
		$ils = trim($ils,",");
		if(empty($ils)) return false;
		$query = sprintf("UPDATE img_links SET
						rebuilt = 0
						WHERE id IN (%s)",
						mysql_real_escape_string($ils));
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function MarkILRebuilt($IL) {
		$query = sprintf("UPDATE img_links SET
						rebuilt = 1
						WHERE id = %d",
						$IL);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		return mysql_affected_rows($this->link);
	}

	public function GetILByImg($artwork_id, $box_ref, $img_id) {
		$query = sprintf("SELECT img_links.id AS PL
						FROM img_links
						LEFT JOIN boxes ON img_links.box_id = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						WHERE pages.ArtworkID = %d
						AND boxes.BoxUID = %d
						AND img_links.img_id = %d
						AND img_links.rebuilt = 0
						ORDER BY img_links.id ASC
						LIMIT 1",
						$artwork_id,
						$box_ref,
						$img_id);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}
}
?>
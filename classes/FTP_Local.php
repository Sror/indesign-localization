<?php
require_once(CLASSES.'File_Zip.php');
class FTP_Local extends File_Zip {
	private $local_dir = '/';
	/**
	 * Change local directory
	 * Return the new directory name
	 *
	 * @param string $local_dir
	 * @return string
	 */
	function local_change_dir($local_dir) {
		$this->local_dir = $local_dir;
		return $this->format_ftp_dir($this->local_dir);
	}
	
	/**
	 * Go to local parent directory
	 * Returns new directory name or FALSE if it fails
	 *
	 * @return string
	 */
	function local_go_parent_dir() {
		$raw_local_dir = '/'.trim($this->local_get_current_dir(),'/');
		return $this->format_ftp_dir(substr($raw_local_dir,0,strrpos($raw_local_dir,'/')+1));
	}
	
	/**
	 * Get current local directory name
	 * Return the current directory name
	 *
	 * @return string
	 */
	function local_get_current_dir() {
		return $this->format_ftp_dir($this->local_dir);
	}
	
	/**
	 * List directory contents on local machine
	 * Return an array of the detailed contents, including name, type, ref, group, user, size, chmod, date, and rawlist
	 *
	 * @param string $local_dir
	 * @return array 
	 */
	function local_list_dir_contents($local_dir) {
		$local_dir = $this->format_local_dir($local_dir);
		if(!is_dir($local_dir)) return FALSE;
		$contents = array();
		if ($dir_handler = opendir($local_dir)) {
			while (($file = readdir($dir_handler)) !== FALSE) {
				$file_path = $local_dir.$file;
				/*
				* Process Control Functions
				* will not function on non-Unix platforms
				*/
				#$user_info = posix_getpwuid(fileowner($content));
				#$user = $user_info['name'];
				#$user_group_info = posix_getgrgid($user_info['gid']);
				#$group = $user_group_info['name'];
				if($file=="." || $file=="..") continue;
				$contents[] = array('name'	=>	$file,
									'type'	=>	filetype($file_path),
									'ref'	=>	filegroup($file_path),
									'group'	=>	fileowner($file_path),
									'user'	=>	fileowner($file_path),
									'size'	=>	filesize($file_path),
									'chmod'	=>	substr(sprintf('%o',fileperms($file_path)),-3),
									'date'	=>	filemtime($file_path));
			}
			closedir($dir_handler);
		}
		return $contents;
	}
	
	/**
	 * Get the local ftp cache time
	 * Return the cache time in string
	 *
	 * @param integer $company_id
	 * @param string $local_dir
	 * @return string
	 */
	public function get_local_ftp_cache_time($company_id, $local_dir) {
		$local_dir = $this->format_ftp_dir($local_dir);
		$query = sprintf("SELECT `time`
						FROM `ftp_cache_local_dir`
						WHERE `company_id` = %d
						AND `dir` = '%s'
						LIMIT 1",
						$company_id,
						$local_dir);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)==1) {
			$row = mysql_fetch_assoc($result);
			return $row['time'];
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Zip a local file or directory
	 *
	 * @param integer $local_cache_id
	 * @param string $zip
	 */
	private function zip_local_ftp_item($local_cache_id, $zip) {
		$query = sprintf("SELECT `companies`.`systemName` AS system_name,
						`ftp_cache_local_dir`.`company_id`, `ftp_cache_local_dir`.`dir`,
						`ftp_cache_local`.`name`, `ftp_cache_local`.`type`
						FROM `ftp_cache_local`
						LEFT JOIN `ftp_cache_local_dir` ON `ftp_cache_local`.`dir_id` = `ftp_cache_local_dir`.`id`
						LEFT JOIN `companies` ON `ftp_cache_local_dir`.`company_id` = `companies`.`companyID`
						WHERE `ftp_cache_local`.`id` = %d
						LIMIT 1",
						$local_cache_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)==1) {
			$row = mysql_fetch_assoc($result);
			$local_path_to_ftp = ROOT.FTP_DIR.$row['system_name'];
			$local_ftp_dir = $row['dir'].$row['name'];
			$company_id = $row['company_id'];
			switch($row['type']) {
				case "file":
					if($this->zip_open($zip)) {
						$this->zip_add_file($local_path_to_ftp.$local_ftp_dir, trim($local_ftp_dir,'/'));
					}
					break;
				case "dir":
					if($this->zip_open($zip)) {
						$this->zip_add_dir($local_ftp_dir);
					}
					$dir_id = $this->rebuild_local_ftp_cache($company_id,$local_ftp_dir,$this->local_list_dir_contents($local_path_to_ftp.$local_ftp_dir));
					$new_query = sprintf("SELECT `id`
										FROM `ftp_cache_local`
										WHERE `dir_id` = %d",
										$dir_id);
					$new_result = mysql_query($new_query, $this->link) or die(mysql_error());
					while($new_row = mysql_fetch_assoc($new_result)) {
						$this->zip_local_ftp_item($new_row['id'],$zip);
					}
					break;
			}
		}
	}
	
	/**
	 * download zipped files and directories from local ftp
	 * Return the full path of zip file
	 *
	 * @param array $local_cache_ids
	 * @return string
	 */
	public function download_local_ftp_items($local_cache_ids=array()) {
		$this->zip_construct();
		$zip = ROOT.TMP_DIR.time().".zip";
		foreach($local_cache_ids as $local_cache_id) {
			$this->zip_local_ftp_item($local_cache_id,$zip);
		}
		$this->zip_close();
		return $zip;
	}
	
	/**
	 * Rebuild local ftp cache
	 * Return the new cache directory id
	 *
	 * @param integer $company_id
	 * @param string $local_dir
	 * @param array $local_dir_contents
	 * @return integer
	 */
	public function rebuild_local_ftp_cache($company_id, $local_dir, $local_dir_contents=array()) {
		$local_dir = $this->format_ftp_dir($local_dir);
		$this->clear_local_ftp_cache($company_id, $local_dir);
		$query = sprintf("INSERT INTO `ftp_cache_local_dir`
						(`company_id`, `dir`, `time`)
						VALUES
						(%d, '%s', NOW())",
						$company_id,
						$local_dir);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		$dir_id = mysql_insert_id($this->link);
		$str = "";
		if(!empty($local_dir_contents)){
		  foreach($local_dir_contents as $local_dir_content) {
			  $str .=  sprintf("(%d, '%s', '%s', '%s', '%s', '%s', %d, '%s', FROM_UNIXTIME(%d)),",
							  $dir_id,
							  mysql_real_escape_string($local_dir_content['name']),
							  mysql_real_escape_string($local_dir_content['type']),
							  mysql_real_escape_string($local_dir_content['ref']),
							  mysql_real_escape_string($local_dir_content['group']),
							  mysql_real_escape_string($local_dir_content['user']),
							  $local_dir_content['size'],
							  mysql_real_escape_string($local_dir_content['chmod']),
							  $local_dir_content['date']);
		  }
		  $str = trim($str,",");
		  if(!empty($str)) {
			  $query = sprintf("INSERT INTO `ftp_cache_local`
							  (`dir_id`, `name`, `type`, `ref`, `group`, `user`, `size`, `chmod`, `date`)
							  VALUES
							  %s",
							  $str);
			  $result = mysql_query($query, $this->link) or die(mysql_error());
		  }
		}
		return $dir_id;
	}
	
	/**
	 * Check if the local ftp cache is usable
	 *
	 * @param integer $company_id
	 * @param string $local_dir
	 * @param integer $cache_life
	 * @return boolean
	 */
	public function is_local_ftp_cache_usable($company_id, $local_dir, $cache_life=600) {
		$local_dir = $this->format_ftp_dir($local_dir);
		$this->local_change_dir($local_dir);
		$query = sprintf("SELECT `time`
						FROM `ftp_cache_local_dir`
						WHERE `company_id` = %d
						AND `dir` = '%s'
						LIMIT 1",
						$company_id,
						$local_dir);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) {
			return FALSE;
		} else {
			$row = mysql_fetch_assoc($result);
			return !((time()-strtotime($row['time']))>$cache_life);
		}
	}
	
	/**
	 * Clear local ftp cache
	 *
	 * @param integer $company_id
	 * @param string $local_dir
	 */
	public function clear_local_ftp_cache($company_id, $local_dir) {
		$local_dir = $this->format_ftp_dir($local_dir);
		$query = sprintf("SELECT `id`
						FROM `ftp_cache_local_dir`
						WHERE `company_id` = %d
						AND `dir` = '%s'
						LIMIT 1",
						$company_id,
						$local_dir);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)==1) {
			$row = mysql_fetch_assoc($result);
			$query = sprintf("DELETE FROM `ftp_cache_local`
							WHERE `dir_id` = %d",
							$row['id']);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			$query = sprintf("DELETE FROM `ftp_cache_local_dir`
							WHERE `id` = %d",
							$row['id']);
			$result = mysql_query($query, $this->link) or die(mysql_error());
		}
	}
	
	/**
	 * Rename a local ftp item
	 *
	 * @param integer $local_cache_id
	 * @param string $local_cache_new_name
	 * @return boolean
	 */
	public function rename_local_ftp_item($local_cache_id, $local_cache_new_name) {
		$query = sprintf("SELECT `ftp_cache_local`.`name`,
						`ftp_cache_local_dir`.`dir`,
						`companies`.`systemName` AS system_name
						FROM `ftp_cache_local`
						LEFT JOIN `ftp_cache_local_dir` ON `ftp_cache_local_dir`.`id` = `ftp_cache_local`.`dir_id`
						LEFT JOIN `companies` ON `ftp_cache_local_dir`.`company_id` = `companies`.`companyID`
						WHERE `ftp_cache_local`.`id` = %d
						LIMIT 1",
						$local_cache_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return FALSE;
		$row = mysql_fetch_assoc($result);
		$dir = ROOT.FTP_DIR.$row['system_name'].$row['dir'];
		return @rename($dir.$row['name'],$dir.$local_cache_new_name);
	}
	
	/**
	 * Create a new directory on local ftp
	 *
	 * @param integer $company_id
	 * @param string $local_ftp_dir
	 * @param string $new_dir_name
	 * @return boolean
	 */
	public function local_ftp_mkdir($company_id, $local_ftp_dir, $new_dir_name) {
		$local_ftp_dir = $this->format_ftp_dir($local_ftp_dir);
		$query = sprintf("SELECT `systemName` AS system_name
						FROM `companies`
						WHERE `companyID` = %d
						LIMIT 1",
						$company_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return FALSE;
		$row = mysql_fetch_assoc($result);
		$dir = ROOT.FTP_DIR.$row['system_name'].$local_ftp_dir.$new_dir_name;
		if(file_exists($dir)) return FALSE;
		return @mkdir($dir);
	}
	
	/**
	 * Delete a local ftp file/directory
	 * Clear local ftp cache at the same time
	 *
	 * @param integer $local_cache_id
	 */
	public function delete_local_ftp_item($local_cache_id) {
		$query = sprintf("SELECT `companies`.`systemName` AS system_name,
						`ftp_cache_local_dir`.`company_id`, `ftp_cache_local_dir`.`dir`,
						`ftp_cache_local`.`name`, `ftp_cache_local`.`type`
						FROM `ftp_cache_local`
						LEFT JOIN `ftp_cache_local_dir` ON `ftp_cache_local`.`dir_id` = `ftp_cache_local_dir`.`id`
						LEFT JOIN `companies` ON `ftp_cache_local_dir`.`company_id` = `companies`.`companyID`
						WHERE `ftp_cache_local`.`id` = %d
						LIMIT 1",
						$local_cache_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)==1) {
			$row = mysql_fetch_assoc($result);
			$local_path_to_ftp = ROOT.FTP_DIR.$row['system_name'];
			$local_ftp_dir = $row['dir'].$row['name'];
			$company_id = $row['company_id'];
			switch($row['type']) {
				case "dir":
					$dir_id = $this->rebuild_local_ftp_cache($company_id, $local_ftp_dir, $this->local_list_dir_contents($local_path_to_ftp.$local_ftp_dir));
					$new_query = sprintf("SELECT `id`
										FROM `ftp_cache_local`
										WHERE `dir_id` = %d",
										$dir_id);
					$new_result = mysql_query($new_query, $this->link) or die(mysql_error());
					if(mysql_num_rows($new_result)) {
						$new_row = mysql_fetch_assoc($new_result);
						$this->delete_local_ftp_item($new_row['id']);
					}
					$this->clear_local_ftp_cache($company_id, $local_ftp_dir);
					@rmdir($local_path_to_ftp.$local_ftp_dir);
					break;
				case "file":
					$query = sprintf("DELETE FROM `ftp_cache_local`
									WHERE `id` = %d",
									$local_cache_id);
					$result = mysql_query($query, $this->link) or die(mysql_error());
					@unlink($local_path_to_ftp.$local_ftp_dir);
					break;
			}
		}
	}

	public function extract_local_ftp_item($local_cache_id) {
		$query = sprintf("SELECT `companies`.`systemName` AS system_name,
						`ftp_cache_local_dir`.`company_id`, `ftp_cache_local_dir`.`dir`,
						`ftp_cache_local`.`name`, `ftp_cache_local`.`type`
						FROM `ftp_cache_local`
						LEFT JOIN `ftp_cache_local_dir` ON `ftp_cache_local`.`dir_id` = `ftp_cache_local_dir`.`id`
						LEFT JOIN `companies` ON `ftp_cache_local_dir`.`company_id` = `companies`.`companyID`
						WHERE `ftp_cache_local`.`id` = %d
						LIMIT 1",
						$local_cache_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$local_path_to_ftp = ROOT.FTP_DIR.$row['system_name'];
		$local_ftp_dir = $row['dir'].$row['name'];
		return $this->unzip($local_path_to_ftp.$row['dir'], $row['name']);
	}
	
	/**
	 * Format a given FTP directory
	 *
	 * @param string $ftp_dir
	 * @return string
	 */
	public function format_ftp_dir($ftp_dir) {
		$ftp_dir = trim($ftp_dir,'/');
		if(!empty($ftp_dir)) {
			return '/'.$ftp_dir.'/';
		} else {
			return '/';
		}
	}
	
	/**
	 * Format a given local directory
	 *
	 * @param string $local_dir
	 * @return string
	 */
	public function format_local_dir($local_dir) {
		return trim($local_dir,'/').'/';
	}
}
?>
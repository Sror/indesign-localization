<?php
require_once(CLASSES.'FTP_Local.php');
/**
 * FTP synchronization and cache handling
 *
 */
class FTP_Sync extends FTP_Local {
	private $ftp_conn;
	private $ftp_type = FTP_BINARY;
	private $ftp_dir = '/';
	
	/**
	 * FTPSync class constructor
	 *
	 * @param string $ftp_host
	 * @param string $ftp_username
	 * @param string $ftp_password
	 * @param integer $ftp_port
	 * @param boolean $ftp_pasv
	 * @param integer $ftp_timeout
	 */
	function __construct($ftp_host, $ftp_username, $ftp_password, $ftp_port=21, $ftp_pasv=TRUE, $ftp_timeout=90) {
		// open connection
		$this->ftp_conn = ftp_connect($ftp_host,$ftp_port,$ftp_timeout);
        if($this->ftp_conn === false) throw new Exception("Fail to connect to host '$ftp_host'.");
		// user login
		$login = ftp_login($this->ftp_conn,$ftp_username,$ftp_password);
        if($login === false) throw new Exception("Fail to login to host '$ftp_host'.");
		// set mode
		$mode = ftp_pasv($this->ftp_conn, $ftp_pasv);
		/*
		* turn off the extended passive mode of server to bypass possible IPv4 sessions timeout
		* some ftp server may not support this
		* enable if necessary
		*/
		#ftp_exec($this->ftp_conn, 'EPSV4 FALSE' );
		// open database connection
		$this->db_connect();
	}
	
	function __destruct() {
		// close connection
		ftp_close($this->ftp_conn);
	}
	
	function get_ftp_type() {
		return $this->ftp_type;
	}
	
	function set_ftp_type($ftp_type) {
		return $this->ftp_type = $ftp_type;
	}
	
	/**
	 * Change directory on FTP
	 * Returns new directory name or FALSE if it fails
	 *
	 * @param string $ftp_dir
	 * @return string
	 */
	function ftp_change_dir($ftp_dir) {
		if(@ftp_chdir($this->ftp_conn,$ftp_dir)) {
			return $this->ftp_set_dir($ftp_dir);
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Go to parent directory on FTP
	 * Returns new directory name or FALSE if it fails
	 *
	 * @return string
	 */
	function ftp_go_parent_dir() {
		@ftp_cdup($this->ftp_conn);
		return $this->ftp_set_dir($this->ftp_get_current_dir());
	}
	
	/**
	 * Set private parametre $ftp_dir
	 * Return the set FTP directory name
	 *
	 * @param string $ftp_dir
	 * @return string
	 */
	function ftp_set_dir($ftp_dir) {
		$this->ftp_dir = $ftp_dir;
		return $this->format_ftp_dir($this->ftp_dir);
	}
	
	/**
	 * Get the current directory name on FTP
	 * Return the current directory name
	 *
	 * @return string
	 */
	function ftp_get_current_dir() {
		return $this->format_ftp_dir(ftp_pwd($this->ftp_conn));
	}
	
	/**
	 * List directory contents on FTP
	 * Return an array of the detailed contents, including name, type, ref, group, user, size, chmod, date, and rawlist
	 *
	 * @param string $ftp_dir
	 * @return array
	 */
	function ftp_list_dir_contents($ftp_dir='/') {
		$ftp_dir = $this->format_ftp_dir($ftp_dir);
		$raw_contents = ftp_rawlist($this->ftp_conn,$ftp_dir);
		$contents = array();
		if(empty($raw_contents)) return $contents;
		foreach($raw_contents as $raw_content) {
			/*
			* rawlist row example
			* drwxr-xr-x    4 spadmin    spadmin          4096 Nov 17  2009 .
			*/
			$row = preg_split("/[\s]+/i", $raw_content,9);
			$file = $row[8];
			if(ftp_systype($this->ftp_conn)=="UNIX") {
				// cheating for a year
				$date3 = date("Y",time()).' '.$row[7];
				if(strtotime($row[6].' '.$row[5].' '.$date3)>time()) {
					$date3 = (date("Y",time())-1).' '.$row[7];
				}
			} else {
				$date3 =$row[7];
			}
			if($file=="." || $file=="..") continue;
			$contents[] = array('name'	=>	$file,
					            'type'	=>	($row[0]{0}=='d') ? 'dir' : 'file',
					            'ref'	=>	$row[1],
					            'group'	=>	$row[2],
					            'user'	=>	$row[3],
					            'size'	=>	$row[4],
					            'chmod'	=>	$this->convert_chmod($row[0]),
					            'date'	=>	($row[0]{0}=='d') ? strtotime($row[6].' '.$row[5].' '.$date3) : ftp_mdtm($this->ftp_conn,$ftp_dir.$file));
		}
		return $contents;
	}
	
	/**
	 * Sync a single file from remote FTP
	 * Return the size of the file downloaded in bytes or FALSE if it fails
	 *
	 * @param string $ftp_dir
	 * @param string $file
	 * @param string $local_dir
	 * @return integer
	 */
	function sync_file_from_ftp($ftp_dir, $file, $local_dir) {
		$ftp_dir = $this->format_ftp_dir($ftp_dir);
		$local_dir = $this->format_local_dir($local_dir);
		if(!file_exists($local_dir)) mkdir($local_dir);
		$file_handler = fopen($local_dir.$file, 'w');
		$ftp_path = $ftp_dir.$file;
		$file_pointer = ftp_nb_fget($this->ftp_conn, $file_handler, $ftp_path, $this->ftp_type);
		while ($file_pointer == FTP_MOREDATA) {
			// can perform other operations here such as process log
			$file_pointer = ftp_nb_continue($this->ftp_conn);
		}
		if($file_pointer == FTP_FINISHED) {
			return ftp_size($this->ftp_conn,$ftp_path);
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Sync file or directories from remote ftp
	 *
	 * @param integer $remote_cache_id
	 * @param string $local_dir
	 */
	public function sync_item_from_ftp($remote_cache_id, $local_dir) {
		$local_dir = $this->format_local_dir($local_dir);
		$query = sprintf("SELECT `ftp_cache_remote_dir`.`ftp_id`, `ftp_cache_remote_dir`.`dir`,
						`ftp_cache_remote`.`name`, `ftp_cache_remote`.`type`
						FROM `ftp_cache_remote`
						LEFT JOIN `ftp_cache_remote_dir` ON `ftp_cache_remote`.`dir_id` = `ftp_cache_remote_dir`.`id`
						WHERE `ftp_cache_remote`.`id` = %d
						LIMIT 1",
						$remote_cache_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)==1) {
			$row = mysql_fetch_assoc($result);
			$ftp_id = $row['ftp_id'];
			$ftp_dir = $row['dir'];
			$ftp_file = $row['name'];
			switch($row['type']) {
				case "dir":
					$local_path = $local_dir.$ftp_file;
					if(!file_exists($local_path)) mkdir($local_path);
					$dir_id = $this->rebuild_remote_ftp_cache($ftp_id, $ftp_dir.$ftp_file, $this->ftp_list_dir_contents($ftp_dir.$ftp_file));
					$new_query = sprintf("SELECT `id`
										FROM `ftp_cache_remote`
										WHERE `dir_id` = %d",
										$dir_id);
					$new_result = mysql_query($new_query, $this->link) or die(mysql_error());
					while($new_row = mysql_fetch_assoc($new_result)) {
						$this->sync_item_from_ftp($new_row['id'],$local_path);
					}
					break;
				case "file":
					$this->sync_file_from_ftp($ftp_dir,$ftp_file,$local_dir);
					break;
			}
		}
	}
	
	/**
	 * Sync a single file to FTP
	 * Return the size of the file uploaded in bytes or FALSE if it fails
	 *
	 * @param string $local_dir
	 * @param string $file
	 * @param string $ftp_dir
	 * @return integer
	 */
	function sync_file_to_ftp($local_dir, $file, $ftp_dir) {
		$local_dir = $this->format_local_dir($local_dir);
		$ftp_dir = $this->format_ftp_dir($ftp_dir);
		@ftp_mkdir($this->ftp_conn,$ftp_dir);
		$file_handler = fopen($local_dir.$file, 'r');
		$ftp_path = $ftp_dir.$file;
		$file_pointer = ftp_nb_fput($this->ftp_conn, $ftp_path, $file_handler, $this->ftp_type);
		while ($file_pointer == FTP_MOREDATA) {
			// can perform other operations here such as process log
			$file_pointer = ftp_nb_continue($this->ftp_conn);
		}
		if($file_pointer == FTP_FINISHED) {
			return ftp_size($this->ftp_conn,$ftp_path);
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Sync a file or a directory to remote ftp
	 *
	 * @param integer $local_cache_id
	 * @param string $ftp_dir
	 */
	public function sync_item_to_ftp($local_cache_id, $ftp_dir) {
		$ftp_dir = $this->format_ftp_dir($ftp_dir);
		$query = sprintf("SELECT `companies`.`systemName` as system_name,
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
			$local_dir = $row['dir'];
			$local_file = $row['name'];
			$company_id = $row['company_id'];
			switch($row['type']) {
				case "dir":
					@ftp_mkdir($this->ftp_conn,$ftp_dir.$local_file);
					$dir_id = $this->rebuild_local_ftp_cache($company_id, $local_dir.$local_file, $this->local_list_dir_contents($local_path_to_ftp.$local_dir.$local_file));
					$new_query = sprintf("SELECT `id`
										FROM `ftp_cache_local`
										WHERE `dir_id` = %d",
										$dir_id);
					$new_result = mysql_query($new_query, $this->link) or die(mysql_error());
					while($new_row = mysql_fetch_assoc($new_result)) {
						$this->sync_item_to_ftp($new_row['id'],$ftp_dir.$local_file);
					}
					break;
				case "file":
					$this->sync_file_to_ftp($local_path_to_ftp.$local_dir,$local_file,$ftp_dir);
					break;
			}
		}
	}
	
	/**
	 * Rename a remote ftp item
	 *
	 * @param integer $remote_cache_id
	 * @param string $remote_cache_new_name
	 * @return boolean
	 */
	public function rename_remote_ftp_item($remote_cache_id, $remote_cache_new_name) {
		$query = sprintf("SELECT `ftp_cache_remote`.`name`,
						`ftp_cache_remote_dir`.`dir`
						FROM `ftp_cache_remote`
						LEFT JOIN `ftp_cache_remote_dir` ON `ftp_cache_remote_dir`.`id` = `ftp_cache_remote`.`dir_id`
						WHERE `ftp_cache_remote`.`id` = %d
						LIMIT 1",
						$remote_cache_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return FALSE;
		$row = mysql_fetch_assoc($result);
		return @ftp_rename($this->ftp_conn,$row['dir'].$row['name'],$row['dir'].$remote_cache_new_name);
	}
	
	public function remote_ftp_mkdir($remote_ftp_dir, $new_dir_name) {
		return @ftp_mkdir($this->ftp_conn,$remote_ftp_dir.$new_dir_name);
	}
	
	/**
	 * Delete a file or directory from remote ftp
	 *
	 * @param integer $remote_cache_id
	 */
	public function delete_remote_ftp_item($remote_cache_id) {
		$query = sprintf("SELECT `ftp_cache_remote_dir`.`ftp_id`, `ftp_cache_remote_dir`.`dir`,
						`ftp_cache_remote`.`name`, `ftp_cache_remote`.`type`
						FROM `ftp_cache_remote`
						LEFT JOIN `ftp_cache_remote_dir` ON `ftp_cache_remote`.`dir_id` = `ftp_cache_remote_dir`.`id`
						WHERE `ftp_cache_remote`.`id` = %d
						LIMIT 1",
						$remote_cache_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)==1) {
			$row = mysql_fetch_assoc($result);
			$ftp_id = $row['ftp_id'];
			$ftp_dir = $row['dir'];
			$ftp_file = $row['name'];
			switch($row['type']) {
				case "dir":
					$dir_id = $this->rebuild_remote_ftp_cache($ftp_id, $ftp_dir.$ftp_file, $this->ftp_list_dir_contents($ftp_dir.$ftp_file));
					$new_query = sprintf("SELECT `id`
										FROM `ftp_cache_remote`
										WHERE `dir_id` = %d",
										$dir_id);
					$new_result = mysql_query($new_query, $this->link) or die(mysql_error());
					if(mysql_num_rows($new_result)) {
						$new_row = mysql_fetch_assoc($new_result);
						$this->delete_remote_ftp_item($new_row['id']);
					}
					$this->clear_remote_ftp_cache($ftp_id, $ftp_dir.$ftp_file);
					@ftp_rmdir($this->ftp_conn,$ftp_dir.$ftp_file);
					break;
				case "file":
					$query = sprintf("DELETE FROM `ftp_cache_remote`
									WHERE `id` = %d",
									$remote_cache_id);
					$result = mysql_query($query, $this->link) or die(mysql_error());
					@ftp_delete($this->ftp_conn,$ftp_dir.$ftp_file);
					break;
			}
		}
	}
	
	/**
	 * Zip a file or directory on remote ftp
	 *
	 * @param integer $local_cache_id
	 * @param string $zip
	 */
	private function zip_remote_ftp_item($remote_cache_id, $zip) {
		$query = sprintf("SELECT `ftp_cache_remote`.`name`, `ftp_cache_remote`.`type`,
						`ftp_cache_remote_dir`.`ftp_id`, `ftp_cache_remote_dir`.`dir`
						FROM `ftp_cache_remote`
						LEFT JOIN `ftp_cache_remote_dir` ON `ftp_cache_remote`.`dir_id` = `ftp_cache_remote_dir`.`id`
						WHERE `ftp_cache_remote`.`id` = %d
						LIMIT 1",
						$remote_cache_id);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)==1) {
			$row = mysql_fetch_assoc($result);
			$ftp_id = $row['ftp_id'];
			$remote_ftp_dir = $row['dir'].$row['name'];
			switch($row['type']) {
				case "file":
					if($this->zip_open($zip)) {
						$this->sync_file_from_ftp($row['dir'],$row['name'],ROOT.TMP_DIR.$row['dir']);
						$tmp_file = ROOT.TMP_DIR.$remote_ftp_dir;
						$this->zip_add_file($tmp_file, trim($remote_ftp_dir,'/'));
						#@unlink($tmp_file);
					}
					break;
				case "dir":
					if($this->zip_open($zip)) {
						$this->zip_add_dir($remote_ftp_dir);
					}
					$dir_id = $this->rebuild_remote_ftp_cache($ftp_id,$remote_ftp_dir,$this->ftp_list_dir_contents($remote_ftp_dir));
					$new_query = sprintf("SELECT `id`
										FROM `ftp_cache_remote`
										WHERE `dir_id` = %d",
										$dir_id);
					$new_result = mysql_query($new_query, $this->link) or die(mysql_error());
					while($new_row = mysql_fetch_assoc($new_result)) {
						$this->zip_remote_ftp_item($new_row['id'],$zip);
					}
					break;
			}
		}
	}
	
	/**
	 * download zipped files and directories from remote ftp
	 * Return the full path of zip file
	 *
	 * @param array $remote_cache_ids
	 * @return string
	 */
	public function download_remote_ftp_items($remote_cache_ids=array()) {
		$this->zip_construct();
		$zip = ROOT.TMP_DIR.time().".zip";
		foreach($remote_cache_ids as $remote_cache_id) {
			$this->zip_remote_ftp_item($remote_cache_id,$zip);
		}
		$this->zip_close();
		return $zip;
	}
	
	/**
	 * Rebuild remote ftp cache
	 * Return the new cache directory id
	 *
	 * @param integer $ftp_id
	 * @param string $ftp_dir
	 * @param array $ftp_dir_contents
	 * @return integer
	 */
	public function rebuild_remote_ftp_cache($ftp_id, $ftp_dir, $ftp_dir_contents=array()) {
		$ftp_dir = $this->format_ftp_dir($ftp_dir);
		$this->clear_remote_ftp_cache($ftp_id, $ftp_dir);
		$query = sprintf("INSERT INTO `ftp_cache_remote_dir`
						(`ftp_id`, `dir`, `time`)
						VALUES
						(%d, '%s', NOW())",
						$ftp_id,
						$ftp_dir);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		$dir_id = mysql_insert_id($this->link);
		$str = "";
		foreach($ftp_dir_contents as $ftp_dir_content) {
			$str .=  sprintf("(%d, '%s', '%s', '%s', '%s', '%s', %d, '%s', FROM_UNIXTIME(%d)),",
							$dir_id,
							mysql_real_escape_string($ftp_dir_content['name']),
							mysql_real_escape_string($ftp_dir_content['type']),
							mysql_real_escape_string($ftp_dir_content['ref']),
							mysql_real_escape_string($ftp_dir_content['group']),
							mysql_real_escape_string($ftp_dir_content['user']),
							$ftp_dir_content['size'],
							mysql_real_escape_string($ftp_dir_content['chmod']),
							$ftp_dir_content['date']);
		}
		$str = trim($str,",");
		if(!empty($str)) {
			$query = sprintf("INSERT INTO `ftp_cache_remote`
							(`dir_id`, `name`, `type`, `ref`, `group`, `user`, `size`, `chmod`, `date`)
							VALUES
							%s",
							trim($str,","));
			$result = mysql_query($query, $this->link) or die(mysql_error());
		}
		return $dir_id;
	}
	
	/**
	 * Check if remote ftp cache is usbale
	 *
	 * @param integer $ftp_id
	 * @param string $ftp_dir
	 * @param integer $cache_life
	 * @return boolean
	 */
	public function is_remote_ftp_cache_usable($ftp_id, $ftp_dir, $cache_life=600) {
		$ftp_dir = $this->format_ftp_dir($ftp_dir);
		$this->ftp_change_dir($ftp_dir);
		$query = sprintf("SELECT `time`
						FROM `ftp_cache_remote_dir`
						WHERE `ftp_id` = %d
						AND `dir` = '%s'
						LIMIT 1",
						$ftp_id,
						$ftp_dir);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) {
			return FALSE;
		} else {
			$row = mysql_fetch_assoc($result);
			return !((time()-strtotime($row['time']))>$cache_life);
		}
	}
	
	/**
	 * Clear remote ftp cache
	 *
	 * @param integer $ftp_id
	 * @param string $ftp_dir
	 */
	public function clear_remote_ftp_cache($ftp_id, $ftp_dir) {
		$ftp_dir = $this->format_ftp_dir($ftp_dir);
		$query = sprintf("SELECT `id`
						FROM `ftp_cache_remote_dir`
						WHERE `ftp_id` = %d
						AND `dir` = '%s'
						LIMIT 1",
						$ftp_id,
						$ftp_dir);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)==1) {
			$row = mysql_fetch_assoc($result);
			$query = sprintf("DELETE FROM `ftp_cache_remote`
							WHERE `dir_id` = %d",
							$row['id']);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			$query = sprintf("DELETE FROM `ftp_cache_remote_dir`
							WHERE `id` = %d",
							$row['id']);
			$result = mysql_query($query, $this->link) or die(mysql_error());
		}
	}
	
	/**
	 * Get the remote ftp cache time
	 * Return the cache time in string
	 *
	 * @param integer $ftp_id
	 * @param string $ftp_dir
	 * @return string
	 */
	public function get_remote_ftp_cache_time($ftp_id, $ftp_dir) {
		$ftp_dir = $this->format_ftp_dir($ftp_dir);
		$query = sprintf("SELECT `time`
						FROM `ftp_cache_remote_dir`
						WHERE `ftp_id` = %d
						AND `dir` = '%s'
						LIMIT 1",
						$ftp_id,
						$ftp_dir);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(mysql_num_rows($result)==1) {
			$row = mysql_fetch_assoc($result);
			return $row['time'];
		} else {
			return FALSE;
		}
	}
	
	/*******************
	* Helper Functions *
	*******************/
	
	/**
	 * Convert file permission to Unix standards
	 *
	 * @param string $chmod
	 * @return string
	 */
	private function convert_chmod($chmod) {
		$trans = array(	'-' => '0',
						'r' => '4',
						'w' => '2',
						'x' => '1'	);
		$chmod = substr(strtr($chmod, $trans), 1);
		$array = str_split($chmod, 3);
		return array_sum(str_split($array[0])).array_sum(str_split($array[1])).array_sum(str_split($array[2]));
	}
}
?>
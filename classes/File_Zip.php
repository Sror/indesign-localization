<?php
require_once(CLASSES.'database.php');
class File_Zip extends Database {
	private $zip;
	/**
	 * Construct zip file
	 *
	 */
	protected function zip_construct() {
		$this->zip = new ZipArchive();
	}
	
	/**
	 * Open zip file
	 *
	 * @param string $zip
	 * @return Boolean
	 */
	protected function zip_open($zip) {
		return ($this->zip->open($zip,ZIPARCHIVE::CREATE)===TRUE);
	}
	
	/**
	 * Add a directory in zip
	 *
	 * @param string $dir
	 * @return boolean
	 */
	protected function zip_add_dir($dir) {
		return $this->zip->addEmptyDir(trim($dir,'/'));
	}
	
	/**
	 * Add a file in zip
	 *
	 * @param string $file_path
	 * @param string $file_dir
	 * @param string $file_name
	 * @return boolean
	 */
	protected function zip_add_file($full_file_path, $zip_file_path) {
		return $this->zip->addFile($full_file_path,$zip_file_path);
	}
	
	/**
	 * Close zip
	 *
	 */
	protected function zip_close() {
		$this->zip->close();
	}

	public function unzip($dir, $filename) {
		$zip = zip_open($dir.$filename);
		if(!is_resource($zip)) return false;
		while($zip_entry = zip_read($zip)) {
			if(zip_entry_open($zip, $zip_entry, "r")) {
				$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				$entry = zip_entry_name($zip_entry);
				if(substr($entry,-1)=="/" && !file_exists($dir.$entry)) {
					mkdir($dir.$entry);
				} else {
					file_put_contents($dir.zip_entry_name($zip_entry), $buf);
				}
				zip_entry_close($zip_entry);
			}
		}
		zip_close($zip);
	}
}
?>
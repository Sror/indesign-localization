<?php
/**
 * Auto detect the file type of supplied artworks
 * @author Richard Thomspon <richard.thomspon@sp-int.com>
 * @copyright 2011 StorePoint International
 */
class File_Detector {

	private $Version = null;
	private $code = null;
	private $Ext = null;
	private $TypeName = null;
	private $xmlFile = null;
	private $File = null;
	private $Filename = null;
	private $signature = null;
	private $sure = 0;

	function __construct($xmlfile, $file, $filename='') {
		$this->setXMLFile($xmlfile);
		$this->setFile($file);
		if(!empty($filename)) $this->setFilename($filename);
		$this->analyse();
	}

	public function setVersion($Version) {
		$this->Version = $Version;
	}

	public function getVersion() {
		return $this->Version;
	}

	public function setExt($Ext) {
		$this->Ext = $Ext;
	}

	public function getExt() {
		return $this->Ext;
	}

	public function setCode($code) {
		$this->code = $code;
	}

	public function getCode() {
		return $this->code;
	}

	public function setTypeName($TypeName) {
		$this->TypeName = $TypeName;
	}

	public function getTypeName() {
		return $this->TypeName;
	}

	private function setSure($sure) {
		$this->sure = $sure;
	}

	public function getSure() {
		return $this->sure;
	}

	public function getSignature() {
		return $this->signature;
	}

	public function getFilename() {
		return (!empty($this->Filename))?$this->Filename:$this->getFile();
	}

	public function setFilename($Filename) {
		$this->Filename = $Filename;;
	}

	private function setFile($File) {
		if (file_exists($File) && is_file($File)) {
			$this->File = $File;
		} else {
			trigger_error("Can not access file $File", E_USER_ERROR);
		}
	}

	public function getFile() {
		return $this->File;
	}

	private function setXMLFile($xml) {
		if (file_exists($xml) && is_file($xml)) {
			$this->xmlFile = $xml;
		} else {
			trigger_error("Can not access file $xml", E_USER_ERROR);
		}
	}

	/*
	 * ScanSignatures reads in the XML and a matches the pattern
	 * to the file to determine the closest match
	 */

	private function ScanSignatures() {
		if (is_null($this->xmlFile))
			return false;
		$XML = simplexml_load_file($this->xmlFile);
		foreach ($XML->File as $File) {
			$sure = 0;
			$Version = "";

			//Check Signature
			if (preg_match("/{$File->Pattern}/", $this->signature, $matches)) {
				$sure += ( (strlen($matches[0]) / strlen($this->signature) ) * 70);
				$Version = $File->Version;
				unset($matches[0]);
				foreach ($matches as $K => $matche) {
					$Version = str_replace("$$K", $matche, $File->Version);
				}
			}

			//Check Extension
			$ext = (strripos($this->getFilename(), '.')) ? strtolower(substr($this->getFilename(), strripos($this->getFilename(), '.') + 1)) : "";
			if (preg_match("%".preg_quote($File->Extension, '%')."%i", $ext, $matches)) {
				$sure += ( (strlen($matches[0]) / strlen($ext)) * 30);
			}

			//Save
			$attr = $File->attributes();
			$code = (!empty($attr["code"])) ? $attr["code"] : "";
			$name = (!empty($attr["name"])) ? $attr["name"] : "";
			$sure = ceil($sure);
			if ($sure >= $this->sure) {
				$this->setSure($sure);
				//$this->setExt($ext); //Passed file Ext
				$this->setExt($File->Extension); //Pattern file Ext
				$this->setTypeName($name);
				$this->setCode($code);
				$this->setVersion($Version);
			}
		}
	}

	/**
	 * analyse gets the header and runs ScanSignatures (see above)
	 */
	private function analyse() {
		$this->signature = $this->getHeader($this->File);
		$this->ScanSignatures();
	}

	/**
	 * getHeader returns the binary data that been converted to hex
	 * from the start of a file to a set length
	 * @param string $file
	 * @param int $length
	 * @return string
	 */
	private function getHeader($file, $length=55) {
		$handle = @fopen($file, "r");
		if ($handle) {
			if (($buffer = fgets($handle, $length)) !== false) {
				return $this->BinToHex($buffer);
			} else {
				return false;
			}
			fclose($handle);
		}
		return false;
	}

	/**
	 * BinToHex converts binary to a hex for easy viewing
	 * @param string $binstr
	 * @return string
	 */
	private function BinToHex($binstr) {
		$HexView = "";
		$binpos = 0;
		$binsize = strlen($binstr);
		$binr = ( ($binsize - $binpos - 16) > 16 ? 16 : $binsize - $binpos - 16 );
		while ($binr > 0) {
			for ($c = 0; $c < $binr; $c++) {
				$HexView .= sprintf("%02x", ord($binstr[$binpos + $c]));
			}
			$binpos += $binr;
			$binr = ( ($binsize - $binpos - 16) > 16 ? 16 : $binsize - $binpos - 16 );
		}
		return $HexView;
	}

}
<?php
class CSVExporter {
	private $handle;
	private $delimiter;
	private $length;
	private $limit;
	
	function __construct($file, $delimiter=',', $enclosure='"', $eol='\n') {
		$this->handle = fopen($file,'a');
		$this->delimiter = $delimiter;
		$this->enclosure = $enclosure;
		$this->eol = $eol;
	}
	
	function __destruct() {
		fclose($this->handle);
	}
	
	public function export_csv($row) {
		return fputcsv($this->handle, $row, $this->delimiter, $this->enclosure);
	}
	
}
?>
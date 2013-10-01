<?php
class CSVImporter extends Database {
	private $handle;
	private $delimiter;
	private $length;
	private $limit;
	
	function __construct($file, $delimiter=",", $length=1000, $limit=500) {
		$this->handle = fopen($file,'r');
		$this->delimiter = $delimiter;
		$this->length = $length;
		$this->limit = $limit;
	}
	
	function __destruct() {
		fclose($this->handle);
	}
	
	public function import_csv($import_id) {
		
		//get the headings
		$row = fgetcsv($this->handle, $this->length, $this->delimiter);
		$count = count($row);
		if($count==0) return false;
		
		//insert headings
		$str = "";
		$loop = 0;
		foreach($row as $k=>$v) {
			//records buffer
			if($loop > $this->limit) {
				$loop = 0;
				$this->insert_import_map($str);
				$str = "";
			}
			$str .= "(".$import_id.",'".mysql_real_escape_string("c".($k+1))."','".mysql_real_escape_string($v)."'),";
			$loop++;
		}
		$this->insert_import_map($str);
		
		//insert table columns
		$query = sprintf("SHOW COLUMNS
							FROM import_rows
							WHERE Field = '%s'", mysql_real_escape_string("c".$count));
		$result = mysql_query($query) or die(mysql_error());
		if(!mysql_num_rows($result)) {
			$query = sprintf("SHOW COLUMNS
								FROM import_rows
								WHERE Field LIKE '%s'","c%");
			$result = mysql_query($query) or die(mysql_error());
			$total = mysql_num_rows($result);
			if($total < $count) {
				$start = $total + 1;
				$str = "";
				for($i=$start; $i<=$count; $i++) {
					$str .= "ADD COLUMN c".$i." text,";
				}
				$query = sprintf("ALTER TABLE import_rows
									%s", trim($str,","));
				$result = mysql_query($query) or die(mysql_error());
			}
		}
		
		//insert data rows
		$line = 0;
		$loop = 0;
		$str = "";
		
		//building fields string
		$fields = "";
		for($k=0; $k<$count; $k++) {
			$f = "c".($k+1);
			$fields .= $f.",";
		}
		$fields = trim($fields,",");
		
		while( ($row = fgetcsv($this->handle, $this->length, $this->delimiter)) !== false ) {
			
			if($loop > $this->limit){
				$this->insert_import_rows($fields, $str);
				$loop = 0;
				$str = "";
			}
			
			//building values string
			$values = "";
			for($k=0; $k<$count; $k++) {
				$v = (!empty($row[$k])) ? $row[$k] : "";
				$values .= "'".mysql_real_escape_string($v)."',";
			}
			$str .= sprintf("(%d, %s),",
						$import_id,
						trim($values,","));
			$loop++;
			$line++;
		}
		$this->insert_import_rows($fields, $str);
		
		return $line;
	}
	
	private function insert_import_map($str) {
		$query = sprintf("INSERT INTO import_map
							(import_id, colname, label)
							VALUE
							%s",
							trim($str,","));
		$result = mysql_query($query) or die(mysql_error());
	}
	
	private function insert_import_rows($fields, $str) {
		$query = sprintf("INSERT INTO import_rows
							(import_id, %s)
							VALUES
							%s",
							$fields,
							trim($str,","));
		$result = mysql_query($query) or die(mysql_error());
	}
	
}
?>
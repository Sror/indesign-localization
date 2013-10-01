<?php

/**
 * Description of CSV
 *
 * @author MadTechie
 */
define("LastInstruction", '***********************************');
require_once(PROCESSES . "BaseProcess.php");

class CSVParsa extends Process {
	function array_to_csv($array, $header_row = true, $col_sep = ",", $row_sep = "\n", $qut = '"'){
		if (!is_array($array) or !is_array($array[0])) return false;
		//Header row.
		if ($header_row){
			foreach ($array[0] as $key => $val){
				//Escaping quotes.
				$key = str_replace($qut, "$qut$qut", $key);
				$output .= "$col_sep$qut$key$qut";
			}
			$output = substr($output, 1)."\n";
		}
		//Data rows.
		foreach ($array as $key => $val){
			$tmp = '';
			foreach ($val as $cell_key => $cell_val){
				//Escaping quotes.
				$cell_val = str_replace($qut, "$qut$qut", $cell_val);
				$tmp .= "$col_sep$qut$cell_val$qut";
			}
			$output .= substr($tmp, 1).$row_sep;
		}
		return $output;
	}
	
	function createArray($exportPara,$field){
		$CSVdata = Array();
		$table = Array();
		$counter=1;
		$lastRow=null;
		foreach($exportPara as $eParas){
			$query_table_data = sprintf("SELECT extra
				    FROM para_extra
				    WHERE para_link_id = %d", $eParas['PL']);
			$tableRs = mysql_query($query_table_data) or die(mysql_error());
			if(mysql_num_rows($tableRs)==1){
				$row_tableRs = mysql_fetch_assoc($tableRs);
				list($Col,$Row) = explode(':',$row_tableRs['extra']);
				if(is_numeric($Col) && is_numeric($Row)){
					if($lastRow!=$Row){
						$counter++;
						$lastRow=$Row;
						if(!empty($table)){
							$table = $this->fillEmptyCells($table);
							$CSVdata = array_merge($CSVdata,$table);
							$table = Array();
						}
					}
					$table[$Row][$Col] = $eParas[$field];
					continue;
				}
			}
			if(!empty($table)){
				$table = $this->fillEmptyCells($table);
				$CSVdata = array_merge($CSVdata,$table);
				$table =Array();
			}
			$CSVdata[] = Array($eParas[$field]);
		}
		if(!empty($table)){
			$table = $this->fillEmptyCells($table);
			$CSVdata = array_merge($CSVdata,$table);
			$table =Array();
		}
		return $CSVdata;
	}
	
	function fillEmptyCells($table){
		$newArray = Array();
		foreach($table as $col => $row){
			$newArray[$col] = array_fill(0, end(array_keys($row))+1, '');
			foreach($table[$col] as $k => $row){
				$newArray[$col][$k] = $table[$col][$k];
			}
		}
		return $newArray;
	}
	
	function createDataArray($exportPara){
		return $this->createArray($exportPara,'ParaText');
	}
	function createPLArray($exportPara){
		return $this->createArray($exportPara,'PL');
	}
	
	function CreateCSVFile($ArtworkID, $TaskID, $lines=0, $Author="", $Operator="", $Company="") {
		if(empty($ArtworkID) || !empty($TaskID)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$ArtworkName = $row['artworkName'];
		$SourceLangID = $row['sourceLanguageID'];
		$SourceLangFlag = substr($row['flag'],0,2);

		$exportPara = $this->ExportPara($ArtworkID,$TaskID,false);
		
		$paras = count($exportPara);
		if ($paras == 0) return false;

		$counter = (!empty($lines))?$lines:$paras;
		//Use amended Data
		foreach($exportPara as $key => $row) {
			if($counter==0) break;
			$Para = $row['ParaText'];
			$amended = $this->GetAmendedPara($row['PL']);
			if($amended!==false && empty($lines)) {
				$Para = $amended['ParaText'];
			}
			$exportPara[$key]['ParaText'] = $Para;
			$counter--;
		}
				
		$CSVdata = $this->createDataArray($exportPara);

		if (empty($CSVdata)) return false;
		//Create CSV
		$csv_data = $this->array_to_csv($CSVdata, false);

		/*
		$query_inCheckRs = sprintf("
			SELECT content FROM instructions
			WHERE sourceLangID = %d", $SourceLangID);
		$inCheckRs = mysql_query($query_inCheckRs) or die(mysql_error());
		$totalRows_inCheckRs = mysql_num_rows($inCheckRs);

		$SLID = ($totalRows_inCheckRs > 0) ? $SourceLangID : 1;

		$query_instructionRs = sprintf("SELECT content
				    FROM instructions
				    WHERE sourceLangID = %d",
						$SLID);
		$instructionRs = mysql_query($query_instructionRs) or die(mysql_error());
		$row_instructionRs = mysql_fetch_assoc($instructionRs);
		*/
		$File = $ArtworkName . "_" . $SourceLangFlag . "_to_" . $TargetLangFlag;
		if (!empty($lines))  $File .= "_sample";

		$File .= ".CSV";
		#header('Content-Encoding: UTF-8');
		#header('Content-type: text/csv; charset=UTF-8');
		#header('Content-Disposition: attachment; filename=Customers_Export.csv');
		#echo "\xEF\xBB\xBF"; // UTF-8 BOM
		
		file_put_contents(ROOT.TMP_DIR.$File,"\xEF\xBB\xBF".$csv_data);
		return $File;
	}
	
	function ReadCSV($File){
		$data = Array();
		if (($handle = fopen($File, "r")) !== FALSE) {
			while (!feof($handle)) {
				$data[] = fgetcsv($handle);
			}
			return $data;
		}
		return false;
	}

	function export($ArtworkID, $TaskID, $lines=0) {
		return $this->CreateCSVFile($ArtworkID, $TaskID, $lines);
	}
	
    function Multi2SingleArray($MultiArray, &$return= Array()) {
        if (!is_array($MultiArray)) return $return[] = $MultiArray;
        foreach ($MultiArray as $Key => $Entry)
			$this->Multi2SingleArray($Entry,$return);
        return $return;
    }
	
	function checkValue($value){
		return !empty($value);
	}

	function import($ArtworkID, $TaskID, $file, $option=1, $CS=true) {
		if(empty($ArtworkID) || !empty($TaskID)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$sourceLangID = $row['sourceLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		//Get Data from CSV
		$CSVData = $this->ReadCSV($file);
		if (!$CSVData) return false;
		//Remove last item "FALSE" ??
		if($CSVData[count($CSVData)-1]===false) unset($CSVData[count($CSVData)-1]);
		
		//Get PL Data from Database
		$exportPara = $this->ExportPara($ArtworkID,$TaskID,false);
		
		if (count($exportPara) == 0) return false;
		$DBdata = $this->createDataArray($exportPara);
		$DBPLdata = $this->createPLArray($exportPara);
		
		//Cleanup
		$Data = Array();
		foreach($DBdata as $key => $data){
			foreach($data as $r => $rows){
				if(empty($DBPLdata[$key][$r])) continue;
				$OldData[$DBPLdata[$key][$r]] = $DBdata[$key][$r];
				$Data[$DBPLdata[$key][$r]] =  utf8_encode($CSVData[$key][$r]);
			}
		}
		unset($CSVData,$DBdata,$DBPLdata);
		
		/*
		//Merge Data into one Array using PL's as keys
		$PLs = $this->Multi2SingleArray($DBPLdata);
		$OldDATAs = $this->Multi2SingleArray($DBdata);
		$DATAs = $this->Multi2SingleArray($CSVData);
		
		var_dump($PLs,$OldDATAs,$DATAs);die();
		
		//remove unused
		$PLs = array_filter($PLs, array($this,"checkValue"));
		$OldDATAs = array_filter($OldDATAs, array($this,"checkValue"));
		$DATAs = array_filter($DATAs, array($this,"checkValue"));
		
		$OldData = array_combine($PLs, $OldDATAs);
		//unset($OldData[""]);
		
		var_dump($PLs,$DATAs);die();
		$Data = array_combine($PLs, $DATAs);
		
		//clean up
		unset($DATAs,$PLs,$DBPLdata,$DBdata,$CSVData);
		*/
		$loose = $CS===true ? 0 : 1 ;
		$import_id = $this->ImportStart($ArtworkID,$TaskID,"CSV",$option,$loose);
		
		$imported = false;
		foreach($Data as $PL=>$AmendedPara) {
			//Get ParaGroup from Database;
			$OrgPara = $OldData[$PL];
			
			$source_para_row = $this->ParaExists($OrgPara,$sourceLangID,$CS);
			//Get ParaGroup from Database;
			if($source_para_row === false || (empty($option) && $AmendedPara==$OrgPara)) {
				$this->AddImportRow($import_id,$OrgPara,$AmendedPara,0);
			} else {
				//we have to loop through all the PLs with ParaID in this artwork
				$source_para_id = $source_para_row['ParaID'];
				$new_para_row = $this->AddParagraph($AmendedPara,$sourceLangID,0,$_SESSION['userID'],PARA_USER,$brandID,$subjectID);
				if($new_para_row === false) continue;
				$new_para_id = $new_para_row['ParaID'];
				$query = sprintf("SELECT paralinks.uID, paralinks.BoxID
								FROM paralinks
								LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
								LEFT JOIN pages ON boxes.PageID = pages.uID
								WHERE pages.ArtworkID = %d
								AND paralinks.ParaID = %d
								ORDER BY paralinks.uID ASC",
								$ArtworkID,
								$source_para_id);
				$result = mysql_query($query) or die(mysql_error());
				while($row = mysql_fetch_assoc($result)) {
					$PL = $row['uID'];
					$BoxID = $row['BoxID'];
					$amended_para_row = $this->GetAmendedPara($PL);
					if($amended_para_row !== false) {
						$amended_para_id = $amended_para_row['ParaID'];
						if($amended_para_id == $new_para_id) continue;
					}
					$this->AmendPara($PL,$new_para_id,$_SESSION['userID']);
					//added for cache optimisation
					$this->AddChangedItem($ArtworkID,$BoxID,$TaskID);
				}
				$this->AddImportRow($import_id,$OrgPara,$AmendedPara,1);
			}
			$imported = true;
			
		}
		$this->ImportEnd($import_id);
		if(!$imported) return false;
		return $import_id;
	}

}
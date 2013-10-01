<?php
/**
 * Testing, This is the main Description
 * 
 * This file demonstrates the use of the @name tag
 * @author Richard Thompson <richard.thompson@sp-int.com>
 * @version 1.0
 * @package sample
 */
/**
 * RTF Class
 * Here is an inline example:
 * <code>
 * <?php
 * echo strlen('999');
 * ?>
 * </code>
 * @example /path/to/example.php How to use this function
 * @example anotherexample.inc This example is in the "examples" subdirectory
 * function test, access is public, will be documented
 * @access public
 * @author Richard Thompson <richard.thompson@sp-int.com>
 * @copyright Copyright (c) 2008, StorePoint International Limited
 * @version -6
 * @param string $Name 
 * @param array $ar 
 * @return bool
 * @todo make it do something
 * @uses subclass sets a temporary variable 
 * @package sample
 */
require_once(PROCESSES."BaseProcess.php");
class RTFEngine extends Process {
	//private $Trans;
	private $BookmarkTag = "TAG";
	/**
	 * @name UnicodeParagraphs
	 * @access private
	 * @var array
	 */
	public $UnicodeParagraphs = array();
	public $UnicodeParagraphs2 = array();

	/**
	 * @name HTMLEncodeParagraphs
	 * @access private
	 * @var array 
	 */
	private $HTMLEncodeParagraphs = array();

	private $RTFInfo = "";
	private $Instructions = "";

	private $RTFParagraphs = array();
	/*function __construct() {
	require_once(CLASSES.'translator.php');
	$this->Trans = new Translator();
	}*/

	/**
	 * RTF test function
	 * @access private
	 * @author Richard Thompson <richard.thompson@sp-int.com>
	 * @copyright Copyright (c) 2008, StorePoint International Limited
	 * @version 0.0.5
	 * @param string $Filename 
	 * @return bool
	 * @todo make it do something
	 * @uses html_entity_decode_utf8 and escapeUnicode
	 */
	public function ReadRTF($Filename="") {
		$rtf =file_get_contents($Filename);
		$sections = array();
		//Swap Bookmakr Start and End around
		$rtf = preg_replace('/\{\\\\\*\\\\bkmkstart '.$this->BookmarkTag.'(\d+)\}\{\\\\\*\\\\bkmkend '.$this->BookmarkTag.'(\d+)\}/sim', '{\*\bkmkend '.$this->BookmarkTag.'\2}{\*\bkmkstart '.$this->BookmarkTag.'\1}', $rtf);

		#preg_match_all('/\{\\\\\*\\\\bkmkstart '.$this->BookmarkTag.'(\d+)\}(.*?)\{\\\\\*\\\\bkmkend '.$this->BookmarkTag.'\1\}/sim', $rtf, $sections, PREG_SET_ORDER);

		/*
		\{\\\*\\bkmkstart TAG(\d+)\}(.*?)\{\\fldrslt(.*?)\{\\\*\\bkmkend TAG\1\}
		*/
		preg_match_all('/\{\\\\\*\\\\bkmkstart '.$this->BookmarkTag.'(\d+)\}(.*?)\{\\\\fldrslt(.*?)\{\\\\\*\\\\bkmkend '.$this->BookmarkTag.'\1\}/sim', $rtf, $sections, PREG_SET_ORDER);
		if(count($sections)< 1) return false;

		foreach($sections as $section) {
			//?$area[$result[1]] = $result[3];

			#echo "<pre>";
			#var_dump($sections[0],$sections[1]);
			#die;
			$PG = $section[1];
			/*	\{\\\*\\ffdeftext(.*?)\}{\\\*\\ffstattext */
			if (preg_match('/\{\\\\\*\\\\ffdeftext(.*?)\}.*?\{\\\\\*\\\\ffstattext/sim', $section[2], $regs)) {
				$PreTranslated = $regs[1];
			} else {
				$PreTranslated = "";
				#echo "Huh!";
			}
			$A = $section[3];

			#foreach($area as $PG=>$A)
			#{
			/* \{\\rtlch\\fcs1 \\af31507 \\ltrch\\fcs0 \\v\\insrsid9796051\\charrsid9796051 (\d+)\} */
			#preg_match('/\{\\\\rtlch\\\\fcs1 \\\\af31507 \\\\ltrch\\\\fcs0 \\\\v\\\\insrsid9796051\\\\charrsid9796051 (\d+)\}/sim', $A, $result);
			#$PG = $result[1];

			//Parapraphs
			$result = array();
			#preg_match_all('/\{\\\\fldrslt(.*?)\\\\sectd/sim', $A, $result, PREG_PATTERN_ORDER);
			#preg_match_all('/\{\\\\fldrslt(.*?)\{\\\\rtlch\\\\fcs1 \\\\af31507 \\\\ltrch\\\\fcs0 \\\\insrsid12519842 \\\\par \}$/sim', $A, $result, PREG_PATTERN_ORDER);
			#var_dump($PG,$A,"<br>");
			if (preg_match('/^(.*?)\\\\par\s*\}/si', $A, $regs)) {
				$A = $regs[1];
			}
			/* Patten =(?<!\\){|(?<!\\)}|\\\w+(?:\s)|\\[^u\']\w+|\r\n|\\(?=})|\\(?={) */

			$result = $this->ParagraphsCleanUP($A);

			#echo "<BR>.$PG".$result[0];
			#}
			if(empty($result)) continue;

			/* Patten =(\\'[A-F\d]{2}|\\u([-\d]*)) */
			//$clean = preg_replace_callback('/\\\\\'([a-f\d]{2})|\\\\u([-\w]*)/sim', array($this,'escapeUnicode'),$result);

			/**
			 * Remove two hex ASCII Chars after unicode!
			 * Also breaks accents
			 */
			///AHHHHHH -  Not sure about this
			//$clean = preg_replace_callback('/\\\\\'([a-f\d]{2})|\\\\u([-\w]*)(?:(?:\\\\\'[a-f\d]{2}){2})/sim', array($this,'escapeUnicode'),$result);
			$clean = preg_replace_callback('/\\\\\'([a-f\d]{2})|\\\\u([-\w]+)(?:(?:\\\\\'[a-f\d]{2}){1,2})|\\\\u(\d+)/sim', array($this,'escapeUnicode'),$result);
			//$PreTranslated = $this->html_entity_decode_utf8(preg_replace('/\\\\\'([a-f\d]{2})/si', '&#x\1;', $PreTranslated));
			
			//$clean = $result;
			$PreTranslated =  html_entity_decode($PreTranslated, ENT_NOQUOTES,"UTF-8");

			$this->UnicodeParagraphs[$PG] = $this->html_entity_decode_utf8($clean);
			$this->UnicodeParagraphs2[$PreTranslated] = $this->html_entity_decode_utf8($clean);
			$this->HTMLEncodeParagraphs[$PG] = $clean;
		}
		return true;
	}

	private function ParagraphsCleanUP($para) {
		/*Also changed line 119*/
		$tags = array(
		  '\lquote'=>'&#8216;',
		  '\rquote'=>"&#8217;",
		  '\\\'93'=>"&#8220;",
		  '\\\'94'=>'&#8221;',
		  '\\\'99'=>'&#8482;',
		  '\\\'80'=>'&euro;',
		  '\\\tab'=>'&#09;',
		  );
		$para = str_ireplace(array_keys($tags),$tags,$para);
		$para = preg_replace('/\r\n+/sim', '', $para);

		//$para = preg_replace('/(?<!\\\\)\{|(?<!\\\\)\}|\\\\\w+(?:\s)|\\\\[^u\']\w+|\r\n|\\\\(?=\})|\\\\(?=\{)/sim', '', $para);
		//$para = preg_replace('/\\\\uc\d\\\\u/im', '\u', $para);
		$para = preg_replace('/(?<!\\\\)\{|(?<!\\\\)\}|\\\\[^u]\w+(?:\s)|\\\\[^u\']\w+|\r\n|\\\\(?=\})|\\\\(?=\{)/sim', '', $para);
		$para = preg_replace('/\\\\uc\d\\\\u/im', '\u', $para);
		$para = substr($para,1);
		return $para;
	}
	
	public function GetHTMLEncodeParagraphs() {
		return $this->HTMLEncodeParagraphs;
	}
	
	public function GetUnicodeParagraphs() {
		return $this->UnicodeParagraphs;
	}

	public function RTFInfo($author="", $operator="", $Company="") {
		#<Info>
		$year = date("Y");
		$month = date("m");
		$day = date("d");
		$hr = date("G");
		$min = trim(date("i"),"0");
		$this->RTFInfo = "{\\info{\\author $author}{\\operator $operator}{\\creatim\\yr$year\\mo$month\\dy$day\\hr$hr\\min$min}{\\revtim\\yr$year\\mo$month\\dy$day\\hr$hr\\min$min}{\\version3}{\\edmins17}{\\nofpages2}{\\nofwords2}{\\nofchars24}{\\*\\company $Company}{\\nofcharsws0}{\\vern32895}{\\*\\passwordhash 010000004c000000010000000480000050c300001400000010000000b86be503cc6be50300000000872817180e74fadccf51093b10a43111354c5509f7df6ef30aaa10da526742c3d09973a8}}";
		return true;
	}

	public function AddPara($PS, $FromPara, $ToPara=NULL, $ToolTip="") {
		if(empty($PS)) return false;
		$ToPara = (isset($ToPara))?$ToPara:$FromPara;
		if(empty($FromPara) || isset($this->RTFParagraphs[$FromPara])) {
			return false;
		}
		$this->RTFParagraphs[$FromPara] = array("PS"=>$PS,"To"=>$ToPara,"ToolTip"=>$ToolTip);
		return true;
	}
	
	public function ClearRTFParagraphs() {
		unset($this->RTFParagraphs);
		return true;
	}

	private function BuildParagraphs() {

		//{\rtlch\fcs1 \af31507 \ltrch\fcs0 \v\insrsid9796051\charrsid9796051 10}
		#/*
		$basePara = '{\rtlch\fcs1 \af31507 \ltrch\fcs0 \insrsid12519842 <FROM> \par }{\field{\*\fldinst {\rtlch\fcs1 \af31507 \ltrch\fcs0 \insrsid5472213  FORMTEXT }{\rtlch\fcs1 \af31507 \ltrch\fcs0 \insrsid12650463 {\*\datafield
000100001400000000000f666f78206a756d706564206f766572000000000022456e7465722027666f78206a756d706564206f7665722720696e206368696e6573650000000000}{\*\formfield{\fftype0\ffownstat\fftypetxt0\ffhps20{\*\ffdeftext <TO>}{\*\ffstattext 
<TIP>}}}}}{\fldrslt {\rtlch\fcs1 \af31507 \ltrch\fcs0 \lang1024\langfe1024\noproof\insrsid5472213 <TO>}}}{\rtlch\fcs1 \af31507 \ltrch\fcs0 \insrsid12519842 \par }';
		# */

		/*
		$basePara = '{\rtlch\fcs1 \af31507 \ltrch\fcs0 \insrsid12519842 <FROM> \par }{\rtlch\fcs1 \af31507 \ltrch\fcs0 \v\insrsid9796051\charrsid9796051 <PS>}{\field{\*\fldinst {\rtlch\fcs1 \af31507 \ltrch\fcs0 \insrsid5472213  FORMTEXT }{\rtlch\fcs1 \af31507 \ltrch\fcs0 \insrsid12650463 {\*\datafield
		000100001400000000000f666f78206a756d706564206f766572000000000022456e7465722027666f78206a756d706564206f7665722720696e206368696e6573650000000000}{\*\formfield{\fftype0\ffownstat\fftypetxt0\ffhps20{\*\ffdeftext <TO>}{\*\ffstattext
		<TIP>}}}}}{\fldrslt {\rtlch\fcs1 \af31507 \ltrch\fcs0 \lang1024\langfe1024\noproof\insrsid5472213 <TO>}}}{\rtlch\fcs1 \af31507 \ltrch\fcs0 \insrsid12519842 \par }';
		*/
		$Paragraph = "";
		#$n=0;
		foreach($this->RTFParagraphs as $From => $V) {
			#$n++;
			$Para = $basePara;
			#$Para = str_replace("<Instructions>",$this->RTFutf8Unicode($this->Instructions),$Para);
			#$Para = str_replace("<PS>",$V['PS'],$Para); //paraset
			$Para = str_replace("<FROM>",$this->RTFutf8Unicode($From),$Para);
			$Para = str_replace("<TO>",$this->RTFutf8Unicode($V['To']),$Para);
			$Para = str_replace("<TIP>",$this->RTFutf8Unicode($V['ToolTip']),$Para);
			$Paragraph .= '{\*\bkmkstart '.$this->BookmarkTag.$V['PS'].'}'.$Para.'{\*\bkmkend '.$this->BookmarkTag.$V['PS'].'}';
		}
		return $Paragraph;
	}

	public function SetInstructions($instructions = "Some Instructions") {
		$this->Instructions = $instructions;
	}

	public function BuildRTF($output="") {
		if(empty($this->RTFInfo)) {
			$this->RTFInfo(); //Build Info
		}
		$RTF = $this->BaseRTF();
		$Paragraph = $this->BuildParagraphs();
		#$hidden = '{\rtlch\fcs1 \af31507 \ltrch\fcs0 \v\insrsid15089003 '."This is hidden".'}';
		$RTF = str_replace("<Instructions>",$this->RTFutf8Unicode($this->Instructions),$RTF);
		$RTF = str_replace("<Section>",$Paragraph,$RTF);
		$RTF = str_replace("<Info>",$this->RTFInfo,$RTF);
		if(empty($output)) return $RTF;
		return file_put_contents($output,$RTF);
	}

	private function BaseRTF() {
		return file_get_contents(RESOURCES."BaseTemplate");
	}


	/*private function escapeUnicode($var)
	{
	if(!empty($var[2]) && is_numeric($var[2]))
	{
	$uchr = (float)$var[2];
	return sprintf("&#%d;",($uchr < 0)?$uchr+65536:$uchr);
	}
	return "";
	}*/


	private function RTFutf8Unicode($str) {
		return $this->unicodeToEntitiesPreservingAscii($this->utf8ToUnicode($str));
	}

	private function unicodeToEntitiesPreservingAscii($unicode) {
		$entities = '';
		foreach( $unicode as $value )
		{
			if ($value != 65279)
			{
				$entities .= ( $value > 127 ) ? '\uc0\u' . $value . ' ' : chr( $value );
			}
		}
		return $entities;
	}

	function CreateRTFFile($ArtworkID, $TaskID, $lines=0, $Author="", $Operator="", $Company="") {
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$ArtworkID = $row['artworkID'];
		$ArtworkName = $row['artworkName'];
		$SourceLangID = $row['sourceLanguageID'];
		$SourceLangFlag = substr($row['SourceLangFlag'],0,2);
		$TargetLangID = $row['desiredLanguageID'];
		$TargetLangFlag = substr($row['TargetLangFlag'],0,2);
		
		$exportPara = $this->ExportPara($ArtworkID,$TaskID);

		$paras = count($exportPara);
		if($paras==0) return false;

		if(!empty($lines)) {
			$counter = $lines;
		} else {
			$counter = $paras;
		}
		
		$RTF = new RTFEngine();
		$RTF->ClearRTFParagraphs();
		$RTF->RTFInfo($Author, $Operator, $Company);

		$query_inCheckRs = sprintf("
			SELECT content FROM instructions
			WHERE sourceLangID = %d", $SourceLangID);
		$inCheckRs = mysql_query($query_inCheckRs) or die(mysql_error());
		$totalRows_inCheckRs = mysql_num_rows($inCheckRs);

		$SLID = ($totalRows_inCheckRs>0) ? $SourceLangID : 1;

		mysql_query("SET CHARACTER SET utf8");
		mysql_query("SET NAMES 'utf8'");
		$query_instructionRs = sprintf("SELECT content
										FROM instructions
										WHERE sourceLangID = %d",
										$SLID);
		$instructionRs = mysql_query($query_instructionRs) or die(mysql_error());
		$row_instructionRs = mysql_fetch_assoc($instructionRs);

		$RTF->SetInstructions($row_instructionRs['content']);
		
		$Required = false;
		foreach($exportPara as $rows) {
			if($counter==0) break;
			$Trans = $this->TranslateText($rows['ParaID'], $TargetLangID, $SourceLangID);
			if($Trans['LC'] == 0)
			{
				$X = $RTF->AddPara($Trans['PG'],$rows['ParaText'],null,"Please translate '{$rows['ParaText']}' to the desired language.");
				$Required = true;
				$counter--;
			} else {
				//Add Translated to RTF if not sample RTF
				if(empty($lines)) {
					$X = $RTF->AddPara($Trans['PG'],$rows['ParaText'],$Trans['Para'],"'{$rows['ParaText']}' has been translated as '{$Trans['Para']}'.");
					$Required = true;
					$counter--;
				}
			}
		}
		if($Required === false) return false;
		
		$File = $ArtworkName."_".$SourceLangFlag."_to_".$TargetLangFlag;
		if(!empty($lines)) {
			$File .= "_sample";
		}
		$File .= ".RTF";
		$RTF->BuildRTF(ROOT.TMP_DIR.$File);
		return $File;
	}

	function export($ArtworkID, $TaskID, $lines=0) {
		return $this->CreateRTFFile($ArtworkID,$TaskID,$lines);
	}
	
	function import($ArtworkID, $TaskID, $file, $option=1, $CS=true) {
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$sourceLanguageID = $row['sourceLanguageID'];
		$TargetLangID = $row['desiredLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		$Decoded = $this->ReadRTF($file);
		if($Decoded === false) return false;
		$loose = $CS===true ? 0 : 1 ;
		$import_id = $this->ImportStart($ArtworkID,$TaskID,"RTF",$option,$loose);
		$par = $this->GetUnicodeParagraphs();
		//require_once(CLASSES."translator.php");
		//$Translator = new Translator();
		foreach($par as $ParaGroup=>$p) {
			$para_row = $this->GetParaByPG($ParaGroup,$sourceLanguageID);
			if($para_row === false || (empty($option) && $p==$para_row['ParaText'])) {
				$this->AddImportRow($import_id,$para_row['ParaText'],$p,0);
			} else {
				$this->AddTranslated($p,$TargetLangID,$ParaGroup,$sourceLanguageID,$TaskID,$_SESSION['userID'],PARA_IMPORT,$brandID,$subjectID);
				$this->AddImportRow($import_id,$para_row['ParaText'],$p,1);
			}
		}
		$this->ImportEnd($import_id);
		return $import_id;
	}
}
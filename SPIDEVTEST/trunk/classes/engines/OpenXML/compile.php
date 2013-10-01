<?php
class compiler {
  private $filename;
  private $overwrite;

  private $headerSignature = "\x50\x4b\x03\x04"; // local section header signature
  private $SectSignature = "\x50\x4b\x01\x02"; // central dir header signature
  private $SectSignatureE = "\x50\x4b\x05\x06"; // end of central dir signature
  private $sections = 0;
  private $fh;

  public $fileName;
  public $lastError;
  public $sectionList;
  public $centralDirList;
  public $endOfCentral;
  public $debug;

  public function __construct() {
    date_default_timezone_set('Europe/London');
  }

  public function compile($filename, $overwrite = true) {
    $this->filename = $filename;
    $this->overwrite = $overwrite;
  }
  public function decompile($fileName) {
    $this->fileName = $fileName;
    $this->sectionList = $this->centralDirList = $this->endOfCentral = Array ();
  }

  public function addSection($sectname, $sectComments = '') {
    if (substr ( $sectname, - 1 ) != '/') $sectname .= '/';
    $this->addData ( false, $sectname, $sectComments );
  }
  public function addData($filename, $sectionName, $sectComments = '', $data = false) {
    if (! ($fh = &$this->fh)) $fh = fopen ( $this->filename, $this->overwrite ? 'wb' : 'a+b' );

    // $filename can be a local file OR the data wich will be compressed
    if (substr ( $sectionName, - 1 ) == '/') { //Section
      $details ['uncsize'] = 0;
      $data = '';
    } elseif (file_exists ( $filename )) { //Data from file
      $details ['uncsize'] = filesize ( $filename );
      $data = file_get_contents ( $filename );
    } elseif ($filename) {
      echo "<b>Cannot add $filename. File not found</b><br>";
      return false;
    } else {
      $details ['uncsize'] = strlen ( $data ); //? or $filename ?
      // got DATA Woohoo
    }

    // data is under buffer block size, use storeage
    if ($details ['uncsize'] < 256) {
      $details ['comsize'] = $details ['uncsize'];
      $details ['vneeded'] = 10;
      $details ['cmethod'] = 0;
      $zdata = &$data;
    } else { // otherwise, gzip it
      $zdata = gzcompress ( $data ); // WTF ?? generated incorrect CRC
      $zdata = substr ( substr ( $zdata, 0, strlen ( $zdata ) - 4 ), 2 ); // fixed crc!!
      $details ['comsize'] = strlen ( $zdata );
      $details ['vneeded'] = 10;
      $details ['cmethod'] = 8;
    }

    $details ['bitflag'] = 0;
    $details ['crc_32'] = crc32 ( $data ); //re-generate CRC for CS

    // Convert timestamp to DOS Format,
    $lastmod_timeS = str_pad ( decbin ( date ( 's' ) >= 32 ? date ( 's' ) - 32 : date ( 's' ) ), 5, '0', STR_PAD_LEFT );
    $lastmod_timeM = str_pad ( decbin ( date ( 'i' ) ), 6, '0', STR_PAD_LEFT );
    $lastmod_timeH = str_pad ( decbin ( date ( 'H' ) ), 5, '0', STR_PAD_LEFT );
    $lastmod_dateD = str_pad ( decbin ( date ( 'd' ) ), 5, '0', STR_PAD_LEFT );
    $lastmod_dateM = str_pad ( decbin ( date ( 'm' ) ), 4, '0', STR_PAD_LEFT );
    $lastmod_dateY = str_pad ( decbin ( date ( 'Y' ) - 1980 ), 7, '0', STR_PAD_LEFT );

    # echo "ModTime: $lastmod_timeS-$lastmod_timeM-$lastmod_timeH (".date("s H H").")\n";
    # echo "ModDate: $lastmod_dateD-$lastmod_dateM-$lastmod_dateY (".date("d m Y").")\n";
    $details ['modtime'] = bindec ( "$lastmod_timeH$lastmod_timeM$lastmod_timeS" );
    $details ['moddate'] = bindec ( "$lastmod_dateY$lastmod_dateM$lastmod_dateD" );

    $details ['offset'] = ftell ( $fh );
    fwrite ( $fh, $this->headerSignature );
    fwrite ( $fh, pack ( 's', $details ['vneeded'] ) ); // version_needed
    fwrite ( $fh, pack ( 's', $details ['bitflag'] ) ); // general_bit_flag
    fwrite ( $fh, pack ( 's', $details ['cmethod'] ) ); // compression_method
    fwrite ( $fh, pack ( 's', $details ['modtime'] ) ); // lastmod_time
    fwrite ( $fh, pack ( 's', $details ['moddate'] ) ); // lastmod_date
    fwrite ( $fh, pack ( 'V', $details ['crc_32'] ) ); // crc-32
    fwrite ( $fh, pack ( 'I', $details ['comsize'] ) ); // compressed_size
    fwrite ( $fh, pack ( 'I', $details ['uncsize'] ) ); // uncompressed_size
    fwrite ( $fh, pack ( 's', strlen ( $sectionName ) ) ); // file_name_length
    fwrite ( $fh, pack ( 's', 0 ) ); // extra_field_length
    fwrite ( $fh, $sectionName ); // sectionName
    // ignoring extra_field
    fwrite ( $fh, $zdata );

    // Append it to central section
    $details ['external_attributes'] = (substr ( $sectionName, - 1 ) == '/' && ! $zdata) ? 16 : 32; // Directory or file name
    $details ['comments'] = $sectComments;
    $this->appendCentralDir ( $sectionName, $details );
    $this->sections ++;
  }
  public function setExtra($filename, $property, $value) {
    $this->centraldirs [$filename] [$property] = $value;
  }
  public function save($compactComments = '') {
    if (! ($fh = &$this->fh)) $fh = fopen ( $this->filename, $this->overwrite ? 'w' : 'a+' );

    $cdrec = "";
    foreach ( $this->centraldirs as $sectionName => $cd ) {
      $cdrec .= $this->SectSignature;
      $cdrec .= "\x0\x0"; // version made by
      $cdrec .= pack ( 'v', $cd ['vneeded'] ); // version needed to extract
      $cdrec .= "\x0\x0"; // general bit flag
      $cdrec .= pack ( 'v', $cd ['cmethod'] ); // compression method
      $cdrec .= pack ( 'v', $cd ['modtime'] ); // lastmod time
      $cdrec .= pack ( 'v', $cd ['moddate'] ); // lastmod date
      $cdrec .= pack ( 'V', $cd ['crc_32'] ); // crc32
      $cdrec .= pack ( 'V', $cd ['comsize'] ); // compressed filesize
      $cdrec .= pack ( 'V', $cd ['uncsize'] ); // uncompressed filesize
      $cdrec .= pack ( 'v', strlen ( $sectionName ) ); // section comment length
      $cdrec .= pack ( 'v', 0 ); // extra field length
      $cdrec .= pack ( 'v', strlen ( $cd ['comments'] ) ); // section comment length
      $cdrec .= pack ( 'v', 0 ); // disk number start
      $cdrec .= pack ( 'v', 0 ); // internal file attributes
      $cdrec .= pack ( 'V', $cd ['external_attributes'] ); // internal file attributes
      $cdrec .= pack ( 'V', $cd ['offset'] ); // relative offset of local header
      $cdrec .= $sectionName;
      $cdrec .= $cd ['comments'];
    }
    $before_cd = ftell ( $fh );
    fwrite ( $fh, $cdrec );

    // end of central dir
    fwrite ( $fh, $this->SectSignatureE );
    fwrite ( $fh, pack ( 'v', 0 ) ); // number of this disk
    fwrite ( $fh, pack ( 'v', 0 ) ); // number of the disk with the start of the central directory
    fwrite ( $fh, pack ( 'v', $this->sections ) ); // total # of entries "on this disk"
    fwrite ( $fh, pack ( 'v', $this->sections ) ); // total # of entries overall
    fwrite ( $fh, pack ( 'V', strlen ( $cdrec ) ) ); // size of central dir
    fwrite ( $fh, pack ( 'V', $before_cd ) ); // offset to start of central dir
    fwrite ( $fh, pack ( 'v', strlen ( $compactComments ) ) ); // .compact file comment length
    fwrite ( $fh, $compactComments );

    fclose ( $fh );
  }

  private function appendCentralDir($filename, $properties) {
    $this->centraldirs [$filename] = $properties;
  }

  public function getList($stopOnFile = false) {
    if (sizeof ( $this->sectionList )) {
      $this->debugMsg ( 1, "Returning already loaded file list." );
      return $this->sectionList;
    }

    // Open file, and set file handler
    $fh = fopen ( $this->fileName, "r" );
    $this->fh = &$fh;
    if (! $fh) {
      $this->debugMsg ( 2, "Failed to load file." );
      return false;
    }

    $this->debugMsg ( 1, "Loading list from 'End of Central Dir' index list..." );
    if (! $this->_loadFileListByEOF ( $fh, $stopOnFile )) {
      $this->debugMsg ( 1, "Failed! Trying to load list looking for signatures..." );
      if (! $this->_loadFileListBySignatures ( $fh, $stopOnFile )) {
	$this->debugMsg ( 1, "Failed! Could not find any valid header." );
	$this->debugMsg ( 2, "IDML File is corrupted or empty" );
	return false;
      }
    }

    if ($this->debug) {
      #------- Debug sectionList
      $kkk = 0;
      echo "<table border='0' style='font: 11px Verdana; border: 1px solid #000'>";
      foreach ( $this->sectionList as $fileName => $item ) {
	if (! $kkk && $kkk = 1) {
	  echo "<tr style='background: #ADA'>";
	  foreach ( $item as $fieldName => $value )
	    echo "<td>$fieldName</td>";
	  echo '</tr>';
	}
	echo "<tr style='background: #CFC'>";
	foreach ( $item as $fieldName => $value ) {
	  if ($fieldName == 'lastmod_datetime')
	    echo "<td title='$fieldName' nowrap='nowrap'>" . date ( "d/m/Y H:i:s", $value ) . "</td>";
	  else
	    echo "<td title='$fieldName' nowrap='nowrap'>$value</td>";
	}
	echo "</tr>";
      }
      echo "</table>";

      #------- Debug centralDirList
      $kkk = 0;
      if (sizeof ( $this->centralDirList )) {
	echo "<table border='0' style='font: 11px Verdana; border: 1px solid #000'>";
	foreach ( $this->centralDirList as $fileName => $item ) {
	  if (! $kkk && $kkk = 1) {
	    echo "<tr style='background: #AAD'>";
	    foreach ( $item as $fieldName => $value )
	      echo "<td>$fieldName</td>";
	    echo '</tr>';
	  }
	  echo "<tr style='background: #CCF'>";
	  foreach ( $item as $fieldName => $value ) {
	    if ($fieldName == 'lastmod_datetime')
	      echo "<td title='$fieldName' nowrap='nowrap'>" . date ( "d/m/Y H:i:s", $value ) . "</td>";
	    else
	      echo "<td title='$fieldName' nowrap='nowrap'>$value</td>";
	  }
	  echo "</tr>";
	}
	echo "</table>";
      }

      #------- Debug endOfCentral
      $kkk = 0;
      if (sizeof ( $this->endOfCentral )) {
	echo "<table border='0' style='font: 11px Verdana' style='border: 1px solid #000'>";
	echo "<tr style='background: #DAA'><td colspan='2'>dUnzip - End of file</td></tr>";
	foreach ( $this->endOfCentral as $field => $value ) {
	  echo "<tr>";
	  echo "<td style='background: #FCC'>$field</td>";
	  echo "<td style='background: #FDD'>$value</td>";
	  echo "</tr>";
	}
	echo "</table>";
      }
    }

    return $this->sectionList;
  }
  public function getExtraInfo($compressedFileName) {
    return isset ( $this->centralDirList [$compressedFileName] ) ? $this->centralDirList [$compressedFileName] : false;
  }
  public function getIDMLInfo($detail = false) {
    return $detail ? $this->endOfCentral [$detail] : $this->endOfCentral;
  }
  public function extract($compressedFileName, $targetFileName = false, $applyChmod = 0777) {
    if (! sizeof ( $this->sectionList )) {
      $this->debugMsg ( 1, "Trying to extract before loading file list... Loading it!" );
      $this->getList ( false, $compressedFileName );
    }

    $fdetails = &$this->sectionList [$compressedFileName];
    if (! isset ( $this->sectionList [$compressedFileName] )) {
      $this->debugMsg ( 2, "File '<b>$compressedFileName</b>' is not compact in the idml." );
      return false;
    }
    if (substr ( $compressedFileName, - 1 ) == "/") {
      $this->debugMsg ( 2, "Trying to extract a folder name '<b>$compressedFileName</b>'." );
      return false;
    }
    if (! $fdetails ['uncompressed_size']) {
      $this->debugMsg ( 1, "File '<b>$compressedFileName</b>' is empty." );
      return $targetFileName ? file_put_contents ( $targetFileName, "" ) : "";
    }

    fseek ( $this->fh, $fdetails ['contents-startOffset'] );
    $fs = fread ( $this->fh, $fdetails ['compressed_size'] );
    $ret = $this->uncompress ( $fs, $fdetails ['compression_method'], $fdetails ['uncompressed_size'], $targetFileName );
    if ($applyChmod && $targetFileName)
      chmod ( $targetFileName, 0777 );

    return $ret;
  }
  public function extractAll($targetDir = false, $baseDir = "", $maintainStructure = true, $applyChmod = 0777) {
    if ($targetDir === false)
      $targetDir = dirname ( $_SERVER ['SCRIPT_FILENAME'] ) . "/";

    $lista = $this->getList ();
    if (sizeof ( $lista ))
      foreach ( $lista as $fileName => $trash ) {
	$dirname = dirname ( $fileName );
	$outDN = "$targetDir/$dirname";

	if (substr ( $dirname, 0, strlen ( $baseDir ) ) != $baseDir) continue;

	if (! is_dir ( $outDN ) && $maintainStructure) {
	  $str = "";
	  $folders = explode ( "/", $dirname );
	  foreach ( $folders as $folder ) {
	    $str = $str ? "$str/$folder" : $folder;
	    if (! is_dir ( "$targetDir/$str" )) {
	      $this->debugMsg ( 1, "Creating folder: $targetDir/$str" );
	      mkdir ( "$targetDir/$str" );
	      if ($applyChmod)
		chmod ( "$targetDir/$str", $applyChmod );
	    }
	  }
	}
	if (substr ( $fileName, - 1, 1 ) == "/") continue;

	$maintainStructure ? $this->extract ( $fileName, "$targetDir/$fileName", $applyChmod ) : $this->extract ( $fileName, "$targetDir/" . basename ( $fileName ), $applyChmod );
      }
  }
  function close() { // Free the file resource
    if ($this->fh)
      fclose ( $this->fh );
  }
  function __destroy() {
    $this->close ();
  }
  private function uncompress(&$content, $mode, $uncompressedSize, $targetFileName = false) {
    switch ($mode) {
      case 0 :
      // Not compressed
	return $targetFileName ? file_put_contents ( $targetFileName, $content ) : $content;
      case 1 :
	$this->debugMsg ( 2, "Shrunk mode is not supported... yet?" );
	return false;
      case 2 :
      case 3 :
      case 4 :
      case 5 :
	$this->debugMsg ( 2, "Compression factor " . ($mode - 1) . " is not supported... yet?" );
	return false;
      case 6 :
	$this->debugMsg ( 2, "Implode is not supported... yet?" );
	return false;
      case 7 :
	$this->debugMsg ( 2, "Tokenizing compression algorithm is not supported... yet?" );
	return false;
      case 8 :
      // Deflate
	return $targetFileName ? file_put_contents ( $targetFileName, gzinflate ( $content, $uncompressedSize ) ) : gzinflate ( $content, $uncompressedSize );
      case 9 :
	$this->debugMsg ( 2, "Enhanced Deflating is not supported... yet?" );
	return false;
      case 10 :
	$this->debugMsg ( 2, "PKWARE Date Compression Library Impoloding is not supported... yet?" );
	return false;
      case 12 :
      // Bzip2
	return $targetFileName ? file_put_contents ( $targetFileName, bzdecompress ( $content ) ) : bzdecompress ( $content );
      case 18 :
	$this->debugMsg ( 2, "IBM TERSE is not supported... yet?" );
	return false;
      default :
	$this->debugMsg ( 2, "Unknown uncompress method: $mode" );
	return false;
    }
  }
  private function debugMsg($level, $string) {
    if ($this->debug) {
      if ($level == 1)
	echo "<b style='color: #777'>dUnzip2:</b> $string<br>";

      if ($level == 2)
	echo "<b style='color: #F00'>dUnzip2:</b> $string<br>";
    }
    $this->lastError = $string;
  }
  public  function getLastError() {
    return $this->lastError;
  }
  private function _loadFileListByEOF(&$fh, $stopOnFile = false) {
    // Check if there's a valid Central Dir signature.
    // Let's consider a file comment smaller than 1024 characters...
    // Actually, it length can be 65536.. But we're not going to support it.


    for($x = 0; $x < 1024; $x ++) {
      fseek ( $fh, - 22 - $x, SEEK_END );

      $signature = fread ( $fh, 4 );
      if ($signature == $this->SectSignatureE) {
	// If found EOF Central Dir
	$eodir ['disk_number_this'] = unpack ( "v", fread ( $fh, 2 ) ); // number of this disk
	$eodir ['disk_number'] = unpack ( "v", fread ( $fh, 2 ) ); // number of the disk with the start of the central directory
	$eodir ['total_entries_this'] = unpack ( "v", fread ( $fh, 2 ) ); // total number of entries in the central dir on this disk
	$eodir ['total_entries'] = unpack ( "v", fread ( $fh, 2 ) ); // total number of entries in
	$eodir ['size_of_cd'] = unpack ( "V", fread ( $fh, 4 ) ); // size of the central directory
	$eodir ['offset_start_cd'] = unpack ( "V", fread ( $fh, 4 ) ); // offset of start of central directory with respect to the starting disk number
	$IDMLFileCommentLenght = unpack ( "v", fread ( $fh, 2 ) ); // idml file comment length
	$eodir ['idmlfile_comment'] = $IDMLFileCommentLenght [1] ? fread ( $fh, $IDMLFileCommentLenght [1] ) : ''; // idml file comment
	$this->endOfCentral = Array ('disk_number_this' => $eodir ['disk_number_this'] [1], 'disk_number' => $eodir ['disk_number'] [1], 'total_entries_this' => $eodir ['total_entries_this'] [1], 'total_entries' => $eodir ['total_entries'] [1], 'size_of_cd' => $eodir ['size_of_cd'] [1], 'offset_start_cd' => $eodir ['offset_start_cd'] [1], 'idmlfile_comment' => $eodir ['idmlfile_comment'] );

	// Then, load file list
	fseek ( $fh, $this->endOfCentral ['offset_start_cd'] );
	$signature = fread ( $fh, 4 );

	while ( $signature == $this->SectSignature ) {
	  $dir ['version_madeby'] = unpack ( "v", fread ( $fh, 2 ) ); // version made by
	  $dir ['version_needed'] = unpack ( "v", fread ( $fh, 2 ) ); // version needed to extract
	  $dir ['general_bit_flag'] = unpack ( "v", fread ( $fh, 2 ) ); // general purpose bit flag
	  $dir ['compression_method'] = unpack ( "v", fread ( $fh, 2 ) ); // compression method
	  $dir ['lastmod_time'] = unpack ( "v", fread ( $fh, 2 ) ); // last mod file time
	  $dir ['lastmod_date'] = unpack ( "v", fread ( $fh, 2 ) ); // last mod file date
	  $dir ['crc-32'] = fread ( $fh, 4 ); // crc-32
	  $dir ['compressed_size'] = unpack ( "V", fread ( $fh, 4 ) ); // compressed size
	  $dir ['uncompressed_size'] = unpack ( "V", fread ( $fh, 4 ) ); // uncompressed size
	  $fileNameLength = unpack ( "v", fread ( $fh, 2 ) ); // filename length
	  $extraFieldLength = unpack ( "v", fread ( $fh, 2 ) ); // extra field length
	  $fileCommentLength = unpack ( "v", fread ( $fh, 2 ) ); // file comment length
	  $dir ['disk_number_start'] = unpack ( "v", fread ( $fh, 2 ) ); // disk number start
	  $dir ['internal_attributes'] = unpack ( "v", fread ( $fh, 2 ) ); // internal file attributes-byte1
	  $dir ['external_attributes1'] = unpack ( "v", fread ( $fh, 2 ) ); // external file attributes-byte2
	  $dir ['external_attributes2'] = unpack ( "v", fread ( $fh, 2 ) ); // external file attributes
	  $dir ['relative_offset'] = unpack ( "V", fread ( $fh, 4 ) ); // relative offset of local header
	  $dir ['file_name'] = fread ( $fh, $fileNameLength [1] ); // filename
	  $dir ['extra_field'] = $extraFieldLength [1] ? fread ( $fh, $extraFieldLength [1] ) : ''; // extra field
	  $dir ['file_comment'] = $fileCommentLength [1] ? fread ( $fh, $fileCommentLength [1] ) : ''; // file comment


	  // Convert the date and time, from MS-DOS format to UNIX Timestamp
	  $BINlastmod_date = str_pad ( decbin ( $dir ['lastmod_date'] [1] ), 16, '0', STR_PAD_LEFT );
	  $BINlastmod_time = str_pad ( decbin ( $dir ['lastmod_time'] [1] ), 16, '0', STR_PAD_LEFT );
	  $lastmod_dateY = bindec ( substr ( $BINlastmod_date, 0, 7 ) ) + 1980;
	  $lastmod_dateM = bindec ( substr ( $BINlastmod_date, 7, 4 ) );
	  $lastmod_dateD = bindec ( substr ( $BINlastmod_date, 11, 5 ) );
	  $lastmod_timeH = bindec ( substr ( $BINlastmod_time, 0, 5 ) );
	  $lastmod_timeM = bindec ( substr ( $BINlastmod_time, 5, 6 ) );
	  $lastmod_timeS = bindec ( substr ( $BINlastmod_time, 11, 5 ) );

	  $this->centralDirList [$dir ['file_name']] = Array ('version_madeby' => $dir ['version_madeby'] [1], 'version_needed' => $dir ['version_needed'] [1], 'general_bit_flag' => str_pad ( decbin ( $dir ['general_bit_flag'] [1] ), 8, '0', STR_PAD_LEFT ), 'compression_method' => $dir ['compression_method'] [1], 'lastmod_datetime' => mktime ( $lastmod_timeH, $lastmod_timeM, $lastmod_timeS, $lastmod_dateM, $lastmod_dateD, $lastmod_dateY ), 'crc-32' => str_pad ( dechex ( ord ( $dir ['crc-32'] [3] ) ), 2, '0', STR_PAD_LEFT ) . str_pad ( dechex ( ord ( $dir ['crc-32'] [2] ) ), 2, '0', STR_PAD_LEFT ) . str_pad ( dechex ( ord ( $dir ['crc-32'] [1] ) ), 2, '0', STR_PAD_LEFT ) . str_pad ( dechex ( ord ( $dir ['crc-32'] [0] ) ), 2, '0', STR_PAD_LEFT ), 'compressed_size' => $dir ['compressed_size'] [1], 'uncompressed_size' => $dir ['uncompressed_size'] [1], 'disk_number_start' => $dir ['disk_number_start'] [1], 'internal_attributes' => $dir ['internal_attributes'] [1], 'external_attributes1' => $dir ['external_attributes1'] [1], 'external_attributes2' => $dir ['external_attributes2'] [1], 'relative_offset' => $dir ['relative_offset'] [1], 'file_name' => $dir ['file_name'], 'extra_field' => $dir ['extra_field'], 'file_comment' => $dir ['file_comment'] );
	  $signature = fread ( $fh, 4 );
	}

	// If loaded centralDirs, then try to identify the offsetPosition of the compressed data.
	if ($this->centralDirList)
	  foreach ( $this->centralDirList as $filename => $details ) {
	    $i = $this->_getFileHeaderInformation ( $fh, $details ['relative_offset'] );
	    $this->sectionList [$filename] ['file_name'] = $filename;
	    $this->sectionList [$filename] ['compression_method'] = $details ['compression_method'];
	    $this->sectionList [$filename] ['version_needed'] = $details ['version_needed'];
	    $this->sectionList [$filename] ['lastmod_datetime'] = $details ['lastmod_datetime'];
	    $this->sectionList [$filename] ['crc-32'] = $details ['crc-32'];
	    $this->sectionList [$filename] ['compressed_size'] = $details ['compressed_size'];
	    $this->sectionList [$filename] ['uncompressed_size'] = $details ['uncompressed_size'];
	    $this->sectionList [$filename] ['lastmod_datetime'] = $details ['lastmod_datetime'];
	    $this->sectionList [$filename] ['extra_field'] = $i ['extra_field'];
	    $this->sectionList [$filename] ['contents-startOffset'] = $i ['contents-startOffset'];
	    if (strtolower ( $stopOnFile ) == strtolower ( $filename )) break;
	  }
	return true;
      }
    }
    return false;
  }
  private function _loadFileListBySignatures(&$fh, $stopOnFile = false) {
    fseek ( $fh, 0 );

    $return = false;
    for(;;) {
      $details = $this->_getFileHeaderInformation ( $fh );
      if (! $details) {
	$this->debugMsg ( 1, "Invalid signature. Trying to verify if is old style Data Descriptor..." );
	fseek ( $fh, 12 - 4, SEEK_CUR ); // 12: Data descriptor - 4: Signature (that will be read again)
	$details = $this->_getFileHeaderInformation ( $fh );
      }
      if (! $details) {
	$this->debugMsg ( 1, "Still invalid signature. Probably reached the end of the file." );
	break;
      }
      $filename = $details ['file_name'];
      $this->sectionList [$filename] = $details;
      $return = true;
      if (strtolower ( $stopOnFile ) == strtolower ( $filename ))
	break;
    }

    return $return;
  }
  private function _getFileHeaderInformation(&$fh, $startOffset = false) {
    if ($startOffset !== false)
      fseek ( $fh, $startOffset );

    $signature = fread ( $fh, 4 );
    if ($signature == $this->headerSignature) {
      # $this->debugMsg(1, "Zip Signature!");


      // Get information about the zipped file
      $file ['version_needed'] = unpack ( "v", fread ( $fh, 2 ) ); // version needed to extract
      $file ['general_bit_flag'] = unpack ( "v", fread ( $fh, 2 ) ); // general purpose bit flag
      $file ['compression_method'] = unpack ( "v", fread ( $fh, 2 ) ); // compression method
      $file ['lastmod_time'] = unpack ( "v", fread ( $fh, 2 ) ); // last mod file time
      $file ['lastmod_date'] = unpack ( "v", fread ( $fh, 2 ) ); // last mod file date
      $file ['crc-32'] = fread ( $fh, 4 ); // crc-32
      $file ['compressed_size'] = unpack ( "V", fread ( $fh, 4 ) ); // compressed size
      $file ['uncompressed_size'] = unpack ( "V", fread ( $fh, 4 ) ); // uncompressed size
      $fileNameLength = unpack ( "v", fread ( $fh, 2 ) ); // filename length
      $extraFieldLength = unpack ( "v", fread ( $fh, 2 ) ); // extra field length
      $file ['file_name'] = fread ( $fh, $fileNameLength [1] ); // filename
      $file ['extra_field'] = $extraFieldLength [1] ? fread ( $fh, $extraFieldLength [1] ) : ''; // extra field
      $file ['contents-startOffset'] = ftell ( $fh );

      // Bypass the whole compressed contents, and look for the next file
      fseek ( $fh, $file ['compressed_size'] [1], SEEK_CUR );

      // Convert the date and time, from MS-DOS format to UNIX Timestamp
      $BINlastmod_date = str_pad ( decbin ( $file ['lastmod_date'] [1] ), 16, '0', STR_PAD_LEFT );
      $BINlastmod_time = str_pad ( decbin ( $file ['lastmod_time'] [1] ), 16, '0', STR_PAD_LEFT );
      $lastmod_dateY = bindec ( substr ( $BINlastmod_date, 0, 7 ) ) + 1980;
      $lastmod_dateM = bindec ( substr ( $BINlastmod_date, 7, 4 ) );
      $lastmod_dateD = bindec ( substr ( $BINlastmod_date, 11, 5 ) );
      $lastmod_timeH = bindec ( substr ( $BINlastmod_time, 0, 5 ) );
      $lastmod_timeM = bindec ( substr ( $BINlastmod_time, 5, 6 ) );
      $lastmod_timeS = bindec ( substr ( $BINlastmod_time, 11, 5 ) );


      // Mount file table
      $i = Array ('file_name' => $file ['file_name'], 'compression_method' => $file ['compression_method'] [1], 'version_needed' => $file ['version_needed'] [1], 'lastmod_datetime' => mktime ( $lastmod_timeH, $lastmod_timeM, $lastmod_timeS, $lastmod_dateM, $lastmod_dateD, $lastmod_dateY ), 'crc-32' => str_pad ( dechex ( ord ( $file ['crc-32'] [3] ) ), 2, '0', STR_PAD_LEFT ) . str_pad ( dechex ( ord ( $file ['crc-32'] [2] ) ), 2, '0', STR_PAD_LEFT ) . str_pad ( dechex ( ord ( $file ['crc-32'] [1] ) ), 2, '0', STR_PAD_LEFT ) . str_pad ( dechex ( ord ( $file ['crc-32'] [0] ) ), 2, '0', STR_PAD_LEFT ), 'compressed_size' => $file ['compressed_size'] [1], 'uncompressed_size' => $file ['uncompressed_size'] [1], 'extra_field' => $file ['extra_field'], 'general_bit_flag' => str_pad ( decbin ( $file ['general_bit_flag'] [1] ), 8, '0', STR_PAD_LEFT ), 'contents-startOffset' => $file ['contents-startOffset'] );
      return $i;
    }
    return false;
  }
}
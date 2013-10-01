<?php
class indesign_versioncontrol{
    private $Service = null;
    private $Database = null;
    private $credits_available = null;
    private $FTP = false;
    private $FTP_keep = false;
    private $campaignID=0;
    private $ArtworkName=0;
    private $version=0;
    private $subjectID=0;
    private $parse_type=0;
    
    function __construct($Service,$Database){
        $this->setService($Service);
        $this->setDatabase($Database);
    }

    public function setCredits_available($credits_available) {
        $this->credits_available = $credits_available;
    }
    
    public function getCredits_available() {
        return $this->credits_available;
    }
    
    public function setService($Service) {
        $this->Service = $Service;
    }

    public function setDatabase($Database) {
        $this->Database = $Database;
    }

    public function getDatabase() {
        return $this->Database;
    }
    
    public function setFTP($FTP) {
        $this->FTP = $FTP;
    }
    
    public function getFTP() {
        return (bool)$this->FTP;
    }
    
    public function getService() {
        return $this->Service;
    }

    public function setFTP_keep($FTP_keep) {
        $this->FTP_keep = $FTP_keep;
    }

    public function getFTP_keep() {
        return (bool)$this->FTP_keep;
    }
    
    public function getCampaignID() {
        return $this->campaignID;
    }

    public function setCampaignID($campaignID) {
        $this->campaignID = $campaignID;
    }

    public function getArtworkName() {
        return $this->ArtworkName;
    }

    public function setArtworkName($ArtworkName) {
        $this->ArtworkName = $ArtworkName;
    }
    
    public function getVersion() {
        return (!empty($this->version)) ? $this->version : date('d-m-Y h:i:s');
    }

    public function setVersion($version) {
        $this->version = $version;
    }
    
    public function getSubjectID() {
        return $this->subjectID;
    }

    public function setSubjectID($subjectID) {
        $this->subjectID = $subjectID;
    }
    public function getParse_type() {
        return $this->parse_type;
    }

    public function setParse_type($parse_type) {
        $this->parse_type = $parse_type;
    }

    function uploadVersionControl($artworkID, $artworkFileTempName, $artworkFileName, $artworkType, $taskID=null){
        if (empty($artworkFileName)) {
            header("Location: index.php?layout=system&id=11");
            exit;
        }
        $DB = $this->getDatabase();
        $credits_available = $this->getCredits_available();
        $conn = $DB->getLink();
        $Service = $this->getService();
        
        // check credit
        $service_process_id = $DB->get_service_process_id($artworkType, SERVICE_UPLOAD, TYPE_ORIGINAL);
        if ($service_process_id === false) error_creating_file('No Service found');
        $transaction = $DB->get_service_process_transaction($service_process_id);
        if ($transaction === false) error_creating_file('No transaction process found');
        $credits_ask = $DB->get_credit_config($_SESSION['packageID'], $service_process_id);
        if ($credits_ask > $credits_available) no_credit_available();

        $FileBasename = RestrictName(BareFilename($artworkFileName, false));
        $FileBasename = strlen($FileBasename) > 50 ? substr($FileBasename, 50) : $FileBasename;
        $FileName = time() . "_" . $FileBasename;
        #$FileName = md5($FileBasename.time().rand());
        $query = sprintf("SELECT ext FROM service_engines WHERE id = %d LIMIT 0,1", $artworkType);
        $result = mysql_query($query, $conn) or die(mysql_error());
        $row = mysql_fetch_assoc($result);
        $FileName .= "." . $row['ext'];

        $Storage = $Service->GetStorage();
        $DestFile = $Storage . $FileName;
        if ($this->getFTP()) {
            if ( !$this->getFTP_keep() ) {
                $moveFile = rename($artworkFileTempName, $DestFile); //AKA move
            } else {
                $moveFile = copy($artworkFileTempName, $DestFile);
            }
        } else {
            $moveFile = move_uploaded_file($artworkFileTempName, $DestFile);
        }
        if ($moveFile === false) access_denied();
        ignore_user_abort(true);
        set_time_limit(0);

        //
        $extra = array(
            "campaignID" => $this->getCampaignID(),
            //"artworkName" => $this->getArtworkName(),
            "subjectID" => $this->getSubjectID(),
            "artworkType" => $artworkType,
            "parse_type" => $this->getParse_type(),
            "uploaderID" => $_SESSION['userID'],
            "version" => $this->getVersion(),
            "parent" => $artworkID
        );
        if ($Service->isValidFile($FileName, '', 'JPG')) {
            $aID = $DB->EditArtworkDetails($Service->getDocInfo(), $extra);

            $oldArtwork = $DB->GetFilenamebyArtwork($artworkID);

            //Save Details to Artwork_version
            //Deactivate all other versions
            $query = sprintf('UPDATE `artwork_versions` SET `active` = NULL WHERE `artwork_id` = "%d" AND `task_id` = "%d"', $artworkID,$taskID);
            $result = mysql_query($query, $conn) or die(mysql_error());

            //Update and make active new Version
            $query = sprintf("INSERT INTO `artwork_versions` (`artwork_id`,`task_id`,`name`,`active`) VALUES (%d, %d, '%s', '%d') 
                        ON DUPLICATE KEY UPDATE `active` = '1'", 
                    $artworkID, 
                    $taskID, 
                    mysql_real_escape_string($_POST['new_version2']),
                    1
            );
            $result = mysql_query($query, $conn) or die($query . mysql_error());
            $VC_id = mysql_insert_id($conn);

            $Service->SetPreviewOutputPath(PREVIEW_DIR);
            $process = new ProcessService($service_process_id);
            $ProcessEngine = $process->getProcessEngine();
            #$ProcessEngine->setBASE($aID,$taskID);
            
            //Basic upload
            $ProcessEngine->addConfig('UploadSettings_IgnoreStories', true);
            if ($ProcessEngine->UploadFile($aID, $FileName) === false) error_creating_file('Upload failed');

            $old_file = OUTPUT_DIR . $oldArtwork;
            
            //Updates links to Stories before previewing
            $XML = new DOMDocument('1.0','UTF-8');
            #$loaded = $XML->load($old_file . '/XML/BASE.XML');
            $oldXMLfile = $old_file . '/XML/BASE.XML';
            $loaded = $XML->load($oldXMLfile);
            if($loaded === false) error_creating_file('Failed to read '.$old_file);
            $XML->formatOutput = true;
            $XML->preserveWhiteSpace = false;
            
            #$Stories = $XML->getElementsByTagName('Stories');
            #if(empty($Stories)) error_creating_file('No Stories');
            #$Stories = $Stories->item(0);
            
            $Stories = $XML->getElementsByTagName('Stories');
            if(empty($Stories)) error_creating_file('Failed to read Stories from BASE.XML');
            $Stories = $Stories->item(0);
            
            $vcXML = new DOMDocument('1.0','UTF-8');
            #$newXMLfile = OUTPUT_DIR . $FileName . '/XML/BASE-' . $VC_id . '.XML';
            $newXMLfile = OUTPUT_DIR . $FileName . '/XML/BASE.XML';
            $loaded = $vcXML->load($newXMLfile);
            if($loaded === false) error_creating_file('Failed to read '.$newXMLfile);
            $vcXML->formatOutput = true;
            $vcXML->preserveWhiteSpace = false;

            //ListExisting Stories
            $StoryID = Array();
            $Storys = $Stories->getElementsByTagName('Story');
            if(!empty($Storys)){
                foreach($Storys as $Story) {
                    if($Story->hasAttribute('ID')) $StoryID[] = $Story->getAttribute('ID');
                }
            }
            
            //Remove Uploaded Stories, that are in the existing artwork
            $vcStories = $vcXML->getElementsByTagName('Stories');
            if(empty($vcStories)) error_creating_file('Failed to read stories from '.$newXMLfile);
            $vcStories = $vcStories->item(0);
            $vcStory = $vcStories->getElementsByTagName('Story');
            $domElemsToRemove = array();
            if(!empty($vcStory)){
                foreach ($vcStory as $domElement) {
                    #if( !($domElement->hasAttribute('ID') && in_array($domElement->getAttribute('ID'), $StoryID) ) ) continue;
                    $domElemsToRemove[] = $domElement;
                }
                //remove
                foreach ($domElemsToRemove as $domElement) {
                    $domElement->parentNode->removeChild($domElement);
                }
            }
            //check some stories were removed, if none then generate error.
            //if(count($domElemsToRemove)==0) error_creating_file('No stories matched existing artwork');
            unset($domElemsToRemove);
            
            //Add existing Stories to VC artwork
            $Storys = $Stories->getElementsByTagName('Story');
            if(!empty($Storys)){
                foreach($Storys as $Story) {
                    // Import the Stories, and all its children, to the document
                    $Engine = $ProcessEngine->getInDesignEngine();
                    if($Story->hasAttribute('File')){
                        $file = $Engine->WinPath($Story->getAttribute('File'));
                        $file = realpath( sprintf("%s\..\Stories_%d\%s",dirname($file), $taskID, basename($file) ) );
                        if($file===false) continue;
                        $Story->setAttribute('File',$Engine->INDDPath($file));
                    } 
                    $newStory = $vcXML->importNode($Story, true);
                    $vcStories->appendChild($newStory);
                }
                
                $vcXML->save($newXMLfile);
                $data = file_get_contents($newXMLfile);
                $data = str_replace('><', ">\n<", $data );
                //$data = str_replace('\n\n', "\n", $data );
                $data = preg_replace('/\n{2,}/sim', "\n", $data);
                file_put_contents($newXMLfile, $data );
            }
            
            //*
            //Move Files
            if (rename(OUTPUT_DIR . $FileName . '/XML/BASE.XML', $old_file . '/XML/BASE-' . $VC_id . '.XML') === false) access_denied();
            if (rename(OUTPUT_DIR . $FileName . '/XML/base.indd', $old_file . '/XML/base-' . $VC_id . '.indd') === false) access_denied();
            if (rename(OUTPUT_DIR . $FileName . '/XML/base.raw', $old_file . '/XML/base-' . $VC_id . '.raw') === false) access_denied();
            //*/

            
            //Remove from Database
            $DB->DeleteArtwork($aID, false);

            /*
            //store artwork fonts
            $fileFontIDs = $Service->GetFileFonts(); //font_id array
            $DB->AddFileFonts($aID,$fileFontIDs);
            //initialise box orders
            $DB->InitialiseBoxOrders($aID);
            //*/
            
            /*
            if(!empty($_POST['update_prework'])) $DB->UpdatePL($artworkID,$aID);
            
            //rebuild preview
            $query_utask = sprintf("SELECT taskID
                                    FROM tasks
                                    WHERE artworkID = %d
                                    ORDER BY taskID ASC", $aID);
            $result_utask = mysql_query($query_utask, $conn) or die(mysql_error());
            while ($row_utask = mysql_fetch_assoc($result_utask)) {
                $taskID = $row_utask['taskID'];
                //update translation records
                if (!empty($_POST['update_task'])) $DB->UpdatePL($artworkID, $aID, $taskID);
                $rebuild = $Service->RebuildFile($aID, $taskID, 0, ROOT . POSTVIEW_DIR, "JPG", 0);
                if ($rebuild === false) continue;
            }
            //*/
            
            //create thumbnails
            $rebuild = $Service->RebuildFile($artworkID, $taskID, 0, ROOT . PREVIEW_DIR, "JPG");
            #$rebuild = $Service->RebuildFile($aID, 0, 0, ROOT . PREVIEW_DIR, "JPG");
            if ($rebuild === false) error_creating_file('Error Rebuilding File');
            $DB->RebuildPageThumbnail(PREVIEW_DIR, $artworkID);
            $DB->log_credit_transaction($_SESSION['companyID'], $_SESSION['userID'], $this->getCampaignID(), $artworkID, 0, $transaction['notes'], $credits_ask);
        } else {
            if ($DestFile != "") {
                @unlink($DestFile);
                do_rmdir(OUTPUT_DIR . $DestFile);
            }
            header("Location: index.php?layout=system&id=11");
            exit;
        }
    }

}
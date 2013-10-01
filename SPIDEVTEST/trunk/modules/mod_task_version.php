<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query_task = sprintf("SELECT tasks.*,
                        artworks.artworkID, artworks.artworkName, artworks.version, artworks.pageCount, artworks.subjectID, artworks.wordCount,
                        campaigns.campaignID, campaigns.campaignName, campaigns.ref,
                        service_engines.name AS serviceName, service_engines.ext AS serviceExt,
                        brands.brandName,
                        L1.languageName AS source_lang_name, L1.flag AS source_flag,
                        L2.languageName AS target_lang_name, L2.flag AS target_flag,
                        U1.userID AS cuid, U1.forename AS cforename, U1.surname AS csurname, U1.email AS cemail,
                        U2.userID AS tuid, U2.forename AS tforename, U2.surname AS tsurname, U2.email AS temail,
                        U3.userID AS auid, U3.forename AS aforename, U3.surname AS asurname, U3.email AS aemail,
                        companies.companyName AS agency,
                        status.statusInfo
                        FROM tasks
                        LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
                        LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
                        LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
                        LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
                        LEFT JOIN brands ON campaigns.brandID = brands.brandID
                        LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
                        LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
                        LEFT JOIN users U1 ON tasks.creatorID = U1.userID
                        LEFT JOIN users U2 ON tasks.translatorID = U2.userID
                        LEFT JOIN users U3 ON tasks.agentID = U3.userID
                        LEFT JOIN companies ON U3.companyID = companies.companyID
                        LEFT JOIN status ON tasks.taskStatus = status.statusID
                        WHERE tasks.taskID = %d
                        LIMIT 1",$id);
$result_task = mysql_query($query_task, $conn) or die(mysql_error());
if(!mysql_num_rows($result_task)) die("Invalid Task");
$row_task = mysql_fetch_assoc($result_task);
?>
<div class="mainwrap">
    <form
	action="index.php?layout=task&do=version_control&id=<?php echo $id; ?>"
	name="edit_task_form"
	method="POST"
	enctype="multipart/form-data" 
	onsubmit="hidediv('helper');Popup('loadingme','waiting');"
        >
    <!-- Version Control v2 -->
    <div id="advancedoptions2" class="greyBar" style="display:block;">
        <div class="arrrgt" id="restoreArrow2" onclick="ResetArrow('newArrow');hidediv('newVersion2');ChangeArrow('restoreArrow2');openandclose('restoreVersion2');">
            <?php echo $lang->display('Version Restore'); ?>
        </div>
        <div id="restoreVersion2" style="display:none;">
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td class="highlight" width="30%">* <?php echo $lang->display('Restore To'); ?>:</td>
                    <td width="70%">
                        <select
                            class="input"
                            onfocus="this.className='inputOn'"
                            onblur="this.className='input'"
                            name="restore2"
                            id="restore2"
                            >
                                <?php
                                $query = sprintf("SELECT artworkID, version
                                                    FROM artworks
                                                    WHERE parent = %d
                                                    OR artworkID = %d", $parent_id, $parent_id);
                                $result = mysql_query($query, $conn) or die(mysql_error());
                                while ($row = mysql_fetch_assoc($result)) {
                                    echo '<option value="' . $row['artworkID'] . '"';
                                    if ($row['artworkID'] == $ref)
                                        echo 'selected="selected"';
                                    echo '>' . $row['version'] . '</option>';
                                }
                                ?>
                        </select>
                        <img src="<?php echo IMG_PATH . "arrow_gold_rgt.png"; ?>">
                        <a href="javascript:openBrWindow('index.php?layout=artwork&id=','restore','','status=1,toolbar=1,location=1,menubar=1,resizable=1,scrollbars=1,width=1024,height=768');">
                            <?php echo $lang->display('Preview') . " (" . $lang->display('Open in New Window') . ")"; ?>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="arrrgt" id="newArrow2" onclick="ResetArrow('restoreArrow2');hidediv('restoreVersion2');ChangeArrow('newArrow2');openandclose('newVersion2');">
            <?php echo $lang->display('Upload New Version'); ?>
        </div>
        <div id="newVersion2" style="display:none;">
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td class="highlight" width="30%"><?php echo $lang->display('Version'); ?>:</td>
                    <td width="70%">
                        <input
                            type="text"
                            class="input"
                            onfocus="this.className='inputOn'"
                            onblur="this.className='input'"
                            name="new_version2"
                            id="new_version2"
                            size="10"
                            maxlength="20"
                            />
                    </td>
                </tr>
                <tr>
                    <td class="highlight" valign="top">* <?php echo $lang->display('Select File'); ?>:</td>
                    <td><?php BuildUploadOption($_SESSION['companyID'], false); ?></td>
                </tr>
            </table>
        </div>
        <!-- End Version Control v2 -->
        <input type="hidden" name="task_id" value="<?php echo $id; ?>" />
        <input
            type="submit"
            class="btnDo"
            onmousemove="this.className='btnOn'"
            onmouseout="this.className='btnDo'"
            title="<?php echo $lang->display('Update'); ?>"
            value="<?php echo $lang->display('Update'); ?>"
            onclick="validateForm('ArtworkName','Artwork name','R','version','Version','R');return document.returnValue;"
            />
        <input
            type="reset"
            class="btnOff"
            onmousemove="this.className='btnOn'"
            onmouseout="this.className='btnOff'"
            title="<?php echo $lang->display('Reset'); ?>"
            value="<?php echo $lang->display('Reset'); ?>"
            />
        </div>
    </form>
</div>
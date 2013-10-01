<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

echo '<form
        action="index.php?layout=artstories&id='.$id.'"
        name="storyedit"
        method="POST"
        enctype="multipart/form-data"
        onsubmit="hidediv(\'helper\');Popup(\'loadingme\',\'waiting\');">';

$query = sprintf("SELECT * FROM `artwork_story_groups` WHERE artwork_id=%d",$id);
$result = mysql_query($query, $conn) or die(mysql_error());
while($row = mysql_fetch_assoc($result)) {
    echo "Story Group: ";
    echo "<input type=\"text\" name=\"storygroup[{$row['id']}]\" value=\"".htmlspecialchars($row['name'])."\" />";
    echo "<input type=\"submit\" value=\"remove\" name=\"remove_storygroup[".$row['id']."]\" />";
    echo "<br/>\n";
}

?>
    <input type="text" name="new_storygroup[]" value="" /><input type="submit" value="Add" />
    <div style="width:100%;text-align: center;">
        <input type="submit" value="Save" />
    </div>
</form>
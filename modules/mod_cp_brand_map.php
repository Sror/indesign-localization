<?php
require_once(dirname(__FILE__).'/../config.php');

$id = (isset($_GET['companyID'])) ? $_GET['companyID'] : 0;
?>
<select
	class="input"
	name="parentID"
	id="parentID"
>
<?php
	echo '<option value="0">'.$lang->display('Please Select if Applicable').'...</option>';
	$query = sprintf("SELECT brandID, brandName
					FROM brands
					WHERE companyID = %d
					AND parentBrandID = 0
					ORDER BY brandName ASC",
					$id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while ($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['brandID'].'">'.$row['brandName'].'</option>';
	}
?>
</select>
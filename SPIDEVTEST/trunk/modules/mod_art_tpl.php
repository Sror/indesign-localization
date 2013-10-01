<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","edit");
require_once(MODULES.'mod_authorise.php');

$artworkID = isset($_GET['id']) ? $_GET['id'] : 0;
$boxID = isset($_GET['box']) ? $_GET['box'] : 0;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
?>
<div class="mainwrap">
	<form
		id="mapform"
		name="mapform"
		method="POST"
		enctype="multipart/form-data"
		action="index.php?layout=arttpl&id=<?php echo $artworkID; ?>&page=<?php echo $page; ?>"
	>
		<div class="list">
			<table width="100%" cellpadding="3" cellspacing="0" border="0">
				<?php
					$Translator = new Translator();
					$TheBoxID = $DB->GetTheBox($boxID);
					$query_para = sprintf("SELECT paralinks.uID as ParalinkID, paralinks.ParaID, paralinks.StoryGroup,
										IF(para_orders.order IS NOT NULL, para_orders.order, paralinks.order) AS StoryOrder,
										paragraphs.ParaText,
										paraset.ParaGroup
										FROM paralinks
										LEFT JOIN paragraphs ON paragraphs.uID = paralinks.ParaID
										LEFT JOIN paraset ON paralinks.ParaID = paraset.ParaID
										LEFT JOIN para_orders ON ( para_orders.pl_id = paralinks.uID AND para_orders.task_id = 0 )
										WHERE paralinks.BoxID = %d
										AND paralinks.active = 1
										ORDER BY
										paralinks.StoryGroup ASC,
										StoryOrder ASC,
										paralinks.uID ASC",
										$TheBoxID);
					$result_para = mysql_query($query_para, $conn) or die(mysql_error());
					if(mysql_num_rows($result_para)) {
						while($row_para = mysql_fetch_assoc($result_para)) {
							$PL = $row_para['ParalinkID'];
							$para_row = $Translator->GetParaByPL($PL);
							if($para_row === false) {
								$ParaText = $row_para['ParaText'];
							} else {
								$ParaText = $para_row['ParaText'];
							}
				?>
				<tr
					id="<?php echo "paraRow".$PL; ?>"
					class="even"
					onmouseover="this.className='hover'"
					onmouseout="this.className='even'"
				>
					<td>
						<?php echo html_display_para($ParaText); ?>
					</td>
				</tr>
				<tr
					class="odd"
					onmouseover="this.className='hover'"
					onmouseout="this.className='odd'"
				>
					<td>
						<select
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input'"
							id="import_map_id[<?php echo $PL; ?>]"
							name="import_map_id[<?php echo $PL; ?>]"
						>
							<?php
								$query = sprintf("SELECT import_map_id
												FROM import_map_para
												WHERE pl_id = %d
												AND artwork_id = %d",
												$PL,
												$artworkID);
								$result = mysql_query($query, $conn) or die(mysql_error());
								$row = mysql_fetch_assoc($result);
								BuildImportMapList($row['import_map_id']);
							?>
						</select>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td align="center">
						<input
							type="button"
							class="btnDo"
							onmousemove="this.className='btnOn'"
							onmouseout="this.className='btnDo'"
							title="<?php echo $lang->display('Update'); ?>"
							value="<?php echo $lang->display('Update'); ?>"
							onclick="SubmitForm('mapform','map');"
						/>
						<input
							type="reset"
							class="btnOff"
							onmousemove="this.className='btnOn'"
							onmouseout="this.className='btnOff'"
							title="<?php echo $lang->display('Reset'); ?>"
							value="<?php echo $lang->display('Reset'); ?>"
						/>
						<input type="hidden" name="form" />
					</td>
				</tr>
				<?php
					} else {
						echo "<tr><td><div class=\"alert\">".$lang->display('No paragraph in this textbox.')."</div></td></tr>";
					}
				?>
			</table>
		</div>
	</form>
</div>
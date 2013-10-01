<?php
$navStatus = array("cpanel");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Control Panel'),$lang->display('CP Intro'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_brand.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Edit').": ".$brand_row['brandName']; ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('companyID','Company','R','name','Brand Name','R'); if(document.returnValue) { SubmitForm('editform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('companyID','Company','R','name','Brand Name','R'); if(document.returnValue) { SubmitForm('editform','apply'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
							<div><?php echo $lang->display('Apply'); ?></div>
						</a>
					</div>
					<!-- Close -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('editform','close');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
							<div><?php echo $lang->display('Close'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="editform"
					name="editform"
					action="index.php?layout=<?php echo $layout; ?>&task=<?php echo $task; ?>&id=<?php echo $id; ?>"
					method="POST"
					enctype="multipart/form-data"
				>
				<div>
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Brand Setup'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Company'); ?></th>
									<td>
										<select
											class="input"
											name="companyID"
											id="companyID"
										>
										<?php BuildCompanyList($brand_row['companyID'],$issuperadmin); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Parent Brand'); ?></th>
									<td>
										<div id="ParentDiv">
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
																$brand_row['companyID']);
												$result = mysql_query($query, $conn) or die(mysql_error());
												while ($row = mysql_fetch_assoc($result)) {
													echo '<option value="'.$row['brandID'].'"';
													if($row['brandID']==$brand_row['parentBrandID']) echo ' selected="selected"';
													echo '>'.$row['brandName'].'</option>';
												}
											?>
											</select>
										</div>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Brand Name'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="name"
											id="name"
											value="<?php echo $brand_row['brandName']; ?>"
										>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Campaigns'); ?></th>
									<td><?php echo $brand_row['campaignno']; ?></td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
		</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
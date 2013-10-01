<?php
$navStatus = array("cpanel");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Control Panel'),$lang->display('CP Intro'));
?>
<div id="wrapperWhite">
	<?php
	BuildHelperDiv('Import/Export');
	?>

	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_translation_memory.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Translation Memory Manager'); ?></div>
				</div>
				<div class="options">
					<!-- New -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('New'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','new');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_new.png">'; ?></div>
							<div><?php echo $lang->display('New'); ?></div>
						</a>
					</div>
					
					<!-- Export -->
					<div title="<?php echo $lang->display('Export'); ?>" onmouseout="this.className='optionOff'" onmouseover="this.className='optionOn'" class="optionOff">
						<a onclick="Popup('helper','blur');DoAjax('','window','modules/mod_tmm_export.php');" href="javascript:void(0);">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_export.png">'; ?></div>
							<div><?php echo $lang->display('Export'); ?></div>
						</a>
					</div>
					<!-- Import -->
					<div title="<?php echo $lang->display('Import'); ?>" onmouseout="this.className='optionOff'" onmouseover="this.className='optionOn'" class="optionOff">
						<a onclick="Popup('helper','blur');DoAjax('','window','modules/mod_tmm_import.php');" href="javascript:void(0);">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_import.png">'; ?></div>
							<div><?php echo $lang->display('Import'); ?></div>
						</a>
					</div>
					
					<!-- Lookup -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Lookup'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','lookup');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_search.png">'; ?></div>
							<div><?php echo $lang->display('Lookup'); ?></div>
						</a>
					</div>
					<!-- Edit -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Edit'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','edit'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_edit.png">'; ?></div>
							<div><?php echo $lang->display('Edit'); ?></div>
						</a>
					</div>
					<!-- Delete -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { SubmitForm('listform','delete'); } }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
							<div><?php echo $lang->display('Delete'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="listform"
					name="listform"
					action="index.php?layout=<?php echo $layout; ?>"
					method="POST"
					enctype="multipart/form-data"
					onsubmit="Popup('loadingme','waiting');"
				>
				<div class="option">
					<div class="left">
						<?php require_once(MODULES.'mod_list_search.php'); ?>
					</div>
					<div class="right">
						<div class="filter">
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_company"
								id="filter_company"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Please Select Company'); ?>"
							>
							<?php BuildCompanyList($company_id,$issuperadmin); ?>
							</select>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_tm_lang"
								id="filter_tm_lang"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select Language'); ?>"
							>
							<?php BuildLangList($lang_id); ?>
							</select>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_tm_type"
								id="filter_tm_type"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Type'); ?>"
							>
							<?php BuildTMTypeList($type_id); ?>
							</select>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="list">
					<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
						<tr>
							<th width="2%" align="center">#</th>
							<th width="2%" align="center">
								<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('listform',this.id,'#listview tr');">
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','flag','<?php echo $pre; ?>');">
									<?php echo $lang->display('Flag'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','language','<?php echo $pre; ?>');">
									<?php echo $lang->display('Language'); ?>
								</a>
							</th>
							<th width="44%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','paragraph','<?php echo $pre; ?>');">
									<?php echo $lang->display('Paragraph'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','wordno','<?php echo $pre; ?>');">
									<?php echo $lang->display('Words'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','type','<?php echo $pre; ?>');">
									<?php echo $lang->display('Type'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','owner','<?php echo $pre; ?>');">
									<?php echo $lang->display('Owner'); ?>
								</a>
							</th>
							<th width="10%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','brand','<?php echo $pre; ?>');">
									<?php echo $lang->display('Brand Name'); ?>
								</a>
							</th>
							<th width="7%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','subject','<?php echo $pre; ?>');">
									<?php echo $lang->display('Subject'); ?>
								</a>
							</th>
							<th width="7%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','time','<?php echo $pre; ?>');">
									<?php echo $lang->display('Last Update'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT paragraphs.uID AS id, paragraphs.ParaText AS paragraph,
												paragraphs.Words AS wordno, paragraphs.timeRef AS time, paragraphs.user_id,
												languages.flag, languages.languageName AS language,
												users.username AS owner,
												para_types.name AS type, para_types.icon,
												brands.brandName AS brand,
												subjects.subjectTitle AS subject
												FROM paragraphs
												LEFT JOIN languages ON paragraphs.LangID = languages.languageID
												LEFT JOIN users ON paragraphs.user_id = users.userID
												LEFT JOIN companies ON users.companyID = companies.companyID
												LEFT JOIN para_types ON paragraphs.type_id = para_types.id
												LEFT JOIN brands ON paragraphs.brand_id = brands.brandID
												LEFT JOIN subjects ON paragraphs.subject_id = subjects.subjectID
												WHERE users.companyID = %d
												%s
												AND (paragraphs.ParaText LIKE '%s'
												OR languages.languageName LIKE '%s'
												OR para_types.name LIKE '%s'
												OR users.username LIKE '%s'
												OR brands.brandName LIKE '%s'
												OR subjects.subjectTitle LIKE '%s')
												GROUP BY paragraphs.uID
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$company_id,
												mysql_real_escape_string($sub),
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												mysql_real_escape_string($by),
												mysql_real_escape_string($order),
												$limit,
												$offset);
							$result = mysql_query($query, $conn) or die(mysql_error());
							$found = mysql_num_rows($result);
							if($found) {
								$counter = $offset+1;
								while($row = mysql_fetch_assoc($result)) {
									$style = $counter%2==0 ? 'even' : 'odd';
									echo '<tr class="'.$style.'">';
									echo '<td align="center">'.$counter.'</td>';
									echo '<td align="center"><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
									echo '<td align="center"><img src="images/flags/'.$row['flag'].'"></td>';
									echo '<td>'.$lang->display($row['language']).'</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');">'.$row['paragraph'].'</a></td>';
									echo '<td align="center">'.$row['wordno'].'</td>';
									echo '<td align="center"><img src="'.IMG_PATH.$row['icon'].'" title="'.$lang->display($row['type']).'"></td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['user_id'].'\');">'.$row['owner'].'</a></td>';
									echo '<td>'.$row['brand'].'</td>';
									echo '<td>'.$lang->display($row['subject']).'</td>';
									echo '<td>'.date(FORMAT_DATE,strtotime($row['time'])).'</td>';
									echo '<td align="center">'.$row['id'].'</td>';
									echo '</tr>';
									$counter++;
								}
							}
						?>
						<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
						<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
					</table>
				</div>
				<?php require_once(MODULES.'mod_list_nav.php'); ?>
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script type="text/javascript" src="javascripts/jquery/jquery-1.6.2.js"></script>
<script type="text/javascript" src="javascripts/functions.js"></script>
<?php
/*
	Notes:
	Tried to keep it so all parameters for the table display are in GET so linking to pages is easy

	TODO:
	* use sorting to enable sorting the table by family,name,style,id
	* save changes before page changes using the navigation links or when navigating away from page (and have a reset button)
	* tighten up the bar above the list table so it looks a bit nicer
	* have inherited substitute links showing the substitution table entry instead of only loading the entire table (could use keyword=)
*/

$REF = get($_GET['ret'],$_SERVER['HTTP_REFERER']);



$navStatus = array("cpanel");
require_once(MODULES.'mod_header.php');

if(!isset($_SESSION['companyID']))
	die('Not logged in');
BuildPageIntro($lang->display('Control Panel'),$lang->display('CP Intro'));
?>
<script>
var InstalledFonts = '<?php
	echo '<option value="-">-</option>';
	$query = sprintf("SELECT fonts.id, fonts.family, fonts.name,
					service_engines.name AS service
					FROM fonts
					LEFT JOIN service_engines ON fonts.engine_id = service_engines.id
					WHERE installed = 1
					ORDER BY service_engines.name ASC,
					name ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result))
		printf('<option value="%d">[%s] %s</option>',
			$row['id'],
			htmlspecialchars($row['service'],ENT_QUOTES),
			htmlspecialchars(empty($row['name']) ? '('.$row['family'].')' : $row['name'],ENT_QUOTES));
?>';

window.PAGL = {
	FontSubstitution:{
		changes:{}
	}
};
// Strikes though 'Inherited Substitute' field if a substitute has been selected
$('.InstalledFonts').live('change',function(e){
	font = $(this).prev().val(); // ID of font getting subsituted
	selected = $(this).children(':selected').val(); // ID of font to substitute with
	inherited = $(this).parent().prev();
	
	if(selected !='-' && inherited.text() != '-'){
		inherited.css('text-decoration','line-through');
	}else{
		inherited.css('text-decoration','none');
	}
	if(selected != $(this).attr('current')){
		$.post('index.php?<?php echo $_SERVER['QUERY_STRING']; ?>',{
			'process_only':true,
			'font[]':font,
			'subs[]':selected});
	}
});

// Populates the .InstalledFonts selectors, this beats sending around 42,276B (~41.3KiB) over 1000 times
$(function(){
	$('.InstalledFonts').each(function(i,e){
		e.innerHTML = InstalledFonts;
		$(e).children('option[value="'+$(this).attr('current')+'"]').attr('selected','1');
	});
	$('.InstalledFonts').trigger('change');
});

/* Saving every single change individually as unload is called after the next page has been requested
// Save changes when page changes
$(window).unload(function(){
	data = {'process_only':true};
	i = 0;
	changes = window.PAGL.FontSubstitution.changes;
	for(font in changes){
		data['font['+  i  +']'] = font;
		data['subs['+ i++ +']'] = changes[font];
	}
	$.ajaxSetup({async:false});
	$.post('index.php?<?php echo $_SERVER['QUERY_STRING']; ?>',data);
});*/

// Automatically clears selectors of the same class that come after the changed .cascadeNav then submits the form
$('.cascadeNav').live('change',function(e){
	$(this).parent().nextAll().find('.cascadeNav').each(function(i,e){
		$(this).children(':selected').removeAttr('selected');
		$(this).children('option[value="0"]').attr('selected',1);
	});
	$(this).parents('form').submit();
});


</script>
<style>
#listview td:nth-child(1),
#listview td:nth-child(5) {
	text-align:center;
}

#navlay {width:100%;}

#navlay th,
#navlay td {
	width:auto;
}

#navlay th:nth-child(5),
#navlay td:nth-child(5) {
	width:100%;
}

option[value="-"] {text-align:center;}

#defaultIn {width:100%;text-align:center;}

#defaultIn th:nth-child(1),
#defaultIn td:nth-child(1) {
	text-align:right;
	width:50%;
	padding-right:5px;
}

#defaultIn th:nth-child(2),
#defaultIn td:nth-child(2) {
	text-align:left;
	width:50%;
	padding-left:5px;
}


</style>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_font_sub.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Font Substitution Manager'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save Changes'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Close -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
						<a href="javascript:void(0);" onclick="window.location = '<?php echo $REF; ?>'">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
							<div><?php echo $lang->display('Close'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<?php
			BuildTipMsg('Set your desired font substituiton for all, used or missing fonts. This can be set within company, campaign, artwork or specific task.');
			?>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="navform"
					name="navform"
					action="index.php"
					method="GET">
				<input type="hidden" name="layout" value="<?php echo $layout; ?>" />
				<input type="hidden" id="page" name="page" value="<?php echo $page; ?>" />
				<input type="hidden" id="limit" name="limit" value="<?php echo $limit; ?>" />
				<input type="hidden" name="ret" value="<?php echo $REF; ?>" />
				
				<div class="option">
					<div class="search">
						<table id="navlay">
							<tr style="text-align:center;">
								<th>
									<?php if($isadmin) echo $lang->display('Company');
									if($isadmin && $companyID) echo '<img style="float:right;margin:auto;" src="'.IMG_PATH.'goes_to.png" />'; ?>
								</th>
								<th>
									<?php if($companyID) echo $lang->display('Campaign');
									if($campaignID) echo '<img style="float:right;margin:auto;" src="'.IMG_PATH.'goes_to.png" />'; ?>
								</th>
								<th>
									<?php if($campaignID) echo $lang->display('Artwork');
									if($artworkID) echo '<img style="float:right;margin:auto;" src="'.IMG_PATH.'goes_to.png" />'; ?>
								</th>
								<th>
									<?php if($artworkID)
										echo $lang->display('Task'); ?>
								</th>
								<th>
									</th>
								<th>
									<?php echo $lang->display('Show'); ?>
								</th>
								<th>
									<?php echo $lang->display('Search'); ?>
								</th>
								<th>
									<?php echo $lang->display('Service'); ?>
								</th>
							</tr>
							<tr>
								<?php
								echo '<td>';
								// Output Companies
								if($isadmin){
									$result = mysql_query("SELECT companyID, companyName FROM companies WHERE 1") or die(mysql_error());
									echo '
										<select class="input cascadeNav" name="companyID" title="Select a company">
											<option value="0">- All Companies -</option>';
									while($row = mysql_fetch_row($result)){
										printf('<option value="%d"%s>%s</option>',$row[0],($row[0]==$companyID?' selected':''),htmlspecialchars($row[1]));
									}
									echo '</select>';
								}
								echo '</td><td>';
								if($companyID){
									$result = mysql_query("SELECT campaigns.campaignID, campaigns.campaignName FROM campaigns LEFT JOIN brands ON brands.brandID = campaigns.brandID WHERE brands.companyID = ${companyID}");
									echo '
										<select class="input cascadeNav" name="campaignID" title="Select a campaign">
											<option value="0">- All Campaigns -</option>';
									while($row = mysql_fetch_row($result)){
										printf('<option value="%d" %s>%s</option>',$row[0],($row[0]==$campaignID?'selected':''),$row[1]);
									}
									echo '</select>';
								}
								echo '</td><td>';
								
								if($campaignID){
									$result = mysql_query("SELECT artworks.artworkID, artworks.artworkName FROM artworks WHERE artworks.campaignID = ${campaignID}");
									echo '
										<select class="input cascadeNav" name="artworkID" title="Select an artwork">
											<option value="0">- All Artworks -</option>';
									while($row = mysql_fetch_row($result)){
										printf('<option value="%d" %s>%s</option>',$row[0],($row[0]==$artworkID?'selected':''),$row[1]);
									}
									echo '</select>';
								}
								echo '</td><td>';
								if($artworkID){
									$result = mysql_query("
										SELECT tasks.taskID, languages.languageName FROM tasks
											LEFT JOIN artworks ON artworks.artworkID = tasks.artworkID
											LEFT JOIN languages ON languages.languageID = tasks.desiredLanguageID
										WHERE
											tasks.artworkID = ${artworkID}");
									echo '<select class="input cascadeNav" name="taskID" title="Select a task"><option value="0">- All Tasks -</option>';
									while($row = mysql_fetch_row($result)){
										printf('<option value="%d" %s>%s</option>',$row[0],($row[0]==$taskID?'selected':''),$row[1]);
									}
									echo '</select>';
								}
								?>
								<td></td>
								
								<td>
									<select class="input" name="show" title="All - Show every font in the system, &#013;Used - Show fonts used in artworks, &#013;Missing - Show fonts used in artworks that are not installed">
										<option<?php if($show=='All') echo ' selected'; ?>>All</option>
										<option<?php if($show=='Used') echo ' selected'; ?>>Used</option>
										<option<?php if($show=='Missing') echo ' selected'; ?>>Missing</option>
									</select>
								</td>
								<td>
									<input
										type="text"
										class="input"
										onfocus="this.className='inputOn'"
										onblur="this.className='input'"
										id="keyword"
										name="keyword"
										value="<?php echo $keyword; ?>"
										title="Text to search for in the font names, families and styles"
									>
								</td>
								<td style="white-space:nowrap;">
									<select
										class="input"
										onfocus="this.className='inputOn'"
										onblur="this.className='input'"
										name="service"
										id="service"
										title="Filter by graphics engine"
									>
									<option value="0">-</option>
									<?php BuildEngineList($service); ?>
									</select>
									
									<input
										type="submit"
										class="btnDo"
										onmousemove="this.className='btnOn'"
										onmouseout="this.className='btnDo'"
										value="<?php echo $lang->display('Go'); ?>"
										title="<?php echo $lang->display('Go'); ?>"
									>
								</td>
							</tr>
							</form>
							<form
								id="listform"
								name="listform"
								action="index.php?<?php echo $_SERVER['QUERY_STRING']; ?>"
								method="POST"
								enctype="multipart/form-data"
								onsubmit="Popup('loadingme','waiting');"
							>
							<tr>
								<td colspan="8" style="background-color:#e0e0e0;padding:5px;" id="pulse">
									<table id="defaultIn">
										<tr>
											<th colspan="2" style="text-align:center;">Substitution for missing fonts</th>
										</tr>
										<tr>
											<th>Inherited Substitute</th>
											<th>Selected Substitute</th>
										</tr>
										
										<tr>
											<td>
											<?php
											$inherited = Font_Substitution::get_inherited_font($row['id'],$levelID,$level);
											if($inherited){
												$info = $DB->get_font_info($inherited['font']);
												if($inherited['level']=='system')
													$inherit = htmlspecialchars($info['name']);
												else
													$inherit = sprintf('<a href="index.php?layout=%s&%s=%d">%s</a>',
														$layout,
														$inherited['level'],
														$inherited['id'],
														htmlspecialchars($info['name']));
											}else{
												$inherit = '-';
											}
											echo $inherit;
											?>
											</td>
											<td>
												<input type="hidden" name="font[]" value="0"/>
												<select name="subs[]" class="input InstalledFonts" current="<?php echo Font_Substitution::get_font_substitution(0,$levelID,$level);?>"></select>
											</td>
										</tr>
									</table>
								
								</td>
							</tr>
						</table>
					</div>
					<div class="clear"></div>
				</div>
				
				<div class="list" style="white-space:nowrap;">
					<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
						<tr>
							<th width="6%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','installed','<?php echo $pre; ?>');">
									<?php echo $lang->display('Installed'); ?>
								</a>
							</th>
							<th width="23%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','family','<?php echo $pre; ?>');">
									<?php echo $lang->display('Family'); ?>
								</a>
							</th>
							<th width="40%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','name','<?php echo $pre; ?>');">
									<?php echo $lang->display('Name'); ?>
								</a>
							</th>
							<th width="15%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','style','<?php echo $pre; ?>');">
									<?php echo $lang->display('Style'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
							<th width="15%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','style','<?php echo $pre; ?>');">
									<?php echo $lang->display('Inherited Substitute'); ?>
								</a>
							</th>
							<th width="15%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','style','<?php echo $pre; ?>');">
									<?php echo $lang->display('Substitute'); ?>
								</a>
							</th>
						</tr>
						<?php
							$keyword = mysql_real_escape_string($keyword);
							switch($show){
							// Show all fonts in the system
							case 'All':
								$query = 'SELECT fonts.* FROM fonts WHERE 1';break;
							// Show fonts that are used in artworks
							case 'Used':
								switch($level){
								case 'task':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN artworks ON artworks.artworkID = artwork_fonts.artwork_id
										LEFT JOIN tasks ON tasks.artworkID = artworks.artworkID
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE tasks.taskID = %d AND fonts.id',$levelID);
									break;
									
								case 'artwork':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE artwork_fonts.artwork_id = %d AND fonts.id',$levelID);
									break;
									
								case 'campaign':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN artworks ON artworks.artworkID = artwork_fonts.artwork_id
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE artworks.campaignID = %d AND fonts.id',$levelID);
									break;
								
								case 'company':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN artworks ON artworks.artworkID = artwork_fonts.artwork_id
										LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
										LEFT JOIN brands ON brands.brandID = campaigns.brandID
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE brands.companyID = %d AND fonts.id',$levelID);
									break;
								
								case 'system':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE 1');
									break;
								};break;
							
							// Show fonts that are used in artworks and are uninstalled
							case 'Missing':
								switch($level){
								case 'task':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN tasks ON tasks.artworkID = artwork_fonts.artwork_id
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE tasks.taskID = %d AND fonts.installed = 0',$levelID);
									break;
									
								case 'artwork':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE artwork_fonts.artwork_id = %d AND fonts.installed = 0',$levelID);
									break;
									
								case 'campaign':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN artworks ON artworks.artworkID = artwork_fonts.artwork_id
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE artworks.campaignID = %d AND fonts.installed = 0',$levelID);
									break;
								
								case 'company':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN artworks ON artworks.artworkID = artwork_fonts.artwork_id
										LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
										LEFT JOIN brands ON brands.brandID = campaigns.brandID
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE brands.companyID = %d AND fonts.installed = 0',$levelID);
									break;
								
								case 'system':
									$query = sprintf('SELECT DISTINCT fonts.* FROM artwork_fonts
										LEFT JOIN fonts ON fonts.id = artwork_fonts.font_id
										WHERE 1 AND fonts.installed = 0');
									break;
								};break;
							}
							
							$result = mysql_query($query) or die(mysql_error());
							$total = mysql_num_rows($result);
							$pages = (int)(($total+$limit-1)/ $limit);
							
							if($service) $query .= sprintf(' AND fonts.engine_id = %d',$service);
							if($keyword){
								if((int)$keyword)
									$query .= ' AND fonts.id = ' . (int)$keyword;
								else
									$query .= " AND (fonts.name LIKE '%${keyword}%' OR fonts.style LIKE '%${keyword}%' OR fonts.family LIKE '%${keyword}%')";
							}
							$query .= sprintf(' LIMIT %d,%d',($page-1)*$limit,$limit);
							
							$result = mysql_query($query) or die(mysql_error());;
							
							while($row = mysql_fetch_assoc($result)){
								$inherited = Font_Substitution::get_inherited_font($row['id'],$levelID,$level);
								if($inherited){
									$info = $DB->get_font_info($inherited['font']);
									if($inherited['level']=='system' || ($inherited['id']==$levelID && $inherited['level']==$level))
										$inherit = htmlspecialchars($info['name']);
									else
										$inherit = sprintf('<a href="index.php?layout=%s&%sID=%d&keyword=%d">%s</a>',
											$layout,
											$inherited['level'],
											$inherited['id'],
											$row['id'],
											htmlspecialchars($info['name']));
								}else{
									$inherit = '-';
								}
								printf('
									<tr>
										<td><img src="%s" title="%s"/></td>
										<td>%s</td>
										<td>%s</td>
										<td>%s</td>
										<td>%d</td>
										<td>%s</td>
										<td>
											<input type="hidden" name="font[]" value="%d"/>
											<select class="input InstalledFonts" name="subs[]" current="%d"></select>
										</td>
									</tr>',
									IMG_PATH.'ico_'.($row['installed']?'enable':'disable').'.png',
									$row['installed']?'Installed':'Not Installed',
									htmlspecialchars($row['family']),
									htmlspecialchars($row['name']),
									htmlspecialchars($row['style']),
									$row['id'],
									$inherit,// Inherited font
									$row['id'],
									Font_Substitution::get_font_substitution($row['id'],$levelID,$level));// Substitute font
							}
						?>
						<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
						<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
					</table>
				</div>
				<input type="hidden" name="form" id="form">
				</form>
				
				
				
				<div class="nav">
					<div class="left">
						<?php echo $lang->display('Found').' <b>'.$total.'</b><span class="span">|</span>'.$lang->display('Display'); ?>
						<select
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input'"
							onchange="$('#navform #limit').val($(this).children(':selected').val());$('#navform').submit();"
							title="Select how many items will appear on the page &#013;Large values will make the page load slower"
						>
						<?php
							$display = array(5,10,15,20,50);
							foreach($display as $d) {
								echo '<option value="'.$d.'"';
								if($d == $limit) echo ' selected="selected"';
								echo '>'.$d.'</option>';
							}
							echo '<option value="'.$total.'"';
							if($total == $limit) echo ' selected="selected"';
							echo '>'.$lang->display('All').'</option>';
						?>
						</select>
						/ <?php echo $lang->display('Page'); ?>
					</div>
					<div class="right">
						<?php echo $lang->display('Page').' <b>'.$page.'</b> / '.$pages.'<span class="span">|</span>'.$lang->display('Go to Page'); ?>
						<select
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input'"
							title="<?php echo $lang->display('Go to Page'); ?>"
							onchange="$('#navform #page').val($(this).children(':selected').val());$('#navform').submit();"
						>
						<?php
							for($i=1; $i<=$pages; $i++) {
								echo '<option value="'.$i.'"';
								if($i == $page) echo ' selected="selected"';
								echo '>'.$i.'</option>';
							}
						?>
						</select>
					</div>
					<div class="mid">
						<?php
							if($page>1) {
								$prev = $page - 1;
								echo '<a href="javascript:void(0);" onclick="$(\'#navform #page\').val('.$prev.');$(\'#navform\').submit();"><img src="'.IMG_PATH.'toolbar/ico_prev_on.png" title="'.$lang->display('Previous Page').'" /></a> ';
							} else {
								echo '<img src="'.IMG_PATH.'toolbar/ico_prev_off.png" />';
							}
							if($page<$pages) {
								$next = $page + 1;
								echo ' <a href="javascript:void(0);" onclick="$(\'#navform #page\').val('.$next.');$(\'#navform\').submit();"><img src="'.IMG_PATH.'toolbar/ico_next_on.png" title="'.$lang->display('Next Page').'" /></a>';
							} else {
								echo '<img src="'.IMG_PATH.'toolbar/ico_next_off.png" />';
							}
						?>
					</div>
					<div class="clear"></div>
				</div>
				
			</div>
		</div>
	</div>
</div>
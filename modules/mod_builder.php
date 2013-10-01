<?php
function BuildStepIndicator($step=1, $total=3) {
	global $lang;
	echo '<span class="span">'.$lang->display("Step $step of $total").'</span>';
	for($i=1; $i<=$total; $i++) {
		$class = "step_off";
		if($i == $step) $class = "step_on";
		echo '<span class="'.$class.'">'.$i.'</span>';
	}
}

function BuildCreditTopupList($limit) {
	for($i=50;$i<=$limit;$i=$i+50) {
		echo '<option value="'.$i.'">'.$i.'</option>';
	}
}

function BuildCreditAllowance($user_id=0) {
	global $DB, $lang;
	echo '<table width="100%" cellpadding="3" cellspacing="0" border="0">';
	echo '<tr>';
	echo '<th width="40%">'.$lang->display('Credit Allowance').' / '.$lang->display('Day').'</th>';
	echo '<td width="60%">';
	echo '<select
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="allowance"
			id="allowance">';
	echo '<option value="-1"';
	$company_id = $DB->get_user_company_id($user_id);
	$allowance = $DB->check_credit_allowance($user_id);
	if($allowance!==false && $allowance==-1) echo ' selected="selected"';
	echo '>'.$lang->display('Unlimited').'</option>';
	for($i=0;$i<=100;$i=$i+10) {
		echo '<option value="'.$i.'"';
		if($allowance!==false && $i==$allowance) echo ' selected="selected"';
		echo '>'.$i.'</option>';
	}
	echo '</select> * '.$lang->display('Subject to availability.').' ['.$DB->count_available_credits($company_id,$user_id).']';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
}

function BuildRating($rating) {
	if(!empty($rating)) {
		for($i=1;$i<=$rating;$i++) {
			echo '<img src="'.IMG_PATH.'ico_star.gif" />';
		}
	}
}

function BuildRatingScale($scale, $rating=0) {
	if(!empty($scale)) {
		for($i=0;$i<=$scale;$i++) {
			echo '<option value="'.$i.'"';
			if(!empty($rating) && $i==$rating) echo ' selected="selected"';
			echo '>'.$i.'</option>';
		}
	}
}

function BuildLangFlags() {
	global $conn;
	if(isset($_SERVER['QUERY_STRING'])) {
		$qs = preg_replace("/&?lang=\w{2}&?/i","",$_SERVER['QUERY_STRING']);
	}
	$qs = (!empty($qs)) ? "&$qs" : "" ;
	$query = sprintf("SELECT name, acronym
						FROM language_options
						ORDER BY id ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	echo '<div id="lanBox">';
	while($row = mysql_fetch_assoc($result)) {
		echo '<span class="flag">';
		echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?lang='.$row['acronym'].$qs.'\');"><img src="images/flags/'.$row['acronym'].'.gif" title="'.$row['name'].'"></a>';
		echo '</span>';
	}
	echo '</div>';
}

function BuildLangDropdown($acronym) {
	global $conn;
	if(isset($_SERVER['QUERY_STRING'])) {
		$qs = preg_replace("/&?lang=\w{2}&?/i","",$_SERVER['QUERY_STRING']);
	}
	$qs = (!empty($qs)) ? "&$qs" : "" ;
	$query = sprintf("SELECT name, acronym
						FROM language_options
						WHERE acronym = '%s'
						LIMIT 1",
						mysql_real_escape_string($acronym));
	$result = mysql_query($query, $conn) or die(mysql_error());
	$found = mysql_num_rows($result);
	if($found) {
		$row = mysql_fetch_assoc($result);
		echo '<div class="langOff" onclick="this.className=(this.className==\'langOn\')?\'langOff\':\'langOn\';SlideDiv(\'langlist\');">';
		echo '<div class="flag"><img src="images/flags/'.$row['acronym'].'.gif" title="'.$row['name'].'"></div>';
		echo '<div class="name">'.$row['name'].'</div>';
		echo '<div class="clear"></div>';
		echo '</div>';
		echo '<div id="langlist" class="dropdown" style="display:none; overflow:hidden; height:207px;">';
		$query = sprintf("SELECT name, acronym
							FROM language_options
							ORDER BY id ASC");
		$result = mysql_query($query, $conn) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?lang='.$row['acronym'].$qs.'\');">';
			echo '<div class="lineOff" onmouseover="this.className=\'lineOn\';" onmouseout="this.className=\'lineOff\';">';
			echo '<div class="ico"><img src="'.IMG_PATH.'arrow_right.png" title="'.$row['name'].'"></div>';
			echo '<div class="ico"><img src="images/flags/'.$row['acronym'].'.gif" title="'.$row['name'].'"></div>';
			echo '<div class="txt">'.$row['name'].'</div>';
			echo '<div class="clear"></div>';
			echo '</div>';
			echo '</a>';
		}
		echo '</div>';
	}
}

function BuildOrders($sum, $selected=0) {
	for($i=0;$i<=$sum;$i++) {
		echo '<option value="'.$i.'"';
		if(!empty($selected) && $i==$selected) echo ' selected="selected"';
		echo '>'.$i.'</option>';
	}
}

function BuildUserSpecs($user_id, $subject_id) {
	global $conn;
	$query = sprintf("SELECT indexID
					FROM userspecs
					WHERE userID = %d
					AND subjectID = %d",
					$user_id,
					$subject_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) echo ' <img src="'.IMG_PATH.'ico_star.gif" />';
}

function BuildUserLangs($user_id, $source_lang_id=0, $target_lang_id=0) {
	global $conn, $lang;
	$condition = !empty($source_lang_id)||!empty($target_lang_id) ? sprintf("AND userlanguages.languageID IN (%d, %d)",$source_lang_id,$target_lang_id) : "";
	$query = sprintf("SELECT languages.languageName, languages.flag, proficiency.proLevel
					FROM userlanguages
					LEFT JOIN languages ON userlanguages.languageID = languages.languageID
					LEFT JOIN proficiency ON userlanguages.proID = proficiency.proID
					WHERE userlanguages.userID = %d
					%s
					ORDER BY
					userlanguages.proID DESC,
					languages.languageName ASC",
					$user_id,
					mysql_real_escape_string($condition));
	$result = mysql_query($query, $conn) or die(mysql_error());
	$str = "";
	while($row = mysql_fetch_assoc($result)) {
		$str .= '<img src="images/flags/'.$row['flag'].'" title="'.$lang->display($row['proLevel']).': '.$lang->display($row['languageName']).'"> ';
	}
	echo trim($str);
}

function BuildLangOptions($lang_id=0) {
	global $conn;
	$query = sprintf("SELECT id, name
					FROM language_options
					ORDER BY id ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['id'].'"';
		if(!empty($lang_id) && $lang_id == $row['id']) echo 'selected="selected"';
		echo '>'.$row['name'].'</option>';
	}
}

function BuildDefaultLangOptions($lang_id=0) {
	global $conn, $lang;
	$query = sprintf("SELECT languageID, languageName
					FROM languages
					ORDER BY languageName ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['languageID'].'"';
		if(!empty($lang_id) && $lang_id == $row['languageID']) echo 'selected="selected"';
		echo '>'.$lang->display($row['languageName']).'</option>';
	}
}

function BuildActiveCampaignList($company_id, $user_id, $campaign_id=0) {
	global $conn, $lang, $DB;
	$query = sprintf("SELECT campaignID, campaignName
					FROM campaigns
					ORDER BY campaignName ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	echo '<option value="">- '.$lang->display('Please Select').' -</option>';
	while($row = mysql_fetch_assoc($result)) {
		if(!$DB->check_campaign_acl($row['campaignID'],$company_id,$user_id)) continue;
		echo '<option value="'.$row['campaignID'].'"';
		if(!empty($campaign_id) && $campaign_id == $row['campaignID']) echo 'selected="selected"';
		echo '>'.$row['campaignName'].'</option>';
	}
}

function BuildCompanyACL($company_id=0, $issuperadmin=false) {
	global $conn, $DB;
	echo '<div class="cacl">';
	$limit = $issuperadmin ? "" : sprintf("WHERE companyID IN (%s)", mysql_real_escape_string ($DB->get_company_list($company_id)));
	$query = sprintf("SELECT companyID, companyName
					FROM companies
					%s
					ORDER BY companyName ASC",
					mysql_real_escape_string($limit));
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<div id="company['.$row['companyID'].']">';
		echo '<input type="checkbox" class="checkbox" id="acl['.$row['companyID'].']" name="acl['.$row['companyID'].']" value="1"';
		if($DB->check_company_acl($company_id,$row['companyID']) || $row['companyID']==$company_id) echo ' checked="checked"';
		if($row['companyID'] == $company_id) {
			echo ' onclick="return false;"';
		}
		echo '> '.$row['companyName'].'</div>';
	}
	echo '</div>';
}

function BuildCampaignACL($company_id=0, $user_id=0, $campaign_id=0, $clear=false) {
	global $conn, $DB;
	echo '<div class="cacl">';
	$query = sprintf("SELECT companyID, companyName
					FROM companies
					WHERE companyID IN (%s)
					ORDER BY companyName ASC",
					mysql_real_escape_string($DB->get_company_list($company_id)));
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		if(empty($campaign_id)) {
			$check_company = (!empty($company_id) && $company_id == $row['companyID']);
		} else {
			$check_company = $DB->check_campaign_acl($campaign_id,$row['companyID']);
		}
		echo '<div id="company['.$row['companyID'].']" class="treeOff" onclick="ChangeTree(\'company['.$row['companyID'].']\');showandhide(\'users['.$row['companyID'].']\')">';
		if(!$clear) {
			echo '<input type="checkbox" class="checkbox" id="acl['.$row['companyID'].'][0]" name="acl['.$row['companyID'].'][0]" value="1"';
			if($check_company && !$clear) echo ' checked="checked"';
			if($row['companyID']==$company_id && !$clear) {
				echo ' onclick="return false;"';
			} else {
				echo ' onclick="GroupCheckbox(this,\'acl['.$row['companyID'].']\')"';
			}
			echo '> ';
		}
		echo $row['companyName'].'</div>';
		$query_user = sprintf("SELECT userID, forename, surname
							FROM users
							WHERE companyID = %d
							ORDER BY forename ASC",
							$row['companyID']);
		$result_user = mysql_query($query_user, $conn) or die(mysql_error());
		echo '<div id="users['.$row['companyID'].']" style="display:none;">';
		while($row_user = mysql_fetch_assoc($result_user)) {
			if(empty($campaign_id)) {
				$check_user = (!empty($company_id) && $company_id == $row['companyID']);
			} else {
				$check_user = $DB->check_campaign_acl($campaign_id,$row['companyID'],$row_user['userID']);
			}
			echo '<div class="item">';
			echo '<input type="checkbox" class="checkbox" id="acl['.$row['companyID'].']['.$row_user['userID'].']" name="acl['.$row['companyID'].']['.$row_user['userID'].']" value="1"';
			if($check_user && !$clear) echo ' checked="checked"';
			if($row_user['userID']==$user_id && !$clear) {
				echo ' onclick="return false;"';
			} else {
				echo ' onclick="addValue(\'user_guests\',\''.$row_user['userID'].',\');"';
			}
			echo '> '.$row_user['forename'].' '.$row_user['surname'].'</div>';
		}
		echo '</div>';
	}
	echo '</div>';
}

function BuildTweakUploadType($service_package_id, $service_engine_id, $transaction_id, $type_id) {
	global $conn, $lang;
	$query = sprintf("SELECT service_transaction_process.id, service_transaction_process.notes
					FROM service_package_items
					LEFT JOIN service_transaction_process ON service_transaction_process.id = service_package_items.service_tID
					LEFT JOIN service_engines ON service_engines.id = service_transaction_process.serviceID
					WHERE service_package_items.packageID = %d
					AND service_transaction_process.serviceID = %d
					AND service_transaction_process.transactionID = %d
					AND service_transaction_process.type_id = %d
					LIMIT 1",
					$service_package_id,
					$service_engine_id,
					$transaction_id,
					$type_id);
	$result = mysql_query($query,$conn) or die(mysql_error());
	if(!mysql_num_rows($result)) return false;
	$row = mysql_fetch_assoc($result);
	echo '<option value="'.$row['id'].'">'.$lang->display($row['notes']).'</option>';
}

function BuildDownloadList($service_package_id, $service_engine_id, $transaction_id, array $types, $allow=false) {
	global $conn, $lang;
	if(!count($types)) return false;
	$str = "";
	foreach($types as $type) {
		$type = (int)$type;
		$str .= "$type,";
	}
	$str = trim($str,',');
	echo '<option value="">- '.$lang->display('Select File Type').' -</option>';
	$query = sprintf("SELECT service_transaction_process.id, service_transaction_process.notes, service_transaction_process.allow
					FROM service_package_items
					LEFT JOIN service_transaction_process ON service_transaction_process.id = service_package_items.service_tID
					LEFT JOIN service_engines ON service_engines.id = service_transaction_process.serviceID
					WHERE service_package_items.packageID = %d
					AND service_transaction_process.serviceID = %d
					AND service_transaction_process.transactionID = %d
					AND service_transaction_process.type_id IN (%s)
					ORDER BY
					service_engines.id ASC,
					service_transaction_process.type_id ASC,
					service_transaction_process.notes ASC",
					$service_package_id,
					$service_engine_id,
					$transaction_id,
					$str);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		if(empty($row['allow']) && empty($allow)) continue;
		echo '<option value="'.$row['id'].'">'.$lang->display($row['notes']).'</option>';
	}
}

function BuildMsgFolderList($cfolder="inbox") {
	global $conn, $lang;
	$folders = array("inbox", "sent", "trashed");
	foreach($folders as $folder) {
		echo '<option value="'.$folder.'"';
		if(!empty($cfolder) && $folder == $cfolder) echo 'selected="selected"';
		echo '>'.$lang->display(ucfirst($folder)).'</option>';
	}
}

function BuildImgTypeList() {
	echo 'JPG | GIF | PNG';
}

function BuildProfileEditList($user_id) {
	global $conn, $lang;
	$query = sprintf("SELECT forename, surname, email, telephone, fax, mobile, langID, defaultLangID
					FROM users
					WHERE userID = %d
					LIMIT 1",
					$user_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(!mysql_num_rows($result)) return false;
	$row = mysql_fetch_assoc($result);
	echo '<tr>';
	echo '<th width="40%">* '.$lang->display('Language Setup').'</th>';
	echo '<td width="60%">';
	echo '<select
			type="text"
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="langID"
			id="langID">';
	BuildLangOptions($row['langID']);
	echo '</select>';
	echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th width="40%">* '.$lang->display('Default Language').'</th>';
	echo '<td width="60%">';
	echo '<select
			type="text"
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="defaultLangID"
			id="defaultLangID">';
	BuildDefaultLangOptions($row['defaultLangID']);
	echo '</select>';
	echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th>* '.$lang->display('Forename').'</th>';
	echo '<td>';
	echo '<input
			type="text"
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="forename"
			id="forename"
			value="'.$row['forename'].'" />';
	echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th>* '.$lang->display('Surname').'</th>';
	echo '<td>';
	echo '<input
			type="text"
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="surname"
			id="surname"
			value="'.$row['surname'].'" />';
	echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th>* '.$lang->display('Email').'</th>';
	echo '<td>';
	echo '<input
			type="text"
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="email"
			id="email"
			value="'.$row['email'].'" />';
	echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th>'.$lang->display('Telephone').'</th>';
	echo '<td>';
	echo '<input
			type="text"
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="telephone"
			id="telephone"
			value="'.$row['telephone'].'" />';
	echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th>'.$lang->display('Fax').'</th>';
	echo '<td>';
	echo '<input
			type="text"
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="fax"
			id="fax"
			value="'.$row['fax'].'" />';
	echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th>'.$lang->display('Mobile').'</th>';
	echo '<td>';
	echo '<input
			type="text"
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="mobile"
			id="mobile"
			value="'.$row['mobile'].'" />';
	echo '</td>';
	echo '</tr>';
}

function BuildUserLangList($user_id) {
	global $conn, $lang;
	$query = sprintf("SELECT languages.flag, languages.languageName, proficiency.proLevel
					FROM userlanguages
					LEFT JOIN languages ON userlanguages.languageID = languages.languageID
					LEFT JOIN proficiency ON userlanguages.proID = proficiency.proID
					WHERE userlanguages.userID = %d
					ORDER BY userlanguages.proID DESC",
					$user_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		while ($row = mysql_fetch_assoc($result)) {
			echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'">';
			echo '<td width="50%"><img src="images/flags/'.$row['flag'].'" title="'.$lang->display($row['languageName']).'"> '.$lang->display($row['languageName']).'</td>';
			echo '<td width="50%">'.$lang->display($row['proLevel']).'</td>';
			echo "</tr>";
		}
	} else {
		echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'">';
		echo '<td colspan="2"><i>'.$lang->display('N/S').'</i></td>';
		echo "</tr>";
	}
}

function BuildLangEditList($user_id) {
	global $conn, $lang;
	$query = sprintf("SELECT *
					FROM userlanguages
					WHERE userID = %d",
					$user_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		echo '<tr align="center">';
		echo '<td width="45%"><b>'.$lang->display('Language').'</b></td>';
		echo '<td width="45%"><b>'.$lang->display('Proficiency').'</b></td>';
		echo '<td width="10%"><b>'.$lang->display('Delete').'</b></td>';
		echo '</tr>';
	}
	while($row = mysql_fetch_assoc($result)) {
		echo '<tr align="center">';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="lang['.$row['indexID'].']"
				name="lang['.$row['indexID'].']">';
		BuildLangList($row['languageID']);
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="pro['.$row['indexID'].']"
				name="pro['.$row['indexID'].']">';
		BuildLangProList($row['proID']);
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<input type="checkbox" id="delete['.$row['indexID'].']" name="delete['.$row['indexID'].']" value="1">';
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr align="center">';
	echo '<td><b>'.$lang->display('Language').'</b></td>';
	echo '<td><b>'.$lang->display('Proficiency').'</b></td>';
	echo '<td></td>';
	echo '</tr>';
	for($i=1; $i<=5; $i++) {
		echo '<tr align="center">';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="new_lang['.$i.']"
				name="new_lang['.$i.']">';
		BuildLangList();
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="new_pro['.$i.']"
				name="new_pro['.$i.']">';
		BuildLangProList();
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '</td>';
		echo '</tr>';
	}
}

function BuildUserSpecList($user_id, $highlight=true) {
	global $conn, $lang;
	$do_highlight = $highlight ? ' class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'"' : '';
	$query = sprintf("SELECT subjects.subjectTitle
					FROM userspecs
					LEFT JOIN subjects ON userspecs.subjectID = subjects.subjectID
					WHERE userspecs.userID = %d
					ORDER BY subjects.subjectTitle ASC",
					$user_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		echo '<tr'.$do_highlight.'>';
		$counter=1;
		while($row = mysql_fetch_assoc($result)) {
			echo '<td width="50%"><img src="'.IMG_PATH.'arrow_gold_rgt.png" /> '.$row['subjectTitle'].'</td>';
			if($counter%2==0) {
				echo '</tr><tr'.$do_highlight.'>';
			}
			$counter++;
		}
		echo '</tr>';
	} else {
		echo '<tr'.$do_highlight.'>';
		echo '<td colspan="2"><i>'.$lang->display('N/S').'</i></td>';
		echo '</tr>';
	}
}

function BuildSpecEditList($user_id) {
	global $conn, $lang;
	echo '<tr>';
	$query = sprintf("SELECT subjectID, subjectTitle
					FROM subjects
					ORDER BY subjectTitle ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	$counter=1;
	while ($row = mysql_fetch_assoc($result)) {
		echo '<td>';
		echo '<input type="checkbox" name="subjectID[]" value="'.$row['subjectID'].'"';
		$query_spec = sprintf("SELECT indexID
							FROM userspecs
							WHERE userID = %d
							AND subjectID = %d
							LIMIT 1",
							$user_id,
							$row['subjectID']);
		$result_spec = mysql_query($query_spec, $conn) or die(mysql_error());
		if(mysql_num_rows($result_spec)) echo ' checked="checked"';
		echo ' /> '.$lang->display($row['subjectTitle']);
		echo '</td>';
		if($counter%2==0) {
			echo '</tr><tr>';
		}
		$counter++;
	}
	echo '</tr>';
}

function BuildUserRateList($user_id) {
	global $conn, $lang;
	$query = sprintf("SELECT userrates.rate, userrates.preference, userrates.sourceLangID, userrates.targetLangID,
					L1.flag AS sourceFlag, L1.languageName AS sourceLang,
					L2.flag AS targetFlag, L2.languageName AS targetLang,
					currencies.currencyAb
					FROM userrates
					LEFT JOIN languages L1 ON userrates.sourceLangID = L1.languageID
					LEFT JOIN languages L2 ON userrates.targetLangID = L2.languageID
					LEFT JOIN currencies ON userrates.currencyID = currencies.currencyID
					WHERE userrates.userID = %d
					ORDER BY userrates.sourceLangID ASC, userrates.targetLangID ASC",
					$user_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		while($row = mysql_fetch_assoc($result)) {
			echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'">';
			echo "<td width=\"50%\">";
			if($row['sourceLangID']) {
				echo '<img src="images/flags/'.$row['sourceFlag'].'" title="'.$lang->display($row['sourceLang']).'" />';
			} else {
				echo '<img src="'.IMG_PATH.'flag_missing.gif" title="'.$lang->display('N/S').'" />';
			}
			echo ' <img src="'.IMG_PATH.'flag_to.gif" title="'.$lang->display('to be translated to').'"> ';
			if($row['targetLangID']) {
				echo '<img src="images/flags/'.$row['targetFlag'].'" title="'.$lang->display($row['targetLang']).'" />';
			} else {
				echo '<img src="'.IMG_PATH.'flag_missing.gif" title="'.$lang->display('N/S').'" />';
			}
			echo "</td>";
			echo "<td width=\"50%\">";
			if($row['preference']==0) {
				echo '<img src="'.IMG_PATH.'ico_private.gif" title="'.$lang->display('Private').'">';
			} else {
				echo $row['rate'].' '.$lang->display($row['currencyAb']);
			}
			echo "</td>";
			echo "</tr>";
		}
	} else {
		echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'">';
		echo "<td colspan=\"2\"><i>".$lang->display('N/S')."</i></td>";
		echo "</tr>";
	}
}

function BuildRateEditList($user_id) {
	global $conn, $lang;
	$query = sprintf("SELECT *
					FROM userrates
					WHERE userID = %d
					ORDER BY
					sourceLangID ASC,
					targetLangID ASC",
					$user_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		echo '<tr align="center">';
		echo '<td width="25%"><b>'.$lang->display('Source Language').'</b></td>';
		echo '<td width="25%"><b>'.$lang->display('Desired Language').'</b></td>';
		echo '<td width="25%"><b>'.$lang->display('Minimum Rate Per Word').'</b></td>';
		echo '<td width="15%"></td>';
		echo '<td width="10%"><b>'.$lang->display('Delete').'</b></td>';
		echo '</tr>';
	}
	while($row = mysql_fetch_assoc($result)) {
		echo '<tr align="center">';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="source_lang['.$row['indexID'].']"
				name="source_lang['.$row['indexID'].']">';
		BuildLangList($row['sourceLangID']);
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="target_lang['.$row['indexID'].']"
				name="target_lang['.$row['indexID'].']">';
		BuildLangList($row['targetLangID']);
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="currency['.$row['indexID'].']"
				name="currency['.$row['indexID'].']">';
		BuildCurrencyList($row['currencyID']);
		echo '</select>';
		echo '<input
				type="text"
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				size="3"
				maxlength="5"
				pattern="\d?(\.\d{1,2})"
				id="rate['.$row['indexID'].']"
				name="rate['.$row['indexID'].']"
				value="'.$row['rate'].'" />';
		echo '</td>';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="preference['.$row['indexID'].']"
				name="preference['.$row['indexID'].']">';
		BuildPrivacyList($row['preference']);
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<input type="checkbox" id="delete['.$row['indexID'].']" name="delete['.$row['indexID'].']" value="1">';
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr align="center">';
	echo '<td><b>'.$lang->display('Source Language').'</b></td>';
	echo '<td><b>'.$lang->display('Desired Language').'</b></td>';
	echo '<td><b>'.$lang->display('Minimum Rate Per Word').'</b></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '</tr>';
	for($i=1; $i<=5; $i++) {
		echo '<tr align="center">';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="new_source_lang['.$i.']"
				name="new_source_lang['.$i.']">';
		BuildLangList();
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="new_target_lang['.$i.']"
				name="new_target_lang['.$i.']">';
		BuildLangList();
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="new_currency['.$i.']"
				name="new_currency['.$i.']">';
		BuildCurrencyList(CURRENCY);
		echo '</select>';
		echo '<input
				type="text"
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				size="3"
				maxlength="5"
				id="new_rate['.$i.']"
				name="new_rate['.$i.']" />';
		echo '</td>';
		echo '<td>';
		echo '<select
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="new_preference['.$i.']"
				name="new_preference['.$i.']">';
		BuildPrivacyList();
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '</td>';
		echo '</tr>';
	}
}

function BuildUserACLList($acl, $user_id, $company_id) {
	global $conn, $lang;
	$query_aco_sec = sprintf("SELECT *
							FROM aco_sections
							WHERE hidden = 0
							ORDER BY order_value ASC");
	$result_aco_sec = mysql_query($query_aco_sec, $conn) or die(mysql_error());
	while($row_aco_sec = mysql_fetch_assoc($result_aco_sec)) {
		echo '<tbody onmouseover="display(\'edit_acl_'.$row_aco_sec['value'].'\');" onmouseout="hidediv(\'edit_acl_'.$row_aco_sec['value'].'\');">';
		echo '<tr class="subject">';
		echo '<td width="90%">ACL - '.$lang->display($row_aco_sec['name']).'</td>';
		echo '<td width="10%">';
		echo '<div id="edit_acl_'.$row_aco_sec['value'].'" style="display:none;"><img src="'.IMG_PATH.'ico_locked.png" title="'.$lang->display('Edit Locked').'" /></div>';
		echo '</td>';
		echo '</tr>';
		$query_aco = sprintf("SELECT *
							FROM aco
							WHERE hidden = 0
							AND section_value = '%s'
							ORDER BY order_value ASC",
							$row_aco_sec['value']);
		$result_aco = mysql_query($query_aco, $conn) or die(mysql_error());
		while($row_aco = mysql_fetch_assoc($result_aco)) {
			echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'">';
			echo '<td><img src="'.IMG_PATH.'arrow_gold_rgt.png" /> '.$lang->display($row_aco['name']).'</td>';
			echo '<td>';
			if($acl->acl_check($row_aco_sec['value'],$row_aco['value'],$company_id,$user_id)) {
				echo "<img src=\"".IMG_PATH."ico_enable.png\" title=\"".$lang->display('Allow')."\" />";
			} else {
				echo "<img src=\"".IMG_PATH."ico_disable.png\" title=\"".$lang->display('Deny')."\" />";
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
	}
}

function BuildACLEditList($acl, $user_id, $company_id, $issuperadmin) {
	global $conn, $lang;
	$query_aco_sec = sprintf("SELECT *
							FROM aco_sections
							WHERE hidden = 0
							ORDER BY order_value ASC");
	$result_aco_sec = mysql_query($query_aco_sec, $conn) or die(mysql_error());
	while($row_aco_sec = mysql_fetch_assoc($result_aco_sec)) {
		echo '<tbody onmouseover="display(\'edit_acl_'.$row_aco_sec['value'].'\');" onmouseout="hidediv(\'edit_acl_'.$row_aco_sec['value'].'\');">';
		echo '<tr class="subject">';
		echo '<td colspan="2">'.$lang->display($row_aco_sec['name']).'</td>';
		echo '</tr>';
		$query_aco = sprintf("SELECT *
							FROM aco
							WHERE hidden = 0
							AND section_value = '%s'
							ORDER BY order_value ASC",
							$row_aco_sec['value']);
		$result_aco = mysql_query($query_aco, $conn) or die(mysql_error());
		$counter=1;
		echo '<tr>';
		while($row_aco = mysql_fetch_assoc($result_aco)) {
			if($row_aco_sec['value']=="system" && $row_aco['value']=="superadmin" && !$issuperadmin) continue;
			echo '<td width="50%">';
			echo '<input type="checkbox" name="aco[]" id="aco[]" value="'.$row_aco['id'].'"';
			if($acl->acl_check($row_aco_sec['value'],$row_aco['value'],$company_id,$user_id)) echo ' checked="checked"';
			echo ' /> '.$lang->display($row_aco['name']);
			echo '</td>';
			if($counter%2==0) {
				echo '</tr><tr>';
			}
			$counter++;
		}
		echo '</tr>';
		echo '</tbody>';
	}
}

function BuildUploadOption($company_id, $multiple=true) {
	global $lang;
	echo '<div class="arrdwn" id="localArrow" onclick="ChangeArrow(\'localArrow\');ResetArrow(\'ftpArrow\');hidediv(\'ftp\');openandclose(\'localfiles\');">';
	echo $lang->display('From Local Computer');
	echo '</div>';
	echo '<div id="localfiles">';
	echo '<div id="filelist">';
	echo '<input type="file" class="input" onfocus="this.className=\'inputOn\'" onblur="this.className=\'input\'" name="artworkFile[]" id="artworkFile[]" size="30" />';
	echo '</div>';
	if($multiple) {
		echo '<div id="addmore">';
		echo '<a href="javascript:void(0);" onclick="insertRow(\'artworkFile[]\');">';
		echo '<img id="addNewArtwork" name="addNewArtwork" src="'.IMG_PATH.'ico_createitems.gif" title="'.$lang->display('Add New Artwork').'" />';
		echo $lang->display('Add New Artwork');
		echo '</a>';
		echo '</div>';
	}
	echo '</div>';
	echo '<div class="arrrgt" id="ftpArrow" onclick="ChangeArrow(\'ftpArrow\');ResetArrow(\'localArrow\');hidediv(\'localfiles\');openandclose(\'ftp\');ResetDiv(\'ftp\');DoAjax(\'companyID='.$company_id.'\',\'ftp\',\'modules/mod_ftp.php\');">';
	echo $lang->display('From System File Manager');
	echo '</div>';
	echo '<div id="ftp" style="display:none;"></div>';
}

function BuildPageJumper($artworkID, $page, $taskID=0) {
	global $conn, $lang, $layout;
	$id = empty($taskID) ? $artworkID : $taskID;
	$query = sprintf("SELECT PreviewFile, Page
					FROM pages
					WHERE ArtworkID = %d
					AND Master = 0
					ORDER BY Page ASC",
					$artworkID);
	$result = mysql_query($query, $conn) or die(mysql_error());
	$pages = mysql_num_rows($result);
	echo $lang->display('Page').' <b>'.$page.'</b> / '.$pages.'<span class="span">|</span>'.$lang->display('Go to Page').' ';
	echo '<select
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			id="page_jumper"
			name="page_jumper"
			title="'.$lang->display('Go to Page').'"
			onchange="goToURL(\'parent\',\'index.php?layout='.$layout.'&id='.$id.'&page=\'+this.value);">';
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['Page'].'"';
		if($page == $row['Page']) echo ' selected="selected"';
		echo '>'.$row['Page'].'</option>';
	}
	echo '</select>';
}

function BuildPageList($artwork_id, $page_no, $page_id) {
	global $conn, $lang, $DB;
	echo '<option value="0">- '.$lang->display('Select Page').' -</option>';
	$pages = $DB->GetAllPages($artwork_id,$page_no);
	if($pages !== false) {
		$query = sprintf("SELECT uID, Page, PageRef, Master
						FROM pages
						WHERE uID IN (%s)
						ORDER BY Page ASC, PageRef ASC",
						mysql_real_escape_string($pages));
		$result = mysql_query($query, $conn) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			echo '<option value="'.$row['uID'].'"';
			if(!empty($page_id) && $row['uID'] == $page_id) echo 'selected="selected"';
			echo '>';
			echo $row['Master'] ? $lang->display('Master Page').' '.$row['PageRef'] : $lang->display('Page').' '.$row['Page'];
			echo '</option>';
		}
	}
}

function BuildLayerList($artwork_id, $layer_id) {
	global $conn, $lang;
	echo '<option value="0">- '.$lang->display('Select Layer').' -</option>';
	$query = sprintf("SELECT id, ref, name, colour
					FROM artwork_layers
					WHERE artwork_id = %d
					ORDER BY ref ASC",
					$artwork_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option style="background-color:#'.$row['colour'].'" value="'.$row['id'].'"';
		if(!empty($layer_id) && $row['id'] == $layer_id) echo 'selected="selected"';
		echo '>'.$row['name'].'</option>';
	}
}

function BuildBoxTypeList($box_type='TEXT') {
	global $conn, $lang;
	echo '<option value="0">- '.$lang->display('Select Box Type').' -</option>';
	$types = array(
		'TEXT' => 'Text',
		'PICT' => 'Image',
		'NONE' => 'N/S',
	);
	foreach($types as $k=>$v) {
		echo '<option value="'.$k.'"';
		if(!empty($box_type) && $k == $box_type) echo 'selected="selected"';
		echo '>'.$lang->display($v).'</option>';
	}
}

function BuildStoryGroupList($artwork_id,$story_group_id=0) {
	global $conn, $lang;
	echo '<option value="0">- '.$lang->display('None').' -</option>';
        
        $query = sprintf('SELECT `artwork_story_groups`.`id`, `artwork_story_groups`.`name`
            FROM `artwork_story_groups`
            WHERE `artwork_id`=%d',$artwork_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
        while($row = mysql_fetch_assoc($result)){
            $sel = ($story_group_id==$row['id'])?' selected=':'';
            printf('<option value="%d"%s>%s</option>',$row['id'],$sel,$row['name']);
        }
}

function BuildPageViewer($artwork_id, $page=1) {
	global $conn, $lang;
	$query = sprintf("SELECT pages.PreviewFile, artworks.pageCount
					FROM pages
					LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
					WHERE pages.ArtworkID = %d
					AND pages.Page = %d
					LIMIT 1",
					$artwork_id,
					$page);
	$result = mysql_query($query, $conn) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	$pages = $row['pageCount'];
	$preview = PREVIEW_DIR.$row['PreviewFile'];
	echo '<div id="pages" class="pages" align="center">';
	echo '<div class="artwork" id="artwork">';
	if((!empty($row['PreviewFile'])) && file_exists(ROOT.$preview)) {
		echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=amend&id='.$artwork_id.'&page='.$page.'\');" title="'.$lang->display('Page').' '.$page.'"><img src="'.$preview.'?'.filemtime(ROOT.$preview).'" /></a>';
	} else {
		echo '<img src="'.IMG_PATH.'img_missing.png" />';
	}
	echo '</div>';
	echo '<div id="pageturner">';
	echo '<div class="left">';
	if($page>1) {
		$previous = $page - 1;
		echo '<a href="javascript:void(0);" onclick="ResetDiv(\'intro\');DoAjax(\'artwork_id='.$artwork_id.'&page='.$previous.'\',\'pages\',\'modules/mod_art_page.php\');"><img src="'.IMG_PATH.'prev.gif" title="'.$lang->display('Previous Page').'" /></a>';
	} else {
		echo '<img src="'.IMG_PATH.'prev_off.gif">';
	}
	echo '</div>';
	echo '<div class="right">';
	if($page<$pages) {
		$next = $page + 1;
		echo '<a href="javascript:void(0);" onclick="ResetDiv(\'intro\');DoAjax(\'artwork_id='.$artwork_id.'&page='.$next.'\',\'pages\',\'modules/mod_art_page.php\');"><img src="'.IMG_PATH.'next.gif" title="'.$lang->display('Next Page').'" /></a>';
	} else {
		echo '<img src="'.IMG_PATH.'next_off.gif">';
	}
	echo '</div>';
	echo '<div class="intro" id="intro"><b>'.$page.' / '.$pages.'</b></div>';
	echo '<div class="clear"></div>';
	echo '</div>';
	echo '</div>';
	return true;
}

function BuildZoomIcons($artwork_id, $page, $task_id=0) {
	global $conn, $lang, $layout;
	$id = empty($task_id) ? $artwork_id : $task_id;
	$query = sprintf("SELECT pages.PreviewFile, pages.PageScale, artworks.artworkName
					FROM pages
					LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
					WHERE pages.ArtworkID = %d
					AND pages.Page = %d
					LIMIT 1",
					$artwork_id,
					$page);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(!mysql_num_rows($result)) return false;
	$row = mysql_fetch_assoc($result);
	$artworkName = $row['artworkName'];
	$previewFile = $row['PreviewFile'];
	$PageScale = $row['PageScale'];
	$max_zoom = round(100*$PageScale);
	if(!empty($task_id) && file_exists(ROOT.POSTVIEW_DIR.BareFilename($previewFile)."-".$task_id.".jpg")) {
		$thumbnail = POSTVIEW_DIR.BareFilename($previewFile)."-".$task_id.".jpg";
	} else {
		if(file_exists(ROOT.POSTVIEW_DIR.$previewFile)) {
			$thumbnail = POSTVIEW_DIR.$previewFile;
		} else {
			$thumbnail = PREVIEW_DIR.$previewFile;
		}
	}
	if(!empty($previewFile) && file_exists($thumbnail)) {
		list($imgWidth, $imgHeight) = @getimagesize($thumbnail);
		$bestfit = floor(800*$max_zoom/$imgWidth);
		$fit = $bestfit - ($bestfit%ZOOM_SCALE);
	} else {
		$fit = 100;
	}
	if(isset($_GET['zoom'])) $_SESSION['zoom'] = $_GET['zoom'];
	$zoom = isset($_GET['zoom']) ? $_GET['zoom'] : ( isset($_SESSION['zoom']) ? $_SESSION['zoom'] : $fit );
	if(isset($_GET['toggle'])) $_SESSION['toggle'] = $_GET['toggle'];
	$toggle = isset($_GET['toggle']) ? $_GET['toggle'] : ( isset($_SESSION['toggle']) ? $_SESSION['toggle'] : DEFAULT_EDIT_MARKS_DISPLAY );
	$toggler = $toggle==0 ? 1 : 0;
	$scale = $zoom/$max_zoom;
	$resize = $imgWidth*$scale;
	$zoomin = (($zoom+ZOOM_SCALE)>=$max_zoom) ? $max_zoom : ($zoom+ZOOM_SCALE);
	$zoomout = (($zoom-ZOOM_SCALE)<=0) ? ZOOM_SCALE : ($zoom-ZOOM_SCALE);
	
	echo '<div class="zoom">';
	echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&id='.$id.'&page='.$page.'&zoom='.$fit.'\');"><img src="'.IMG_PATH.'btn_best_fit.png" title="'.$lang->display('Best Fit').'"></a>';
	echo '</div>';
	echo '<div class="zoom">';
	echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&id='.$id.'&page='.$page.'&zoom='.$max_zoom.'\');"><img src="'.IMG_PATH.'btn_actual_size.png" title="'.$lang->display('Actual Size').'"></a>';
	echo '</div>';
	echo '<div class="zoom">';
	echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&id='.$id.'&page='.$page.'&zoom='.$zoomin.'\');"><img src="'.IMG_PATH.'btn_zoom_in.png" title="'.$lang->display('Zoom In').'"></a>';
	echo '</div>';
	echo '<div class="zoom">';
	echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&id='.$id.'&page='.$page.'&zoom='.$zoomout.'\');"><img src="'.IMG_PATH.'btn_zoom_out.png" title="'.$lang->display('Zoom Out').'"></a>';
	echo '</div>';
	echo '<div class="zoom">';
	echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&id='.$id.'&page='.$page.'&toggle='.$toggler.'\');"><img src="'.IMG_PATH.'btn_img';
	if(!$toggle) echo '_edit';
	echo '.png" title="'.$lang->display('Toggle Edit Marks').'"></a>';
	echo '</div>';
	return array('scale'=>$scale,'resize'=>$resize,'PageScale'=>$PageScale, 'toggle'=>$toggle);
}

function BuildImgOption($content) {
	global $conn, $lang, $DB;
	echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"3\" border=\"0\">";
	echo "<tr>";
	echo "<td>";
	/*
	echo "<div
			class=\"arrrgt\"
			id=\"libArrow\"
			onclick=\"
			ChangeArrow('libArrow');
			ResetArrow('localArrow','ftpArrow');
			hidediv('local_file','local_ftp');
			openandclose('img_lib');\">".$lang->display('Image Library')."</div>";
	echo "<div id=\"img_lib\" class=\"thumbnails\" style=\"display:none;\">";
	$query = sprintf("SELECT images.content
					FROM images
					LEFT JOIN users ON users.userID = images.user_id
					WHERE images.type_id = %d
					AND users.companyID = %d",
					IMG_LIBRARY,
					$_SESSION['companyID']);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		$img_content = $row['content'];
		//copy to tmp folder for preview purpose
		$tmp_file = ROOT.TMP_DIR.basename($img_content);
		if(!file_exists($tmp_file)) {
			copy($img_content, $tmp_file);
		}
		echo "<div id=\"$img_content\" class=\"";
		if($img_content==$content) {
			echo "thumbnailOn";
		} else {
			echo "thumbnailOff";
		}
		echo "\" onclick=\"resetClassName('div','thumbnailOn','thumbnailOff');this.className='thumbnailOn';setValue('img_content','".addslashes($img_content)."');\" title=\"".basename($img_content)."\"><div class=\"img\"><img src=\"";
		if(ValidateImage($img_content)) {
			echo TMP_DIR.basename($img_content);
		} else {
			echo IMG_PATH."header/ico_file.png";
		}
		echo "\" /></div>";
		echo "<div class=\"txt\">".basename($img_content)."</div>";
		echo "</div>";
	}
	echo "<div class=\"clear\"></div>";
	echo "</div>";
	 * 
	 */
	echo "<div
			class=\"arrrgt\"
			id=\"localArrow\"
			onclick=\"
			ChangeArrow('localArrow');
			ResetArrow('libArrow','ftpArrow');
			hidediv('img_lib','local_ftp');
			openandclose('local_file');\">".$lang->display('From Local Computer')."</div>";
	echo "<div id=\"local_file\" style=\"display:none;\">";
	echo "<input
			type=\"file\"
			class=\"btnOff\"
			onmousemove=\"this.className='btnOn'\"
			onmouseout=\"this.className='btnOff'\"
			id=\"img_file\"
			name=\"img_file\" />";
	echo "</div>";
	$system_name = $DB->get_system_name($_SESSION['companyID']);
	$local_path_to_ftp = ROOT.FTP_DIR.$system_name;
	if(stripos($content, $local_path_to_ftp) === false) {
		$local_ftp_dir = '/';
		$filename = '';
	} else {
		$local_file_path = str_ireplace($local_path_to_ftp, "", $content);
		$elements = explode('/', $local_file_path);
		$filename = array_pop($elements);
		$local_ftp_dir = implode('/',$elements);
		$local_ftp_dir = trim($local_ftp_dir,'/');
		if(!empty($local_ftp_dir)) {
			$local_ftp_dir = '/'.$local_ftp_dir.'/';
		} else {
			$local_ftp_dir = '/';
		}
	}
	echo "<div
			class=\"arrrgt\"
			id=\"ftpArrow\"
			onclick=\"
			ChangeArrow('ftpArrow');
			ResetArrow('libArrow','localArrow');
			hidediv('img_lib','local_file');
			openandclose('local_ftp');
			ResetDiv('local_ftp');
			DoAjax('companyID=".$_SESSION['companyID']."&dir=".$local_ftp_dir."&file=".$filename."','local_ftp','modules/mod_ftp_dir.php');\">".$lang->display('From System File Manager')."</div>";
	echo "<div id=\"local_ftp\" style=\"display:none;\"><img src=\"".IMG_PATH."loading.gif\" /></div>";
	echo "<input type=\"hidden\" id=\"img_content\" name=\"img_content\" value=\"$content\" />";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
}

function BuildEngineList($engine_id) {
	global $conn, $lang;
	$query = sprintf("SELECT id, name
					FROM service_engines
					WHERE ext IN ('QXP','INDD')
					ORDER BY name ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['id'].'"';
		if(!empty($engine_id) && $row['id'] == $engine_id) echo 'selected="selected"';
		echo '>'.$row['name'].'</option>';
	}
}

function BuildYearList($year) {
	global $lang;
	$year = (int)$year;
	$year_now = (int)date("Y");
	for($y=$year_now;$y>=$year_now-10;$y--) {
		echo '<option value="'.$y.'"';
		if(!empty($year) && $y == $year) echo 'selected="selected"';
		echo '>'.$y.'</option>';
	}
}

function BuildMonthList($month) {
	global $lang;
	$month = (int)$month;
	for($m=12;$m>0;$m--) {
		echo '<option value="'.$m.'"';
		if(!empty($month) && $m == $month) echo 'selected="selected"';
		echo '>'.$m.'</option>';
	}
}

function BuildDayList($day) {
	global $lang;
	$day = (int)$day;
	for($d=31;$d>0;$d--) {
		echo '<option value="'.$d.'"';
		if(!empty($day) && $d == $day) echo 'selected="selected"';
		echo '>'.$d.'</option>';
	}
}

function BuildUserGroupList($ug_id=0, $is_super_admin=false) {
	global $conn,$lang;
	echo '<option value="">- '.$lang->display('Please Select').' -</option>';
	$sub = $is_super_admin ? "" : "WHERE value <> 'superadmin'";
	$query = sprintf("SELECT id, name
					FROM aro_groups
					%s
					ORDER BY id ASC",
					$sub);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['id'].'"';
		if(!empty($ug_id) && $row['id'] == $ug_id) echo 'selected="selected"';
		echo '>'.$lang->display($row['name']).'</option>';
	}
}

function BuildCompanyList($company_id, $is_super_admin=false, $filter_company_id=0) {
	global $conn;
	$company_id=(int)$company_id;
	$sub = $is_super_admin ? "" : "WHERE companyID = $company_id OR parentCompanyID = $company_id";
	$query = sprintf("SELECT companyID, companyName
					FROM companies
					%s
					ORDER BY companyName ASC",
					$sub);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['companyID'].'"';
		if(!empty($filter_company_id)){
		  if($row['companyID'] == $filter_company_id){
		    echo 'selected="selected"';
		  }
		}else{
		  if(!empty($company_id) && $row['companyID'] == $company_id) echo 'selected="selected"';
		}
		echo '>'.$row['companyName'].'</option>';
	}
}

function BuildFTPHostList($company_id) {
	global $conn, $DB;
	$query = sprintf("SELECT id, ftp_host, ftp_memo
					FROM ftps
					WHERE company_id IN (%s)
					ORDER BY ftp_host ASC",
					mysql_real_escape_string($DB->get_company_groups($company_id)));
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['id'].'">'.$row['ftp_host'];
		if(!empty($row['ftp_memo'])) echo " ({$row['ftp_memo']})";
		echo '</option>';
	}
}

function BuildImportStatusList($imported) {
	global $lang;
	$array = array(
		0 => "Non-imported",
		1 => "Imported",
		2 => "All"
	);
	foreach($array as $k=>$v) {
		echo '<option value="'.$k.'"';
		if(!empty($imported) && $k == $imported) echo 'selected="selected"';
		echo '>'.$lang->display($v).'</option>';
	}
}

function BuildFontSubList($font_id=0) {
	global $conn, $lang;
	echo '<option value="0">- '.$lang->display('Default Substitute Font').' -</option>';
	$query = sprintf("SELECT fonts.id, fonts.family, fonts.name,
					service_engines.name AS service
					FROM fonts
					LEFT JOIN service_engines ON fonts.engine_id = service_engines.id
					WHERE installed = 1
					ORDER BY service_engines.name ASC,
					name ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['id'].'"';
		if(!empty($font_id) && $row['id'] == $font_id) echo ' selected="selected"';
		echo '>['.$row['service'].'] ';
		echo '('.$row['family'].') '.$row['name'];
		echo '</option>';
	}
}

function BuildCampaignProgressBar($campaign_id) {
	global $conn, $lang;
	$progress_campaign = 0;
	$query_artwork = sprintf("SELECT artworkID
							FROM artworks
							WHERE campaignID = %d
							AND live = 1
							ORDER BY artworkID ASC",$campaign_id);
	$result_artwork = mysql_query($query_artwork,$conn) or die(mysql_error());
	$found_artwork = mysql_num_rows($result_artwork);
	if(!empty($found_artwork)) {
		$progress_artwork = 100 / $found_artwork;
		while($row_artwork = mysql_fetch_assoc($result_artwork)) {
			$query_task = sprintf("SELECT userWords, tmWords, missingWords
								FROM tasks
								WHERE artworkID = %d
								ORDER BY taskID ASC",
								$row_artwork['artworkID']);
			$result_task = mysql_query($query_task,$conn) or die(mysql_error());
			$found_task = mysql_num_rows($result_task);
			if(!empty($found_task)) {
				$progress_task = $progress_artwork / $found_task;
				while($row_task = mysql_fetch_assoc($result_task)) {
					$progress = 0;
					$total_words = $row_task['userWords']+$row_task['tmWords']+$row_task['missingWords'];
					if($total_words > 0) {
						$progress = ($row_task['userWords']+$row_task['tmWords']) / $total_words;
					}
					$progress_campaign += $progress_task * $progress;
				}
			}
		}
	}
	$progress_campaign = round($progress_campaign);
	echo '<div class="progress">';
	echo 	'<div class="progressBar" title="'.$progress_campaign.'% '.$lang->display('Complete').'">';
	echo 		'<div class="left"><img src="'.IMG_PATH.'prounit.png" width="'.$progress_campaign.'" height="10" /></div>';
	echo 	'</div>';
	echo 	'<div class="percentage"><b>'.$progress_campaign.'%</b></div>';
	echo '</div>';
}

function BuildArtworkProgressBar($artwork_id) {
	global $conn, $lang;
	$progress_artwork = 0;
	$query_task = sprintf("SELECT userWords, tmWords, missingWords
						FROM tasks
						WHERE artworkID = %d
						ORDER BY taskID ASC",
						$artwork_id);
	$result_task = mysql_query($query_task,$conn) or die(mysql_error());
	$found_task = mysql_num_rows($result_task);
	if(!empty($found_task)) {
		$progress_task = 100 / $found_task;
		while($row_task = mysql_fetch_assoc($result_task)) {
			$progress = 0;
			$total_words = $row_task['userWords']+$row_task['tmWords']+$row_task['missingWords'];
			if($total_words > 0) {
				$progress = ($row_task['userWords']+$row_task['tmWords']) / $total_words;
			}
			$progress_artwork += $progress_task * $progress;
		}
	}
	$progress_artwork = round($progress_artwork);
	echo '<div class="progress">';
	echo 	'<div class="progressBar" title="'.$progress_artwork.'% '.$lang->display('Complete').'">';
	echo 		'<div class="left"><img src="'.IMG_PATH.'prounit.png" width="'.$progress_artwork.'" height="10" /></div>';
	echo 	'</div>';
	echo 	'<div class="percentage"><b>'.$progress_artwork.'%</b></div>';
	echo '</div>';
}

function BuildTaskProgressBar($task_id) {
	global $conn, $lang;
	$query = sprintf("SELECT userWords, tmWords, missingWords
					FROM tasks
					WHERE taskID = %d
					LIMIT 1",
					$task_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(!mysql_num_rows($result)) return false;
	$row = mysql_fetch_assoc($result);
	$totalWords = $row['userWords'] + $row['tmWords'] + $row['missingWords'];
	$totalDone = $row['userWords'] + $row['tmWords'];
	$uProgress = 0;
	$tmProgress = 0;
	$Progress = 0;
	if($totalWords > 0) {
		$uProgress = round($row['userWords']/$totalWords*100);
		$tmProgress = round($row['tmWords']/$totalWords*100);
		$Progress = $uProgress + $tmProgress;
	}
	echo '<div class="progress">';
	echo 	'<div>'.$lang->display('Word Count').': <b>'.$totalDone.'</b> / '.$totalWords.' ('.$row['missingWords'].')</div>';
	echo 	'<div class="progressBar">';
	echo 		'<div class="left"><img src="'.IMG_PATH.'prounit.png" width="'.$uProgress.'" height="10" title="'.$lang->display('User').': '.$row['userWords'].' ('.$uProgress.'%)" /></div>';
	echo 		'<div class="left"><img src="'.IMG_PATH.'tmunit.png" width="'.$tmProgress.'" height="10" title="'.$lang->display('Translation Memory').': '.$row['tmWords'].' ('.$tmProgress.'%)" /></div>';
	echo 	'</div>';
	echo 	'<div class="percentage"><b>'.$Progress.'%</b></div>';
	echo '</div>';
}

function BuildAdvancedTaskProgressBar($task_id, $PL=0) {
	global $conn, $lang;
	$query = sprintf("SELECT userWords, tmWords, missingWords
					FROM tasks
					WHERE taskID = %d
					LIMIT 1",
					$task_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(!mysql_num_rows($result)) return false;
	$row = mysql_fetch_assoc($result);
	$totalWords = $row['userWords'] + $row['tmWords'] + $row['missingWords'];
	$totalDone = $row['userWords'] + $row['tmWords'];
	$uProgress = 0;
	$tmProgress = 0;
	$Progress = 0;
	if($totalWords > 0) {
		$uProgress = round($row['userWords']/$totalWords*100);
		$tmProgress = round($row['tmWords']/$totalWords*100);
		$Progress = $uProgress + $tmProgress;
	}
	echo '<div class="progress">';
	echo	'<div class="percentage"><b>'.$Progress.'%</b></div>';
	echo	'<a href="javascript:void();" onclick="Popup(\'helper\',\'blur\');DoAjax(\'id='.$task_id.'&pl='.$PL.'\',\'window\',\'modules/mod_task_check.php\');">';
	echo	'<div class="progressBar">';
	echo		'<div class="left"><img src="'.IMG_PATH.'prounit.png" width="'.$uProgress.'" height="10" title="'.$lang->display('User').': '.$row['userWords'].' ('.$uProgress.'%)" /></div>';
	echo		'<div class="left"><img src="'.IMG_PATH.'tmunit.png" width="'.$tmProgress.'" height="10" title="'.$lang->display('Translation Memory').': '.$row['tmWords'].' ('.$tmProgress.'%)" /></div>';
	echo	'</div>';
	echo	'</a>';
	echo	'<div class="wordcount">'.$lang->display('Word Count').': <b>'.$totalDone.'</b> / '.$totalWords.' ('.$row['missingWords'].')</div>';
	echo '</div>';
}

function BuildTaskStatusIcon($status_id) {
	global $conn, $lang;
	$query = sprintf("SELECT statusInfo
					FROM status
					WHERE statusID = %d
					LIMIT 1",
					$status_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(!mysql_num_rows($result)) return false;
	$row = mysql_fetch_assoc($result);
	switch($status_id) {
		case 6:
			echo '<img src="'.IMG_PATH.'status_bit.gif"';
			break;
		case 7:
			echo '<img src="'.IMG_PATH.'status_empty.gif"';
			break;
		case 8:
			echo '<img src="'.IMG_PATH.'status_half.gif"';
			break;
		case 9:
			echo '<img src="'.IMG_PATH.'status_3q.gif"';
			break;
		case 10:
			echo '<img src="'.IMG_PATH.'status_full.gif"';
			break;
		default:
			echo '<img src="'.IMG_PATH.'status_flash.gif"';
	}
	echo ' title="'.$lang->display($row['statusInfo']).'" /> ';
}

function BuildTemplateList($template_name) {
	#$templates = array("default", "green", "grey", "scarlet", "grey2");
	//Build Own list
	$path = dirname(__FILE__).'/../templates/';
	if ($handle = opendir($path)) {
		while (($entry = readdir($handle))!==false) {
			if ($entry != "." && $entry != "..") {
				if(
					(file_exists($path.$entry.'/css/') && is_dir($path.$entry.'/css/')) && 
					(file_exists($path.$entry.'/css/') && is_file($path.$entry.'/css/default.css')) && 
					(file_exists($path.$entry.'/images/') && is_dir($path.$entry.'/images/'))
				)
				$templates[] = $entry;
			}
		}
		closedir($handle);
	}

	foreach($templates as $template) {
		echo '<option value="'.$template.'"';
		if($template == $template_name) echo 'selected="selected"';
		echo '>'.$template.'</option>';
	}
}

function BuildCurrencyList($currency_id=0) {
	global $conn;
	$query = sprintf("SELECT *
					FROM currencies
					ORDER BY currencyAb ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['currencyID'].'"';
		if(!empty($currency_id) && $row['currencyID'] == $currency_id) echo 'selected="selected"';
		echo '>'.$row['currencyAb'].'</option>';
	}
}

function BuildPrivacyList($preference=1) {
	global $lang;
	$options = array(0=>"Private",1=>"Public");
	foreach($options as $k=>$v) {
		echo '<option value="'.$k.'"';
		if(!empty($preference) && $k == $preference) echo 'selected="selected"';
		echo '>'.$lang->display($v).'</option>';
	}
}

function BuildSPList($service_package_id=0) {
	global $conn;
	$query = sprintf("SELECT id, name
					FROM service_packages
					ORDER BY name ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['id'].'"';
		if(!empty($service_package_id) && $row['id'] == $service_package_id) echo 'selected="selected"';
		echo '>'.$row['name'].'</option>';
	}
}

function BuildParentCompanyList($company_id=0, $parent_company_id=0) {
	global $conn, $lang;
	echo '<option value="0">- '.$lang->display('Please Select if Applicable').' -</option>';
	$query = sprintf("SELECT companyID, companyName
					FROM companies
					WHERE companyID <> %d
					ORDER BY companyName ASC",
					$company_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while ($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['companyID'].'"';
		if(!empty($parent_company_id) && $row['companyID'] == $parent_company_id) echo 'selected="selected"';
		echo '>'.$row['companyName'].'</option>';
	}
}

function BuildFileTypeList($service_package_id, $transaction_id, $type_id=0, $service_engine_id=0, $title="") {
	global $conn;
	echo '<option value="0">- '.$title.' -</option>';
	$query = sprintf("SELECT service_engines.id, service_engines.name, service_engines.ext,
					service_transaction_process.allow
					FROM service_package_items
					LEFT JOIN service_transaction_process ON service_transaction_process.id = service_package_items.service_tID
					LEFT JOIN service_engines ON service_engines.id = service_transaction_process.serviceID
					WHERE service_package_items.packageID = %d
					AND service_transaction_process.transactionID = %d
					AND service_transaction_process.type_id = %d
					ORDER BY service_engines.name ASC",
					$service_package_id,
					$transaction_id,
					$type_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['id'].'"';
		if(!empty($service_engine_id) && $row['id']==$service_engine_id) echo 'selected="selected"';
		echo '>'.$row['name'].' ('.$row['ext'].')</option>';
	}
}

function BuildHelperDiv($title) {
	global $lang;
	$title = DisplayString($title);
	echo '<div id="helper" style="display:none;">';
	echo 	'<div id="blur" class="blur" align="center" onclick="fadeOut(\'helper\');"></div>';
	echo 	'<div id="root" class="root" style="left:300px;top:100px;">';
	echo 		'<div id="handle" class="handle">';
	echo 			'<div class="left">';
	echo 				'<b>'.SYSTEM_NAME.' - '.$title.'</b>';
	echo 			'</div>';
	echo 			'<div class="right">';
	echo 				'<a href="javascript:void(0);" onclick="BlurDiv(\'layerform\',\'colour\');fadeOut(\'helper\');">';
	echo 					'<img src="'.IMG_PATH.'ico_close.png" title="'.$lang->display('Close').'">';
	echo 				'</a>';
	echo 			'</div>';
	echo 			'<div class="clear"></div>';
	echo 		'</div>';
	echo 		'<div id="window" class="window">';
	echo 			'<div class="loading"><img src="images/loading.gif"></div>';
	echo 		'</div>';
	echo 	'</div>';
	echo '</div>';
}

function BuildReceiverList($userID=0) {
	global $conn, $lang;
	echo '<option value="">- '.$lang->display('Please Select Contact').' -</option>';
	$query = sprintf("SELECT users.userID, users.forename, users.surname,
						companies.companyName
						FROM users
						LEFT JOIN companies ON users.companyID = companies.companyID
						WHERE users.userID <> %d
						ORDER BY forename ASC",
						$_SESSION['userID']);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['userID'].'"';
		if(!empty($userID) && $row['userID']==$userID) echo 'selected="selected"';
		echo '>'.$row['forename'].' '.$row['surname'].', '.$row['companyName'].'</option>';
	}
}

function BuildPageCols($artworkID, $page, $taskID=0, $istemplate=0) {
	global $lang,$layout;
	echo '<div class="pageToolbarL">';
	echo '<div id="pageTool" class="pageToolOff"><a href="javascript:void(0);" onclick="if(document.getElementById(\'pageColL\').style.display==\'none\') {SetClassName(\'pageTool\',\'pageToolOn\');display(\'pageColL\');ResetDiv(\'pageColL\');DoAjax(\'layout='.$layout.'&artworkID='.$artworkID.'&page='.$page.'&taskID='.$taskID.'&istemplate='.$istemplate.'&show_pages=1\',\'pageColL\',\'modules/mod_page_previews.php\');} else {SetClassName(\'pageTool\',\'pageToolOff\');DoAjax(\'show_pages=0\',\'pageColL\',\'modules/mod_page_previews.php\');hidediv(\'pageColL\');}"><img src="'.IMG_PATH.'toolbar/ico_pages.png" title="'.$lang->display('Pages').'" /></a></div>';
	echo '</div>';
	echo '<div id="pageColL" class="pageColL" style="display:none;"></div>';
	echo '<div class="pageToolbarR">';
	if(empty($taskID) && isset($_SESSION['userID'])) echo '<div id="guestTool" class="pageToolOff"><a href="javascript:void(0);" onclick="SetClassName(\'commentTool\',\'pageToolOff\');SetClassName(\'amendTool\',\'pageToolOff\');SetClassName(\'guestTool\',\'pageToolOn\');display(\'pageColR\');ResetDiv(\'pageColR\');DoAjax(\'artworkID='.$artworkID.'\',\'pageColR\',\'modules/mod_art_guests.php\');"><img src="'.IMG_PATH.'toolbar/ico_guest.png" title="'.$lang->display('Guests').'" /></a></div>';
	echo '<div id="commentTool" class="pageToolOff"><a href="javascript:void(0);" onclick="SetClassName(\'guestTool\',\'pageToolOff\');SetClassName(\'amendTool\',\'pageToolOff\');SetClassName(\'commentTool\',\'pageToolOn\');display(\'pageColR\');ResetDiv(\'pageColR\');DoAjax(\'artworkID='.$artworkID.'&page='.$page.'&taskID='.$taskID.'\',\'pageColR\',\'modules/mod_page_comments.php\');"><img src="'.IMG_PATH.'toolbar/ico_comment.png" title="'.$lang->display('Comments').'" /></a></div>';
	echo '<div id="amendTool" class="pageToolOff"><a href="javascript:void(0);" onclick="SetClassName(\'guestTool\',\'pageToolOff\');SetClassName(\'commentTool\',\'pageToolOff\');SetClassName(\'amendTool\',\'pageToolOn\');display(\'pageColR\');ResetDiv(\'pageColR\');DoAjax(\'layout='.$layout.'&artworkID='.$artworkID.'&page='.$page.'&taskID='.$taskID.'\',\'pageColR\',\'modules/mod_page_amended.php\');"><img src="'.IMG_PATH.'toolbar/ico_amend.png" title="'.$lang->display('Amended').'" /></a></div>';
	echo '</div>';
	echo '<div id="pageColR" class="pageColR" style="display:none;"></div>';
}

function BuildImportMapList($import_map_id=0) {
	global $conn, $lang;
	echo '<option value="">- '.$lang->display('Please Select').' -</option>';
	echo '<option value=""></option>';
	$query = sprintf("SELECT imports.id, imports.name, imports.time,
						users.username
						FROM imports
						LEFT JOIN users ON imports.user_id = users.userID
						WHERE users.companyID = %d
						ORDER BY imports.time DESC",
						$_SESSION['companyID']);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		$rows_query = sprintf("SELECT id
							FROM import_rows
							WHERE import_id = %d",
							$row['id']);
		$rows_result = mysql_query($rows_query, $conn) or die(mysql_error());
		$rows_found = mysql_num_rows($rows_result);
		echo '<option value="">+ '.date(FORMAT_DATE,strtotime($row['time'])).'_'.$row['name'].'_'.$row['username'].' ('.$rows_found.' rows)</option>';
		$map_query = sprintf("SELECT id, label
							FROM import_map
							WHERE import_id = %d
							ORDER BY id ASC",
							$row['id']);
		$map_result = mysql_query($map_query, $conn) or die(mysql_error());
		while($map_row = mysql_fetch_assoc($map_result)) {
			echo '<option value="'.$map_row['id'].'"';
			if(!empty($import_map_id) && $import_map_id==$map_row['id']) echo 'selected="selected"';
			echo '>&nbsp;|- '.$map_row['label'].'</option>';
		}
		echo '<option value=""></option>';
	}
}

function BuildParserType($parse_type=1) {
	global $lang;
	echo '<option value="'.PARSE_BY_PARAGRAPH.'"';
	if($parse_type==PARSE_BY_PARAGRAPH) echo 'selected="selected"';
	echo '>'.$lang->display('Paragraph').'</option>';
	echo '<option value="'.PARSE_BY_SENTENCE.'"';
	if($parse_type==PARSE_BY_SENTENCE) echo 'selected="selected"';
	echo '>'.$lang->display('Sentence').'</option>';
}

function BuildViewList($view) {
	global $lang;
	$options = array("thumbnails"=>"Thumbnails","list"=>"List");
	foreach($options as $k => $v) {
		echo '<option value="'.$k.'"';
		if($k==$view) echo 'selected="selected"';
		echo '>'.$lang->display($v).'</option>';
	}
}

function BuildBrandList($brandID=0, $company_id=0) {
	global $conn, $lang, $layout;
	if(empty($company_id)) $company_id = $_SESSION['companyID'];
	echo '<option value="">- '.$lang->display('Select Brand').' -</option>';
	$query = sprintf("SELECT * FROM brands
					WHERE companyID = %d AND parentBrandID = 0
					ORDER BY brandName ASC",
					$company_id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['brandID'].'"';
		if($brandID==$row['brandID']) echo 'selected="selected"';
		echo '>'.$row['brandName'].'</option>';

		$subQuery = sprintf("SELECT * FROM brands
							WHERE parentBrandID <> 0 AND parentBrandID = %d
							ORDER BY brandName ASC", $row['brandID']);
		$subResult = mysql_query($subQuery, $conn) or die(mysql_error());
		$subFound = mysql_num_rows($subResult);

		if($subFound) while($subrow = mysql_fetch_assoc($subResult)) {
			echo '<option value="'.$subrow['brandID'].'"';
			if($brandID==$subrow['brandID']) echo 'selected="selected"';
			echo '>&nbsp;&nbsp;&nbsp;&nbsp;'.$subrow['brandName'].'</option>';
		}
	}
}

function BuildLangList($langID=0) {
	global $conn, $lang;
	echo '<option value="">- '.$lang->display('Select Language').' -</option>';
	$query = sprintf("SELECT * FROM languages ORDER BY languageName ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['languageID'].'"';
		if(!empty($langID) && $langID==$row['languageID']) echo 'selected="selected"';
		echo '>'.$lang->display($row['languageName']).'</option>';
	}
}

function BuildLangProList($proID=0) {
	global $conn, $lang;
	echo '<option value="">- '.$lang->display('Proficiency').' -</option>';
	$query = sprintf("SELECT * FROM proficiency ORDER BY ProID ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['proID'].'"';
		if(!empty($proID) && $proID==$row['proID']) echo 'selected="selected"';
		echo '>'.$lang->display($row['proLevel']).'</option>';
	}
}

function BuildTargetLangList($langID) {
	global $conn, $lang;
	echo '<option value="">- '.$lang->display('Select Language').' -</option>';
	$query = sprintf("SELECT * FROM languages WHERE languageID <> %d ORDER BY languageName ASC", $langID);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['languageID'].'">'.$lang->display($row['languageName']).'</option>';	
	}
}

function BuildTargetLangOption($langID, $selected=array(), $columns=3) {
	global $conn, $lang;
	$width = (int) 100 / $columns;
	if(!is_array($selected)) {
		if(!empty($selected)) {
			$selected = trim($selected,",;");
			if(!empty($selected)) {
				$selected = preg_split("%[,;]%sim",$selected);
			} else {
				$selected = array();
			}
		} else {
			$selected = array();
		}
	}
	$query = sprintf("SELECT * FROM languages WHERE languageID <> %d ORDER BY languageName ASC", $langID);
	$result = mysql_query($query, $conn) or die(mysql_error());
	$counter = 0;
	echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
	echo '<tr>';
	while($row = mysql_fetch_assoc($result)) {
		echo '<td width="'.$width.'%"><input type="checkbox" id="targetLangID[]" name="targetLangID[]" value="'.$row['languageID'].'"';
		if(in_array($row['languageID'],$selected)) echo 'checked="checked"';
		echo ' />'.$lang->display($row['languageName']).'</td>';
		$counter++;
		if($counter%$columns==0) echo '</tr><tr>';
	}
	echo '</tr>';
	echo '</table>';
}

function BuildAgencyList($company_id, $selected=0) {
	global $conn, $lang, $DB;
	echo '<option value="">- '.$lang->display('Select Agency').' -</option>';
	$query = sprintf("SELECT companyID, companyName
					FROM companies
					WHERE companyID IN (%s)
					AND agency = 1
					ORDER BY companyName ASC",
					mysql_real_escape_string($DB->get_company_list($company_id)));
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['companyID'].'"';
		if(!empty($selected) && $row['companyID']==$selected) echo ' selected="selected"';
		echo '>'.$row['companyName'].'</option>';
	}
}

function BuildTipMsg($msg,$obj_id="") {
	global $lang;
	if(!empty($obj_id)) {
		$tid =  $obj_id;
	} else {
		$rand = rand(1,99);
		$tid = "tip_$rand";
	}
	echo '<div class="tip" id="'.$tid.'">';
	echo '<div class="close"><a href="javascript:void(0);" onclick="fadeOut(\''.$tid.'\');"><img src="'.IMG_PATH.'btn_close.png" title="'.$lang->display('Close').'"></a></div>';
	echo '<div class="ico"><img src="'.IMG_PATH.'ico_help.png"></div>';
	echo '<div class="msg">'.$msg.'</div>';
	echo '<div style="clear:both"></div>';
	echo '</div>';
}

function BuildPageIntro($title,$intro="") {
	global $lang;
	echo '<div id="introPage">';
	echo '<div class="globe"><img src="'.IMG_PATH.'ico_intro_page.gif" /></div>';
	echo '<div class="title">'.$title.'</div>';
	if(!empty($intro)) echo '<div class="txt">'.$intro.'</div>';
	require_once(MODULES.'mod_help.php');
	echo '<div class="clear"></div>';
	echo '</div>';
}

function BuildSubjectList($subjectID=0) {
	global $conn, $lang;
	echo '<option value="0">- '.$lang->display('Select Subject').' -</option>';
	$query = sprintf("SELECT * FROM subjects WHERE streamID >0 ORDER BY subjectTitle ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	$found = mysql_num_rows($result);
	if($found) while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['subjectID'].'"';
		if(!empty($subjectID) && $subjectID==$row['subjectID']) echo 'selected="selected"';
		echo '>'.$lang->display($row['subjectTitle']).'</option>';
	}
}

function BuildTMTypeList($typeID=0) {
	global $conn, $lang;
	$query = sprintf("SELECT id, name
					FROM para_types
					ORDER BY id ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['id'].'"';
		if(!empty($typeID) && $typeID==$row['id']) echo 'selected="selected"';
		echo '>'.$lang->display($row['name']).'</option>';
	}
}

function PrintCampStatus($status) {
	global $conn, $lang;
	$query = sprintf("SELECT statusInfo
					FROM status
					WHERE statusID = %d
					LIMIT 1",
					$status);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(!mysql_num_rows($result)) return false;
	$row = mysql_fetch_assoc($result);
	return $lang->display($row['statusInfo']);
	
}

function BuildCampStatusList($status=0) {
	global $conn, $lang;
	$query = sprintf("SELECT * FROM status
					WHERE statusID < 5
					ORDER BY statusID ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['statusID'].'"';
		if(!empty($status) && $status==$row['statusID']) echo 'selected="selected"';
		echo '>'.$lang->display($row['statusInfo']).'</option>';
	}
}

function BuildArtStatusList($status=0) {
	global $conn, $lang;
	$query = sprintf("SELECT * FROM status
					WHERE statusID IN (%d,%d)
					ORDER BY statusID ASC",
					STATUS_ACTIVE,
					STATUS_TRASHED);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['statusID'].'"';
		if(!empty($status) && $status==$row['statusID']) echo 'selected="selected"';
		echo '>'.$lang->display($row['statusInfo']).'</option>';
	}
}

function BuildArtworkStatusList($status=1) {
	global $lang;
	$array = array(0=>"Trashed",1=>"Active");
	foreach($array as $k=>$v) {
		echo '<option value="'.$k.'"';
		if(!empty($status) && $status==$k) echo 'selected="selected"';
		echo '>'.$lang->display($v).'</option>';
	}
}

function BuildTaskStatusList($status=0) {
	global $conn, $lang;
	$query = sprintf("SELECT * FROM status
						WHERE statusID > 4
						ORDER BY statusID ASC");
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo '<option value="'.$row['statusID'].'"';
		if(!empty($status) && $status==$row['statusID']) echo 'selected="selected"';
		echo '>'.$lang->display($row['statusInfo']).'</option>';
	}
}

function BuildFTPList($dir) {
	$ftp = ROOT.FTP_DIR.$dir;
	if(!file_exists($ftp)) mkdir($ftp);
	$files = ListFiles($ftp);
	foreach ($files as $file) {
		$filename = substr($file,strrpos($file,$ftp)+strlen($ftp)+1);
		if(substr_count($filename,"Thumbs.db")==0) {
			echo '<option value="'.$ftp.'/'.$filename.'" title="'.$filename.'">'.$filename.'</option>';
		}
	}
}

function BuildFlagList($flag="") {
	global $conn, $lang;
	echo '<option value="">- '.$lang->display('Please Select').' -</option>';
	$dir = ROOT."/images/flags";
	$files = ListFiles($dir);
	foreach ($files as $file) {
		$filename = substr($file,strrpos($file,$dir)+strlen($dir)+1);
		if(substr_count($filename,"Thumbs.db")==0) {
			echo '<option value="'.$filename.'" title="'.$filename.'"';
			if(!empty($flag) && $flag==$filename) echo 'selected="selected"';
			echo '>'.$filename.'</option>';
		}
	}
}

function BuildSPItemList($service_package_id=0) {
	global $conn, $lang, $DB;
	echo '<tr>';
	echo '<th>'.$lang->display('Service Package Items').'</th>';
	echo '<td>';
	echo '<table width="100%" cellpadding="3" cellspacing="0" border="0">';
	echo '<tr>';
	echo '<td align="center" width="10%" class="highlight">';
	echo '<input
			type="checkbox"
			class="checkbox"
			name="itemall"
			id="itemall"
			onclick="GroupCheckbox(this,\'item\')"
			title="'.$lang->display('Select All').'"/>';
	echo '</td>';
	echo '<td width="25%" class="highlight">'.$lang->display('Type').'</td>';
	echo '<td width="50%" class="highlight">'.$lang->display('Action').'</td>';
	echo '<td align="center" width="15%" align="center" class="highlight">'.$lang->display('Credits').'</td>';
	echo '</tr>';
	echo '</table>';
	echo '</td>';
	echo '</tr>';
	$query_engine = sprintf("SELECT id, name
							FROM service_engines
							ORDER BY name ASC");
	$result_engine = mysql_query($query_engine,$conn) or die(mysql_error());
	while($row_engine = mysql_fetch_assoc($result_engine)) {
		echo '<tr>';
		echo '<th valign="middle">'.$row_engine['name'].'</th>';
		echo '<td>';
		$query = sprintf("SELECT service_transaction_process.id, service_transaction_process.notes, service_types.name
						FROM service_transaction_process
						LEFT JOIN service_types ON service_transaction_process.type_id = service_types.id
						WHERE service_transaction_process.serviceID = %d
						ORDER BY
						service_transaction_process.type_id ASC,
						service_transaction_process.transactionID ASC",
						$row_engine['id']);
		$result = mysql_query($query, $conn) or die(mysql_error());
		if(mysql_num_rows($result)) {
			echo '<table width="100%" cellpadding="3" cellspacing="0" border="0">';
			$counter = 1;
			while($row = mysql_fetch_assoc($result)) {
				$item_info = $DB->get_service_package_item_info($service_package_id,$row['id']);
				$style = $counter%2==0 ? 'even' : 'odd';
				echo '<tr class="'.$style.'" onmouseover="this.className=\'hover\'" onmouseout="this.className=\''.$style.'\'">';
				echo '<td align="center" width="10%">';
				echo '<input
						type="checkbox"
						class="checkbox"
						name="item['.$row['id'].']"
						id="item['.$row['id'].']"
						value="'.$row['id'].'"';
				if($item_info !== false) echo ' checked="checked"';
				echo '/>';
				echo '</td>';
				echo '<td width="25%">'.$lang->display($row['name']);
				echo '<td width="50%">'.$lang->display($row['notes']).'</td>';
				echo '<td width="15%" align="center">';
				echo '<select
						class="input"
						onfocus="this.className=\'inputOn\'"
						onblur="this.className=\'input\'"
						name="credits['.$row['id'].']"
						id="credits['.$row['id'].']">';
				for($i=0;$i<=10;$i++) {
					echo '<option value="'.$i.'"';
					if($i == $item_info['credits']) echo ' selected="selected"';
					echo '>'.$i.'</option>';
				}
				echo '</select>';
				echo '</td>';
				echo '</tr>';
				$counter++;
			}
			echo '</table>';
		} else {
			echo '<div class="grey" align="center">-</div>';
		}
		echo '</td>';
		echo '</tr>';
	}
}
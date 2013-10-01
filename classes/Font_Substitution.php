<?php
// In case this is called out of the index.php process
require_once(dirname(__FILE__).'/../config.php');
// Just wraps around mysqli to get the defaulf connection info from the constants instead of ini_get('mysqli.xxx')
//$Database = new mysqli(HOST_NAME,DB_USER_NAME,DB_PASSWORD,DB_NAME);

class Font_Substitution{
	public static $substitution_levels = array(4=>'task',3=>'artwork',2=>'campaign',1=>'company');

	public static function useFont($font_id,$levelId,$level='artwork'){
		//= = Substituted with same font
		//- = Inherited Font
		//* = Effectual Font
		//+ = Direct Substitution
		$sub_font_id = self::get_font_substitution($font_id,$levelId,$level);
		if($sub_font_id!==false){
			$sub_type = '+';
		}else{
			$sub_type = '*';
			$sub_font_id = self::get_effectual_font($font_id,$levelId,$level,FALSE);
		}
		if($font_id == $sub_font_id) {
			$sub_type = '=';
		} else {
			if($sub_font_id == 0){
				$sub_type = '-';
				$sub_font_id = self::get_inherited_font($font_id,$levelId,$level);
				$sub_font_id = $sub_font_id['font'];
				$count++;
			}
		}
		return array("font"=>$sub_font_id,"sub_type"=>$sub_type);
	}
	
	// these will use the same table and format as the usual substitutions but with a font_id of 0 as that isn't used
	public static function set_default_font($substitution,$id,$level='company'){
		return self::set_font_substitution(0,$substitution,$id,$level);
	}

	public static function get_default_font($id,$level='company'){
		return self::get_effectual_font(0,$id,$level);
	}

	// return true if an existing substitution was overwritten
	public static function set_font_substitution($font_id,$substitution,$id,$level='company'){
		$query = sprintf('SELECT * FROM substitutions WHERE level = \'%s\' AND ID = %d AND fontID = %d LIMIT 1',
			mysql_real_escape_string($level),$id,$font_id);
		
		$result = mysql_query($query) or die('Unable to check substitution font: '.mysql_error());
		
		$return = false;
		if(mysql_num_rows($result) > 0){
			self::remove_font_substitution($font_id,$id,$level);
			$return = true;
		}
		
		//$query->close();
		$query = sprintf('INSERT INTO substitutions (level,ID,fontID,substitution) VALUES (\'%s\',%d,%d,%d)',
			mysql_real_escape_string($level),$id,$font_id,$substitution);
		$result = mysql_query($query) or die('Unable to set substitution font: '.mysql_error());
		
		return $return;
	}

	// return true if removed, false otherwise
	public static function remove_font_substitution($font_id,$id,$level='company'){
		$query = sprintf('DELETE FROM substitutions WHERE level = \'%s\' AND ID = %d AND fontID = %d',
			mysql_real_escape_string($level),$id,$font_id);
		$result = mysql_query($query) or die('Unable to remove substitution font: '.mysql_error());
		
		return mysql_affected_rows() == 1;
	}

	// return substitute font for specified level if one exists (does not go up the heirachy)
	public static function get_font_substitution($font_id,$id,$level='company'){
		$query = sprintf('SELECT substitution FROM substitutions WHERE level = \'%s\' AND ID = %d AND fontID = %d',
			mysql_real_escape_string($level),$id,$font_id);
		
		$result = mysql_query($query) or die('Unable to get font substitution: ' . mysql_error());
		if(mysql_num_rows($result) == 0) return FALSE;
		$row = mysql_fetch_row($result);
		return $row[0];
	}
	
	

	// deletes all the font substitutions at and below the specified level, returns the number of substitutions deleted
	public static function remove_font_substitutions($company,$campaign=0,$artwork=0,$task=0){
		global $Database;
		foreach(self::$substitution_levels as $level=>$id){
			if($id = $$id)break;
		}
		
		switch($level){
		case 4: // delete at task level
			$query = $Database->prepare('
				DELETE FROM substitutions
				WHERE
					(substitutions.level = 4 AND substitutions.ID = ?)');
			$query->bind_param('d',$id);
			break;
		case 3: // delete at artwork level
			$query = $Database->prepare('
				DELETE FROM substitutions USING substitutions,tasks 
				WHERE
					(substitutions.level = 3 AND substitutions.ID = ?) OR
					(substitutions.level = 4 AND substitutions.ID = tasks.taskID AND tasks.artworkID = ?)');
			$query->bind_param('dd',$id,$id);
			break;
		case 2: // delete at campaign level
			$query = $Database->prepare('
			DELETE FROM substitutions USING substitutions,tasks,artworks
			WHERE
				(substitutions.level = 2 AND substitutions.ID = ?) OR
				(substitutions.level = 3 AND substitutions.ID = artworks.artworkID AND artworks.campaignID = ?) OR
				(substitutions.level = 4 AND substitutions.ID = tasks.taskID AND tasks.artworkID = artworks.artworkID)');
			$query->bind_param('dd',$id,$id);
			break;
		case 1: // delete at company level
			$query = $Database->prepare('
			DELETE FROM substitutions USING substitutions,tasks,artworks,campaigns,brands
			WHERE
				(substitutions.level = 1 AND substitutions.ID = ?) OR
				(substitutions.level = 2 AND substitutions.ID = campaigns.campaignID AND campaigns.brandID = brands.companyID AND brands.companyID = ?) OR
				(substitutions.level = 3 AND substitutions.ID = artworks.artworkID AND artworks.campaignID = campaigns.campaignID) OR
				(substitutions.level = 4 AND substitutions.ID = tasks.taskID AND tasks.artworkID = artworks.artworkID)');
			$query->bind_param('dd',$id,$id);
		}
		
		if(!$query->execute()) die('Unable to remove substitution fonts: '.$query->error);
		
		return $query->affected_rows;
	}
	
	public static function get_effectual_font($font_id,$id,$level='company',$default=TRUE){
		switch($level){
		case 'task':
			$sub = self::get_font_substitution($font_id,$id,'task');
			if($sub)
				return $sub;
			
			$query = sprintf("SELECT artworkID FROM tasks WHERE taskID = %d",
				$id);
			$result = mysql_query($query) or die(mysql_error());
			$row = mysql_fetch_row($result);
			$id = $row[0];
		
		case 'artwork':
			$sub = self::get_font_substitution($font_id,$id,'artwork');
			if($sub)
				return $sub;
			
			$query = sprintf('SELECT campaignID FROM artworks WHERE artworkID = %d',
				$id);
			$result = mysql_query($query) or die(mysql_error());
			$row = mysql_fetch_row($result);
			$id = $row[0];
			
		case 'campaign':
			$sub = self::get_font_substitution($font_id,$id,'campaign');
			if($sub)
				return $sub;
			
			$query = sprintf('SELECT brands.companyID FROM campaigns LEFT JOIN brands ON brands.brandID = campaigns.brandID WHERE campaigns.campaignID = %d',
				$id);
			$result = mysql_query($query) or die(mysql_error());
			$row = mysql_fetch_row($result);
			$id = $row[0];
			
		case 'company':
			$sub = self::get_font_substitution($font_id,$id,'company');
			if($sub)
				return $sub;
		}

		if($font_id == 0)
			return $default?DEFAULT_SUB_FONT_ID:0;
		$query = sprintf('SELECT installed FROM `fonts` WHERE id = %d',
			$font_id);
		$result = mysql_query($query) or die(mysql_error());
		$row = mysql_fetch_row($result);
		$installed = $row[0];
		if($installed)
			return $font_id;
		// gets here when there are no substitutions
		return self::get_effectual_font(0,$id,$level,$default);
	}
	
	
	public static function get_inherited_font($font_id,$id,$level='company'){
		$oid = $id;
		switch($level){
		case 'task':
			$query = sprintf('SELECT artworkID FROM tasks WHERE taskID = %d',$id);
			$result = mysql_query($query) or die(mysql_error());
			$row = mysql_fetch_row($result);
			$id = $row[0];
			
			$sub = self::get_font_substitution($font_id,$id,'artwork');
			if($sub)
				return array('level'=>'artwork','id'=>$id,'font'=>$sub);
		
		case 'artwork':
			$query = sprintf('SELECT campaignID FROM artworks WHERE artworkID = %d',$id);
			$result = mysql_query($query) or die(mysql_error());
			$row = mysql_fetch_row($result);
			$id = $row[0];
			
			$sub = self::get_font_substitution($font_id,$id,'campaign');
			if($sub)
				return array('level'=>'campaign','id'=>$id,'font'=>$sub);
			
		case 'campaign':
			
			$query = sprintf('SELECT brands.companyID FROM campaigns LEFT JOIN brands ON brands.brandID = campaigns.brandID WHERE campaigns.campaignID = %d',$id);
			$result = mysql_query($query) or die(mysql_error());
			$row = mysql_fetch_row($result);
			$id = $row[0];
			
			$sub = self::get_font_substitution($font_id,$id,'company');
			if($sub)
				return array('level'=>'company','id'=>$id,'font'=>$sub);
			
		case 'company':
			break;
		}

		$query = sprintf('SELECT installed FROM `fonts` WHERE id = %d',
			$font_id);
		$result = mysql_query($query) or die(mysql_error());
		$row = mysql_fetch_row($result);
		$installed = $row[0];
		// gets here if there is no inherited substitution
		if($installed)
			return FALSE;
		// if the font is missing then look for defautl fonts
		if($font_id){
			$here = self::get_font_substitution(0,$oid,$level);
			if($here)
				return array('level'=>$level,'id'=>$oid,'font'=>$here);
			return self::get_inherited_font(0,$oid,$level);
		}
		// gets here when there are no default font substitutions so have to use system substitute
		return array('level'=>'system','id'=>0,'font'=>DEFAULT_SUB_FONT_ID);
	}
	
}



























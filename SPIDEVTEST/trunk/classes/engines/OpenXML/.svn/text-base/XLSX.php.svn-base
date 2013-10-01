<?php
/**
 * 
 */
class XLSX{
	private $handler;
	/*
	MIME stands for Multipurpose Internet Mail Extensions, and provides a standard methodology
	for specifying the content type of files within an IDML package. For more on MIME media types
	in general, see <hyperlink>http://www.ietf.org/rfc/rfc2045.txt</hyperlink>. For more information
	on the content of the MIMETYPE file in an IDML package, refer to <hyperlink>“Appendix A:
	Universal Container Format.”</hyperlink> 
	*/
	private $MIMETYPE;
	/*
	The designmap file is the key to all of the other files that appear within the IDML package.
	This file specifies the order in which the spreads appear in the document, maintains the cross references between the resources and content of the file, 
	and defines a variety of document-level attributes not supported by other files.
	*/
	private $designmap;
	private $MasterSpreads;
	private $Resources;
	private $METAINF;
	private $XML;
	
	private $Spreads;
	private $Stories;
	
	function __construct($filename){
		//Useable resources
		$this->Resources = new Resources();
		$this->MasterSpreads = new MasterSpreads();
		$this->METAINF = new METAINF();
		$this->XML = new XML();
		$this->Stories = new Stories();
		$this->Spreads = new Spreads();
		
		require_once dirname(__FILE__)."/compile.php";
		$this->handler = new compiler();
		$this->handler->decompile($filename);
		$this->handler->debug = false;
		$files = $this->handler->getList();
		foreach(array_keys($files) as $file){
			$info = explode('/',$file);
			switch(count($info)){
				case 1: //Root level
					list($section) = explode(".",$info[0]);
					switch(strtolower($section)){
						case "mimetype":
							$this->MIMETYPE = new MIMETYPE($this->handler, $file);
							break;
						case "designmap":
							$this->designmap = new designmap($this->handler, $file);
							break;
					}
					break;
				case 2: //Level 2 Items
					$section = str_replace("-","",$info[0]);

					
					switch($section){
						case "METAINF":
							list($p) = explode(".",$info[1]);
							$part = "set{$p}";
							$this->METAINF->$part(new $p($this->handler,$file));
							break;
						case "Resources":
						case "XML":
							list($p) = explode(".",$info[1]);
							$part = "set{$p}";
							$this->$info[0]->$part(new $p($this->handler,$file));
							break;
						case "MasterSpreads":
						case "Spreads":
						case "Stories":
							list($p) = explode("_",$info[1]);
							$part = "add{$p}";
							$this->$info[0]->$part(new $p($this->handler,$file));
							break;
					}
					break;
				default:
					//Ignore Others
					continue;
					break;
			}
		}
	}
	
	public function reBuild($filename){
		$this->handler = new compiler();
		$this->handler->compile($filename,true);

		$this->handler->addData(false, $this->MIMETYPE->getName(), '',$this->MIMETYPE->getValue());
		$this->handler->addData(false, $this->designmap->getName(), '',$this->designmap->getValue()->asXML());
				
		$Sections = array('MasterSpreads', 'Stories', 'Spreads');
		foreach($Sections as $Section){
			$this->handler->addSection($Section);
			$part = "get{$Section}";
			foreach( $this->$Section->$part() as $part){
				#echo $part->getName();
				#echo $part->getValue()->asXML();
				$this->handler->addData(false, $part->getName(), '',$part->getValue()->asXML());
			}
		}

		//Resources
		//var_dump( (string)$this->Resources->getGraphic() );
		#var_dump( $this->Resources->getGraphic()->getName() );
		#var_dump( $this->Resources->getGraphic()->getValue()->asXML() );
		$this->handler->addData(false, $this->Resources->getGraphic()->getName(), '',$this->Resources->getGraphic()->getValue()->asXML());
		
		#var_dump( $this->Resources->getFonts()->getName() );
		#var_dump( $this->Resources->getFonts()->getValue()->asXML() );
		$this->handler->addData(false, $this->Resources->getFonts()->getName(), '',$this->Resources->getFonts()->getValue()->asXML());
		
		#var_dump( $this->Resources->getStyles()->getName() );
		#var_dump( $this->Resources->getStyles()->getValue()->asXML() );
		$this->handler->addData(false, $this->Resources->getStyles()->getName(), '',$this->Resources->getStyles()->getValue()->asXML());

		#var_dump( $this->Resources->getPreferences()->getName() );
		#var_dump( $this->Resources->getPreferences()->getValue()->asXML() );
		$this->handler->addData(false, $this->Resources->getPreferences()->getName(), '',$this->Resources->getPreferences()->getValue()->asXML());
		
		
		//METAINF
		#var_dump( $this->METAINF->getcontainer()->getValue()->asXML() );
		#var_dump( $this->METAINF->getmetadata()->getValue()->asXML() );
		$this->handler->addData(false, $this->METAINF->getcontainer()->getName(), '',$this->METAINF->getcontainer()->getValue()->asXML());
		$this->handler->addData(false, $this->METAINF->getmetadata()->getName(), '',$this->METAINF->getmetadata()->getValue()->asXML());

		//XML
		#var_dump( $this->XML->getBackingStory()->getValue()->asXML() );
		#var_dump( $this->XML->getTags()->getValue()->asXML() );
		$this->handler->addData(false, $this->XML->getBackingStory()->getName(), '',$this->XML->getBackingStory()->getValue()->asXML());
		$this->handler->addData(false, $this->XML->getTags()->getName(), '',$this->XML->getTags()->getValue()->asXML());
		
		$this->handler->save();
	}
	
	function getMIMETYPE(){
		return $this->MIMETYPE;
	}
	function getdesignmap(){
		return $this->designmap;
	}
	function getResources(){
		return $this->Resources;
	}
	function getXML(){
		return $this->XML;
	}
	function getMETAINF(){
		return $this->METAINF;
	}

	function getMasterSpreads(){
		return $this->MasterSpreads;
	}
	function getStories(){
		return $this->Stories;
	}
	function getSpreads(){
		return $this->Spreads;
	}	
}

abstract class STRINGPart{
	protected $handler;
	private $name;
	private $data;
	function __construct(&$handler, $part=""){
		$this->handler = $handler;
		if(!empty($part)) $this->loadValue($part);
	}
	function loadValue($part){
		$this->name = $part;
		$this->data = $this->handler->extract($part);
	}
	function setValue($string){
		$this->data = $string;
	}
	function __toString(){
		return $this->data;
	}
	function getName(){
		return $this->name;
	}
	function getValue(){
		return $this->data;
	}
}
abstract class XMLPart{
	protected $handler;
	private $name;
	private $data;
	function __construct(&$handler, $part=""){
		$this->handler = $handler;
		if(!empty($part)) $this->loadValue($part);
	}
	function loadValue($part){
		$this->name = $part;
		$this->data = simplexml_load_string($this->handler->extract($part));
	}
	function setValue($string){
		$this->data = $string;
	}
	function __toString(){
		return $this->data->asXML();
	}
	function getName(){
		return $this->name;
	}
	function getValue(){
		return $this->data;
	}
}

class MIMETYPE extends STRINGPart{
	
}
class designmap extends XMLPart{
	
}

/*
The Resources section in an IDML package contains elements that are commonly used by other
files within the document, such as colors, fonts, and paragraph styles. In addition, most of the
preferences for the document are stored in this section. */
class Resources{
	/*
	Graphic contains the inks, colors, swatches, gradients, mixed inks, mixed ink groups, tints, and stroke styles contained in the document.
	*/
	private $Graphic;

	/*
	The Fonts contains the fonts used in the document (including composite fonts, if any).
	*/
	private $Fonts;

	/*
	The Styles file contains all of the paragraph, character, object, cell, table, and table of contents (TOC) styles used in the document.
	*/
	private $Styles;

	/*
	The Preferences file contains representations of all of the document preferences.
	*/
	private $Preferences;
	
	
	function setGraphic(Graphic $value){
		$this->Graphic = $value;
	}
	function getGraphic(){
		return $this->Graphic;
	}
	
	function setFonts(Fonts $value){
		$this->Fonts = $value;
	}
	function getFonts(){
		return $this->Fonts;
	}
	
	function setStyles(Styles $value){
		$this->Styles = $value;
	}
	function getStyles(){
		return $this->Styles;
	}
	
	function setPreferences(Preferences $value){
		$this->Preferences = $value;
	}
	function getPreferences(){
		return $this->Preferences;
	}
}
class Graphic extends XMLPart{
	
}
class Fonts extends XMLPart{
	
}
class Styles extends XMLPart{
	
}
class Preferences extends XMLPart{
	
}

/*
The Spreads section contains the XML files representing the spreads in the document. Each spread
contains all of the page items (rectangles, ellipses, graphic lines, polygons, groups, buttons, and
text frames) that appear on the pages of the spread. The <Spread> element also contains <Page>
elements, which contain attributes and elements that relate to the pages of the spread. Note that
<Page> elements do not contain page items.
Spreads do not contain text stream content—the <TextFrame>
XML elements in the spread refer to <Story> elements contained the files in the Stories section. */
class Spreads{
	private $Spread = array();
	function addSpread(Spread $value){
		$this->Spread[] = $value;
	}
	function getSpreads(){
		return $this->Spread;
	}
}
class Spread extends XMLPart{

}

/*
The Stories section contains all of the stories in the InDesign document. Each XML file in the Stories section of an IDML archive exported from InDesign represents the contents of a single story (as a <Story> element)and all of the formatting attributes applied to the text in the story.
Stories can also contain other objects, such as inline or anchored frames within the text, or XML elements that have been associated with the text.
Paragraph, character, and object styles used to format the text of the story are not defined within a <Story> element. Instead, the story contains cross references (using the unique Self attribute) to the corresponding styles in the Styles stored in the Resources section of the archive.*/
class Stories{
	private $Story = array();
	function addStory(Story $value){
		$this->Story[] = $value;
	}
	function getStories(){
		return $this->Story;
	}
}
class Story extends XMLPart{
	
}

/*
MasterSpreads, xml contains the master spreads of the document, stored as <MasterSpread> elements. Each <MasterSpread> element within this file contains all of the page items (rectangles, ellipses, graphic lines, polygons, groups, buttons, and text frames) that appear on the pages of the master spread. 
*/
class MasterSpreads{
	private $MasterSpread = array();
	function addMasterSpread(MasterSpread $value){
		$this->MasterSpread[] = $value;
	}
	function getMasterSpreads(){
		return $this->MasterSpread;
	}
}
class MasterSpread extends XMLPart{
	
}

class METAINF{
	private $container;
	private $metadata;
	
	function setcontainer(container $value){
		$this->container = $value;
	}
	function getcontainer(){
		return $this->container;
	}
	function setmetadata(metadata $value){
		$this->metadata = $value;
	}
	function getmetadata(){
		return $this->metadata;
	}
}

/*
The container is a standard part of a UCF package and describes the file encoding used by the files in the package. 
container also includes a reference to the root document of the IDML package (usually designmap).
*/
class container extends XMLPart{
	
}
class metadata extends XMLPart{
	
}

/*
The XML section contains XML elements and settings used in the InDesign 	document.
The XML elements referred to here are the XML elements that actually appear in the InDesign
document (i.e., what you see in the Structure view in the InDesign user interface); not the
contents of the XML files in the IDML archive. Though an IDML file is made up of XML, the
InDesign
document it describes does not necessarily contain XML elements.
 */
class XML{
	/*
	The BackingStory.
	xml part contains the unplaced XML content of the InDesign document (i.e., XML content that has not yet been associated with an element in the layout).
	*/
	private $BackingStory;
	/*
	The Tags contains the XML tag definitions stored in the InDesign document, including unused tags.
	*/
	private $Tags;
	
	/*
	The Mapping contains the style to tag and tag to style mappings defined in the InDesign document.
	private $Mapping;
	
	function setMapping(Mapping $value){
		$this->Mapping = $value;
	}
	function getMapping(){
		return $this->Mapping;
	}
	*/
	
	function setBackingStory(BackingStory $value){
		$this->BackingStory = $value;
	}
	function getBackingStory(){
		return $this->BackingStory;
	}

	function setTags(Tags $value){
		$this->Tags = $value;
	}
	function getTags(){
		return $this->Tags;
	}
}
class BackingStory extends XMLPart{
	
}
class Tags extends XMLPart{
	
}
/*
class Mapping extends XMLPart{
	
}
*/
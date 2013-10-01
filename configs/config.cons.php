<?php
// general preferences
define("DAYS_AS_URGENT",3);												//days remaining marked as urgent
define("JPEG_QUALITY",1);												//Quark jpeg preview quality
define("ZOOM_SCALE",10);												//the scale of zoom in/out between 0 and 50
define("RPP",20);														//display records per page
define("DEFAULT_VIEW","thumbnails");									//default view
define("DEFAULT_FILE_TYPE",0);											//default file type 1:QXP 6:IDML 7:INDD
define("ADMIN_USERID",7);												//admin userID
define("ADMIN_COMPANYID",1);											//admin companyID
define("GOOGLE_BOT_ID",113);											//google bot user id
define("DEFAULT_SUB_FONT_ID",65);										//default substitute font id
define("DEFAULT_IMG_DIR","/");											//default img dir
define("DEFAULT_LAYER_COLOUR","000000");								//default layer colour
define("DEFAULT_DASHED_LINE_COLOUR","FFFFFF");							//default dashed line colour
define("DEFAULT_DASHED_LINE_PIXELS",10);								//default dashed line pixels
define("DEFAULT_LANGUAGE",1);											//default interface language
define("DEFAULT_ICON_COMMENT",IMG_PATH."header/ico_comment.png");		//default comments icon
define("DEFAULT_ICON_AMEND",IMG_PATH."header/ico_amend.png");			//default amends icon
define("DEFAULT_ICON_MORE",IMG_PATH."header/ico_more.png");				//default more icon
define("DEFAULT_ICON_WIDTH",24);										//default icon width
define("DEFAULT_ICON_HEIGHT",24);										//default icon height
define("FILE_TYPE_DETECTOR",RESOURCES."FileTypes.xml");					//the file detector
define("DEFAULT_EDIT_MARKS_DISPLAY",1);									//0-off 1-on
define("THUMBNAIL_MAX_WIDTH",150);										//thumbnail max width
define("THUMBNAIL_MAX_HEIGHT",150);										//thumbnail max height
define("MAX_STRING_LENGTH",25);											//max string length for display
define("BREADCRUMBS_ARROW",'<span class="span"><img src="'.IMG_PATH.'arrow_right.png" /></span>');

// declare constants for status see table status
define("STATUS_ACTIVE",1);
define("STATUS_COMPLETE",2);
define("STATUS_ARCHIVED",3);
define("STATUS_TRASHED",4);

// declare constants for para types
define("PARA_USER",1);
define("PARA_GOOGLE",2);
define("PARA_PHRASE",3);
define("PARA_GLOSSARY",4);
define("PARA_IMPORT",5);
define("PARA_UPLOAD",6);

// declare constants for img types
define("IMG_UPLOAD",1);
define("IMG_LIBRARY",2);

// declare constants for para parser type
define("PARSE_BY_PARAGRAPH",1);
define("PARSE_BY_SENTENCE",2);

// declare constants for service transactions
define("SERVICE_UPLOAD",1);
define("SERVICE_DOWNLOAD",2);
define("SERVICE_IMPORT",3);
define("SERVICE_EXPORT",4);
define("SERVICE_REBUILD",5);

// declare constants for service transaction types
define("TYPE_ORIGINAL",0);
define("TYPE_TEMPLATE",1);
define("TYPE_PREWORK",2);
define("TYPE_TRANSLATION",3);
define("TYPE_TWEAK",4);

// declare constants for service engines
define("ENGINE_QUARK_ID",1);
define("ENGINE_INDESIGN_ID",7);

// decalre types for paragraph changes
define("TYPE_MERGE",1);
define("TYPE_SPLIT",2);

// joboptions storage information
define('JOBOPTIONS_EXTENSION','.joboptions');
define('JOBOPTIONS_DEL','.removed');
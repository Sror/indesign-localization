<?php
//Mail Setup
define("MAIL_HOST","smtp.sp-int.com");									//SMTP mail server
define("MAIL_PORT",587);												//SMTP mail port
define("MAIL_USER","webmaster@sp-int.com");								//mail server username
define("MAIL_PASS","87purple87");										//mail server password
define("MAIL_REPLY_TO","support@sp-int.com");							//reply address
define("MAIL_REPLY_NAME","SPI Support");								//reply name
define("MAIL_FROM","webmaster@sp-int.com");									//from address
define("MAIL_FROM_NAME",SYSTEM_NAME." ".SYSTEM_VERSION);				//from name
define("MAIL_WORD_WRAP",100);											//email word wrap
define("MAIL_ATTCHMENT_PROCESS",34);									//export processor for email attachment
define("MAIL_ATTCHMENT_SAMPLE_LINES",20);								//lines in sample file
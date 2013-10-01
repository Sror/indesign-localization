//global
var tBox;

function get_httpxml() {
	var httpxml;
	try {
		// Firefox, Opera 8.0+, Safari
		httpxml = new XMLHttpRequest();
	}
	catch(e) {
		// Internet Explorer
		try {
			httpxml = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch(e) {
			try {
				httpxml = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(e) {
				alert("Your browser does not support AJAX!");
				return false;
			}
		}
	}
	return httpxml;
}

function ListTM(TaskID, PL, DivID) {
	ResetDiv(DivID);
	var httpxml = get_httpxml();

	function stateck() {
		if(httpxml.readyState==4) {
			document.getElementById(DivID).innerHTML=httpxml.responseText;
		}
	}

	var params = "task=" + TaskID + "&PL=" + PL;
	httpxml.open("POST", "modules/mod_tm.php", true);
	httpxml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	httpxml.setRequestHeader("Content-length", params.length);
	httpxml.setRequestHeader("Connection", "close");
	httpxml.onreadystatechange = stateck;
	httpxml.send(params);
}

function SaveTranslation(TaskID, PL, Translation, TypeID) {
	var httpxml = get_httpxml();

	function stateck() {
		if(httpxml.readyState==4) {
			document.getElementById(tBox).focus();
		}
	}

	var params = "do=save&task=" + TaskID + "&PL=" + PL + "&trans=" + encodeURIComponent(Translation) + "&type=" + TypeID;
	httpxml.open("POST", "modules/mod_tm.php", true)
	httpxml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	httpxml.setRequestHeader("Content-length", params.length);
	httpxml.setRequestHeader("Connection", "close");
	httpxml.onreadystatechange = stateck;
	httpxml.send(params);
}

function RemoveTM(ParaID, PL, TaskID) {
	var httpxml = get_httpxml();
	
	function stateck() {
		if(httpxml.readyState==4 && document.getElementById(tBox)) {
			document.getElementById(tBox).innerHTML='';
			document.getElementById(tBox).focus();
		}
	}

	var params = "do=remove&PL=" + PL + "&para=" + ParaID + "&task=" + TaskID;
	httpxml.open("POST", "modules/mod_tm.php", true)
	httpxml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	httpxml.setRequestHeader("Content-length", params.length);
	httpxml.setRequestHeader("Connection", "close");
	httpxml.onreadystatechange = stateck;
	httpxml.send(params);
}

function PickTM(TaskID, PL, ParaID, TM) {
	var httpxml = get_httpxml();
	
	function stateck() {
		if(httpxml.readyState==4) {
			document.getElementById(tBox).focus();
			document.getElementById(tBox).innerHTML=decodeURIComponent(TM);
		}
	}

	var params = "do=pick&task=" + TaskID + "&PL=" + PL + "&para=" + ParaID;
	httpxml.open("POST", "modules/mod_tm.php", true)
	httpxml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	httpxml.setRequestHeader("Content-length", params.length);
	httpxml.setRequestHeader("Connection", "close");
	httpxml.onreadystatechange = stateck;
	httpxml.send(params);
}

function NoteTM(ParaID, Notes) {
	var httpxml = get_httpxml();

	function stateck() {
		if(httpxml.readyState==4) {
			document.getElementById(tBox).focus();
		}
	}

	var params = "do=note&para=" + ParaID + "&notes=" + encodeURIComponent(Notes);
	httpxml.open("POST", "modules/mod_tm.php", true)
	httpxml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	httpxml.setRequestHeader("Content-length", params.length);
	httpxml.setRequestHeader("Connection", "close");
	httpxml.onreadystatechange = stateck;
	httpxml.send(params);
}

function setMT(taskID, PL, sourceObjectId, targetObjectId) {
	var data = document.getElementById(sourceObjectId).innerHTML;
	data = data.replace(/\n|\r/img, "");
	var myregexp = /<A[\s\S]*?<\/SPAN>(.*?)<\/A>/im;
	var match = myregexp.exec(data);
	if (match != null) {
		result = trim(match[1]);
		result = result.replace(/<br\s*\/?>/img, "\n");
		result = Encoder.htmlDecode(result);
		document.getElementById(targetObjectId).value = result;
		SaveTranslation(taskID,PL,result,2); // mark as google entry
		document.getElementById(targetObjectId).focus();
	}
}
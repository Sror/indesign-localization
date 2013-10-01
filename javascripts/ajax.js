function DoAjax(nvp,destDIV,destURL) {
	ResetDiv(destDIV);
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
	
	function stateck()  {
		if(httpxml.readyState == 4) {
			document.getElementById(destDIV).innerHTML = httpxml.responseText;
			if(destURL=="modules/mod_art_layers.php") jscolor.init();
		}
	}
	
	httpxml.open("GET",destURL+"?"+nvp,true);
	httpxml.onreadystatechange = stateck;
	httpxml.send(null);
}

function AjaxPost(nvp,destDIV,destURL) {
	ResetDiv(destDIV);
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
	
	function stateck() {
		if(httpxml.readyState == 4) {
			document.getElementById(destDIV).innerHTML = httpxml.responseText;
		}
	}
	
	httpxml.onreadystatechange = stateck;
	httpxml.open("POST",destURL,true);
	httpxml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	httpxml.setRequestHeader("Content-length", nvp.length);
	httpxml.send(nvp);
}
// initialise
var timerlen = 5;
var slideAniLen = 250;

var timerID = new Array();
var startTime = new Array();
var obj = new Array();
var endHeight = new Array();
var moving = new Array();
var dir = new Array();

function SlideDiv(DivId) {
	if(document.getElementById(DivId).style.display=="none") {
		SlideDown(DivId);
	} else {
		SlideUp(DivId);
	}
}

function SlideDown(DivId){
	if(moving[DivId]) return;
	
	// cannot slide down something that is already visible
	if(document.getElementById(DivId).style.display != "none") return;
	
	moving[DivId] = true;
	dir[DivId] = "down";
	StartSlide(DivId);
}

function SlideUp(DivId){
	if(moving[DivId]) return;
	
	// cannot slide up something that is already hidden
	if(document.getElementById(DivId).style.display == "none") return;
	
	moving[DivId] = true;
	dir[DivId] = "up";
	StartSlide(DivId);
}

function StartSlide(DivId){
	obj[DivId] = document.getElementById(DivId);
	
	endHeight[DivId] = parseInt(obj[DivId].style.height);
	startTime[DivId] = (new Date()).getTime();
	
	if(dir[DivId] == "down"){
		obj[DivId].style.height = "1px";
	}
	
	obj[DivId].style.display = "block";
	
	timerID[DivId] = setInterval('SlideTick(\'' + DivId + '\');',timerlen);
}

function SlideTick(DivId){
	var elapsed = (new Date()).getTime() - startTime[DivId];
	
	if (elapsed > slideAniLen)
		EndSlide(DivId)
	else {
		var d =Math.round(elapsed / slideAniLen * endHeight[DivId]);
		if(dir[DivId] == "up") {
			d = endHeight[DivId] - d;
		}
		
		obj[DivId].style.height = d + "px";
	}
	
	return;
}

function EndSlide(DivId){
	clearInterval(timerID[DivId]);
	
	if(dir[DivId] == "up") {
		obj[DivId].style.display = "none";
	}
	
	obj[DivId].style.height = endHeight[DivId] + "px";
	
	delete(moving[DivId]);
	delete(timerID[DivId]);
	delete(startTime[DivId]);
	delete(endHeight[DivId]);
	delete(obj[DivId]);
	delete(dir[DivId]);
	
	return;
}
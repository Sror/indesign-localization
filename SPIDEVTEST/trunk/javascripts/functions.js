/***************************
 * start of jquery functions *
 ***************************/
function jQueryCheckLanguageSelection(DivId) {
	if(findObj(DivId)) {
		var checkboxes = $('#'+DivId+' input:checkbox:checked');
		if(checkboxes.length == 0) {
			alert("Please select at least one language.");
			return false;
		} else {
			return true;
		}
	} else {
		return true;
	}
}

function jQueryGetRadioValue() {
	return $('input:radio:checked').attr('value');
}

function jQueryGetValue(ObjId) {
	return $('#' + ObjId).attr('value');
}

function jQueryCheckAll(DivId, ObjId, Element) {
	var checker = $('#' + ObjId).is(':checked');
	$('#' + DivId + ' :checkbox').attr('checked', checker);
	if(checker) {
		$(Element).addClass('selected');
	} else {
		$(Element).removeClass('selected');
	}
}

$(document).ready(
	function() {
		$('.thumbnailBox .off')
		.filter(':has(:checkbox:checked)')
		.addClass('selected')
		.end()
		.click(
			function(event) {
				$(this).toggleClass('selected');
				if(event.target.type !== 'checkbox') {
					$(':checkbox', this)
					.attr('checked',
						function() {
							return !this.checked;
						}
					);
				}
			}
		)
		.hover(
			function() {
				$(this).addClass('on');
			},
			function() {
				$(this).removeClass('on');
			}
		);
	}
);

$(document).ready(
	function() {
		$('#listview tr')
		.filter(':has(:checkbox:checked)')
		.addClass('selected')
		.end()
		.click(
			function(event) {
				$(this).toggleClass('selected');
				if(event.target.type !== 'checkbox') {
					$(':checkbox', this)
					.attr('checked',
						function() {
							return !this.checked;
						}
					);
				}
			}
		)
		.hover(
			function() {
				$(this).addClass('hover');
			},
			function() {
				$(this).removeClass('hover');
			}
		);
	}
);
/***************************
 * end of jquery functions *
 ***************************/

function SetClassName(objId, newCLassName) {
	if(findObj(objId)) {
		document.getElementById(objId).className = newCLassName;
	}
}

function BlurDiv(FormName, ObjName) {
	var theForm = document.forms[FormName];
	if(theForm) {
		for(i=0; i<theForm.length; i++) {
			if(theForm[i].type == 'text' && theForm[i].name.indexOf(ObjName)==0) {
				theForm[i].blur();
			}
		}
	}
}

function setValue(objID, objValue) {
	if(findObj(objID)) {
		document.getElementById(objID).value = objValue;
	}
}

function addValue(objID, objValue) {
	if(findObj(objID)) {
		document.getElementById(objID).value += objValue;
	}
}

function resetClassName(inTag, oldClassName, newCLassName) {
	var elements = new Array();
	var elements = document.getElementsByTagName(inTag);
	for (var e = 0; e < elements.length; e++) {
		if (elements[e].className == oldClassName) {
			objId = elements[e].id;
			document.getElementById(objId).className = newCLassName;
		}
	}
}

function validatePwd(ObjAId,ObjBId){
	var myErr = "";
	if(document.getElementById(ObjAId).value != document.getElementById(ObjBId).value){
		myErr = '- Passwords do not match.\n';
		alert('Invalid data entry:\n'+myErr);
	}
	document.returnValue = (myErr == "");
}

function ResetScrollbar() {
	scroll(0,0);
}

function GetScrollTop() {
	var BodyScrollTop = document.body.scrollTop;
	if (BodyScrollTop == 0) {
		if(window.pageYOffset) {
			BodyScrollTop = window.pageYOffset;
		} else {
			BodyScrollTop = (document.body.parentElement) ? document.body.parentElement.scrollTop : 0;
		}
	}
	return BodyScrollTop;
}

function DoFloat() {
	if(findObj('loadingme')) document.getElementById('msgbox').style.top = parseInt(GetScrollTop()) + 'px';
	if(findObj('helper')) document.getElementById('root').style.top = (parseInt(GetScrollTop())+100) + 'px';
}

function Popup(ObjId,BlurObjId) {
	display(ObjId);
	DoFloat();
	document.getElementById(BlurObjId).style.height = document.getElementById('bottomline').offsetTop + 'px';
}

function SubmitForm(FormName, Action) {
	if(findObj('helper')) hidediv('helper');
	Popup('loadingme','waiting');
	if(typeof document.forms[FormName].form != "undefined"){
		document.forms[FormName].form.value = Action;
		document.forms[FormName].submit();
	}else{
		var thisForm = document.getElementById(FormName);
		thisForm.form.value = Action;
		thisForm.submit();
	}
}

function SetOrder(FormName, ByValue, OrderValue) {
	document.forms[FormName].by.value = ByValue;
	document.forms[FormName].order.value = OrderValue;
	document.forms[FormName].submit();
}

function CheckTheBox(ObjId) {
	document.getElementById(ObjId).checked = true ;
}

function CheckTheBoxOnly(ObjId, ObjName) {
	var theElement = document.getElementById(ObjId);
	var theForm = theElement.form;
	for(i=0; i<theForm.length; i++) {
		if(theForm[i].type == 'checkbox' && theForm[i].name.indexOf(ObjName)==0) {
			theForm[i].checked = false;
		}
	}
	theElement.checked = true;
}

function GetCheckedValues(FormName) {
	var theForm = document.forms[FormName];
	var str = "";
	for(i=0; i<theForm.length; i++) {
		if(theForm[i].type=='checkbox' && theForm[i].checked && theForm[i].name!='checkall') {
			str += theForm[i].value + ',';
		}
	}
	return str;
}

function GroupCheckbox(theElement, ObjName) {
	var theForm = theElement.form;
	for(i=0; i<theForm.length; i++) {
		if(theForm[i].type == 'checkbox' && theForm[i].name.indexOf(ObjName)==0) {
			theForm[i].checked = theElement.checked;
		}
	}
}

function ForceGroupCheckbox(theElement, ObjName) {
	var theForm = theElement.form;
	for(i=0; i<theForm.length; i++) {
		if(theForm[i].type == 'checkbox' && theForm[i].name.indexOf(ObjName)==0) {
			theForm[i].checked = true;
		}
	}
}

function CheckSelected(FormName, ObjName) {
	theForm = document.forms[FormName];
	var Counter = 0;
	for(i=0; i<theForm.length; i++) {
		if(theForm[i].type == 'checkbox' && theForm[i].name != 'checkall' && theForm[i].name.indexOf(ObjName)==0 && theForm[i].checked == true) {
			Counter++;
		}
	}
	if(Counter==0) {
		alert('Please select at least one record.');
		return false;
	} else {
		return true;
	}
}

function ChangeTree(ObjId) {
	document.getElementById(ObjId).className = (document.getElementById(ObjId).className=='treeOn') ? 'treeOff' : 'treeOn';
}

function ChangeArrow(ObjId) {
	document.getElementById(ObjId).className = (document.getElementById(ObjId).className=='arrrgt') ? 'arrdwn' : 'arrrgt';
}

function ResetArrow() {
	var args = ResetArrow.arguments;
	for(i=0; i<args.length; i++) {
		if(findObj(args[i])) document.getElementById(args[i]).className = 'arrrgt';
	}
}

function swapLang(firstObjId, secondObjId) {
	var x = document.getElementById(secondObjId).selectedIndex;
	var y = document.getElementById(firstObjId).selectedIndex;
	document.getElementById(firstObjId).selectedIndex = x;
	document.getElementById(secondObjId).selectedIndex = y;
}

function doResize(objectId,size) {
	var obj = findObj(objectId);
	if(obj) {
		var txt = obj.value;
		var arrtxt = txt.split('\n');
		var rowsbyline = arrtxt.length;
		var rowsbytext = Math.ceil(txt.length/size);
		obj.rows = (rowsbytext>rowsbyline) ? rowsbytext : rowsbyline ;
	}
}

function adjustTMTop(sTop) {
	if(document.getElementById('translationPreview').style.display == "none") {
		sTop = sTop - 250;
	}
	return sTop;
}

function moveTMbox(objectId) {
	var xTop = document.getElementById(objectId).offsetTop;
	var sTop = 568;
	sTop = adjustTMTop(sTop);
	document.getElementById('tabTable').style.top = (sTop+parseInt(xTop)) + 'px' ;
}

function catchTab(item,e){
	if(navigator.userAgent.indexOf("Gecko")==0){
		c=e.which;
	} else {
		c=e.keyCode;
	}
	if(c==9){
		replaceSelection(item,String.fromCharCode(9));
		setTimeout("document.getElementById('"+item.id+"').focus();",0);
		return false;
	}
		    
}

function replaceSelection (input, replaceString) {
	if (input.setSelectionRange) {
		var selectionStart = input.selectionStart;
		var selectionEnd = input.selectionEnd;
		input.value = input.value.substring(0, selectionStart)+ replaceString + input.value.substring(selectionEnd);
    
		if (selectionStart != selectionEnd){ 
			setSelectionRange(input, selectionStart, selectionStart + 	replaceString.length);
		} else {
			setSelectionRange(input, selectionStart + replaceString.length, selectionStart + replaceString.length);
		}

	} else if (document.selection) {
		var range = document.selection.createRange();

		if (range.parentElement() == input) {
			var isCollapsed = range.text == '';
			range.text = replaceString;

			if (!isCollapsed)  {
				range.moveStart('character', -replaceString.length);
				range.select();
			}
		}
	}
}

function setSelectionRange(input, selectionStart, selectionEnd) {
	if (input.setSelectionRange) {
		input.focus();
		input.setSelectionRange(selectionStart, selectionEnd);
	} else if (input.createTextRange) {
		var range = input.createTextRange();
		range.collapse(true);
		range.moveEnd('character', selectionEnd);
		range.moveStart('character', selectionStart);
		range.select();
	}
}

function setTitleToSelectedText (select) {
	if (select.selectedIndex > -1) {
		select.title = select.options[select.selectedIndex].text;
	}
}

function goToURL() {
	Popup('loadingme','waiting');
	var i, args=goToURL.arguments;
	document.returnValue = false;
	for (i=0; i<(args.length-1); i+=2) {
		eval(args[i]+".location='"+args[i+1]+"'")
	};
}

function preloadImages() {
	var d=document;
	if(d.images){
		if(!d.MM_p) d.MM_p=new Array();
		var i,j=d.MM_p.length,a=preloadImages.arguments;
		for(i=0; i<a.length; i++) {
			if (a[i].indexOf("#")!=0){
				d.MM_p[j]=new Image;
				d.MM_p[j++].src=a[i];
			}
		}
	}
}

function swapImgRestore() {
	var i,x,a=document.MM_sr;
	for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function swapImage() {
	var i,j=0,x,a=swapImage.arguments;
	document.MM_sr=new Array;
	for(i=0;i<(a.length-2);i+=3)
		if ((x=findObj(a[i]))!=null){
			document.MM_sr[j++]=x;
			if(!x.oSrc) x.oSrc=x.src;
			x.src=a[i+2];
		}
}

function findObj(n, d) {
	var p,i,x;
	if(!d) d=document;
	if((p=n.indexOf("?"))>0&&parent.frames.length) {
		d=parent.frames[n.substring(p+1)].document;
		n=n.substring(0,p);
	}
	if(!(x=d[n])&&d.all) x=d.all[n];
	for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=findObj(n,d.layers[i].document);
	if(!x && d.getElementById) x=d.getElementById(n);
	return x;
}

function setTextOfTextfield(objName,x,newText) {
	var obj = findObj(objName);
	if (obj) obj.value = newText;
}

function jumpMenuGo(selName,targ,restore){
	var selObj = findObj(selName);
	if (selObj) jumpMenu(targ,selObj,restore);
}

function jumpMenu(targ,selObj,restore){
	eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
	if (restore) selObj.selectedIndex=0;
}

function validateForm() {
	var i,p,q,nm,field,test,num,min,max,errors='',args=validateForm.arguments;
	for (i=0; i<(args.length-2); i+=3) {
		field=args[i+1];
		test=args[i+2];
		val=findObj(args[i]);
		if(val) {
			nm=val.name;
			if ((val=val.value)!="") {
				if (test.indexOf('isEmail')!=-1) {
					p=val.indexOf('@');
					if(p<1 || p==(val.length-1)) {
						errors+='- '+field+' must contain an e-mail address.\n';
					}
				} else if(test!='R') {
					num = parseFloat(val);
					if(isNaN(val)) {
						errors+='- '+field+' must contain a number.\n';
					}
					if(test.indexOf('inRange') != -1) {
						p=test.indexOf(':');
						min=test.substring(8,p);
						max=test.substring(p+1);
						if(num<min || max<num) {
							errors+='- '+field+' must contain a number between '+min+' and '+max+'.\n';
						}
					}
				}
			} else if(test.charAt(0) == 'R') {
				errors += '- '+field+' is required.\n';
			}
		}
	}
	if (errors) {
		alert('Invalid data entry:\n'+errors);
	}
	document.returnValue = (errors == '');
}

function validateInput(FormName,InputType,ObjName) {
	var counter=0;
	var theForm = document.forms[FormName];
	for(i=0; i<theForm.length; i++) {
		if(theForm[i].type == InputType && theForm[i].checked && theForm[i].name.indexOf(ObjName)==0) {
			counter++;
		}
	}
	if(counter) {
		return true;
	} else {
		alert('Please select at least one record.');
		return false;
	}
}

function openBrWindow(theURL,objectID,winName,features) {
	var ov = document.getElementById(objectID).value;
	window.open(theURL+ov,winName,features);
}

function openandclose(eid) {
	var element = document.getElementById(eid);
	element.style.display = element.style.display=="none" ? "block" : "none";
}

function showandhide(barId,arrowId) {
	document.getElementById(barId).style.display=(document.getElementById(barId).style.display=="none") ? "block" : "none";
	swaparrow(arrowId);
}

function swaparrow(arrowId) {
	document.getElementById(arrowId).src = (document.getElementById(arrowId).src.indexOf("images/ico_plus.png")>0) ? document.getElementById(arrowId).src.replace("images/ico_plus.png","images/ico_minus.png") : document.getElementById(arrowId).src.replace("images/ico_minus.png","images/ico_plus.png");
}

function showbar(barId,arrowId) {
	document.getElementById(barId).style.display = "block";
	document.getElementById(arrowId).src = "images/ico_minus.png";
}

function hidebar(barId,arrowId) {
	document.getElementById(barId).style.display = "none";
	document.getElementById(arrowId).src = "images/ico_plus.png";
}

function display() {
	var args = display.arguments;
	for(i=0; i<args.length; i++) {
		if(findObj(args[i])) document.getElementById(args[i]).style.display="block";
	}
}

function hidediv() {
	var args = hidediv.arguments;
	for(i=0; i<args.length; i++) {
		if(findObj(args[i])) document.getElementById(args[i]).style.display = "none";
	}
}

function ResetDiv(DivId) {
	document.getElementById(DivId).innerHTML = '<div class="loading"><img src="images/loading.gif" /></div>';
}

function activatetab(listID,divID,otherlistID1,otherdivID1,otherlistID2,otherdivID2) {
	document.getElementById(listID).className="selected";
	document.getElementById(divID).className="";
	document.getElementById(otherlistID1).className="";
	document.getElementById(otherdivID1).className="tabcontent";
	document.getElementById(otherlistID2).className="";
	document.getElementById(otherdivID2).className="tabcontent";
}

function onandoff(textboxID,trID,imgID) {
	if(findObj(trID)) {
		document.getElementById(trID).className=(document.getElementById(trID).className=="off")?"on":"off";
	}
	if(findObj(textboxID) && findObj(imgID)) {
		document.getElementById(imgID).style.display=(document.getElementById(textboxID).value == "")?"none":"";
	}
}

function compareFields(f1,f2,rule,errorMsg){
	var myErr = "";
	if(eval("findObj('"+f1+"').value"+rule+"findObj('"+f2+"').value")){
		alert(unescape(errorMsg));
		myErr += 'errorMsg';
	}
	document.returnValue = (myErr == "");
}

function ShowFlagIcon(flag,icon) {
	var x=document.getElementById(flag).value;
	if(x!=0) {
		document.getElementById(icon).style.display = "";
		document.getElementById(icon).src = 'images/flags/'+x;
	}
	else {
		document.getElementById(icon).style.display = "none";
	}
}

function insertRow(ObjName) {
	var oTarget = document.getElementById("filelist");
	var oDiv, oInput;
	oDiv = document.createElement("DIV");
	oInput = document.createElement("INPUT");
	oInput.setAttribute("type","file");
	oInput.setAttribute("class","input");
	oInput.setAttribute("onfocus","this.className='inputOn'");
	oInput.setAttribute("onblur","this.className='input'");
	oInput.setAttribute("name",ObjName);
	oInput.setAttribute("id",ObjName);
	oInput.setAttribute("size","30");
	oDiv.appendChild(oInput);
	oTarget.appendChild(oDiv);
	oInput.focus();
}

function splitPara(FormName, ObjId, RefId) {
	theForm = document.forms[FormName];
	var Counter = 0;
	var ObjName = ObjId + "[" + RefId + "]";
	for(i=0; i<theForm.length; i++) {
		if(theForm[i].tagName == 'TEXTAREA' && theForm[i].name.indexOf(ObjName)==0) {
			Counter++;
		}
	}
	var TextareaId = ObjId + "[" + RefId + "][" + Counter + "]";
	var oTarget = document.getElementById(ObjId + '_' + RefId);
	var oDiv, oInput;
	oDiv = document.createElement("DIV");
	oInput = document.createElement("TEXTAREA");
	oInput.setAttribute("class","input");
	oInput.setAttribute("name",TextareaId);
	oInput.setAttribute("name",TextareaId);
	oInput.setAttribute("rows","1");
	oInput.setAttribute("cols","80");
	oInput.setAttribute("onfocus","this.className='inputOn';onandoff('" + TextareaId + "','paraRow_" + RefId + "','statusChecker_" + RefId + "');doResize('" + TextareaId + "',120);");
	oInput.setAttribute("onblur","this.className='input';onandoff('" + TextareaId + "','paraRow_" + RefId + "','statusChecker_" + RefId + "');");
	oDiv.appendChild(oInput);
	oTarget.appendChild(oDiv);
	oInput.focus();
}
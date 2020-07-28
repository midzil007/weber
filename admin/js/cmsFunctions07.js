function getUrl(u){
	location.href=u;	
}

function hideSelects() {
	var allSelects = document.getElementsByTagName('select');  
	for (var i = 0; i < allSelects.length; i++) {			
		allSelects[i].style.visibility = 'hidden';		
	}
}

function showSelects() {
	var allSelects = document.getElementsByTagName('select'); 	
	for (var i = 0; i < allSelects.length; i++) {
		allSelects[i].style.visibility = 'visible';		
	}
}

function checkAll(form, state) {	
	fObj = dojo.byId(form);	
	for (i=0; i<fObj.elements.length; i++) {
		e = fObj.elements[i];
		if (e.type == 'checkbox') {
			e.checked = state;	
		}		
	}	
}

function getModal(){
	return dijit.byId('modalWindow');
}

function getModalWindow(no){
	if(!no){
		return getModal();
	} else {
		return dijit.byId('modalWindow' + no);
	}
	return dijit.byId('modalWindow');
}


function setPushState(currentUrl, arrayPushState,idFlexi)
	{
	 url = '';
	 	for	(index = 0; index < arrayPushState.length; index++) {
    			element = arrayPushState[index];    			 
    			 if ($("#"+element).val()) {
    			 	url = url+$("#"+element).attr('name')+'/'+$("#"+element).val()+'/';
 
    			 }
			}	 		
			history.pushState(null, null, currentUrl+url);
			$(idFlexi).flexOptions({ url:  currentUrl+'ajax/1/getItems/1/'+url});

			$(idFlexi).flexReload();
	 }

function showModal(title, template, no, width, height, contentLoaded){
	mwindow = getModalWindow(no);
	mwindow.titleNode.innerHTML = title;	
	
	if(!width){
		var ww = screen.width;
		if(ww < 800){
			width = 800;		
		} else if(ww < 1100) {
			width = 950;
		} else if(ww < 1300) {
			width = 950;
		} else {
			width = 950;
		}	
	}
	
	if(contentLoaded){
		
	} else {
		mwindow.setHref(template);	
	}
	
	var sizes = new sizer(width, height);	
	mwindow.resize(sizes);
	
	mwindow.show();
	return false;
}

function sizer(width, height, top, left) {
  this.w = width;
  this.h = height;
  this.t = top;
  this.l = left;
}



function refreshTab ( tabId, newUrl ) {
	//alert(tabId);
	//alert(newUrl);
	
	if(newUrl){
		dijit.byId(tabId).setHref(newUrl);
	} else {
		dijit.byId(tabId).refresh();
	}
	return false;
}

function setWysiwygContent( hiddenInputId ){ 

	
	try
	{ 
		dojo.byId(hiddenInputId).value = tinyMCE.get(hiddenInputId).getContent(); 
		return true;
	}
	catch(err)
	{
		try 
		{
			tinyMCE.triggerSave();
			//dojo.byId(hiddenInputId).value = tinyMCE.get(hiddenInputId).getContent(); 
			return true;
		}
		catch(err) 
		{
			return 1;//  confirm('HTML se nepodařilo uložit. Doporučujeme text zkopírovat a stránku znovu zeditovat (Ne). Přesto uložit a zavřít toto okno?');			
		}	
	}	
}

function uploadFile(refreshUrl, newVersion, directUpload, callbackInput){
	
	var callbackInput = callbackInput;
	dojo.style(dojo.byId('inputField'),"display","none");
	dojo.style(dojo.byId('progressField'),"display","inline"); 
	
	if(newVersion){
		action = "/cms/sf/uploadFileVersion";
	} else {
		action = "/cms/sf/uploadFile";
	}
	dojo.io.iframe.send({
		url: action,
		method: "post",
		handleAs: "text",
		form: dojo.byId('uploadForm'),
		handle: function(data,ioArgs){
			//alert(data);
			var foo = dojo.fromJson(data);
			dojo.byId("err").innerHTML = foo.html;
			
			if (foo.status == "success"){
				dojo.style(dojo.byId('inputField'),"display","inline");
				dojo.byId('fileInput').value = '';
				//dojo.byId('descr').value = '';
				
				dojo.style(dojo.byId('progressField'),"display","none"); 
				//refreshTab('fileSystem',0);
				if(directUpload){
					SetNWUrl( callbackInput, foo.value, foo.value2);
					setTimeout("getModalWindow(2).hide()",500);
				} else {
					if(!newVersion){
						setTimeout("getModal().hide()",800);
						setTimeout(
							function() { 
								dijit.byId('vypisDole').setHref(refreshUrl);
							}, 1500);
					} else {
						setTimeout("getModalWindow(2).hide()",800);
					}
				}
				//dojo.byId('uploadedFiles').innerHTML += "success: File: " + foo.details.name + " size: " + foo.details.size +"<br>"; 		
			}else{				
				dojo.style(dojo.byId('inputField'),"display","inline");
				dojo.style(dojo.byId('progressField'),"display","none"); 
			}	
		}
	});
	return false;
}
function addAdvert(action, advertPositionId){  
	$.post( action, function( data ) {
	  $( "#pos_" + advertPositionId ).append( data );
	});

	return false;
}
 
function removeAdvert(aTableId){
	removeElById(aTableId);
}

// Remove functions
var removeEl = function (el) {
    el.parentNode.removeChild(el);
}

var removeElById = function (id) {
    var el = document.getElementById(id);
    el.parentNode.removeChild(el); // or instead of this line, put removeEl(el)
}

function submitFormAjax( submitForm, submitUrl, parentTab, type, extra, usePost) {
	var kw = { 
		url: submitUrl,
		load: function(data){ 
			data = new String(data);
			
			switch(type)
			{		
			case 'modal-refresh':
			  	dojo.byId("modalContent").innerHTML = data;
			  	break
			case 'modal-setHref':
				getModal().setHref(extra+data);
				break
			case 'tab-sethref':				
				dijit.byId(parentTab).setHref(extra+data);
				break
			case 'setSelectedPages':
				data = dojo.fromJson(data);			
				dojo.byId("err_" + parentTab).innerHTML = data.html;
				inputName = data.value3;
				dojo.byId(inputName+"_title").value = data.value;
				dojo.byId(inputName).value = data.value2;
				setTimeout("getModalWindow(2).hide()",500);
				break
				
			case 'setMailRecipients':
				data = dojo.fromJson(data);		
				//alert(data);		
				//alert(data.value);				
				dojo.byId("err_" + parentTab).innerHTML = data.html;
				dojo.byId("recipients").value = data.value;
				dojo.byId("recipientsCount").innerHTML = '(celkem:' + data.value2 + ')';
				break
				
			case 'tab-submit-refresh':	 
				if(data.length > 0 ){  
					data = dojo.fromJson(data);	 
				}  
				dijit.byId(parentTab).refresh();   
				break;
			
			case 'tab-submit':	
			case 'tab-submit-close':	
			case 'modal':	
			default:
							
				//alert(data);		
				data = dojo.fromJson(data);
				
				if(data.status == '1'){
					dojo.byId("err_" + submitForm).innerHTML = data.html;
					if(type != 'tab-submit'){
						setTimeout("getModal().hide()",1000);
					}						
					if(data.redirectPage){
						setTimeout(function() {window.location.href=data.redirectPage;}, 1900);								
					} else if(data.redirectTabUrl){
						setTimeout(function() {dijit.byId(parentTab).setHref(data.redirectTabUrl);}, 1900);									
					} else {
						var counter = 0;
						parentTabs = parentTab.split(',');		  
						for(i=0; i < parentTabs.length; i++){		
							setTimeout(
								function() { 
									var t = parentTabs[counter]; dijit.byId((t)).refresh(); counter++;
								}, 1900);
						}
					}
				} else {
					dojo.byId("err_" + submitForm).innerHTML = data.html;	
				}
			  break    
			}
			
			
		},
		error: function(data){
				alert("Nastala chyba.: " + data + ' Prosím zkuste akci znovu, nebo kontaktujte administrátora systému.');
		},
		timeout: 90000,    
		form: (submitForm) 
	};
	if(usePost){
		dojo.xhrPost(kw);
	} else {			
		dojo.xhrGet(kw);
	}
	return false;
}

var FileBrowserDialogue = {
    init : function () {
        // Here goes your code for setting your custom things onLoad.
    },
    mySubmit : function (fileUrl) {
        var URL = fileUrl;
		parent.tinymce.activeEditor.windowManager.getParams().setUrl(fileUrl);
		parent.tinymce.activeEditor.windowManager.close();
    }
}
 
function OpenFile( fileUrl )
{
	FileBrowserDialogue.mySubmit(fileUrl);
}
  
function OpenFileNW( inputId, title, fileUrl )
{

	try
	{
			
		FileBrowserDialogue.mySubmit(fileUrl);   
	}
	catch(err)
	{
		try
		{
			
			window.top.opener.SetNWUrl( inputId, title, encodeURI( fileUrl ) ) ;
			window.top.close();
			window.top.opener.focus() ;	
		}
		catch(err)
		{
			
		}
	}
}

function openFileUploader(title, url){
	showModal(title, url, 2, 550);
}

function openFileUploaderNew(title, url){
	showModal(title, url, 3, 550, false, true);
}

function openMultiPageSelect(title, url){
	showModal(title, url, 2, 350);
}

function SetNWUrl( inputId, title, fileUrl ){
	try
	{
		dojo.byId(inputId + "_title").value = trim(title);
		dojo.byId(inputId).value = trim(fileUrl);
	}
	catch(err)
	{
		
	}
	
}

function trim(str)
{
   return str.replace(/^\s*|\s*$/g,"");
}

function mOver( el ){
	el.style.background='#eef3f7';
}

function mOut( el ){
	el.style.background='#ffffff';
}

function confirmSubmit() {
	var agree=confirm('Opravdu smazat ?');
	if (agree){
		return true;
	} else {
		return false;
	}
}

function confirmSubmit2(txt) {
	var agree=confirm(txt);
	if (agree){
		return true;
	} else {
		return false;
	}
}

/* module help */
function showHelp(url) {
	ak_pop = makePopup(-1,-1,'helpPop', url,'helpPop',900,550,'scrollbars=auto,resizable=1');		
	ak_pop.focus();
	return false;
}

/* FILES */
function showUploader(url, trigger) {
	ak_pop = makePopup(-1,-1,'helpPop', url,'uploader',550,350,'scrollbars=no,resizable=1');		
	ak_pop.focus();
	return false;
}

window.saveAndClose = function(uploadedNames, uploadedPaths){	
	tNo = window.triggerNo;
	tName = window.triggerName;
		
	for(i = 0; i < uploadedNames.length; i++){
		//alert(uploadedNames[i] + " - " + uploadedPaths[i]);
		inputId = tName + "_fileSelect" + tNo;
		tNo++;
		//window.triggerNo = $fileNumber; window.triggerName 
		showNextOption( tName + '_filerow', tName + '_fileSelect', tName + '_filesCount');
		name = new String(uploadedNames[i]);
		n = name.substring(0, (name.length - 4));
		SetNWUrl(inputId, n, uploadedPaths[i]);		 
	}
}

/* window open */
function makePopup(windowPosX,windowPosY,windowName,url,name,w,h,extra){
	var titlebarHeight = 28;
	str="height="+h+",width="+w+","+extra;
	if(parseInt(navigator.appVersion)>3) // supports screen.width
		if (windowPosX == -1) {
			str+=",left=" + (screen.width -w)/2 + ",top=" + parseInt(((screen.height -h)-titlebarHeight)/2);
		}
		else {
			str+=",left="+windowPosX+",top="+windowPosY;
		}
	var k = eval(windowName + "=window.open('" + url + "','" + name + "','" + str + "')");
	return k;
}

function openFileBrowser( callBackInput ) {
	ak_pop = makePopup(-1,-1,'tab', '/cms/sf/index/isPopup/1/nowysiwyg/1/callBackInput/' + callBackInput, 'tab',950,600,'scrollbars=no');		
	ak_pop.focus();
	return false;
}


function openPageBrowser( callBackInput ) {
	ak_pop = makePopup(-1,-1,'tab', '/cms/pages/index/isPopup/1/nowysiwyg/1/isPageSelectPopup/1/callBackInput/' + callBackInput, 'tab',950,600,'scrollbars=no');		
	ak_pop.focus();
	return false;
}

function showTerm(url) {
	ak_pop = makePopup(-1,-1,'tab', url,'tab',760,300,'scrollbars=auto');		
	ak_pop.focus();
	return false;
}

function openWindow( url, width, height ) {
	ak_pop = makePopup(-1,-1,'www', url, 'www',width,height,'scrollbars=no');		
	ak_pop.focus();
	return false;
}


/******** prace s inputy **************/

function remove_txt(txt,input) {
	if(input.value == txt){
		input.value = "";
	}
}

function set_txt(txt,input) {
	if(input.value == ""){
		input.value = txt;
	}
}

/**
  * Inserts multiple fields.
  *
  */
function selectStackItems(myListBox, myQuery) {  
    if(myListBox.options.length > 0) {
        box_locked = true; 
        var chaineAj = "";
        var NbSelect = 0;
        for(var i=0; i<myListBox.options.length; i++) {
            if (myListBox.options[i].selected){
                NbSelect++;
                if (NbSelect > 1)
                    chaineAj += ", ";
                chaineAj += myListBox.options[i].value;
            }
        }

        //IE support
        if (document.selection) {
            myQuery.focus();
            sel = document.selection.createRange();
            sel.text = chaineAj;
            document.sqlform.insert.focus();
        }
        //MOZILLA/NETSCAPE support
        else if (myQuery.selectionStart || myQuery.selectionStart == "0") {
            var startPos = myQuery.selectionStart;
            var endPos = myQuery.selectionEnd;
            var chaineSql = myQuery.value;  

            myQuery.value = chaineSql.substring(0, startPos) + chaineAj + chaineSql.substring(endPos, chaineSql.length);
        } else {
            myQuery.value += chaineAj;
        }
        box_locked = false;
    }
}
  
function switchButtons()
{
	try
	{ 
		var btn = dijit.byId('saveContentB2').domNode;
		dojo.style(btn, {visibility:'visible'});
	}
	catch(err) {}
	try
	{
		var btn = dijit.byId('updateStr2').domNode;
		dojo.style(btn, {visibility:'visible'});
	}
	catch(err) {}
	try
	{
		var btn = dijit.byId('saveContentB').domNode;
		dojo.style(btn, {visibility:'hidden'});
	}
	catch(err) {}
	try
	{
		var btn = dijit.byId('updateStr').domNode;
		dojo.style(btn, {visibility:'hidden'});
	}
	catch(err) {}	
}



function removeAllEditors () {    
    var i;
	
    for (i = 0; i < tinyMCE.editors.length; i++) { 
		ta = tinyMCE.editors[i].getElement();    
		//tinyMCE.execCommand('mceFocus', false, ta.id); 
		tinymce.EditorManager.execCommand('mceRemoveControl', true, ta.id);               
		tinyMCE.execCommand('mceRemoveControl', true, ta.id);  
        //  tinyMCE.editors[i].remove(); 
		// tinyMCE.editors[i].destroy() // or destroy() ?
    }for (i = 0; i < tinyMCE.editors.length; i++) { 
		ta = tinyMCE.editors[i].getElement();   
		//tinyMCE.execCommand('mceFocus', false, ta.id);  
		tinymce.EditorManager.execCommand('mceRemoveControl', true, ta.id);                      
		tinyMCE.execCommand('mceRemoveControl', true, ta.id);  
        //  tinyMCE.editors[i].remove(); 
		// tinyMCE.editors[i].destroy() // or destroy() ?
    }for (i = 0; i < tinyMCE.editors.length; i++) { 
		ta = tinyMCE.editors[i].getElement();   
		//tinyMCE.execCommand('mceFocus', false, ta.id);       
		tinymce.EditorManager.execCommand('mceRemoveControl', true, ta.id);                  
		tinyMCE.execCommand('mceRemoveControl', true, ta.id);  
        //  tinyMCE.editors[i].remove(); 
		// tinyMCE.editors[i].destroy() // or destroy() ?
    } 
} 
function showNextOption( prefix, inputPrefix, hiddenId )
{
	var optionsCount = new Number(dojo.byId(hiddenId).value);			
	nOpt = optionsCount+1;
	var nextOption = dojo.byId(prefix + nOpt);	
	if(nextOption){	
		nextOption.style.display = 'block';
		dojo.byId(hiddenId).value = nOpt;
	}
	return false;
}

function saveOptions( formEl, prefix, prefixDenied, saveInputId )
{
	
	//  alert(saveInputId); 
	var values = new Array();
	var x = 0;
	for (i=0; i<formEl.elements.length; i++) {
		e = formEl.elements[i];	 
		//alert(e.id);	
		if(e.id.search(prefix) > -1 && e.id.search(prefixDenied) == -1 && e.value){
			 
			if(e.id.search('_alt') > -1 || e.id.search('_url') > -1 ){ // to ukladam do meta
				
			} else {
				x++;
				values[x] = e.value;
			}
		}		
	}
	
	if(!values.join(';')){ 
		var values = new Array(); 
		var x = 0;
		for (i=0; i<formEl.elements.length; i++) {
			e = formEl.elements[i];	
						
			if(e.id.search(prefix) > -1  && e.id.search(prefixDenied) == -1 && e.value){
				//alert(e.id + " - " + e.value);
				if(e.id.search('_alt') > -1 || e.id.search('_url') > -1 ){ // to ukladam do meta
				
				} else {
					x++;  
					values[x] = e.value;
				}
			}		
		}
	} else {
		
	}
	 
	 //alert(values.join(';'));
	i = $( "#" + saveInputId );  
	document.getElementById(saveInputId).value = values.join(';');
	 
	//  alert(values.join(';'));    
	
	return false;
}

function removeOption( prefix, inputPrefix, id )
{
	var optionId = id.substr(6,2);			
	// radek
	
	var option = dojo.byId(prefix + optionId);
	option.style.display = 'none';
	//input
	
	
	var optionInput = dijit.byId(inputPrefix + optionId);
	var optionInputTitle = dijit.byId(inputPrefix + optionId + "_title"); // title
	if(optionInput){
		optionInput.setValue('');	
		optionInput.setValue('');	
	} else {
		var optionInput = dojo.byId(inputPrefix + optionId);
		var optionInputTitle = dojo.byId(inputPrefix + optionId + "_title"); // title
		optionInput.value = '';	
		optionInputTitle.value = '';	
	}
			
	return false;
}



function addMultipleItem( textDivId, hiddenId, TextToAdd, ValueToAdd )
{
	
	if(TextToAdd && ValueToAdd){
		
		var prevValue = dojo.byId(hiddenId).value;
		var values = prevValue.split(';');
		for(i=0; i < values.length; i++){		
			if(values[i] == ValueToAdd){
				alert('Pložka již existuje');
				return false;
			}
		}
		
		dojo.byId(textDivId).innerHTML = dojo.byId(textDivId).innerHTML + TextToAdd;			
		dojo.byId(hiddenId).value = dojo.byId(hiddenId).value + ";" + ValueToAdd;
	}
	return false;
}

function removeMultipleItem( hiddenId, textLiId, ValueToRemove )
{				
	var prevValue = dojo.byId(hiddenId).value;			
	var values = prevValue.split(';'); 
	var newValues = new Array();
	for(i=0; i < values.length; i++){
		alert(values[i] + " - " + ValueToRemove);
		if(values[i] != ValueToRemove){
			newValues[i] = values[i];
		}
	}
				
	dojo.byId(textLiId).style.display = 'none;';
	dojo.byId(hiddenId).value = newValues.join(';');
	
	return false;
}

function moveFile( mover, inputPrefix, direction )
{		
	row = new Number(mover.attributes.no.value);
	txt = dojo.byId(inputPrefix + row + "_title").value;
	val = dojo.byId(inputPrefix + row + "").value;
	
	switch(direction){
		default:
		case 'up':	
			if(row > 1){
				row2 = row - 1;						
			}
			break;
			
		case 'down':
			if(row < 50){
				row2 = row + 1;						
			}
			break;
	}
	
	txt2 = dojo.byId(inputPrefix + row2 + "_title").value;
	val2 = dojo.byId(inputPrefix + row2 + "").value;
	
	dojo.byId(inputPrefix + row + "_title").value = txt2;
	dojo.byId(inputPrefix + row + "").value = val2;
	dojo.byId(inputPrefix + row2 + "_title").value = txt;
	dojo.byId(inputPrefix + row2 + "").value = val;
				
				
	return false;
}
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

function openPop(url,width,height) {
	ak_pop = makePopup(-1,-1,'tab', url,'tab',width,height,'scrollbars=yes');		
	ak_pop.focus();	
}



function openPhotogallery(url) {
	ak_pop = makePopup(-1,-1,'tab', url,'tab',600,800,'scrollbars=yes');		
	ak_pop.focus();	
	return false;
}

function bookmarkPage(title,url){
	if (window.sidebar){ // firefox
		window.sidebar.addPanel(title, url, url);
	} else if(window.opera && window.print){ // opera
		var elem = document.createElement('a');
		elem.setAttribute('href',url);
		elem.setAttribute('title',title);
		elem.setAttribute('rel','sidebar');
		elem.click();
	}
	else if(document.all) {// ie
		window.external.AddFavorite(url, title);
	}
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

function CheckAll(f,el) {
	for (var i=0;i<f.elements.length;i++)
	{
		var e=f.elements[i];
		if (e.name==el) e.checked=f.check_all.checked;
	}
}

/** images*/

function loadImage(imgId, newSrc, preload){
	objImage = new Image();
	objImage.onLoad = imageLoaded(imgId, newSrc);
	objImage.src = newSrc;
	
	if(preload){
		img = document.getElementById(imgId);
		img.src = preload;
		img.className = 'preloadImg'
	}
	return false;
}

function imageLoaded(imgId, newSrc){
	img = document.getElementById(imgId);
	this.newSrc = newSrc;
	setTimeout("img.src = this.newSrc; img.className = ''",1000);
}


/** blocks */
function manageBlock(trigger, blockId){	
	block = document.getElementById(blockId);
	switch(trigger.className){
		case 'plus':	
			block.style.display = 'block';		
			trigger.className = 'minus';	
			break;
		case 'minus':	
			block.style.display = 'none';		
			trigger.className = 'plus';	
			break;
	}
}



var zIndex = 9;
var currentTip = 1;
var maxx = 0;
function showTip(tNo){
    for(i = 1; i <= maxx; i++){
        try{
            //$('tip' + i).setStyle('display', 'none'); 
            $('tipTrigger'  + i).removeClass('active'); 
        } catch(err) {
			
        }
    }  
	
    currentTip = tNo;
	     
    try{ 
        tip = $('tip' + tNo);
        tip.fade('hide');  
        tip.setStyle('z-index', zIndex++);  
        tip.fade('in');  
		 
        $('tipTrigger'  + tNo).addClass('active');
    } catch(err) { 
    }  
    return false; 
	  
}

function slideTips(){
	if(currentTip == maxx){
 		currentTip = 0;  
	} 
	showTip(currentTip + 1); 
}

var periodical; 

function showLog(bid,height,lock,lockHeight)
{
	block = $(bid);  
	lock = $(lock);
	if( block.getStyle('height')  == '0px'){
		lockHeight = 0;
		lock.setStyle('overflow','hidden');
		$$('.upArr1').setStyle('display','inline');
		$$('.downArr1').setStyle('display','none');
	} else {  
		height = 0; 
		lock.setStyle('overflow','inherit');
		$$('.upArr1').setStyle('display','none');
		$$('.downArr1').setStyle('display','inline');
	}
	
	block.tween('height', height); 
	lock.tween('height', lockHeight); 
	return false; 	
	}

function showBlock(bid, height){

	block = $(bid);  
	if( block.getStyle('height')  == '0px'){
		$$('.upArr').setStyle('display','inline');
		$$('.downArr').setStyle('display','none');
	} else {  
		height = 0; 
		$$('.upArr').setStyle('display','none');
		$$('.downArr').setStyle('display','inline');
	}
	
	block.tween('height', height); 
	return false; 
}


function showBlock2(bid, height){
	block = $(bid);  
	if( block.getStyle('height')  == '0px'){
		}
		else{
			height = 0; 	
			}
	block.tween('height', height); 
	return false; 
}

function showBlock3(bid, height, maxheight)
{
		block = $(bid);  
		block.setStyle('overflow','hidden');
		var size = block.getSize();
		size.y = size.y;
		if(size.y>height)
		{
			block.tween('height', height); 
			$('allPhoto').set('text','Zobrazit celou fotogalerii');
		}
		else
		{
			block.tween('height', maxheight); 
			$('allPhoto').set('text','Zobrazit pouze 3 fotografie');
			}
			return false; 
		
}
function showImage(path, title, href, id){
	img = $$('.detailImg');
	a = $$('.mbb');

	var i = 1;
	a.each(function(el){
					if(i == id){
						el.removeClass('disable');
						
						}
					else{
					    el.addClass('disable');
						}
						i++;
	});

	img.src = path;
	img.alt = title;
	a.href = href;
	return false; 
}


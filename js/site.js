window.addEvent('domready', function() {
	 Cufon.replace('h1,h2,h3,h4,h5,.menu,.heading,#slider a.more', {
                    fontFamily: 'HelveticaNeueLTProCn',
                    hover: true
                });
			});

 
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
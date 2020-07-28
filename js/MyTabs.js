function showTab(tId, show){
	for(i = 1; i <= 15; i++){ 
		try{
			$('ll' + tId + i).removeClass('active'); 
		} catch(err) {
			
		}
	} 
	
	for(i = 1; i <= 15; i++){
		try{
			$('t' + tId + i).fade('hide');  
			$('t' + tId + i).setStyle('display', 'block');    
			
		} catch(err) {
			 
		}
	}
	var query = location.href.split('#');
	if(query[1] || show!=1){
		history.pushState('', '', '#tab'+show);
	}
	var container = $('tabContentContainer');  
	var	abox= $('t' + tId + show);
	// console.log(tId);
	var size = abox.getSize();  

	var height = size.y;
	if(height < 150 ){ 
		height = 150;	
	}  
	container.tween('height', height); 
	abox.fade('in');    

	$('ll' + tId + show).addClass('active'); 
	  
	return false; 
	
} 
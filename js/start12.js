var logShown = false;
window.addEvent('load', function(){
	var padding = {x: 40, y: 40};
	var Tips3 = new Tips($$('.botip'), {
		showDelay: 0,  
		hideDelay: 100,  
		fixed: false,
		windowPadding : padding
	});	   
	
}); 
	
window.addEvent('domready', function(){
	//$('loginform').setStyle('display', 'block'); 
	//$('loginform').fade('hide'); 
	  
	
	try{	
		var lt = $('logintrigger'); 
		lt.addEvent('click', function() {  
			 if(logShown){					 
			 	hideLogin();
			 } else { 
				showLogin();				 
			 } 
		});  
		  
	} catch(err) {
			
	} 
	
	var txt;
	
	
	$$('.orderForm').each(function(f){ 
								 
		if(f){
		var action = f.get('action');
		if(!action){  
			action = '/nakupni-kosik'; 
		}
		    
		 	btn = f.getElement('input.addToBasket');
			btn.addEvent('click', function(e) {			  
				new Event(e).stop(); 
				var req = new Request({			 	
					 method: 'post', 
					 url: action + "?ajax=1",   
					 data: f,   
					 onRequest: function() { 
						var scroll = new Fx.Scroll(window, { wait: false, duration: 900, transition: Fx.Transitions.Quad.easeInOut }); 
						scroll.toElement('header');   
					 },
					 onComplete: function(response) {  
						txt = $('binto');
						(function(){  
							txt.set('html', response);   
							
							txt2 = $('bpc');    
							txt2.fade('hide');
							txt2.fade('in');  
								
							vt = $('visibletrigger');     
							vt.addEvent('click', function(e) {
								$('visiBlock').removeClass('active');
								$('visiBlock').addClass('hide');  
								(function(){  $('visiBlock').removeClass('hide'); }).delay(1000);	
							});	
														  
						}).delay(1000);		
						
					 }     
			 }).send(); 
	
			});
		
		}
	});	
	
	try{		
	
		vt = $('visibletrigger');     
		vt.addEvent('click', function(e) {
			$('visiBlock').removeClass('active');
			$('visiBlock').addClass('hide');  
			(function(){  $('visiBlock').removeClass('hide'); }).delay(1000);	
		});	
		
		  
	} catch(err) { 
		
	}
	
	try{		
	 
		var inputWord = $('searchInput');   
		if(inputWord){ 
			new Autocompleter.Request.HTML(inputWord, '/?autocomplete=1', { 
				'indicatorClass': 'autocompleter-loading', autoSubmit: true, forceSelect: true, 
				onSelection: function(){$('searchTopForm').submit();} 
			});  
		}       
		//var  fc = new FormCheck('loginForm'); 
		 
		 
	} catch(err) {
			
	} 
});   

function hideLogin(){
	
		var lb = $('loginbody'); 
	$$("#login ul").setStyle('display', 'none');
		var basket = $('binto');
	basket.fade('in');
	logShown = false;  
} 

function showLogin(){ 
	
	var lb = $('loginbody'); 
	$$("#login ul").setStyle('display', 'block');
	var basket = $('binto');
	 logShown = true; 
	 basket.fade('hide');  
	 lb.addClass('shown'); 
}

function submitBasket(pid){ 	  
	
	$('bpid').set('value', pid);  
	
	f = $('orderFormBasket'); 
	var action = f.get('action');
	 
	var req = new Request({			 	
		 method: 'post', 
		 url: action + "?ajax=1",   
		 data: f,   
		 onRequest: function() { 
			   
		 },
		 onComplete: function(response) {  
			txt = $('binto');  
		 
			txt.set('html', response);   
			
			
			txt2 = $('bpc');   
			if(txt2){
				txt2.fade('hide');
				txt2.fade('in');  
			} 
				
			vt = $('visibletrigger');  
			if(vt){
				vt.addEvent('click', function(e) {
					$('visiBlock').removeClass('active');
					$('visiBlock').addClass('hide');  
					(function(){  $('visiBlock').removeClass('hide'); }).delay(1000);	
				});	 
			} 
			
		 }     
 	}).send();    
	
	return false; 
}
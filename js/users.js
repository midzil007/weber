function showLogin(){
	$('loginform').fade();
	return false; 
	
} 
window.addEvent('domready', function(){
	$('loginform').setStyle('display', 'block'); 
	$('loginform').fade('hide'); 
});   


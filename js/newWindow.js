window.addEvent('load', function(){
	ref = document.getElements('.nw');
	ref.each(function(el)
		{	
		el.setProperty('target','_blank');
		});
	var txt = document.body.innerHTML;
	var re2=/\[at\]/g; 
	var result2=txt.replace(re2,"@");       
	document.body.innerHTML = result2;
});

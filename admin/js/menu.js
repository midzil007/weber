var logUserWidth = 245;
var arrowWidth = 20;
var menuItemWidth = 91;	

var menuEl = dojo.byId('nav');

function resizeMenu(){
	try {
		var menuNecessaryWidth = menuItemsCount * menuItemWidth;			
		var vpObject = dijit.getViewport(menuEl);
		menuWidth = vpObject.w - logUserWidth;
		var displayableItemsCount = Math.floor(menuWidth / menuItemWidth); 
								
		for (i=0; i < menuItemsCount; i++)
		{
			if (i < displayableItemsCount){
				 dojo.byId('mis' + i).style.display = 'none';
				 dojo.byId('mi' + i).style.display = 'block';
			} else {
				dojo.byId('mi' + i).style.display = 'none';
				dojo.byId('mis' + i).style.display = 'block';
			}				
		}
					
		if(menuNecessaryWidth > menuWidth){
			//alert('malo mista na menu :)');
			leftPos = displayableItemsCount * menuItemWidth;
			dojo.byId('ddMenuTriger').style.display = 'block';
			dojo.byId('ddMenuTriger').style.left = ( leftPos + 5) + "px";
			dojo.byId('dropdown').style.left = (leftPos - 5) + "px";
		} else {
			dojo.byId('ddMenuTriger').style.display = 'none';
		}
		//alert(menuWidth);
	} catch(err)	{	}	
}
//dojo.connect(window,'onresize',"document.ResizeMenu()");
var ddTimeout;
function showDDMenu(){
	dojo.byId('dropdown').style.display = 'block';
	try { clearTimeout(ddTimeout) }
	catch(err)	{	}	

	
}

function hideDDMenu(){
	ddTimeout = setTimeout("dojo.byId('dropdown').style.display = 'none';", 500);			
}

window.onresize = resizeMenu;	
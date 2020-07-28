function showIterator( prefix )
{
	var i = 1;
	while(dojo.byId(prefix + i) != null)
	{
		obj = dojo.byId(prefix + i);
		if( obj.style.display == 'none' )
		{
			obj.style.display = '';
			break;
		}
		i++;
	}
	
	return false;
}

function orderChange( prefix, postfix , id)
{
	obj = dojo.byId(prefix + id + postfix);
	selected = obj.selectedIndex;
	var i = 1;
	while(dojo.byId(prefix + i + postfix) != null)
	{
		if(i == id)
		{
			i++
			continue;
		}
		obj = dojo.byId(prefix + i + postfix);
		if( obj.selectedIndex >= selected)
			obj.selectedIndex++;
		i++;
	}
}

function copyInput(from, to){
	obj = dojo.byId(from);
	obj2 = dojo.byId(to);
	if(!obj2.value)
		obj2.value = obj.value;
	return false;
}
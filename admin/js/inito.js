//dojo.require("dojo.data.ItemFileWriteStore");		 
dojo.require("dojox.widget.FileInput"); 
dojo.require("dojo.io.iframe"); 
dojo.require("dijit.ProgressBar"); 
dojo.require("dijit.form.NumberTextBox"); 

/* ***** DOJO BUILD HOWTO  */
//build.bat profile=cms action=release optimize=comments copyTests=false
/*****************************/
		
dojo.addOnLoad(function(){			
	//dojo.byId('loaderInner').innerHTML += " OK ";			
	//setTimeout("hideLoader()",250);		
			
	try { dojo.byId('menu').style.visibility = "visible"; }
	catch(err)	{	}	
	
	dojo.subscribe("maintree", null, function(message){
		if(message.event=="execute"){	
			getNodeUrl(structure.getValue(message.item, treeItemIdentifier));
		}
	});					
});


//dojo.addOnLoad(showSelects());

setTimeout("refreshSession()", 1000 * 60 * 10 )
function refreshSession(){
	setTimeout("refreshSession()", 1000 * 60 * 10)
	
	dojo.xhrGet( { //        
        url: "/cms/index/index", 
        handleAs: "text",
        timeout: 50000, // Time in milliseconds
        load: function(response, ioArgs) {             
        	return response; 
        },
        error: function(response, ioArgs) { //          
          return response; // 
          }
        });
}

function hideLoader(){
	showSelects();
	var loader = dojo.byId('loader'); 
	loader.style.display = "none"; 
	/*		
	var loader = dojo.byId('loader'); 
		//dojo.connect(loader, 'onEnd', 'alert');
	

	dojo.fadeOut({ node: loader, duration:500,
		onEnd: function(){ 
			showSelects();
			loader.style.display = "none"; 
		}
	}).play();
	*/
}

dojo.declare(
	"AdvancedTree",
	dijit.Tree,
{
	path: '',
	curentNode: '',
	getPath: function (node, separator) {
		var path = separator;
		do {
			path = separator + this.tree.store.getIdentity(node.item) + path;
			node = node.getParent();
		} while ('dijit._TreeNode' == node.declaredClass);
		return path;
	},
	expander: function (node)
	{
		if (node.declaredClass == 'dijit._TreeNode') {
			dojo.connect(node, 'addChild', this, 'expander');
		}
		var nodePath = this.getPath(node, '/');
		substrPath = this.path.substr(0, nodePath.length);
		//alert(nodePath + " / " + this.path.substr(0, nodePath.length));
		if (( substrPath == nodePath) && node.isFolder) {					
		
			this._controller._expand(node);
		}
	},
	addChild: function ()
	{
		dijit.Tree.prototype.addChild.apply(this, arguments);
		this.expander(arguments[0], 1);
	
	},
	getIconClass: function( item){
		//alert(item.type);
        if(item.type != ""){
            return "TreeIcon_"+item.type;
        }
        else{
            return "TreeIcon999";
        }
    },
    setActive: function( item){
		//alert(item.type);
		//alert(this.curentNode);
		if(this.curentNode == item.nodeId){
            return "dijitTreeContentActive";
        }else{
            return '';            
        }
    }
});			

function getIcon(item){
	if(item){
	    return "TreeIcon_"+item.type;
	}
	else{
	    return "TreeIcon999";
	}	
}

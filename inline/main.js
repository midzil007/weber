

function ut(){
	
};

var options = ["price", "video", "skladem"];
$(document).ready(function() {
    //toggle `popup` / `inline` mode
    $.fn.editable.defaults.mode = 'inline';   
	pos = $(".editableEditor").offset();  
//$(".editable-buttons").({ top: 	pos.top, left: 	pos.left});
$(".editableEditor").attr('data-orig',$(".editableEditor").html());
$(".editableEditor").click(function(){
	$('body').append('<div class="editable-buttons pos-abs"><button type="submit" class="btn editorSubmit btn-primary btn-sm editable-submit"><i class="glyphicon glyphicon-ok"></i></button><button type="button" class="btn buttonCancel btn-default btn-sm editable-cancel"><i class="glyphicon glyphicon-remove"></i></button></div>');
	$(".editable-buttons").offset({ top: pos.top+20, left: pos.left+$(".editableEditor").width()+15});
	
	$(".editorSubmit").click(function(){
	
	$.ajax({
		type:"POST",
		url:'/cms/inlineedit/setproperty/nodeId/'+$('.editableEditor').attr('rel'),
		data:"value="+encodeURIComponent($(".editableEditor").html())+"&name="+$('.editableEditor').attr('datatype'),
		success:function(){
			$(".editableEditor").attr('data-orig',$(".editableEditor").html());
			$('.pos-abs').remove();
			}
	});
	});
	$('.buttonCancel').click(function(){
		$(".editableEditor").html($(".editableEditor").attr('data-orig'));
 	 $('.pos-abs').remove();
 });
});



// $(document).on('click', function(e) {
    // if((!$.contains($('.buttonCancel').get(0), e.target) && !$.contains($('.editorSubmit').get(0), e.target)) && !$.contains($('.mce-tinymce').get(0), e.target) ) {
		// //setOrig();
       // // $('.pos-abs').hide();
    // } /// jestli potvrdím
	// else if($.contains($('.editorSubmit').get(0), e.target))
	// {
		// $.ajax({
		// type:"POST",
		// url:'/cms/inlineedit/setproperty/nodeId/'+$('.editableEditor').attr('rel'),
		// data:"value="+encodeURIComponent($(".editableEditor").html())+"&name="+$('.editableEditor').attr('datatype'),
		// success:function(){
// 			
			// $(".editableEditor").attr('data-orig',$(".editableEditor").html());
			// }
			// });
		// }
	// else if($.contains($('.buttonCancel').get(0), e.target))
	// {
	// $('.pos-abs').remove();
// 
// 		  
	// }
// 	 
// });


 

function setOrig(){
	   
	    
}
		
/// jen pro titulek
$('#pageTitle').editable({
   type: 'text',
    url: '/cms/inlineedit/settitle/nodeId/'+$('#pageTitle').attr('rel'),
    title: 'Vložte hodnotu',
	success: function(response) {
		//alert(response);  
		history.pushState(false, false, response);
	}
});

// nastaví všem prvkům v options možnost editace 
jQuery.each( options, function(  i, val) {
		$('#'+val).editable({
})
	});
$('.price-list').editable({});
});
 
 
$(document).bind('drop dragover', function (e) {
    e.preventDefault();
}); 
   
$(function () {
    'use strict';

    $('.fileupload').each(function () {
		$(this).fileupload(
			{
			dropZone: $(this) 
			}
			,'option', { 
				autoUpload: true 
			}  
		).bind('filecompleted', function (e, data) { alert(data); )
	}); 
	
    
	$('#fileupload').fileupload('option', { 
            autoUpload: true 
        });
		
        // Load existing files:
        $.ajax({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: $('#fileupload').fileupload('option', 'url'),
            dataType: 'json',
            context: $('#fileupload')[0]
        }).done(function (result) {
			
			
	
            $(this).fileupload('option', 'done')
                .call(this, null, {result: result});
        });
		
		 
 
});  
 
setTimeout("refreshSession()", 1000 * 60 * 7 ); 
function refreshSession(){
	setTimeout("refreshSession()", 1000 * 60 * 7); 
	
	var jqxhr = $.ajax( "/cms/index/index" )
    .done(function() {  })
    .fail(function() {  }) 
    .always(function() {  }); 

}

/*
$(function() {
    $( "input[type=submit], input.fsubmit, button.nice" )
      .button()
      .click(function( event ) { 
         
      });
  }); 
*/
$(function() {
	$.datepicker.regional['cs'] = {
        closeText: 'Zavřít',
        prevText: '&#x3c;Dříve',
        nextText: 'Později&#x3e;',
        currentText: 'Nyní',
        monthNames: ['leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen',
            'září', 'říjen', 'listopad', 'prosinec'],
        monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
        dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
        dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        weekHeader: 'Týd',
        dateFormat: 'dd.mm.yy', 
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
});  
    
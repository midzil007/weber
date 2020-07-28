  
		var f = $('#basketForm');		
		var recalcArea = $('#recalcArea');
  
		function upOnChange(id){
				count = parseInt($('#'+id).val()) + 1;
				$('#'+id).val(count);
						total = parseInt($('#basket-counter').html());
			t = total + 1;
			$('#basket-counter').html(t);  
					calculatePay();   
		}

		function downOnChange(id){
			count = parseInt($('#'+id).val()) - 1;
			total = parseInt($('#basket-counter').html());
			t = total - 1;
			$('#basket-counter').html(t); 
 				$('#'+id).val(count);
					calculatePay();
			
		}
 
		function initOnChange(){
			//ref = recalcArea.getElements('.refresh');
			$(".refresh").change(function () {
				calculatePay();
			});
			
			
		}


		
		initOnChange();

		function setTotalPrice(){
			if($('#basket-icon-maly-cw')){
				//alert('ds');
				//alert($('#shopPrice').text());
				$('#basket-icon-maly-cw').html($('#shopPrice').text());
				}
			else{
				//$('#showPrice').set('text', '0 Kč');
				}
			
			}

		function del(ident){
				$('#'+ident).val(0);
				calculatePay();   
			return false; 
		}
		
		function calculate(){
			var req = new Request({				
                method: 'post',
                url: f.get('action') + "?ajax=1&action=refreshBasket",  
                data: f,    
                onRequest: function() {   
				recalcArea.fade(0.1); 
				 },
                onComplete: function(response) {  
					(function(){
						recalcArea.set('html', response); 
						recalcArea.fade(1); 
						initOnChange();
						upOnChange();
					
						downOnChange();
						setTotalPrice();
					}).delay(200);  			 
					
                }     
       		}).send(); 
		}
		 function initOnChangeDel(){
				$(".pradio").change(function () {
					calculatePay();
				});
			};


			function initOnChangePay(){  
				$(".platby").change(function () {
					calculatePay();
				});
				
			};




		function calculatePay(){
			  var postData = f.serializeArray();
			 $.ajax({
			 		url: f.attr('action') + "?ajax=1&action=refreshBasket",
			 		type: 'post',
			 			data:postData,
			 		success:function(result){
    			
    				$('#recalcArea').html(result);
						initOnChangePay();
						initOnChange();
						initOnChangeDel();
						setTotalPrice();
    				
  }});
					
		}
		initOnChangePay();
						initOnChange();
						initOnChangeDel();   
	
$("#showAdress" ).click(function() {
	if('block'== $("#otherAdress").css('display'))
		{
		  $("#otherAdress").fadeOut(400);
		  $(".downArr").css('display','inline');
		  $(".upArr").css('display','none');
		}
	else
		{
		  $("#otherAdress").fadeIn(400);
		  $(".downArr").css('display','none');
		  $(".upArr").css('display','inline');
		}
	return false;

}); 

function redirect(url){
	$("#payment-link").toggle(); 
	if(url != false) 
	{
		$(".redirect").toggle(); 
		$(".loader").toggle(); 
		var counter = 5;
		setInterval(function() {
		  counter--;
		  if (counter >= 0) {
			span = document.getElementById("count");
			span.innerHTML = counter;
		  }
		  // Display 'counter' wherever you want to display it.
		  if (counter === 0) {
		  //    alert('this is where it happens');
			  clearInterval(counter);
		  }
	  
		}, 1000);  
	  
				  setTimeout(function(){ window.location = url; }, 5000);
	}
}
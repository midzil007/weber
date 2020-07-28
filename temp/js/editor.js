function ut(){
	$.ajax({
		type:"POST",
		url:'save.php',
		data:"text="+encodeURIComponent($(".editable").html()),
		success:function(){$('.action').fadeIn(750).delay(3000).fadeOut(750)}
	});
    return false;
};
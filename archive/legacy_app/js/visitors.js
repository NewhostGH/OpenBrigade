var auto_refresh = setInterval(
function () {
	$('#counter').load('chat_message.php?counter=1').fadeIn("slow");
},
3000); 
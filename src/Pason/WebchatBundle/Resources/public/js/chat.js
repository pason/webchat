/**
 * @author Pason Slawomir
 */



$(function () {

	var content = $('#chatContent');
	var input = $('#messageInput');
	var status = $('#status');
	var myColor = false;
	var myName = false;
	 
	window.WebSocket = window.WebSocket || window.MozWebSocket;
	
	if (!window.WebSocket) {
		content.html($('<p>', { text: 'Twoja przeglądarka nie obsługuje WebSockets.'} ));
		input.hide();		
		$('span').hide();	
	return;
	
	}

	var connection = new WebSocket('ws://127.0.0.1:8000/chat');
		 
	connection.onopen = function () {
		input.removeAttr('disabled');
		status.text('Choose name:');
	};
	
	connection.onerror = function (error) {
		content.html($('<p>', { text: 'Brak połączenia z serwerem' } ));
	};



	connection.onmessage  = function (message) {		
		try { 		
			var json = JSON.parse(message.data);			
		} catch (e) {
			console.log('Bład parsowania JSON: ', message.data);
		return;
	
	}


	if (json.type === 'color') { 
		myColor = json.data;
		status.text(myName + ': ').css('color', myColor);
		input.removeAttr('disabled').focus();
	
	} else if (json.type === 'history') { 
	
		for (var i=0; i < json.data.length; i++) {		
			pushMessage(json.data[i].author, json.data[i].text,
			json.data[i].color, new Date(json.data[i].time));	
		}

	} else if (json.type === 'message') { 

			input.removeAttr('disabled'); 
			pushMessage(json.data.author, json.data.text,
			json.data.color, new Date(json.data.time));

	} 

};

 



input.keydown(function(e) {
	if (e.keyCode === 13) {
		var msg = $(this).val();
	if (!msg) {	
		return;
}

connection.send(msg);

$(this).val('');

input.attr('disabled', 'disabled');

 
if (myName === false) {
	myName = msg;
	}
}

});

 
setInterval(function() {
	if (connection.readyState !== 1) {
		status.text('Error');	
		input.attr('disabled', 'disabled');
	}
}, 3000);

 
function pushMessage(author, message, color, dt) {
	dt = new Date(dt * 1000);
	content.append('<p><span style="color:' + color + '">' + author + '</span> @ ' +	
			+ (dt.getHours() < 10 ? '0' + dt.getHours() : dt.getHours()) + ':'
			+ (dt.getMinutes() < 10 ? '0' + dt.getMinutes() : dt.getMinutes())
			+ ': ' + message + '</p>');
	
	}
});


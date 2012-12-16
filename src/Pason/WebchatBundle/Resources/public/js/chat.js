/**
 * @author Pason Slawomir
 */



$(document).ready(function() {

	var content = $('#chatContent');
	var input = $('#messageInput');
	var status = $('#status');
	var chanels = $('#list');
	var myName = false;
	var chanel = false;
	 
	window.WebSocket = window.WebSocket || window.MozWebSocket;
	
	if (!window.WebSocket) {
		content.html($('<p>', { text: 'Twoja przeglądarka nie obsługuje WebSockets.'} ));
		input.hide();		
		$('span').hide();	
	return;
	
	}

	var connection = new WebSocket(wsServerAddress+'/chat');
		 
	connection.onopen = function () {
		input.removeAttr('disabled');
		status.text('Wpisz widomość:');
		connection.send(JSON.stringify({type:'session', 'data': getCookie('PHPSESSID')}));
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

		 if (json.type === 'history') { 
			
			content.html(''); 
			for (var i=0; i < json.data.length; i++) {		
				pushMessage(json.data[i].author, json.data[i].text, new Date(json.data[i].time));	
			}
	
		} else if (json.type === 'chanels') { 
			
			pushChanels(json.data);
			input.removeAttr('disabled'); 
			
		} else if (json.type === 'message') { 
	
			input.removeAttr('disabled'); 
			pushMessage(json.data.author, json.data.text, new Date(json.data.time));
		} 
	};
	
	
	input.keydown(function(e) {
		if (e.keyCode === 13) {
			var msg = $(this).val();
		if (!msg) {	
			return;
		}
	
		connection.send(JSON.stringify({type:'message', 'data': msg, 'chanel': chanel}));
		
		$(this).val('');
		
		input.attr('disabled', 'disabled');
		
		 
		if (myName === false) {
			myName = msg;
			}
		}
	});
	
	 
	setInterval(function() {
		if (connection.readyState !== 1) {
			status.text('Błąd!');	
			input.attr('disabled', 'disabled');
		}
	}, 3000);
	
	 
	function pushMessage(author, message, dt) {
		dt = new Date(dt * 1000);
		content.append('<p><span>' + author + '</span> @ ' +	
				+ (dt.getHours() < 10 ? '0' + dt.getHours() : dt.getHours()) + ':'
				+ (dt.getMinutes() < 10 ? '0' + dt.getMinutes() : dt.getMinutes())
				+ ': ' + message + '</p>');	
		
		content.scrollTop( content.prop("scrollHeight") );
	}
	
	function pushChanels(data){		
		
		chanels.html('');
		$.each(data, function(key, value) { 
			if(chanel == false){
				chanel = key;
			}
			if(chanel == key){ 
				chanels.append('<li class="active"><a href="#" data-id="' + key + '" >' + value + '</a></li>');
			} else {
				chanels.append('<li><a href="#" data-id="' + key + '" >' + value + '</a></li>');
			}
		});
		
		$("#list li a").click(function () {			
			$("#list li.active").removeClass('active');
			$(this).parent().addClass('active');
			chanel = $(this).attr('data-id');
			connection.send(JSON.stringify({type:'chanel', 'data': $(this).attr('data-id')}));
		});
		
	}
	
	$('#buttonChanel').click(function() {		
		var newChanel = $('#addChanel').val();
		connection.send(JSON.stringify({type:'newchanel', 'data': newChanel}));		
	});
	
});



function getCookie(c_name){
    var i,x,y,ARRcookies=document.cookie.split(";");

    for (i=0;i<ARRcookies.length;i++)
    {
        x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
        y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
        x=x.replace(/^\s+|\s+$/g,"");
        if (x==c_name)
        {
            return unescape(y);
        }
     }
}


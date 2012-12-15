<?php

/**
 * 
 * @author Pason Slawomir
 *
 */


namespace Pason\WebchatBundle\Services;

use \InvalidArgumentException;
use Varspool\WebsocketBundle\Application\Application;


class ChatService extends Application {
	
	private $history;
	
	CONST HISTORYLENGHT = 100;
	
	public function onData($payload, $connection) {
		
		$userColor = dechex(rand(0,10000000));
		
		//$connection->send(json_encode(array('type' => 'color', 'data' => $userColor)));
		
		$userName = 'slawek';
		
		$input = array(
			'time' => strtotime('now'),
			'text' => strip_tags($payload->getPayload()),
			'author' => $userName,
			'color' => $userColor
		);
		
		$history[] = $input;
		
		$input = (object)$input;
		
		$connection->send(json_encode(array('type' => 'message', 'data' => $input)));
		
		
		//$this->sendToAll(json_encode(array('data' => $payload->getPayload())));
	} 
	
	
	
	
	
}
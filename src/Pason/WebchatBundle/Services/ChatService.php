<?php

/**
 * 
 * @author Pason Slawomir
 *
 */


namespace Pason\WebchatBundle\Services;

use \InvalidArgumentException;
use Varspool\WebsocketBundle\Application\Application;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Request;


class ChatService extends Application {
	
	private $history;
	private $container;
	private $entityManager;
	private $users;
	private $chanels = array('1' => 'room1', '2' => 'room2'); // Domyślne pokoje
	
	CONST HISTORYLENGHT = 100;   // Bufor historii
	
	/**
	 * 
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		parent::__construct();
		$this->container = $container;
		$this->entityManager = $this->container->get('doctrine')->getEntityManager();
		
		foreach($this->chanels as $id => $chanel){
			$this->history[$id] = array();
		}
	}
	
	/**
	 * 
	 * @param unknown_type $client
	 */
	public function onConnect($client){
		$this->clients[$client->getId()] = $client;
		$client->send(json_encode(array('type' => 'chanels', 'data' => $this->chanels)));	
	}
	
	public function onData($payload, $connection) {
		
		$json = $payload->getPayload();
		$data = json_decode($json);
			
		switch ($data->type){
			case 'session':
				
				$sessionId = $data->data;
				$user = $this->getUser($sessionId);
				reset($this->chanels);
				$user->chanel = key($this->chanels);
				$this->users[$connection->getId()] = $user;
				$connection->send(json_encode(array('type' => 'history', 'data' => $this->history[$user->chanel])));
				break;
				
			case 'message':
			
				$message = $data->data;
				$userName = $this->users[$connection->getId()]->username;			
				
				$input = array(
						'time' => strtotime('now'),
						'text' => strip_tags($message),
						'author' => $userName
				);
				
				if(count($this->history[$data->chanel]) > self::HISTORYLENGHT){
					array_shift($this->history[$data->chanel]);
				}
						
				$this->history[$data->chanel][] = $input;
				
				$input = (object)$input;
				
				$this->sendToChanel(json_encode(array('type' => 'message', 'data' => $input)),$data->chanel);
				break;
		
			case 'chanel':
			
				$selectedChanel = $data->data;
				$this->users[$connection->getId()]->chanel = $selectedChanel;
				$connection->send(json_encode(array('type' => 'history', 'data' => $this->history[$selectedChanel])));
				break;
				
			case 'newchanel':
				
				$roles = $this->users[$connection->getId()]->roles;
				
				if(in_array('ROLE_ADMIN',$roles) || in_array('ROLE_SUPER_ADMIN',$roles)) {
				
					$newChanel = $data->data;
					$this->chanels[] = $newChanel;
					end($this->chanels);         
					$key = key($this->chanels);
				
					$this->history[$key] = array();
				
					$this->sendToAll(json_encode(array('type' => 'chanels', 'data' => $this->chanels)));
				}
				
				break;
		} 
	} 
	
	/**
	 * 
	 * @param unknown_type $data
	 */
	public function sendToChanel($data, $chanel)
	{
		$collected = array();
		foreach ($this->clients as $client) {
			if($this->users[$client->getId()]->chanel == $chanel) {			
				$collected[] = $client->send($data);
			}
		
		}
		return $collected;
	}
	
	/**
	 * 
	 */
	public function onDisconnect($client){
			
		if(array_key_exists($client->getId(), $this->clients)) {
			unset($this->clients[$client->getId()]);
			unset($this->users[$client->getId()]);
		}
	}
	
	
	/**
	 * Pobiera zalogowanego użytkownika
	 * @param String $sessionId
	 */
	private function getUser($sessionId){
		
		$url =  'http://'.
				$this->container->getParameter('domain') .
				$this->container->get('router')->generate('pason_webchat_user');
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . $sessionId);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		$user = json_decode($result);
		
		return $user;
		
	}
	
}
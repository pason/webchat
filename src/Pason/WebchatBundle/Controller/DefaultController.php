<?php

/**
 * 
 * @author Pason Slawomir
 *
 */

namespace Pason\WebchatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    
	/**
	 * Index Action
	 */
	public function indexAction(){
		return $this->redirect($this->generateUrl("pason_webchat_chat"));
    }
    
    /**
     * Chat action
     */
    public function chatAction(){
    	
    	$wsServer = $this->container->getParameter('websocket');
    	
    	return $this->render('PasonWebchatBundle:Default:chat.html.twig', array('wsServer' => $wsServer));
    }
    
    /**
     * Return logged user entity
     */
    public function getuserAction(){
    	
    	$user = $this->container->get('security.context')->getToken()->getUser();
    	
    	$serializer = $this->container->get('serializer');
    	$json = $serializer->serialize($user, 'json');
    	
    	$response = new Response($json);
    	return $response;
    }
    
}

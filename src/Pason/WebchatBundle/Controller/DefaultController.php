<?php

/**
 * 
 * @author Pason Slawomir
 *
 */

namespace Pason\WebchatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    
	/**
	 * Index Action
	 */
	public function indexAction(){
        return $this->render('PasonWebchatBundle:Default:index.html.twig', array());
    }
    
    /**
     * Chat action
     */
    public function chatAction(){
    	return $this->render('PasonWebchatBundle:Default:chat.html.twig', array());
    }
    
}

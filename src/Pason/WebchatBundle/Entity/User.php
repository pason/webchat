<?php


namespace Pason\WebchatBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	public $chanel; //current chanel
	
	public function __construct()
	{
		parent::__construct();		
	}
	
	public function setChanel($chanel){
		$this->chanel = $chanel;
	}
	
	public function getChanel(){
		return $this->chanel;
	}
}
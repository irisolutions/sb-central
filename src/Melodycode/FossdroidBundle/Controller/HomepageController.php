<?php

namespace Melodycode\FossdroidBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class HomepageController extends Controller {

    public function indexAction() 
    {
    	
    	//$user = $this->getUser();
    	//var_dump($user);
        return $this->render('MelodycodeFossdroidBundle:Homepage:index.html.twig');
    }

}

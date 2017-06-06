<?php

namespace Iris\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PDO;

//include 'ChromePhp.php';
//use ChromePhp;

class AdminController extends Controller {
	public function accountAction() 
    {
    	$context = $this->container->get('security.context');
    	
    	if( !$context->isGranted('IS_AUTHENTICATED_FULLY') )
    	//	return $this->forward('MelodycodeFossdroidBundle:Homepage:index');
    	return $this->redirect($this->generateUrl('homepage'));
    	
    	return new Response('<html><body>Administrator Account Details go here</body></html>');
    }

 	
}

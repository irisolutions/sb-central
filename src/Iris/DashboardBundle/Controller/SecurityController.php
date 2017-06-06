<?php

namespace Iris\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

use PDO;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {

  		/*
    	$authenticationUtils = $this->get('security.authentication_utils');

    	// get the login error if there is one
    	$error = $authenticationUtils->getLastAuthenticationError();

    	// last username entered by the user
    	$lastUsername = $authenticationUtils->getLastUsername();

    	return $this->render('MelodycodeFossdroidBundle:Security:login.html.twig', array(
        	'last_username' => $lastUsername,
        	'error'         => $error,
    	));
    
    
       return;
       */
    	
        $session = $request->getSession();
        
        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) 
        {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } 
        else 
        {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        }
        
        return $this->render(
            'DashboardBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContextInterface::LAST_USERNAME),
                'error'         => $error,
            )
        );
        
    }
    public function loginCheckAction()
	{
	
	}
}

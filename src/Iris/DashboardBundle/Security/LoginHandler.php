<?php

namespace Iris\DashboardBundle\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class LoginHandler implements AuthenticationSuccessHandlerInterface
{
	
	protected $router;
	//protected $security;
	
	public function __construct(Router $router)
	{
		$this->router = $router;
	//	$this->security = $security;
	}
	//*/
  public function onAuthenticationSuccess(Request $request, TokenInterface $token)
  {
    	//$referer_url = $request->headers->get('referer');
		//$response = new RedirectResponse($referer_url);
		
		
		
		//$session = $request->getSession();
		
		// This is a placeholder in case you want to do something after login
		// currently we don't do anything here
		
		//echo 'We are here';
		$response = new RedirectResponse($this->router->generate('dashboard_homepage'));
		return $response;
  }
  
}
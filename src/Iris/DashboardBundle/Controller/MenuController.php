<?php

namespace Iris\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PDO;
//include 'ChromePhp.php';
//use ChromePhp;
class MenuController extends Controller 
{

public function indexAction() 
{
        //return new Response('<html><body>The Menu goes here</body></html>');
         //return $this->render('DashboardBundle:Menu:index.html.twig');
         
         
        //return new Response('<html><body>The Menu goes here</body></html>');
        $commands = array();
        
        $context = $this->container->get('security.context');
    			
    	if ( $context->isGranted('ROLE_DEVELOPMENT') ) 
    	{
    	
    		$commands[] = array('path'=>'','icon'=>'','name'=>'Applications');
    		
         	$commands[] = array('path'=>'dashboard_dev_new_app','icon'=>'add_circle','name'=>'New Application');
         	
            $commands[] = array('path'=>'dashboard_dev_manage_app','icon'=>'view_list','name'=>'Manage Applications');
        }
        
        if ( $context->isGranted('ROLE_MARKETING') ) 
    	{
    	
    		$commands[] =  array('path'=>'','icon'=>'','name'=>'Bundles');
    		
         	$commands[] =  array('path'=>'dashboard_marketing_new_bundle','icon'=>'add_circle','name'=>'New Bundle');
         	
            $commands[] =  array('path'=>'dashboard_marketing_manage_bundle','icon'=>'loop','name'=>'Manage Bundles');
        }
        
        if ( $context->isGranted('ROLE_RANDD') ) 
    	{
    	
    		$commands[] =  array('path'=>'','icon'=>'','name'=>'Statistics');
    		
         	$commands[] =  array('path'=>'dashboard_dev_new_app','icon'=>'add_circle','name'=>'Application Usage');
         	
            $commands[] =  array('path'=>'dashboard_dev_new_app','icon'=>'loop','name'=>'Application Tracking');
        }

                 
         return $this->render('DashboardBundle:Menu:index.html.twig',array(
                    'commands' => $commands ));
}

}

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
    		
         	$commands[] =  array('path'=>'dashboard_sales_new_bundle','icon'=>'add_to_photos','name'=>'New Bundle');
         	
            $commands[] =  array('path'=>'dashboard_sales_manage_bundle','icon'=>'view_list','name'=>'Manage Bundles');
            
            //
            
            $commands[] =  array('path'=>'','icon'=>'','name'=>'Clients');
            
            $commands[] =  array('path'=>'dashboard_sales_add_client','icon'=>'add_circle_outline','name'=>'New Client');
            
            $commands[] =  array('path'=>'dashboard_sales_manage_clients','icon'=>'view_list','name'=>'Manage Clients');
    		
         	$commands[] =  array('path'=>'dashboard_sales_manage_application','icon'=>'account_box','name'=>'Applications/Clients');
         	
            $commands[] =  array('path'=>'dashboard_sales_manage_bundles','icon'=>'contacts','name'=>'Bundles/Clients');
            
            
           
            
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

<?php

namespace Iris\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpFoundation\StreamedResponse;

use PDO;
//include 'ChromePhp.php';
//use ChromePhp;

// Flash Messages Styles
//success (green)
//info (blue)
//warning (yellow)
//danger (red)

class MarketingController extends Controller 
{


public function manageBundleAction() 
{

		return new Response('<html><body>Manage Bundle Page goes here</body></html>');
	
		//$context = $this->container->get('security.context');
    	
    	//if( !$context->isGranted('IS_AUTHENTICATED_FULLY') )
    	//	return $this->redirect($this->generateUrl('homepage'));
    	
    	
    	$dbname = $this->container->getParameter('store_database_name');
    	$dbuser = $this->container->getParameter('store_database_user');
    	$dbpass = $this->container->getParameter('store_database_password');
    	$dbhost = $this->container->getParameter('store_database_host');
    		
    	$conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    	
    	$stmt = $conn->prepare('SELECT * FROM Application');
		$stmt->execute();
		
		$applications = $stmt->fetchAll();
		
	
    	return $this->render('DashboardBundle:Developer:manage-app.html.twig', array(
                    'applications' => $applications));	
}


public function newBundleAction() 
{
        return new Response('<html><body>New Bundle Page goes here</body></html>');
        
        $request=$this->get('request');
        
        if ($request->getMethod() == 'POST') 
        {
            //$app-name = $request->request->get('app-name');
            //$output = shell_exec('sudo -u apache /var/www/html/update-store.sh 2>&1');
            //return $this->render('DashboardBundle:Developer:new-app-result.html.twig',array('output' => $output ));
            
            if ( $this->scriptStillExecuting('update-store.sh'))
        	{
        
        	 	//return new Response('<html><body><div>An Add Application Operation is in progress, please try again later</div> <div><input type="button" value="Try Again" onClick="window.history.back()"></div></body> </html>');
        		
        		//return $this->render('DashboardBundle:Developer:new-app.html.twig');
        		$request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Another Add/Update/Delete Application operation is in progress .. Try again in a few seconds');
        		return $this->redirect($request->headers->get('referer'));
        	}
        	
        	$app_name 				= $request->request->get('app-name');
        	$app_bundle				= $request->request->get('app-bundle');
			$app_controller_binary 	= $request->request->get('app-controller-binary');
			$app_description		= $request->request->get('app-description');
			$app_dongle_binary		= $request->request->get('app-dongle-binary');
			$app_identifier			= $request->request->get('app-identifier');
			$app_payment_model	    = $request->request->get('app-payment-model');
			$app_price				= $request->request->get('app-price');	
			$app_summary			= $request->request->get('app-summary');
			$app_version 			= $request->request->get('app-version');
			$app_category 			= $request->request->get('app-category');
        	
        	// we check if the two binary files are where they should be other wise we fail
        	
        	$repo_dir 		= $this->container->getParameter('melodycode_fossdroid.local_path_repo');
        	$metadata_dir 	= $this->container->getParameter('melodycode_fossdroid.local_path_metadata');
        	$controller_file = $repo_dir.'/'.$app_controller_binary;
        	$dongle_file	 = $repo_dir.'/'.$app_dongle_binary;
        	
        	if ( !file_exists($controller_file) /*|| !file_exists($dongle_file)*/ )
        	{
        		$request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Controller and/or Dongle binary file(s) do(es) not exist');
        		return $this->redirect($request->headers->get('referer'));
        	
        	}
        	
        	// now we create the meta data file for the application
        	
        	
        	
        	$metadata_file = $metadata_dir.'/'.$app_identifier.'.txt';
        	
        	$content = 'License:Unknown'.PHP_EOL.'Web Site:'.PHP_EOL.'Source Code:'.PHP_EOL.'Issue Tracker:'.PHP_EOL.'Changelog:'.PHP_EOL.'Summary:%s'.PHP_EOL.'Description:'.PHP_EOL.'%s'.PHP_EOL.'.'.PHP_EOL.'Name:%s'.PHP_EOL.'Categories:%s'.PHP_EOL.'';
        	$content = sprintf($content, $app_summary,$app_description,$app_name,$app_category);
        	
        	$success = file_put_contents($metadata_file, $content, LOCK_EX);
        	
        	if ( !$success )
        	{
        		$request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Unable to write metadata file');
        		return $this->redirect($request->headers->get('referer'));
        	
        	}
        	
        	
        	// if we are here then the binary files are in place and the meta data file was created so we insert into the DB
        	
        	$dbname     = $this->container->getParameter('store_database_name');
    		$username   = $this->container->getParameter('store_database_user');
    		$password   = $this->container->getParameter('store_database_password');
    		$servername = $this->container->getParameter('store_database_host');
    		
    		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    		// set the PDO error mode to exception
    		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    		$stmt = $conn->prepare('INSERT INTO Application (ID,Version,Name,DongleAppName,ControllerAppName,Price) VALUES (?,?,?,?,?,?)');
    		
    		try
    		{
    		
				$stmt->execute([$app_identifier,$app_version,$app_name,$app_dongle_binary,$app_controller_binary,$app_price]);
        	}
        	catch (\PDOException $e)
        	{
        		// if something goes wrong we fail
        	
        		//throw $e;
        		
        		$error =''; 
        		
        		if ( $e->errorInfo[1] == 1062 ) // contrains violation i.e. the app_id:app_version already exists
        		{
       				 // Take some action if there is a key constraint violation, i.e. duplicate name
       				 $error = 'Operation Aborted .. The Application Identifier & Version must be unique [Error]'.$e->getMessage();
    			} 
    			else
    				$error = 'Operation Aborted ..'.$e->getMessage();
        		
        		$request->getSession()->getFlashBag()->add('danger', $error);
        		return $this->redirect($request->headers->get('referer'));
        	
        	}

        	// if we are here then all is good let us launch the update script
            
            return $this->render('DashboardBundle:Developer:new-update-delete-app-result.html.twig',array('operation'=>'new'));
            
        }
        
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');
    	
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    	$stmt = $conn->prepare('SELECT * FROM Category');
    	$stmt->execute();
    	
    	$categories = $stmt->fetchAll();
        
        return $this->render('DashboardBundle:Developer:new-update-app.html.twig',array('update'=>false,'categories'=>$categories));
         
}

}

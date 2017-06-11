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
    	
    	
    	$stmt = $conn->prepare('SELECT Bundle.ID, Bundle.Name ,sum(Price) as Price FROM Application ,Bundle,BundleApplication
  WHERE 
Application.ID=BundleApplication.ApplicationID and
Bundle.ID=BundleApplication.BundleID and Application.ID in (

SELECT ApplicationID FROM BundleApplication
                    WHERE BundleID=Bundle.ID) group by Bundle.ID');
		$stmt->execute();
		
		$bundle = $stmt->fetchAll();
		
	
    	return $this->render('DashboardBundle:Marketing:manage-bundle.html.twig', array(
                    'bundles' => $bundle));  
}


public function newBundleAction() 
{
        
                   

        $request=$this->get('request');
        
        if ($request->getMethod() == 'POST') 
        {
                //first get the value of post variables 
        	$bundle_name		= $request->request->get('bundle-name');
		$bundle_description	= $request->request->get('bundle-description');
		$app_identifiers	= $request->request->get('app-identifiers');//string of application id's separated with ','
        	$app_identifiers        = explode(",", $app_identifiers);
        	
        	
        	
        	// connect to database
        	
        	$dbname     = $this->container->getParameter('store_database_name');
    		$username   = $this->container->getParameter('store_database_user');
    		$password   = $this->container->getParameter('store_database_password');
    		$servername = $this->container->getParameter('store_database_host');
    		
    		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    		// set the PDO error mode to exception
    		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
                //insert the new bundle
    		$stmt = $conn->prepare('INSERT INTO Bundle (Name,Description) VALUES (?,?)');
    		
    		try
    		{
                    $stmt->execute([$bundle_name,$bundle_description]);
        	}
        	catch (\PDOException $e)
        	{
        		$error =''; 
    				$error = 'Operation Aborted ..'.$e->getMessage();
        		
        		$request->getSession()->getFlashBag()->add('danger', $error);
        		return $this->redirect($request->headers->get('referer'));
        	}
        	// get  ID for the new bundle
            $stmt = $conn->prepare('select ID from Bundle where Name=?');
            $stmt->execute([$bundle_name]);
            $bundle_id=$stmt->fetchAll()[0]['ID'];
            $stmt = $conn->prepare('INSERT INTO BundleApplication (BundleID,ApplicationID) VALUES (?,?)');
            //save the list of applications inside bundle 
            
            for($i=0;$i<count($app_identifiers);$i++)
            {
                $stmt->execute([$bundle_id,$app_identifiers[$i]]);
            }
            return $this->render('DashboardBundle:Marketing:new-update-delete-bundle-result.html.twig');
            
        }
        
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');
    	
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    	$stmt = $conn->prepare('SELECT * FROM Application');
        try
        {
            $stmt->execute();
    	}
        catch (\PDOException $e)
        {
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
    	$applications = $stmt->fetchAll();
        
return $this->render('DashboardBundle:Marketing:new-update-bundle.html.twig',array('update'=>false,'apps'=>$applications));         
}
public function editBundleAction($slug)
{
    $request=$this->get('request');
        
        if ($request->getMethod() == 'POST') 
        {
                //first get the value of post variables 
        	$bundle_name		= $request->request->get('bundle-name');
		$bundle_description	= $request->request->get('bundle-description');
		$app_identifiers	= $request->request->get('app-identifiers');//string of application id's separated with ','
        	$app_identifiers        = explode(",", $app_identifiers);
        	
        	
        	
        	// connect to database
        	
        	$dbname     = $this->container->getParameter('store_database_name');
    		$username   = $this->container->getParameter('store_database_user');
    		$password   = $this->container->getParameter('store_database_password');
    		$servername = $this->container->getParameter('store_database_host');
    		
    		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    		// set the PDO error mode to exception
    		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
                
    		
        	// get  ID for bundle
            $stmt = $conn->prepare('select ID from Bundle where Name=?');
            $stmt->execute([$bundle_name]);
            $bundle_id=$stmt->fetchAll()[0]['ID'];
            //update description
            $stmt = $conn->prepare('update Bundle set Description=? where ID=?');
            try
            {
                    $stmt->execute([$bundle_description,$bundle_id]);                
            }
            catch (\PDOException $e)
            {
                $error = 'Operation Aborted ..'.$e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));
            }
            //delete all delete all bundle applications
            $stmt = $conn->prepare('delete from BundleApplication  where BundleID=?');
            try
            {
                    $stmt->execute([$bundle_id]);                
            }
            catch (\PDOException $e)
            {
                $error = 'Operation Aborted ..'.$e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));
            }
            
            
            //insert application list to bundle
            $stmt = $conn->prepare('INSERT INTO BundleApplication (BundleID,ApplicationID) VALUES (?,?)');
            try
            {
                for($i=0;$i<count($app_identifiers);$i++)
                {
                    $stmt->execute([$bundle_id,$app_identifiers[$i]]);                
                }
            }
            catch (\PDOException $e)
            {
                $error = 'Operation Aborted ..'.$e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));
            }
            return $this->render('DashboardBundle:Marketing:new-update-delete-bundle-result.html.twig');
            
        }
        $bundle_id=$slug;    
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');
    	
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        //get application list inside bundle
        $stmt = $conn->prepare('SELECT  * FROM Application
  WHERE ID  in (SELECT ApplicationID FROM BundleApplication
                    WHERE BundleID=?);');
        try
        {
            $stmt->execute([$bundle_id]);
    	}
        catch (\PDOException $e)
        {
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $apps_of_Bundle=$stmt->fetchAll();
        //get bundle info
    	$stmt = $conn->prepare('SELECT * FROM Bundle where ID=?');
        try
        {
            $stmt->execute([$bundle_id]);
    	}
        catch (\PDOException $e)
        {
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $bundle=$stmt->fetchAll()[0];
        //get application that are not inside bundle
    	$stmt = $conn->prepare('SELECT  * FROM Application
  WHERE ID NOT in (SELECT ApplicationID FROM BundleApplication
                    WHERE BundleID=?);');
        try
        {
            $stmt->execute([$bundle_id]);
    	}
        catch (\PDOException $e)
        {
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
    	$applications = $stmt->fetchAll();
return $this->render('DashboardBundle:Marketing:new-update-bundle.html.twig',array('update'=>true,'apps'=>$applications,'bundle'=>$bundle,'appsOfBundle'=>$apps_of_Bundle));     
    
}
public function deleteBundleAction($slug)
{
    //connect to database
	$bundle_identifier = $slug;	
        $request=$this->get('request');
	$dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //delete bundle
    	$stmt = $conn->prepare('DELETE FROM Bundle WHERE ID = ?');   		
    	try
    	{	
            $stmt->execute([$bundle_identifier]);
        }
        catch (\PDOException $e)
        {
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        //delete application list of bundle 
        $stmt = $conn->prepare('DELETE FROM BundleApplication WHERE BundleID = ?');
    	try
    	{
            $stmt->execute([$bundle_identifier]);
        }
        catch (\PDOException $e)
        {
            $error = 'Operation Aborted ..'.$e->getMessage();       		
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        //dlete subscriptions for the bundle
       	$stmt = $conn->prepare('DELETE FROM Subscription WHERE BundleID = ?');
    	try
    	{	
            $stmt->execute([$bundle_identifier]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
            return $this->render('DashboardBundle:Marketing:new-update-delete-bundle-result.html.twig');
}

}

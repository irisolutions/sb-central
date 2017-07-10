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

public function manageApplicationsAction()
{
    $request=$this->get('request');
    //connect to database
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //get applications with number of clients
    	$stmt = $conn->prepare('Select *,(select count(*)  from Client,Purchase where Client.ID=Purchase.ClientID and Purchase.ApplicationID=Application.ID) As NumberOfClients from Application order by Application.ID ');   		
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
        $applications=$stmt->fetchAll();
      return $this->render('DashboardBundle:Marketing:manage-application.html.twig', array(
                    'applications' => $applications));  
}
public function showApplicationClientsAction($slug)
{
    $request=$this->get('request');
    $applicationID=$slug;
        //connect to database
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //get application clients
    	$stmt = $conn->prepare('Select *,(select Version from ControllerInstallation as cont where clientID=Purchase.ClientID  and ApplicationID=Purchase.ApplicationID and DownloadDate in(select max(cont.DownloadDate) as m from ControllerInstallation as cont where clientID=Purchase.ClientID  and ApplicationID=Purchase.ApplicationID )) as Version from Purchase,Client where ApplicationID=? and Client.ID=ClientID');   		
            	try
    	{	
            $stmt->execute([$applicationID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        
        $clients=$stmt->fetchAll();
        
      return $this->render('DashboardBundle:Marketing:show-application-clients.html.twig', array(
                    'clients' => $clients)); 
      
}
public function manageBundlesAction()
{
    $request=$this->get('request');
      //connect to database
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //get Bundles with number of clients
    	$stmt = $conn->prepare('Select *,(select count(*)  from Client,Subscription where Client.ID=Subscription.ClientID and Subscription.BundleID=Bundle.ID) As NumberOfClients from Bundle order by Bundle.ID');   		
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
        $Bundles=$stmt->fetchAll();
      return $this->render('DashboardBundle:Marketing:manage-bundles.html.twig', array(
                    'bundles' => $Bundles));
}
public function showBundleClientsAction($slug)
{
    $request=$this->get('request');
        $BundleID=$slug;
        //connect to database
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //get Bundle  clients
    	$stmt = $conn->prepare('Select * from Subscription,Client where BundleID=? and Client.ID=ClientID');   		
            	try
    	{	
            $stmt->execute([$BundleID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $clients=$stmt->fetchAll();
      return $this->render('DashboardBundle:Marketing:show-bundle-clients.html.twig', array(
                    'clients' => $clients)); 
}
public function manageClientsAction()
{
    $request=$this->get('request');
    $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
       //get all cliets with number of bundles and number of applications that every client have
            	$stmt = $conn->prepare('SELECT *,(SELECT COUNT(*) FROM Bundle,Subscription where BundleID=Bundle.ID and ClientID=Client.ID) as numberOfBundles,
(SELECT COUNT(*) FROM Application,Purchase where ApplicationID=Application.ID and ClientID=Client.ID) as numberOfApplications from Client');   		
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
        $clients=$stmt->fetchAll();
     return $this->render('DashboardBundle:Marketing:manage-clients.html.twig', array(
                    'clients' => $clients));
}
public function showClientBundlesAction($slug)
{
    $request=$this->get('request');
          $ClientID=$slug;
        //connect to database
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //get applications with number of clients
          if ($request->getMethod() == 'POST') 
        {
              $delete		= $request->request->get('delete');
              $edit		= $request->request->get('edit');
              $deletedbundlename		= $request->request->get('deletedbundle');
              if($delete )
              {
                  //check if the user select row if not throw exception
                if($deletedbundlename)
                {
                    //find id for the bundle
                    $stmt = $conn->prepare('Select ID from Bundle where Name=?'); 
                    try
                    {	
                        $stmt->execute([$deletedbundlename]);
                    }
                    catch (\PDOException $e)
                    {
                        $error = 'Operation Aborted ..'.$e->getMessage();
                        $request->getSession()->getFlashBag()->add('danger', $error);
                        return $this->redirect($request->headers->get('referer'));
                    }
                    $deletedbundleID=$stmt->fetchAll();
                    $deletedbundleID=$deletedbundleID[0][0];
                    //dlete subscrioption  
                    $stmt = $conn->prepare('delete from Subscription Where ClientID=? and BundleID=? '); 
                  
                    try
                    {	
                        $stmt->execute([$ClientID,$deletedbundleID]);
                    }
                    catch (\PDOException $e)
                    {
                        $error = 'Operation Aborted ..'.$e->getMessage();
                        $request->getSession()->getFlashBag()->add('danger', $error);
                        return $this->redirect($request->headers->get('referer'));
                    }
                    $request->getSession()->getFlashBag()->add('success', "Bundle Removed Successfully");
                        return $this->redirect($request->headers->get('referer'));
                }
                else
                {
                     try
                    {
                     
                       throw new \Symfony\Component\Intl\Exception\OutOfBoundsException();
                    } catch (\Exception $e)
                    {
                        $error="Select Bundle to remove";
                        $request->getSession()->getFlashBag()->add('danger', $error);
                        return $this->redirect($request->headers->get('referer'));
                    }
                }
                //
              }//delete opration done.
              else if($edit)
              {
                  $bundlename		= $request->request->get('editbundle');
                   $StartDate              =$request->request->get('StartDate');
                $EndDate               =$request->request->get('EndDate');
                //check data is it valid or not if not throw exception.
                if($EndDate&&$StartDate)
                {
                    //get ID of the bundle first 
                   $stmt = $conn->prepare('Select ID from Bundle where Name=?'); 
                    try
                    {	
                        $stmt->execute([$bundlename]);
                    }
                    catch (\PDOException $e)
                    {
                        $error = 'Operation Aborted ..'.$e->getMessage();
                        $request->getSession()->getFlashBag()->add('danger', $error);
                        return $this->redirect($request->headers->get('referer'));
                    }
//update the bundle now 
                    $bundleID=$stmt->fetchAll();
                    $bundleID=$bundleID[0][0];
                    $stmt = $conn->prepare('update Subscription Set Start = ? , End = ? Where ClientID=? and BundleID=? '); 
                  
                    try
                    {	
                        $stmt->execute([$StartDate,$EndDate,$ClientID,$bundleID]);
                    }
                    catch (\PDOException $e)
                    {
                        $error = 'Operation Aborted ..'.$e->getMessage();
                        $request->getSession()->getFlashBag()->add('danger', $error);
                        return $this->redirect($request->headers->get('referer'));
                    }
                  
                  $request->getSession()->getFlashBag()->add('success', "Bundle Edited Successfully");
                        return $this->redirect($request->headers->get('referer'));
                }//
                try
                {
                     
                    throw new \Symfony\Component\Intl\Exception\OutOfBoundsException();
                } catch (\Exception $e)
                {
                   $error="Fill All Fields";
                    $request->getSession()->getFlashBag()->add('danger', $error);
                    return $this->redirect($request->headers->get('referer'));
                }
              }
              else//here it's add operation
              {
                $newbundleID		= $request->request->get('newbundle');
                $StartDate              =$request->request->get('StartDate');
                $EndDate               =$request->request->get('EndDate');
                //check if data is valid if not throw exception
                if($newbundleID&&$StartDate&&$EndDate)
                {
                    $stmt = $conn->prepare('Insert into Subscription (BundleID,ClientID,Start,End) values (?,?,?,?) ');   		
                    try
                    {	
                        $stmt->execute([$newbundleID,$ClientID, $StartDate,$EndDate]);
                    }
                    catch (\PDOException $e)
                    {
                        $error = 'Operation Aborted ..'.$e->getMessage();
                        $request->getSession()->getFlashBag()->add('danger', $error);
                        return $this->redirect($request->headers->get('referer'));
                    }
                   $request->getSession()->getFlashBag()->add('success', "Bundle Added Successfully");
                        return $this->redirect($request->headers->get('referer'));
                }
                try
                {
                     
                    throw new \Symfony\Component\Intl\Exception\OutOfBoundsException();
                } catch (\Exception $e)
                {
                   $error="Fill All Fields";
                    $request->getSession()->getFlashBag()->add('danger', $error);
                    return $this->redirect($request->headers->get('referer'));
                }
                //first get the value of post variables 
        	//$bundle_name		= $request->request->get('bundle-name');
              }
        }
    	//if it's not post request then show client bunles
        $stmt = $conn->prepare('Select * from Subscription,Bundle where BundleID=Bundle.ID and ClientID=?');   		
            	try
    	{	
            $stmt->execute([$ClientID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $bundles=$stmt->fetchAll();
        //this for the list of bundles that client are not subscripe when add subscription
        $stmt = $conn->prepare('select * from Bundle where ID not in (Select BundleID from Subscription,Bundle where BundleID=Bundle.ID and ClientID=?)');   		
            	try
    	{	
            $stmt->execute([$ClientID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $allbundles=$stmt->fetchAll();
      return $this->render('DashboardBundle:Marketing:show-client-bundles.html.twig', array(
                    'bundles' => $bundles,'allbundles'=>$allbundles)); 
       
}
public function showClientApplicationsAction($slug)
{
    
    $request=$this->get('request');
          $ClientID=$slug;
                  //connect to database
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          if ($request->getMethod() == 'POST') 
        {
              $delete		= $request->request->get('delete');
              $deletedappname		= $request->request->get('deletedapp');
              if($delete )
              {
                if($deletedappname)
                {
                    $stmt = $conn->prepare('Select ID from Application where Name=?'); 
                    try
                    {	
                        $stmt->execute([$deletedappname]);
                    }
                    catch (\PDOException $e)
                    {
                        $error = 'Operation Aborted ..'.$e->getMessage();
                        $request->getSession()->getFlashBag()->add('danger', $error);
                        return $this->redirect($request->headers->get('referer'));
                    }
                    $deletedappID=$stmt->fetchAll();
                    $deletedappID=$deletedappID[0][0];
                    $stmt = $conn->prepare('delete from Purchase Where ClientID=? and ApplicationID=? ');   		
                    try
                    {	
                        $stmt->execute([$ClientID,$deletedappID]);
                    }
                    catch (\PDOException $e)
                    {
                        $error = 'Operation Aborted ..'.$e->getMessage();
                        $request->getSession()->getFlashBag()->add('danger', $error);
                        return $this->redirect($request->headers->get('referer'));
                    }
                    $request->getSession()->getFlashBag()->add('success', "Application Removed Successfully");
                        return $this->redirect($request->headers->get('referer'));
                }
                 try
                {
                     
                    throw new \Symfony\Component\Intl\Exception\OutOfBoundsException();
                } catch (\Exception $e)
                {
                   $error="Select Application to remove";
                    $request->getSession()->getFlashBag()->add('danger', $error);
                    return $this->redirect($request->headers->get('referer'));
                }
              }
              else
              {
                $newappID		= $request->request->get('newapp');
                if($newappID)
                {
                    $stmt = $conn->prepare('Insert into Purchase (ApplicationID,ClientID,Date) values (?,?,?) ');   		
                    try
                    {	
                        $stmt->execute([$newappID,$ClientID, date('y-m-d')]);
                    }
                    catch (\PDOException $e)
                    {
                        $error = 'Operation Aborted ..'.$e->getMessage();
                        $request->getSession()->getFlashBag()->add('danger', $error);
                        return $this->redirect($request->headers->get('referer'));
                    }
                    $request->getSession()->getFlashBag()->add('success', "Application Added Successfully");
                        return $this->redirect($request->headers->get('referer'));
                }
                try
                {
                     
                    throw new \Symfony\Component\Intl\Exception\OutOfBoundsException();
                } catch (\Exception $e)
                {
                   $error="Select Application to add";
                    $request->getSession()->getFlashBag()->add('danger', $error);
                    return $this->redirect($request->headers->get('referer'));
                }
                //first get the value of post variables 
        	//$bundle_name		= $request->request->get('bundle-name');
              }
        }

        //get applications 
    	$stmt = $conn->prepare('Select *,( (select Version from ControllerInstallation as cont where clientID=Purchase.ClientID and ApplicationID=Purchase.ApplicationID and DownloadDate in(select max(cont.DownloadDate) as m from ControllerInstallation as cont where clientID=Purchase.ClientID  and ApplicationID=Purchase.ApplicationID ))) as Version from Purchase,Application where ApplicationID=Application.ID and ClientID=?');   		
            	try
    	{	
            $stmt->execute([$ClientID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $applications=$stmt->fetchAll();
        //get the application that the client does not have
        $stmt = $conn->prepare('Select *  from Application where Application.ID not in(select  ApplicationID from Purchase where ClientID=?)');   		
            	try
    	{	
            $stmt->execute([$ClientID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $apps=$stmt->fetchAll();
      return $this->render('DashboardBundle:Marketing:show-client-applications.html.twig', array(
                    'applications' => $applications,'apps'=>$apps)); 
}
public function showClientProfileAction($slug)
{
     $request=$this->get('request');
   $ClientID=$slug;
                  //connect to database
        $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($request->getMethod() == 'POST') 
            {
                //this for the update operation 
              $Name		= $request->request->get('Name');
              $Email		= $request->request->get('Email');
              $Password		= $request->request->get('Password');
              $Address		= $request->request->get('Address');
              $ID               = $request->request->get('ID');
              $stmt = $conn->prepare('update  Client Set ID=?, Name=?,Email=?,Password=?,Address=? where ID=?');   		
              try
              {	
                $stmt->execute([$ID,$Name,$Email,$Password,$Address,$ClientID]);
              }
              catch (\PDOException $e)
              {
                $error = 'Operation Aborted ..'.$e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));
              }
              $request->getSession()->getFlashBag()->add('success', "Client Profile Updated Successfully");
                        return $this->redirect($this->generateUrl('dashboard_marketing_client_profile',array('slug' => $ID)));
            }
            //if not update show the client 
        $stmt = $conn->prepare('select * from Client where ID=?');   		
        try
    	{	
            $stmt->execute([$ClientID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $client=$stmt->fetchAll();
        $client=$client[0];
        $stmt = $conn->prepare('select (select count(*) from Subscription where ClientID=Client.ID) as subcount , (select count(*) from Purchase where ClientID=Client.ID) as appcount from Client where ID=?');   		
            	try
    	{	
            $stmt->execute([$ClientID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $counts=$stmt->fetchAll();
        $counts=$counts[0];
    return $this->render('DashboardBundle:Marketing:client-profile-page.html.twig', array(
                    'client' => $client,'counts'=>$counts,'add'=>false));
}
public function addClientAction()
{
     $request=$this->get('request');
      $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($request->getMethod() == 'POST') 
            {
              $Name		= $request->request->get('Name');
              $Email		= $request->request->get('Email');
              $Password		= $request->request->get('Password');
              $Address		= $request->request->get('Address');
              $ID               = $request->request->get('ID');
               $stmt = $conn->prepare('Insert into  Client (ID,Name,Email,Password,Address) values (?,?,?,?,?)');   		
              try
              {	
                $stmt->execute([$ID,$Name,$Email,$Password,$Address]);
              }
              catch (\PDOException $e)
              {
                $error = 'Operation Aborted ..'.$e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));
              }
              $request->getSession()->getFlashBag()->add('success', "Client Added Successfully");
                        return $this->redirect($this->generateUrl('dashboard_marketing_manage_clients'));
            }
    return $this->render('DashboardBundle:Marketing:client-profile-page.html.twig', array(
                    'add'=>true));
}
public function deleteClientAction($slug)
{
    
       $request=$this->get('request');
       $ID=$slug;
      $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');	
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $deleteFromPurchase = $conn->prepare('delete from Purchase where ClientID=?');  
                $deleteFromSubscription=$conn->prepare('delete from Subscription where ClientID=?');  
                $deleteFromContins=$conn->prepare('delete from ControllerInstallation where ClientID=?');
                $deleteFromDongelins=$conn->prepare('delete from DongleInstallation where ClientID=?'); 
                $deleteFromClient=$conn->prepare('delete from Client where ID=?');
                try
              {	
                $deleteFromPurchase     ->execute([$ID]);
                $deleteFromSubscription ->execute([$ID]);
                $deleteFromContins      ->execute([$ID]);
                $deleteFromDongelins    ->execute([$ID]);
                $deleteFromClient       ->execute([$ID]);
              }
              catch (\PDOException $e)
              {
                $error = 'Operation Aborted ..'.$e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));
              }
              $request->getSession()->getFlashBag()->add('success', "Client Deleted Successfully");
              return $this->redirect($this->generateUrl('dashboard_marketing_manage_clients'));
              
}
}

<?php

namespace Iris\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use PDO;
//include 'ChromePhp.php';
//use ChromePhp;

// Flash Messages Styles
//success (green)
//info (blue)
//warning (yellow)
//danger (red)

class CommandController extends Controller
{

public function clientConfigAction($slug,$file)
{

    $response = $this->forward('DashboardBundle:Sales:getClientConfigFile', ['clientID'  => $slug,'fileName' => $file]);

    return $response;

}

public function appListAction($slug)
{
       $user = $slug;

    	$ClientID   = $user;

      $dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');

    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //get applications
    	$stmt = $conn->prepare('Select ApplicationID from Purchase WHERE Purchase.ClientID=?');

        try
    	{
            $stmt->execute([$ClientID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            
            return new Response('<html><body>'.$error.'</body></html>');
        }

        $purchasedApps = $stmt->fetchAll();
        //get the application that the client does not have

        $stmt = $conn->prepare('Select ApplicationID from Subscription,BundleApplication WHERE BundleApplication.BundleID = Subscription.BundleID AND NOW() < End AND NOW() > Start AND Subscription.ClientID = ?');

        try
    	{
            $stmt->execute([$ClientID]);
       	}
       	catch (\PDOException $e)
       	{
            $error = 'Operation Aborted ..'.$e->getMessage();
            
            return new Response('<html><body>'.$error.'</body></html>');
        }

        $subscribedApps = $stmt->fetchAll();



        $content = "";

        foreach ($purchasedApps as $app)
        {
    		$content = $content.$app[0]."\n";
		}

		foreach ($subscribedApps as $app)
        {
    		$content = $content.$app[0]."\n";
		}




	////
	////

	//$content = "com.tmendes.dadosd\n";

	$response = new Response($content);

    $d = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT/*DISPOSITION_INLINE*/, 'applist'. '.txt');
    $response->headers->set('Content-Disposition', $d);


    return $response;


/*
			$app_identifier = $slug;
			$app_version	= $version;

		 	$request=$this->get('request');

            if ( $this->scriptStillExecuting('update-store.sh'))
        	{

        	 	//return new Response('<html><body><div>An Add Application Operation is in progress, please try again later</div> <div><input type="button" value="Try Again" onClick="window.history.back()"></div></body> </html>');

        		//return $this->render('DashboardBundle:Developer:new-app.html.twig');
        		$request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Another Add/Update/Delete Application operation is in progress .. Try again in a few seconds');
        		return $this->redirect($request->headers->get('referer'));
        	}


        	$dbname     = $this->container->getParameter('store_database_name');
    		$username   = $this->container->getParameter('store_database_user');
    		$password   = $this->container->getParameter('store_database_password');
    		$servername = $this->container->getParameter('store_database_host');

    		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    		// set the PDO error mode to exception
    		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    		$stmt = $conn->prepare('SELECT * FROM Version WHERE ApplicationID = ? AND Version = ?');

    		try
    		{

				$stmt->execute([$app_identifier,$app_version]);
        	}
        	catch (\PDOException $e)
        	{
        		// if something goes wrong we fail

        		//throw $e;


    			$error = 'Operation Aborted ..'.$e->getMessage();

        		$request->getSession()->getFlashBag()->add('danger', $error);
        		return $this->redirect($request->headers->get('referer'));

        	}

    		$theversion = $stmt->fetchAll();

    		$controller_binary 	= $theversion[0]['ControllerAppName'];
    		$dongle_binary		= $theversion[0]['DongleAppName'];



        	// delete metadata

        	$success1 = unlink($controller_binary_file);
        	//$success2 = unlink($dongle_binary_file);

        	// note that we don't delete binary files, this is the responsibility of the uploader
        	// deleting the metadata is enough for the application to disappear from the store

        	if ( !$success1  )
        	{
        		$request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Unable to delete binary file:'.$controller_binary_file);
        		return $this->redirect($request->headers->get('referer'));

        	}

        	/////

    		$stmt = $conn->prepare('DELETE FROM Version WHERE ApplicationID = ? AND Version = ?');

    		try
    		{

				$stmt->execute([$app_identifier,$app_version]);
        	}
        	catch (\PDOException $e)
        	{
        		// if something goes wrong we fail

        		//throw $e;


    			$error = 'Operation Aborted ..'.$e->getMessage();

        		$request->getSession()->getFlashBag()->add('danger', $error);
        		return $this->redirect($request->headers->get('referer'));

        	}



        	$stmt = $conn->prepare('DELETE FROM ControllerInstallation WHERE ApplicationID = ? AND Version =?');

    		try
    		{

				$stmt->execute([$app_identifier,$app_version]);
        	}
        	catch (\PDOException $e)
        	{
        		// if something goes wrong we fail

        		//throw $e;


    			$error = 'Operation Aborted ..'.$e->getMessage();

        		$request->getSession()->getFlashBag()->add('danger', $error);
        		return $this->redirect($request->headers->get('referer'));

        	}

        	$stmt = $conn->prepare('DELETE FROM DongleInstallation WHERE ApplicationID = ? AND Version =?');

    		try
    		{

				$stmt->execute([$app_identifier,$app_version]);
        	}
        	catch (\PDOException $e)
        	{
        		// if something goes wrong we fail

        		//throw $e;


    			$error = 'Operation Aborted ..'.$e->getMessage();

        		$request->getSession()->getFlashBag()->add('danger', $error);
        		return $this->redirect($request->headers->get('referer'));

        	}

        	// if we are here then all is good let us launch the update script

            return $this->render('DashboardBundle:Developer:new-update-delete-app-result.html.twig',array('operation'=>'delete'));

            */
}

}

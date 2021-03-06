<?php

namespace Melodycode\FossdroidBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PDO;

//include 'ChromePhp.php';
//use ChromePhp;

class ApplicationController extends Controller {

	// this action is triggered when the id sent to the application controll is the id 
	// of an application according to the storedb and not according to the maindb
	public function _indexAction($slug)
	{
	
		$dbname = $this->container->getParameter('database_name');
    	$dbuser = $this->container->getParameter('database_user');
    	$dbpass = $this->container->getParameter('database_password');
    	$dbhost = $this->container->getParameter('database_host');
    		
    	$clientID = $this->getUser()->getUsername();
    	
    	$conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    	
    	$stmt = $conn->prepare('SELECT slug FROM application WHERE id  = ?');
		$stmt->execute([$slug]);
		
		if ( $stmt->rowCount() < 1 )
		{
            throw $this->createNotFoundException('The application does not exist');
    	}
		
		$result = $stmt->fetchAll();
		
		//var_dump($result);
		$appID = $result[0]['slug'];
	
		return $this->indexReply($appID);
	}

    public function indexAction($slug) 
    {
        return $this->indexReply($slug);
    }
    
    private function indexReply($slug)
    {
    
    	$repository = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Application');
        $application = $repository->findOneBySlug($slug);
        
    
        if (!$application) {
            throw $this->createNotFoundException('The application does not exist');
        }
        
        //ChromePhp::log($application);
        
        $redirection_arr = $this->getAppPaymentModel($application->getId());
        
        
        $bundle_arr 	 = $this->getAppBundle($application->getId());
        
        
        //ChromePhp::log($redirection_arr);
        //ChromePhp::log($bundle_arr);

        return $this->render('MelodycodeFossdroidBundle:Application:index2.html.twig', array(
                    'application' => $application,
                    'remote_browser_app' => $this->container->getParameter('melodycode_fossdroid.remote_browser_app'),
                    'redirection' => $redirection_arr,
                    'bundles' => $bundle_arr ));
    
    
    }


    public function downloadAction($slug) {
        $repository = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Application');
        $application = $repository->findOneBySlug($slug);

        if (!$application) {
            throw $this->createNotFoundException('The application does not exist');
        }

        return new RedirectResponse($this->container->getParameter('melodycode_fossdroid.remote_path_apks') . $application->getApk());
    } 
    public function buyAction($slug) 
    {
        $repository = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Application');
        $application = $repository->findOneBySlug($slug);

        if (!$application) {
            throw $this->createNotFoundException('The application does not exist');
        }

        return new RedirectResponse($this->container->getParameter('melodycode_fossdroid.remote_path_apks') . $application->getApk());
    }   
    public function getAppBundle($appID)
    {
    
    	$dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');
    
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    	
    	$stmt = $conn->prepare('SELECT * FROM Bundle WHERE ID IN (SELECT BundleID FROM BundleApplication WHERE ApplicationID = ? )');
		$stmt->execute([$appID]);
		
		$result = $stmt->fetchAll();
								
    	return $result;
    
    }
    private function getAppPaymentModel($appID)
    {
    		// open DB connection to remote DB and use the $appID to query the DB to get the
            // payment model of the application (free, purchased or subscribed) etc )
            
            $label = "Download";
            $link  = "";
            
           $dbname     = $this->container->getParameter('store_database_name');
    	   $username   = $this->container->getParameter('store_database_user');
    	   $password   = $this->container->getParameter('store_database_password');
    	   $servername = $this->container->getParameter('store_database_host');
    
		   
			//ChromePhp::log($appID);
			
			
		     //ChromePhp::log('Hello console!');
			 //ChromePhp::log($_SERVER);
			 //ChromePhp::warn('something went wrong!');

			try 
			{
    			$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    			// set the PDO error mode to exception
    			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    			
    			$stmt = $conn->prepare('SELECT * FROM Application WHERE ID = ?');
				$stmt->execute([$appID]);
    			
    			if ( $stmt->rowCount() < 1 )
    			{
    			
    				echo "Failure retrieving application from storedb ";
    				//ChromePhp::warn("Failure retrieving application from storedb ");
    			    
    				return null;
    			}
    			
    			$result = $stmt->fetchAll();
    			
    			//ChromePhp::log($result);
    			//ChromePhp::log($result[0]['Price']);
    			//echo "Connected successfully"; 
    			
    			// TODO: we are assuming we always return 1 entry, fix this later
    			
    			$price = $result[0]['Price'];
    			
    			$context = $this->container->get('security.context');
    			
    			if( !$context->isGranted('IS_AUTHENTICATED_FULLY') ) // this is an anonymous user
    			{
    				
    				$label = "Download";
            		$link  = "login";
            		
    				return ['label'=>$label,'link'=>$link];;
    			}
    			
    			$clientID = $this->getUser()->getUsername();
			
    			
    			if ( $price == 0 ) // the price is zero, so this is a free app, the option is download
    			{
    			    
    				$label = "Download";
            		$link  = "application_download";
            		
            	
    			}
    			else // app has a price
    			{
    			
    				// ToDo check that even if the client bought the application/subscribed to it, that he is now trying to install it on the same machine
    				// could do the check here (with the help of a hardwareID or could encrypt the app so that it only works on the machine it was first downloaded on
    				
    				$stmt = $conn->prepare('SELECT * FROM Purchase WHERE ApplicationID = ? AND ClientID = ?');
					$stmt->execute([$appID,$clientID]);
    			
    			 	if ( $stmt->rowCount() >= 1 ) // client has purchased the app
    			 	{
    			 		$label = "Download";
    			 		$link = "application_download";
    			 	
    			 	}
    			 	else // if not check if the client is subscribed to any of the bundles which contain the application
    			 	{
    			 		
    			 		
    			 		$stmt = $conn->prepare('SELECT * FROM Subscription WHERE ClientID = ? AND BundleID IN (SELECT BundleID FROM BundleApplication WHERE ApplicationID = ? )');
						$stmt->execute([$clientID,$appID]);
						
						$valid_subscription = 0 ; 
						
						foreach ($stmt as $row)
						{
							
							$start = $row['Start'];
							$end   = $row['End'];
							$today = $today = date("Y-m-d");
							
							if ( $today > $start && $today < $end) // if he has a valid subscription
							{
								$valid_subscription = 1;
										
								break;
							}
						}
						
    			 		if ( $valid_subscription )
    			 		{
    			 			$label = "Download";
    			 			$link = "application_download";
    			 		
    			 		}
    			 		else // the client didn't buy the application nor is subscribed to it, then offer him to buy it
    			 		{
    			 			$label = "Buy";
    			 			$link = "application_buy";
    			 		
    			 		}
    			 	}
    			}
    			
    			return ['label'=>$label,'link'=>$link];;
    			
    		}
			catch(PDOException $e)
    		{
    			echo "Connection failed: " . $e->getMessage();
    			//ChromePhp::warn('Error connecting to the storedb database or retrieving app information');
    			
    			return null;
    		}
    		
    		$conn = null;
    }

}

<?php

namespace Melodycode\FossdroidBundle\Controller;

use Doctrine\DBAL\Driver\PDOException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PDO;

//include 'ChromePhp.php';
//use ChromePhp;

class ClientController extends Controller {
	public function accountAction() 
    {
        $request=$this->get('request');

    	$context = $this->container->get('security.context');
    	
    	if( !$context->isGranted('IS_AUTHENTICATED_FULLY') )
    	//	return $this->forward('MelodycodeFossdroidBundle:Homepage:index');
    	return $this->redirect($this->generateUrl('homepage'));
        $clientID = $this->getUser()->getUsername();

        $dbname = $this->container->getParameter('store_database_name');
        $dbuser = $this->container->getParameter('store_database_user');
        $dbpass = $this->container->getParameter('store_database_password');
        $dbhost = $this->container->getParameter('store_database_host');
        $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($request->getMethod() == 'POST')
        {
            $newpass=$request->request->get('new-password');
            $stmt = $conn->prepare('update Client Set Password=?');
            try {
                $stmt->execute([$newpass]);
                $request->getSession()->getFlashBag()->add('success', "Password Updated successfully");
            }
            catch (PDOException $e)
            {
                $request->getSession()->getFlashBag()->add('danger', "Password Update failed");
            }
        }
        $stmt = $conn->prepare('select * from Client where  ID=?');
        try {
            $stmt->execute([$clientID]);
            $clientDetails=$stmt->fetch();
        }
        catch (PDOException $e)
        {

        }
        return $this->render('MelodycodeFossdroidBundle:Client:account.html.twig',array("clientDetails"=>$clientDetails));
    }

 	public function subsAction()
    {
    	$context = $this->container->get('security.context');
    	
    	if( !$context->isGranted('IS_AUTHENTICATED_FULLY') )
    	return $this->redirect($this->generateUrl('homepage'));
    	
    	$dbname = $this->container->getParameter('store_database_name');
    	$dbuser = $this->container->getParameter('store_database_user');
    	$dbpass = $this->container->getParameter('store_database_password');
    	$dbhost = $this->container->getParameter('store_database_host');
    		
    	$clientID = $this->getUser()->getUsername();
    	
    	$conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    	
    	$stmt = $conn->prepare('SELECT * FROM Bundle, Subscription WHERE Bundle.ID = Subscription.BundleID AND Subscription.ClientID = ?');
		$stmt->execute([$clientID]);
		
		$subs = $stmt->fetchAll();
		
		//var_dump($subs);
		
    	return $this->render('MelodycodeFossdroidBundle:Client:subs.html.twig', array(
                    'subs' => $subs));	
    
    }

    public function appsAction() 
    {
    
    	$context = $this->container->get('security.context');
    	
    	if( !$context->isGranted('IS_AUTHENTICATED_FULLY') )
    		return $this->redirect($this->generateUrl('homepage'));
    	
    	
    	$dbname = $this->container->getParameter('store_database_name');
    	$dbuser = $this->container->getParameter('store_database_user');
    	$dbpass = $this->container->getParameter('store_database_password');
    	$dbhost = $this->container->getParameter('store_database_host');
    		
    	$clientID = $this->getUser()->getUsername();
    	
    	//var_dump($clientID);
    	
    	$conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    	
    	$stmt = $conn->prepare('SELECT * FROM Application WHERE ID IN (SELECT ApplicationID FROM Purchase WHERE ClientID  = ?)');
		$stmt->execute([$clientID]);
		
		$purchased = $stmt->fetchAll();
		
		//var_dump($purchased);
		
		$stmt = $conn->prepare('SELECT Application.*, Bundle.Name AS BundleName, Bundle.ID AS BundleID FROM Application, Bundle, BundleApplication, Subscription WHERE Application.ID = BundleApplication.ApplicationID AND BundleApplication.BundleID = Bundle.ID AND Subscription.BundleID = Bundle.ID AND Subscription.ClientID = ? ORDER BY Application.Name;');
		$stmt->execute([$clientID]);
		
		$subscribed = $stmt->fetchAll();
		
		//var_dump($subscribed);

    	
    	return $this->render('MelodycodeFossdroidBundle:Client:apps.html.twig', array(
                    'purchased' => $purchased,
                    'subscribed' => $subscribed));	
    
    	//return new Response('<html><body>Apps Details go here</body></html>');
    
       
    }
/*
    public function getAppBundle($appID)
    {
    
    	$servername = "localhost";
		$username = "main-user";
		$password = "iris";
		$clientID = 'najah_child'; // ToDo retrieve this from session
    
    	$conn = new PDO("mysql:host=$servername;dbname=storedb", $username, $password);
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
            
            $servername = "localhost";
			$username = "main-user";
			$password = "iris";
			$clientID = 'najah_child'; // ToDo retrieve this from session
			
			//ChromePhp::log($appID);
			
			
		     //ChromePhp::log('Hello console!');
			 //ChromePhp::log($_SERVER);
			 //ChromePhp::warn('something went wrong!');

			try 
			{
    			$conn = new PDO("mysql:host=$servername;dbname=storedb", $username, $password);
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
    			
    			if ( $price == 0 ) // the price is zero, so this is a free app, the option is download
    			{
    			    
    				$label = "Download";
            		$link  = "";
            		
            	
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
    			 		$link = "";
    			 	
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
    			 			$link = "";
    			 		
    			 		}
    			 		else // the client didn't buy the application nor is subscribed to it, then offer him to buy it
    			 		{
    			 			$label = "Buy";
    			 			$link = "";
    			 		
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
*/
}

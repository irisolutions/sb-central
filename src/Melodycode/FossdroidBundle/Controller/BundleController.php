<?php

namespace Melodycode\FossdroidBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PDO;

//include 'ChromePhp.php';
//use ChromePhp;

class BundleController extends Controller 
{

    public function indexAction($slug) 
    {
    	$BundleID = $slug;
    	
    	// ToDo: in case we put the storedb on a separate host, we need to decouple the 
    	// the db connections here
    	
        $dbname     = $this->container->getParameter('database_name');
        $dbname2    = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');
    
  
		$clientID = $this->getUser()->getUsername();
    
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    	// ToDo: optimize this Subquery/Join later by adding a bundleID field in the maindb.application table. You need to change the parser script to support reading
    	// the BundleID(s) from the meta data file for each application
    	
    	$stmt = $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
		$stmt->execute([$BundleID]);
		
		$applications = $stmt->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
		
		//var_dump($applications);
		$conn = null;
		
		$conn = new PDO("mysql:host=$servername;dbname=$dbname2", $username, $password);
		$stmt = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
		$stmt->execute([$BundleID]);
		
		if ( $stmt->rowCount() < 1 )
    	{	
    		throw $this->createNotFoundException('The bundle does not exist!');
    	}
    	
    	$bundleinfo = $stmt->fetch();
        
        return $this->render('MelodycodeFossdroidBundle:Bundle:index.html.twig', array(
                    'bundle' => $bundleinfo,
                    'applications' => $applications
        ));
    	
    }
/*
    public function whatsnewAction($slug) 
    {
        $repository_category = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Category');
        $category = $repository_category->findOneBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException('The category does not exist');
        }

        $repository_application = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Application');
        $applications = $repository_application->findByPublished(0, 'created_at', $slug);

        return $this->render('MelodycodeFossdroidBundle:Category:whatsnew.html.twig', array(
                    'category' => $category,
                    'applications' => $applications
        ));
    }
*/    

}
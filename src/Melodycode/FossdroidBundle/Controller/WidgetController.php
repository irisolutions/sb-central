<?php

namespace Melodycode\FossdroidBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PDO;

class WidgetController extends Controller {

    public function whatsnewAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Application');
        $applications = $repository->findByPublished($this->container->getParameter('melodycode_fossdroid.widget_limit'), 'created_at', $request->get('slug_selected'));

        return $this->render('MelodycodeFossdroidBundle:Widget:whatsnew.html.twig', array(
                    'applications' => $applications,
                    'category_slug' => $request->get('slug_selected')
                        )
        );
    }

    public function categoriesAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Category');
        $categories = $repository->findBy(array('is_published' => 1), array('count' => 'DESC'));
        
        
    	$dbname     = $this->container->getParameter('store_database_name');
    	$username   = $this->container->getParameter('store_database_user');
    	$password   = $this->container->getParameter('store_database_password');
    	$servername = $this->container->getParameter('store_database_host');
    	try
	{	
    		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    		// set the PDO error mode to exception
    		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    		$stmt = $conn->prepare('SELECT * FROM Bundle ORDER BY Name');
		$stmt->execute();
		
		$bundles = $stmt->fetchAll();
		
		
        	return $this->render('MelodycodeFossdroidBundle:Widget:categories.html.twig', array(
                    'categories' => $categories,
                    'slug_selected' => $request->get('slug_selected'),
                    'bundles'=> $bundles
                        )
        	);
	}
	catch(PDOException $e)
	{
		return new Response(
            '<html><body>Your Name is: hi</body></html>');
	}
return new Response(
            '<html><body>Your Name is: hi</body></html>');
    }

    public function searchAction(Request $request) {
        return $this->render('MelodycodeFossdroidBundle:Widget:search.html.twig', array(
                    'q' => $request->get('q')
                        )
        );
    }

}

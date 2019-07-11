<?php

namespace Iris\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PDO;

//include 'ChromePhp.php';
//use ChromePhp;


class AdminController extends Controller {
	public function accountAction() 
    {

    	
    	$context = $this->container->get('security.context');
    	

    	if( !$context->isGranted('IS_AUTHENTICATED_FULLY') )
    	//	return $this->forward('MelodycodeFossdroidBundle:Homepage:index');
    	return $this->redirect($this->generateUrl('homepage'));
        
        $request=$this->get('request');
        $ID=$this->getUser()->getUsername();
        $dbname     = $this->container->getParameter('store_database_name');
        $username   = $this->container->getParameter('store_database_user');
        $password   = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');

        //print("contacting db for admin user/pass");
        //return;

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($request->getMethod() == 'POST')
        {
            $newID				= $request->request->get('ID');
            $UserName 				= $request->request->get('Name');
            $Password 				= $request->request->get('Password');
            $Email 				= $request->request->get('Email');

            $stmt = $conn->prepare('UPDATE Administrator SET ID =?, Name=?,Password=?, Email=? WHERE ID=?');
            try
            {
                $stmt->execute([$newID,$UserName,$Password,$Email,$ID]);
            }        catch (\PDOException $e)
            {
                // if something goes wrong we fail

                //throw $e;


                $error = 'Operation Aborted ..'.$e->getMessage();
                print($error);
                die();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));

            }

            return $this->redirect($request->headers->get('referer'));


        }

        $stmt = $conn->prepare('select * from Administrator where ID=?');
        try
        {
            $stmt->execute([$ID]);
        }        catch (\PDOException $e)
        {
            // if something goes wrong we fail

            //throw $e;


            $error = 'Operation Aborted ..'.$e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));

        }
        $userDetail=$stmt->fetch();
    	return $this->render('DashboardBundle:Account:account.html.twig',array('user'=>$userDetail));
    }

 	
}

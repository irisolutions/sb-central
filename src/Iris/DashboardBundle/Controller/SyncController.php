<?php
/**
 * Created by PhpStorm.
 * User: Khaled
 * Date: 3/14/2018
 * Time: 4:44 PM
 */

namespace Iris\DashboardBundle\Controller;

use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class SyncController extends Controller
{
    public function getAppsAction()
    {
        $request = $this->get('request');
        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
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
//        $applications = $stmt->fetchObject();
        $applications = $stmt->fetchAll(PDO::FETCH_OBJ);

//        $request->getSession()->getFlashBag()->add('success', "Client Added Successfully");
        return new JsonResponse(array('success'=>true,'apps'=>$applications));
    }

    function getAppsSyncAction()
    {
        $request = $this->get('request');
        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $stmt = $conn->prepare('SELECT * FROM applicationssync');
        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            $error = 'Operation Aborted ..' . $e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
//        $applications = $stmt->fetchAll();
        $applications = $stmt->fetchAll(PDO::FETCH_OBJ);

//        $request->getSession()->getFlashBag()->add('success', "Client Added Successfully");
        return new JsonResponse(array('success' => true, 'apps' => $applications));
    }

}
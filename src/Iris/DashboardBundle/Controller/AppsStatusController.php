<?php
/**
 * Created by PhpStorm.
 * User: Khaled
 * Date: 4/9/2018
 * Time: 2:40 PM
 */

namespace Iris\DashboardBundle\Controller;


use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
class AppsStatusController extends Controller
{

    public function getControllerAppsAction()
    {
        $request = $this->get('request');
        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $userName = $request->request->get('UserName');

            $stmt1 = $conn->prepare('Select * from controllerinstallation where ClientID=?');
            try {
                $stmt1->execute([$userName]);
            } catch (\PDOException $e) {
                $error = 'Operation Aborted ..' . $e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
//                return $this->redirect($request->headers->get('referer'));
                return new JsonResponse(array('success'=>false));
            }
            $applications = $stmt1->fetchAll(PDO::FETCH_OBJ);
            return new JsonResponse(array('success'=>true,'apps'=>$applications));
        }

        //todo -Khaled- : return status instead of status number
        return new JsonResponse(array('success'=>false));
    }


    public function getDongleAppsAction()
    {
        $request = $this->get('request');
        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $userName = $request->request->get('UserName');

            $stmt1 = $conn->prepare('Select * from dongleinstallation where ClientID=?');
            try {
                $stmt1->execute([$userName]);
            } catch (\PDOException $e) {
                $error = 'Operation Aborted ..' . $e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
//                return $this->redirect($request->headers->get('referer'));
                return new JsonResponse(array('success'=>false));
            }
            $applications = $stmt1->fetchAll(PDO::FETCH_OBJ);
            return new JsonResponse(array('success'=>true,'apps'=>$applications));
        }

        //todo -Khaled- : return status instead of status number
        return new JsonResponse(array('success'=>false));
    }

   }
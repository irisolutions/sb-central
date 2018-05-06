<?php
/**
 * Created by PhpStorm.
 * User: Khaled
 * Date: 2/20/2018
 * Time: 12:21 PM
 */

namespace Iris\DashboardBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PDO;
use Symfony\Component\HttpFoundation\JsonResponse;


class TokensController extends Controller
{


    function addNewTokenAction()
    {
        $request = $this->get('request');
        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($request->getMethod() == 'POST') {
            $token = $request->request->get('Token');
            $userName = $request->request->get('UserName');
            $type = $request->request->get('Type');

            //todo : khaled 21/Feb Try to make one access to DB
            //todo : khaled 21/Feb Check tokens and emails before add new one

//            INSERT INTO Tokens (Token,Device,UID) VALUES (?,?,?) ON DUPLICATE KEY UPDATE Token=?
//            $stmt = $conn->prepare('Insert into  Tokens (Token,Device,UID) values (?,?,? )');
            $stmt = $conn->prepare('INSERT INTO Tokens (Token,Device,UID) VALUES (?,?,?) ON DUPLICATE KEY UPDATE Token = ?');
            try {
                $stmt->execute([$token, $type, $userName,$token]);
            } catch (\PDOException $e) {
                $error = 'Operation Aborted ..' . $e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return new JsonResponse(array('token' => 'fail', 'error' => $error));
            }
            $request->getSession()->getFlashBag()->add('success', "Client Added Successfully");
            return new JsonResponse(array('token' => $token, 'UID' => $userName, 'type' => $type));
        }
        return new JsonResponse(array('token' => $token, 'UID' => $userName, 'type' => $type));
    }

    function deleteTokenAction()
    {
        $request = $this->get('request');
        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($request->getMethod() == 'POST') {
            $token = $request->request->get('Token');
            $userName = $request->request->get('UserName');
            $type = $request->request->get('Type');

//            $stmt = $conn->prepare('DELETE FROM Tokens WHERE Token = ? AND Device = ? AND UID = ?)');
            $stmt = $conn->prepare('DELETE FROM Tokens WHERE Token = ?');

            try {
//                $stmt->execute([$token, $type, $userName]);
                $stmt->execute([$token]);
            } catch (\PDOException $e) {
                $error = 'Operation Aborted ..' . $e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
                return new JsonResponse(array('token' => 'fail', 'error' => $error));
            }
            $request->getSession()->getFlashBag()->add('success', "Client Added Successfully");
            return new JsonResponse(array('token' => $token, 'UID' => $userName, 'type' => $type));
        }
        return new JsonResponse(array('token' => 'fail', 'error' => "no post request"));
    }

}
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
//            $stmt1 = $conn->prepare('Select ID from Client where Email=?');
//            try {
//                $stmt1->execute([$email]);
//                $stmt1->execute([$localemail]);
//            } catch (\PDOException $e) {
//                $error = 'Operation Aborted ..' . $e->getMessage();
//                $request->getSession()->getFlashBag()->add('danger', $error);
//                return $this->redirect($request->headers->get('referer'));
//            }
//            $UID = $stmt1->fetchAll()[0][0];
//            $stmt = $conn->prepare('Insert into  Tokens (TOKEN,TYPE,UID) values (?,?,? )');
            $stmt = $conn->prepare('Insert into  tokens (TOKEN,TYPE,UID) values (?,?,? )');
            try {
                $stmt->execute([$token, $type, $userName]);
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

    public function getTokens($slug)
    {

        $user = $slug;
        $ClientID = $user;

        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //get applications
        $stmt = $conn->prepare('Select ApplicationID from Purchase WHERE Purchase.ClientID=?');

        try {
            $stmt->execute([$ClientID]);
        } catch (\PDOException $e) {
            $error = 'Operation Aborted ..' . $e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }

        $purchasedApps = $stmt->fetchAll();
        //get the application that the client does not have

        $stmt = $conn->prepare('Select ApplicationID from Subscription,BundleApplication WHERE BundleApplication.BundleID = Subscription.BundleID AND NOW() < End AND NOW() > Start AND Subscription.ClientID = ?');

        try {
            $stmt->execute([$ClientID]);
        } catch (\PDOException $e) {
            $error = 'Operation Aborted ..' . $e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }

        $subscribedApps = $stmt->fetchAll();

        $content = "";

        foreach ($purchasedApps as $app) {
            $content = $content . $app[0] . "\n";
        }

        foreach ($subscribedApps as $app) {
            $content = $content . $app[0] . "\n";
        }

        //$content = "com.tmendes.dadosd\n";
        $response = new Response($content);

        $d = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT/*DISPOSITION_INLINE*/, 'applist' . '.txt');
        $response->headers->set('Content-Disposition', $d);

        return $response;
    }

}
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
use Symfony\Component\Process\Process;
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

            $stmt1 = $conn->prepare('Select * from ControllerInstallation where ClientID=?');
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

            $stmt1 = $conn->prepare('Select * from DongleInstallation where ClientID=?');
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


    public function changeControllerAppStatusAction()
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
            $appID = $request->request->get('appID');
            $status= $request->request->get('status');

            $stmt1 = $conn->prepare('update ControllerInstallation set status=? where ClientID=? and ApplicationID=?');
            try {
                $stmt1->execute([$status,$userName,$appID]);
            } catch (\PDOException $e) {
                $error = 'Operation Aborted ..' . $e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
//                return $this->redirect($request->headers->get('referer'));
                return new JsonResponse(array('success'=>false));
            }
//            $applications = $stmt1->fetchAll(PDO::FETCH_OBJ);
            return new JsonResponse(array('success'=>true));
        }

        //todo -Khaled- : return status instead of status number
        return new JsonResponse(array('success'=>false));
    }


    public function changeDongleAppStatusAction()
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
            $appID = $request->request->get('appID');
            $status= $request->request->get('status');

            $stmt1 = $conn->prepare('update DongleInstallation set status=? where ClientID=? and ApplicationID=?');
            try {
                $stmt1->execute([$status,$userName,$appID]);
            } catch (\PDOException $e) {
                $error = 'Operation Aborted ..' . $e->getMessage();
                $request->getSession()->getFlashBag()->add('danger', $error);
//                return $this->redirect($request->headers->get('referer'));
                return new JsonResponse(array('success'=>false));
            }
//            $applications = $stmt1->fetchAll(PDO::FETCH_OBJ);
            return new JsonResponse(array('success'=>true));
        }

        //todo -Khaled- : return status instead of status number
        return new JsonResponse(array('success'=>false));
    }
    public  function downloadAppAction($slug,$client)
    {
        $request = $this->get('request');
        $applicationID = $slug;
        //application ID ready
        if ($this->getUser()) {
            //get user ID
            $clientID = $client;
            //user ID ready

//           get application type
            $dbname = $this->container->getParameter('store_database_name');
            $dbuser = $this->container->getParameter('store_database_user');
            $dbpass = $this->container->getParameter('store_database_password');
            $dbhost = $this->container->getParameter('store_database_host');
            $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare('SELECT * FROM storedb.Application WHERE storedb.Application.ID  = ?');
            $stmt->execute([$applicationID]);


            $applicationDetail = $stmt->fetch();
            $stmt = $conn->prepare('SELECT * FROM storedb.Client WHERE storedb.Client.Name  = ?');
            $stmt->execute([$client]);
            $clientID=$stmt->fetch()['ID'];

            //application type ready
            //change status
            $stmt = $conn->prepare('SELECT * FROM storedb.Status WHERE storedb.Status.status= "website_downloaded" ');
            $stmt->execute();
            $status = $stmt->fetch()['PK'];
            $date = date("Y-m-d H:i:s");

            if ($applicationDetail['Type'] == 'dongle') {
                //GET THE WEBSITE DOWNLOADED NUMBER
                $stmt = $conn->prepare('UPDATE storedb.DongleInstallation  SET storedb.DongleInstallation.Status = ? , DongleInstallation.WebDownloadDate=? where storedb.DongleInstallation.ApplicationID=? and storedb.DongleInstallation.ClientID=?');
                $stmt->execute([$status, $date, $applicationID, $clientID]);

            } elseif ($applicationDetail['Type'] == 'tablet') {
                $stmt = $conn->prepare('UPDATE storedb.ControllerInstallation  SET storedb.ControllerInstallation.Status = ?  , ControllerInstallation.WebDownloadDate=?where storedb.ControllerInstallation.ApplicationID=? and storedb.ControllerInstallation.ClientID=?');
                $stmt->execute([$status, $date, $applicationID, $clientID]);

            }
            $this->pushNotification('curl --data "AppID=' . $applicationID . '&Type=' . $applicationDetail['Type'] . '&UserName=' . $clientID . '"http://iris-store.iris.ps/IrisCentral/web/app_dev.php/dashboard/command/pushDownloadNotification');

        }
        return $this->redirect($request->headers->get('referer'));

    }
    public function uninstallApplicationAction($slug,$client)
    {
        $request = $this->get('request');
        $applicationID=$slug;

        if($this->getUser())
        {
            //get user ID

            //user ID ready

//           get application type
            $dbname = $this->container->getParameter('store_database_name');
            $dbuser = $this->container->getParameter('store_database_user');
            $dbpass = $this->container->getParameter('store_database_password');
            $dbhost = $this->container->getParameter('store_database_host');
            $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare('SELECT * FROM storedb.Application WHERE storedb.Application.ID  = ?');
            $stmt->execute([$applicationID]);
            $applicationDetail = $stmt->fetch();
            //application type ready
            //change status
            $stmt = $conn->prepare('SELECT * FROM storedb.Client WHERE storedb.Client.Name  = ?');
            $stmt->execute([$client]);
            $clientID =$stmt->fetch()['ID'];
            $stmt = $conn->prepare('SELECT * FROM storedb.Status WHERE storedb.Status.status= "uninstall" ');
            $stmt->execute();
            $status=$stmt->fetch()['PK'];

            if ($applicationDetail['Type'] == 'dongle') {
                //GET THE WEBSITE DOWNLOADED NUMBER
                $stmt = $conn->prepare('UPDATE storedb.DongleInstallation  SET storedb.DongleInstallation.Status = ?  where storedb.DongleInstallation.ApplicationID=? and storedb.DongleInstallation.ClientID=?');
                $stmt->execute([$status,$applicationID, $clientID]);
            } elseif ($applicationDetail['Type'] == 'tablet') {
                $stmt = $conn->prepare('UPDATE storedb.ControllerInstallation  SET storedb.ControllerInstallation.Status = ?  where storedb.ControllerInstallation.ApplicationID=? and storedb.ControllerInstallation.ClientID=?');
                $stmt->execute([$status,$applicationID, $clientID]);
            }

            $this->pushNotification('curl --data "AppID='.$applicationID.'&Type='.$applicationDetail['Type'].'&UserName='.$clientID.'"http://iris-store.iris.ps/IrisCentral/web/app_dev.php/dashboard/command/pushDownloadNotification');
            return $this->redirect($request->headers->get('referer'));

        }
    }
    public function pushNotification($message)
    {
        $process = new Process($message);
        $process->run();
    }
}
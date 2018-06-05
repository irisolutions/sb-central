<?php

namespace Iris\DashboardBundle\Controller;

use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

//include 'ChromePhp.php';
//use ChromePhp;

// Flash Messages Styles
//success (green)
//info (blue)
//warning (yellow)
//danger (red)

class Developer1Controller extends Controller
{

    public function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }


    public function liveExecuteCommand($cmd)
    {

        while (@ ob_end_flush()) ; // end all output buffers if any

        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output = "";
        $complete_output = "";

        while (!feof($proc)) {
            $live_output = fread($proc, 4096);
            $complete_output = $complete_output . $live_output;
            echo "$live_output";
            @ flush();
        }

        pclose($proc);

        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        return array(
            'exit_status' => intval($matches[0]),
            'output' => str_replace("Exit status : " . $matches[0], '', $complete_output)
        );
    }

    public function scriptStillExecuting($script)
    {

        $command = 'pgrep -fl sh.*.sudo.*apache.*' . $script;
        //$command = 'pgrep -fl '.$script;
        $output = shell_exec($command);

        $request = $this->get('request');

//        return (strlen($output) > 0);

    }

    public function trackingAction()
    {
        return $this->render('DashboardBundle:Sales:Tracking.html.twig');
    }

    public function executeCommand($cmd)
    {

        $response = new StreamedResponse();
        $script = $cmd . ' 2>&1';
        $process = new Process($script);

        $response->setCallback(function () use ($process) {
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo '' . $buffer; // standard output
                } else {
                    echo '' . $buffer; // standard error
                    //echo '<br>';
                }
                ob_flush();
                flush();

            });
        });

        $response->setStatusCode(200);

        return $response;
    }


    public function newAppResultAction()
    {
        return $this->executeCommand('sudo -u apache /var/www/html/update-store.sh');
        $context = $this->container->get('security.context');

        if (!$context->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render('DashboardBundle:Homepage:index.html.twig');

        //auth
//        return $this->executeCommand('sudo -u apache /var/www/html/update-store.sh');

        $response = new StreamedResponse();
        $script = $cmd . ' 2>&1';
        $process = new Process($script);

        $response->setCallback(function () use ($process) {
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo '' . $buffer; // standard output
                } else {
                    echo '' . $buffer; // standard error
                }
                ob_flush();
                flush();

            });
        });
        $response->setStatusCode(200);
        return $response;
    }

    public function manageVersionAction($slug)
    {
        $context = $this->container->get('security.context');

        if (!$context->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render('DashboardBundle:Homepage:index.html.twig');

        //auth
        $dbname = $this->container->getParameter('store_database_name');
        $dbuser = $this->container->getParameter('store_database_user');
        $dbpass = $this->container->getParameter('store_database_password');
        $dbhost = $this->container->getParameter('store_database_host');

        $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare('SELECT * FROM Application WHERE ID=? ;');
        $stmt->execute([$slug]);

        $name = $stmt->fetchAll();

        $stmt = $conn->prepare('SELECT * FROM Version WHERE ApplicationID=? ;');
        $stmt->execute([$slug]);

        $versions = $stmt->fetchAll();

        $stmt = $conn->prepare('SELECT COUNT(*) AS Count FROM Version WHERE ApplicationID=? ;');
        $stmt->execute([$slug]);

        $count = $stmt->fetchAll();

        return $this->render('DashboardBundle:Developer:manage-version.html.twig', array(
            'name' => $name[0]['Name'],
            'versions' => $versions,
            'count' => $count[0]['Count']));
    }

    public function manageAppAction()
    {
        $context = $this->container->get('security.context');

        if (!$context->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render('DashboardBundle:Homepage:index.html.twig');

        //auth
        $dbname = $this->container->getParameter('store_database_name');
        $dbuser = $this->container->getParameter('store_database_user');
        $dbpass = $this->container->getParameter('store_database_password');
        $dbhost = $this->container->getParameter('store_database_host');

        $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare('select *,count(*) as ' . "'Count'" . ' from storedb.Application,storedb.Version WHERE storedb.Application.ID = storedb.Version.ApplicationID GROUP BY storedb.Version.ApplicationID;');
        $stmt->execute();

        $applications = $stmt->fetchAll();

        return $this->render('DashboardBundle:Developer:manage-app.html.twig', array(
            'applications' => $applications));
    }
    public function get_Store_DB_Object()
    {
        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }
    public function typeChangeTo($app_id,$type,$request)
    {
        $conn = $this->get_Store_DB_Object();
        $stmt="";
        $request->getSession()->getFlashBag()->add('danger', $type);

        if($type=="dongle")
        {
            $stmt = $conn->prepare('INSERT INTO DongleInstallation (DongleInstallation.ClientID,DongleInstallation.ApplicationID,DongleInstallation.Version,DongleInstallation.Subscription,DongleInstallation.Status)
            SELECT ControllerInstallation.ClientID,ControllerInstallation.ApplicationID,ControllerInstallation.Version,ControllerInstallation.Subscription,ControllerInstallation.Status from ControllerInstallation where ControllerInstallation.ApplicationID=?;
            delete from ControllerInstallation where ControllerInstallation.ApplicationID=?;');
            $stmt->execute([$app_id,$app_id]);
            $request->getSession()->getFlashBag()->add('danger', "i am here change to dongel  ");

        }
        else
        {
            $stmt=$conn->prepare('INSERT INTO ControllerInstallation (ControllerInstallation.ClientID,ControllerInstallation.ApplicationID,ControllerInstallation.Version,ControllerInstallation.Subscription,ControllerInstallation.Status)
            SELECT DongleInstallation.ClientID,DongleInstallation.ApplicationID,DongleInstallation.Version,DongleInstallation.Subscription,DongleInstallation.Status from DongleInstallation where DongleInstallation.ApplicationID=?;
            delete from DongleInstallation where DongleInstallation.ApplicationID=?;');
            $stmt->execute([$app_id,$app_id]);
            $request->getSession()->getFlashBag()->add('danger', "i am here change to tablet");

        }
    }
    public function UpdateApplication($request)
    {
        $app_name = $request->request->get('app-name');
        $app_description = $request->request->get('app-description');
        $app_identifier = $request->request->get('app-identifier');
        $app_price = $request->request->get('app-price');
        $app_summary = $request->request->get('app-summary');
        $app_category = $request->request->get('app-category');
        $app_type = $request->request->get('app-type');

        // we check if the two binary files are where they should be other wise we fail
        $repo_dir = $this->container->getParameter('melodycode_fossdroid.local_path_repo');
        $metadata_dir = $this->container->getParameter('melodycode_fossdroid.local_path_metadata');
        $metadata_file = $metadata_dir . '/' . $app_identifier . '.txt';
        $content = 'License:Unknown' . PHP_EOL . 'Web Site:' . PHP_EOL . 'Source Code:' . PHP_EOL . 'Issue Tracker:' . PHP_EOL . 'Changelog:' . PHP_EOL . 'Summary:%s' . PHP_EOL . 'Description:' . PHP_EOL . '%s' . PHP_EOL . '.' . PHP_EOL . 'Name:%s' . PHP_EOL . 'Categories:%s' . PHP_EOL . '';
        $content = sprintf($content, $app_summary, $app_description, $app_name, $app_category);
        $success = file_put_contents($metadata_file, $content, LOCK_EX);

        if (!$success) {
            $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Unable to write metadata file');
            return $this->redirect($request->headers->get('referer'));
        }
        $conn = $this->get_Store_DB_Object();
        $this->typeChangeTo($app_identifier,$app_type,$request);
        $stmt = $conn->prepare('UPDATE Application SET ID =?, Name=?,Price=?, Type=? WHERE ID=?');
        try {
            $stmt->execute([$app_identifier, $app_name, $app_price, $app_type, $app_identifier]);
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) // contrains violation i.e. the app_id:app_version already exists
            {
                // Take some action if there is a key constraint violation, i.e. duplicate name
                $error = 'Operation Aborted .. The Application Identifier must be unique [Error]' . $e->getMessage();
            } else {
                $error = 'Operation Aborted ..' . $e->getMessage();
            }
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        return $this->render('DashboardBundle:Developer:new-update-delete-app-result.html.twig', array('operation' => 'update'));
    }
    public function editAppAction($slug)
    {
        $context = $this->container->get('security.context');
        if (!$context->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render('DashboardBundle:Homepage:index.html.twig');

        //if we are here user authenticated
        // if we are here then the binary files are in place and the meta data file was created so we insert into the DB
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            if ($this->scriptStillExecuting('update-store.sh')) {
                //return new Response('<html><body><div>An Add Application Operation is in progress, please try again later</div> <div><input type="button" value="Try Again" onClick="window.history.back()"></div></body> </html>');
                $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Another Add/Update/Delete Application operation is in progress .. Try again in a few seconds');
                return $this->redirect($request->headers->get('referer'));
            }

            return $this->UpdateApplication($request);
            // if we are here then all is good let us launch the update script
        }
        $conn = $this->get_Store_DB_Object();
        $stmt = $conn->prepare('SELECT * FROM Application WHERE ID = ?');
        $stmt->execute([$slug]);
        $result = $stmt->fetchAll();
        $repo_dir = $this->container->getParameter('melodycode_fossdroid.local_path_repo');
        $metadata_dir = $this->container->getParameter('melodycode_fossdroid.local_path_metadata');
        // now we create the meta data file for the application
        $metadata_file = $metadata_dir . '/' . $slug . '.txt';
        if (!file_exists($metadata_file)) {
            $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. metadata file:' . $metadata_file . ' does not exist');
            return $this->redirect($request->headers->get('referer'));
        }

        $content = file_get_contents($metadata_file);
        $summary = $this->get_string_between($content, 'Summary:', 'Description');
        $description = $this->get_string_between($content, 'Description:', 'Name:');
        $start = strpos($content, 'Categories:') + strlen('Categories:');
        $end = strlen($content);
        $appCategory = substr($content, $start, $end);
        $appCategory = trim($appCategory);
        $summary = trim($summary);
        $description = trim($description);

        $stmt = $conn->prepare('SELECT * FROM Category');
        $stmt->execute();
        $categories = $stmt->fetchAll();

        return $this->render('DashboardBundle:Developer:new-update-app-1.html.twig', array(
            'update' => true,
            'application' => $result[0],
            'categories' => $categories,
            'appCategory' => $appCategory,
            'summary' => $summary,
            'description' => $description));

    }

    public function newVersionAction($slug)
    {
        $context = $this->container->get('security.context');

        if (!$context->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render('DashboardBundle:Homepage:index.html.twig');
        //if we are here user authenticated
        $app_identifier = $slug;
        $request = $this->get('request');
        if ($this->scriptStillExecuting('update-store.sh')) {
            $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Another Add/Update/Delete Application operation is in progress .. Try again in a few seconds');
            return $this->redirect($request->headers->get('referer'));
        }
        if ($request->getMethod() == 'POST') {
            $app_identifier = $request->request->get('app-identifier');
            $app_version = $request->request->get('app-version');
            // we check if the two binary files are where they should be other wise we fail
            $repo_dir = $this->container->getParameter('melodycode_fossdroid.local_path_repo');
            $metadata_dir = $this->container->getParameter('melodycode_fossdroid.local_path_metadata');
            $target_dir = $repo_dir;
            $target_file = $target_dir . '/' . basename($_FILES["app-binary"]["name"]);
            $controller_file = $repo_dir . '/' . basename($_FILES["app-binary"]["name"]);
            $dongle_file = $repo_dir . '/' . basename($_FILES["app-binary"]["name"]);
            $FileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if (file_exists($target_file)) {
                $request->getSession()->getFlashBag()->add('danger', 'File Already Exist');
                return $this->redirect($request->headers->get('referer'));
            }
            if ($FileType != "apk") {
                $request->getSession()->getFlashBag()->add('danger', 'Uploaded file is not apk file');
                return $this->redirect($request->headers->get('referer'));
            }
            if (!move_uploaded_file($_FILES["app-binary"]["tmp_name"], $target_file)) {
                $request->getSession()->getFlashBag()->add('danger', 'Sorry, there was an error uploading your file.');
                return $this->redirect($request->headers->get('referer'));
            }
            if (!file_exists($controller_file) /*|| !file_exists($dongle_file)*/) {
                $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Controller and/or Dongle binary file(s) do(es) not exist');
                return $this->redirect($request->headers->get('referer'));

            }

            $request->getSession()->getFlashBag()->add('success', 'Done.');

            // if we are here then the binary files are in place so we insert into the DB

            $dbname = $this->container->getParameter('store_database_name');
            $username = $this->container->getParameter('store_database_user');
            $password = $this->container->getParameter('store_database_password');
            $servername = $this->container->getParameter('store_database_host');

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $app_controller_binary = basename($_FILES["app-binary"]["name"]);

            try {
                $stmt = $conn->prepare('SELECT * FROM Application WHERE Application.ID=?');
                $stmt->execute([$app_identifier]);
                $app = $stmt->fetch();
                if ($app['Type'] == 'dongle') {

                    $stmt = $conn->prepare('INSERT INTO Version (Version,ApplicationID,DongleAppName,ControllerAppName) VALUES (?,?,?,?)');
                    $request->getSession()->getFlashBag()->add('danger', $app_controller_binary);
                    $stmt->execute([$app_version, $app_identifier, $app_controller_binary, $app_controller_binary]);
                    $stmt = $conn->prepare('update DongleInstallation set DongleInstallation.Status=(select Status.PK from Status where Status.status="need_update") where DongleInstallation.ApplicationID=?');
                    $stmt->execute([$app_identifier]);
                } else {
                    $stmt = $conn->prepare('INSERT INTO Version (Version,ApplicationID,DongleAppName,ControllerAppName) VALUES (?,?,?,?)');
                    $stmt->execute([$app_version, $app_identifier, $app_controller_binary, $app_controller_binary]);
                    $stmt = $conn->prepare('update ControllerInstallation set ControllerInstallation.Status=(select Status.PK from Status where Status.status="need_update") where ControllerInstallation.ApplicationID=?');
                    $stmt->execute([$app_identifier]);
                }

            } catch (\PDOException $e) {
                // if something goes wrong we fail

                //throw $e;

                $error = '';

                if ($e->errorInfo[1] == 1062) // contrains violation i.e. the app_id:app_version already exists
                {
                    // Take some action if there is a key constraint violation, i.e. duplicate name
                    $error = 'Operation Aborted .. The Application Identifier & Version must be unique [Error]' . $e->getMessage();
                } else
                    $error = 'Operation Aborted ..' . $e->getMessage();

                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));

            }

            // if we are here then all is good let us launch the update script

            return $this->render('DashboardBundle:Developer:new-update-delete-app-result.html.twig', array('operation' => 'new'));

            /*
            $msg = 'The new version was added successfully';
            $request->getSession()->getFlashBag()->add('success', $msg);
            return $this->redirect($request->headers->get('referer'));
            */
            //return $this->render('DashboardBundle:Developer:new-update-delete-app-result.html.twig',array('operation'=>'new'));
            //return $this->redirect($request->getUri());
        }


        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare('SELECT * FROM Application WHERE ID = ?');
        $stmt->execute([$app_identifier]);

        $applications = $stmt->fetchAll();


        return $this->render('DashboardBundle:Developer:new-update-version1.html.twig', array('update' => false, 'application' => $applications[0]));


    }

    public function deleteVersionAction($slug, $version)
    {
        $context = $this->container->get('security.context');

        if (!$context->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render('DashboardBundle:Homepage:index.html.twig');

        //auth
        $app_identifier = $slug;
        $app_version = $version;

        $request = $this->get('request');

        if ($this->scriptStillExecuting('update-store.sh')) {
            $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Another Add/Update/Delete Application operation is in progress .. Try again in a few seconds');
            return $this->redirect($request->headers->get('referer'));
        }

        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare('SELECT * FROM Version WHERE ApplicationID = ? AND Version = ?');

        try {

            $stmt->execute([$app_identifier, $app_version]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail
            //throw $e;

            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }

        $theversion = $stmt->fetchAll();
        $controller_binary = $theversion[0]['ControllerAppName'];
        $dongle_binary = $theversion[0]['DongleAppName'];

        $repo_dir = $this->container->getParameter('melodycode_fossdroid.local_path_repo');

        $controller_binary_file = $repo_dir . '/' . $controller_binary;
        $controller_dongle_file = $repo_dir . '/' . $dongle_binary;

        if (!file_exists($controller_binary_file) /*|| !file_exists($dongle_binary_file)*/) {
            $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. unable to find binary file:' . $controller_binary_file);
            return $this->redirect($request->headers->get('referer'));

        }

        $success1 = unlink($controller_binary_file);
        //$success2 = unlink($dongle_binary_file);

//        Todo : khaled : delete meta date
        // note that we don't delete binary files, this is the responsibility of the uploader
        // deleting the metadata is enough for the application to disappear from the store

        if (!$success1 /*|| !$success2*/) {
            $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Unable to delete binary file:' . $controller_binary_file);
            return $this->redirect($request->headers->get('referer'));
        }

        $stmt = $conn->prepare('DELETE FROM Version WHERE ApplicationID = ? AND Version = ?');

        try {

            $stmt->execute([$app_identifier, $app_version]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail
            //throw $e;
            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }

        $stmt = $conn->prepare('DELETE FROM ControllerInstallation WHERE ApplicationID = ? AND Version =?');

        try {
            $stmt->execute([$app_identifier, $app_version]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail
            //throw $e;
            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }

        $stmt = $conn->prepare('DELETE FROM DongleInstallation WHERE ApplicationID = ? AND Version =?');

        try {

            $stmt->execute([$app_identifier, $app_version]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail
            //throw $e;
            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }

        // if we are here then all is good let us launch the update script
        return $this->render('DashboardBundle:Developer:new-update-delete-app-result.html.twig', array('operation' => 'delete'));
    }

    public function deleteAppAction($slug)
    {
        $context = $this->container->get('security.context');

        if (!$context->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render('DashboardBundle:Homepage:index.html.twig');

        //auth
        $app_identifier = $slug;

        $request = $this->get('request');

        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare('SELECT COUNT(*) AS Count FROM Version WHERE ApplicationID = ?');

        try {

            $stmt->execute([$app_identifier]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail
            //throw $e;

            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));

        }

        $count_row = $stmt->fetchAll();
        $count = $count_row[0]['Count'];

        if ($count > 1) {
            $request->getSession()->getFlashBag()->add('danger', 'You cannot delete an application with multiple versions. Delete all the versions except for one first.');
            return $this->redirect($request->headers->get('referer'));
        }

        if ($this->scriptStillExecuting('update-store.sh')) {
            $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Another Add/Update/Delete Application operation is in progress .. Try again in a few seconds');
            return $this->redirect($request->headers->get('referer'));
        }

        // Get the version of the remaining version
        // Get the names of the associated binary files
        // Check if the meta-data and binary files exist
        // if all exist, proceed deleteing the the version (copy common code to a function and call it from delete version and from here)
        // then delete the metadata file, app refernces from the tables
        // invoke the update script.

        $metadata_dir = $this->container->getParameter('melodycode_fossdroid.local_path_metadata');

        $metadata_file = $metadata_dir . '/' . $app_identifier . '.txt';

        if (!file_exists($metadata_file)) {
            $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. unable to find metadata file:' . $metadata_file);
            return $this->redirect($request->headers->get('referer'));

        }

        $success = unlink($metadata_file);

//        todo : delete metadata
        // note that we don't delete binary files, this is the responsibility of the uploader
        // deleting the metadata is enough for the application to disappear from the store

        if (!$success) {
            $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Unable to delete metadata file');
            return $this->redirect($request->headers->get('referer'));

        }
        // if we are here then metadata file is deleted and all that is left is to remove all traces of the file from the DB

        $stmt = $conn->prepare('DELETE FROM Application WHERE ID = ? ');

        try {
            $stmt->execute([$app_identifier]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail
            //throw $e;

            $error = 'Operation Aborted ..' . $e->getMessage();
            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }
        $stmt = $conn->prepare('DELETE FROM BundleApplication WHERE ApplicationID = ?');

        try {

            $stmt->execute([$app_identifier]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail
            //throw $e;

            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }

        $stmt = $conn->prepare('DELETE FROM ControllerInstallation WHERE ApplicationID = ?');

        try {

            $stmt->execute([$app_identifier]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail
            //throw $e;
            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));
        }

        $stmt = $conn->prepare('DELETE FROM DongleInstallation WHERE ApplicationID = ?');

        try {

            $stmt->execute([$app_identifier]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail

            //throw $e;


            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));

        }

        $stmt = $conn->prepare('DELETE FROM Purchase WHERE ApplicationID = ?');

        try {

            $stmt->execute([$app_identifier]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail

            //throw $e;


            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));

        }
        $stmt = $conn->prepare('DELETE FROM Version WHERE ApplicationID = ?');
        try {
            $stmt->execute([$app_identifier]);
        } catch (\PDOException $e) {
            // if something goes wrong we fail

            //throw $e;


            $error = 'Operation Aborted ..' . $e->getMessage();

            $request->getSession()->getFlashBag()->add('danger', $error);
            return $this->redirect($request->headers->get('referer'));

        }
        // if we are here then all is good let us launch the update script


        return $this->render('DashboardBundle:Developer:new-update-delete-app-result.html.twig', array('operation' => 'delete'));
    }

    public function newAppUploadAction()
    {
        $context = $this->container->get('security.context');

        if (!$context->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render("DashboardBundle:Homepage:index.html.twig");

        //auth
        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {

            if ($this->scriptStillExecuting('update-store.sh')) {
                $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Another Add/Update/Delete Application operation is in progress .. Try again in a few seconds');
                return $this->redirect($request->headers->get('referer'));
            }

            $repo_dir = $this->container->getParameter('melodycode_fossdroid.local_path_repo');
            $metadata_dir = $this->container->getParameter('melodycode_fossdroid.local_path_metadata');
            $target_dir = $repo_dir;
            $target_file = $target_dir . '/' . basename($_FILES["app-binary"]["name"]);

            $FileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if (file_exists($target_file)) {
                $request->getSession()->getFlashBag()->add('danger', 'File Already Exist');
                return $this->redirect($request->headers->get('referer'));
            }
            if ($FileType != "apk") {
                $request->getSession()->getFlashBag()->add('danger', 'Uploaded file is not apk file');
                return $this->redirect($request->headers->get('referer'));
            }
            if (!move_uploaded_file($_FILES["app-binary"]["tmp_name"], $target_file)) {
                $request->getSession()->getFlashBag()->add('danger', 'Sorry, there was an error uploading your file.');
                return $this->redirect($request->headers->get('referer'));
            }
            $request->getSession()->getFlashBag()->add('success', 'Done.');

            $app_name = $request->request->get('app-name');
            $app_bundle = $request->request->get('app-bundle');
            $app_binary = $request->request->get('app-binary');
            $app_description = $request->request->get('app-description');
            $app_identifier = $request->request->get('app-identifier');
            $app_payment_model = $request->request->get('app-payment-model');
            $app_price = $request->request->get('app-price');
            $app_summary = $request->request->get('app-summary');
            $app_version = $request->request->get('app-version');
            $app_category = $request->request->get('app-category');
            $app_type = $request->request->get('app-type');

            // we check if the two binary files are where they should be other wise we fail
            $app_dongle_bin = basename($_FILES["app-binary"]["name"]);
            $app_controller_binary = basename($_FILES["app-binary"]["name"]);
            $controller_file = $repo_dir . '/' . basename($_FILES["app-binary"]["name"]);
            $dongle_file = $repo_dir . '/' . basename($_FILES["app-binary"]["name"]);
            if (!file_exists($controller_file) /*|| !file_exists($dongle_file)*/) {
                $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Controller and/or Dongle binary file(s) do(es) not exist');
                return $this->redirect($request->headers->get('referer'));
            }

            // now we create the meta data file for the application
            $metadata_file = $metadata_dir . '/' . $app_identifier . '.txt';

            $content = 'License:Unknown' . PHP_EOL . 'Web Site:' . PHP_EOL . 'Source Code:' . PHP_EOL . 'Issue Tracker:' . PHP_EOL . 'Changelog:' . PHP_EOL . 'Summary:%s' . PHP_EOL . 'Description:' . PHP_EOL . '%s' . PHP_EOL . '.' . PHP_EOL . 'Name:%s' . PHP_EOL . 'Categories:%s' . PHP_EOL . '';
            $content = sprintf($content, $app_summary, $app_description, $app_name, $app_category);

            $success = file_put_contents($metadata_file, $content, LOCK_EX);

            if (!$success) {
                $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Unable to write metadata file');
                return $this->redirect($request->headers->get('referer'));
            }

            // if we are here then the binary files are in place and the meta data file was created so we insert into the DB

            $dbname = $this->container->getParameter('store_database_name');
            $username = $this->container->getParameter('store_database_user');
            $password = $this->container->getParameter('store_database_password');
            $servername = $this->container->getParameter('store_database_host');

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare('INSERT INTO Application (ID,Name,Price,Type) VALUES (?,?,?,?)');

            try {

                $stmt->execute([$app_identifier, $app_name, $app_price, $app_type]);
            } catch (\PDOException $e) {
                // if something goes wrong we fail
                //throw $e;
                $error = '';

                if ($e->errorInfo[1] == 1062) // contrains violation i.e. the app_id:app_version already exists
                {
                    // Take some action if there is a key constraint violation, i.e. duplicate name
                    $error = 'Operation Aborted .. The Application Identifier must be unique [Error]' . $e->getMessage();
                } else
                    $error = 'Operation Aborted ..' . $e->getMessage();

                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));
            }

            $stmt = $conn->prepare('INSERT INTO Version (ApplicationID,Version,DongleAppName,ControllerAppName) VALUES (?,?,?,?)');

            try {

                $stmt->execute([$app_identifier, $app_version, $app_dongle_bin, $app_controller_binary]);
            } catch (\PDOException $e) {
                // if something goes wrong we fail

                //throw $e;

                $error = '';

                if ($e->errorInfo[1] == 1062) // contrains violation i.e. the app_id:app_version already exists
                {
                    // Take some action if there is a key constraint violation, i.e. duplicate name
                    $error = 'Operation Aborted .. The Application Identifier & Version must be unique [Error]' . $e->getMessage();
                } else
                    $error = 'Operation Aborted ..' . $e->getMessage();

                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));

            }

            // if we are here then all is good let us launch the update script
            return $this->render('DashboardBundle:Developer:new-update-delete-app-result.html.twig', array('operation' => 'new'));

        }

        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare('SELECT * FROM Category');
        $stmt->execute();
        $categories = $stmt->fetchAll();
        return $this->render('DashboardBundle:Developer:new-update-app-1.html.twig', array('update' => false, 'categories' => $categories));

    }

    public function newAppAction()
    {
        $context = $this->container->get('security.context');

        if (!$context->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render("DashboardBundle:Homepage:index.html.twig");

        //auth
        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {
            //$app-name = $request->request->get('app-name');
            //$output = shell_exec('sudo -u apache /var/www/html/update-store.sh 2>&1');
            //return $this->render('DashboardBundle:Developer:new-app-result.html.twig',array('output' => $output ));

            if ($this->scriptStillExecuting('update-store.sh')) {

                //return new Response('<html><body><div>An Add Application Operation is in progress, please try again later</div> <div><input type="button" value="Try Again" onClick="window.history.back()"></div></body> </html>');

                //return $this->render('DashboardBundle:Developer:new-app.html.twig');
                $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Another Add/Update/Delete Application operation is in progress .. Try again in a few seconds');
                return $this->redirect($request->headers->get('referer'));
            }

            $app_name = $request->request->get('app-name');
            $app_bundle = $request->request->get('app-bundle');
            $app_controller_binary = $request->request->get('app-controller-binary');
            $app_description = $request->request->get('app-description');
            $app_dongle_binary = $request->request->get('app-dongle-binary');
            $app_identifier = $request->request->get('app-identifier');
            $app_payment_model = $request->request->get('app-payment-model');
            $app_price = $request->request->get('app-price');
            $app_summary = $request->request->get('app-summary');
            $app_version = $request->request->get('app-version');
            $app_category = $request->request->get('app-category');

            // we check if the two binary files are where they should be other wise we fail

            $repo_dir = $this->container->getParameter('melodycode_fossdroid.local_path_repo');
            $metadata_dir = $this->container->getParameter('melodycode_fossdroid.local_path_metadata');
            $controller_file = $repo_dir . '/' . $app_controller_binary;
            $dongle_file = $repo_dir . '/' . $app_dongle_binary;

            if (!file_exists($controller_file) /*|| !file_exists($dongle_file)*/) {
                $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Controller and/or Dongle binary file(s) do(es) not exist');
                return $this->redirect($request->headers->get('referer'));

            }

            // now we create the meta data file for the application


            $metadata_file = $metadata_dir . '/' . $app_identifier . '.txt';

            $content = 'License:Unknown' . PHP_EOL . 'Web Site:' . PHP_EOL . 'Source Code:' . PHP_EOL . 'Issue Tracker:' . PHP_EOL . 'Changelog:' . PHP_EOL . 'Summary:%s' . PHP_EOL . 'Description:' . PHP_EOL . '%s' . PHP_EOL . '.' . PHP_EOL . 'Name:%s' . PHP_EOL . 'Categories:%s' . PHP_EOL . '';
            $content = sprintf($content, $app_summary, $app_description, $app_name, $app_category);

            $success = file_put_contents($metadata_file, $content, LOCK_EX);

            if (!$success) {
                $request->getSession()->getFlashBag()->add('danger', 'Operation Aborted .. Unable to write metadata file');
                return $this->redirect($request->headers->get('referer'));

            }

            // if we are here then the binary files are in place and the meta data file was created so we insert into the DB

            $dbname = $this->container->getParameter('store_database_name');
            $username = $this->container->getParameter('store_database_user');
            $password = $this->container->getParameter('store_database_password');
            $servername = $this->container->getParameter('store_database_host');

            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare('INSERT INTO Application (ID,Name,Price) VALUES (?,?,?)');

            try {

                $stmt->execute([$app_identifier, $app_name, $app_price]);
            } catch (\PDOException $e) {
                // if something goes wrong we fail

                //throw $e;

                $error = '';

                if ($e->errorInfo[1] == 1062) // contrains violation i.e. the app_id:app_version already exists
                {
                    // Take some action if there is a key constraint violation, i.e. duplicate name
                    $error = 'Operation Aborted .. The Application Identifier must be unique [Error]' . $e->getMessage();
                } else
                    $error = 'Operation Aborted ..' . $e->getMessage();

                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));

            }

            $stmt = $conn->prepare('INSERT INTO Version (ApplicationID,Version,DongleAppName,ControllerAppName) VALUES (?,?,?,?)');

            try {

                $stmt->execute([$app_identifier, $app_version, $app_dongle_binary, $app_controller_binary]);
            } catch (\PDOException $e) {
                // if something goes wrong we fail

                //throw $e;

                $error = '';

                if ($e->errorInfo[1] == 1062) // contrains violation i.e. the app_id:app_version already exists
                {
                    // Take some action if there is a key constraint violation, i.e. duplicate name
                    $error = 'Operation Aborted .. The Application Identifier & Version must be unique [Error]' . $e->getMessage();
                } else
                    $error = 'Operation Aborted ..' . $e->getMessage();

                $request->getSession()->getFlashBag()->add('danger', $error);
                return $this->redirect($request->headers->get('referer'));

            }

            // if we are here then all is good let us launch the update script

            return $this->render('DashboardBundle:Developer:new-update-delete-app-result.html.twig', array('operation' => 'new'));

        }

        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare('SELECT * FROM Category');
        $stmt->execute();

        $categories = $stmt->fetchAll();

        return $this->render('DashboardBundle:Developer:new-update-app.html.twig', array('update' => false, 'categories' => $categories));

    }
}

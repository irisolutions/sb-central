<?php
namespace Melodycode\FossdroidBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PDO;



class HomepageController extends Controller {

    public function indexAction() 
    {   $BundleID = "1";
        $BundleID2 = "2";
        $BundleID3 = "3";
        $BundleID4 = "4";
        $BundleID5 = "5";
        $BundleID6 = "6";
        // ToDo: in case we put the storedb on a separate host, we need to decouple the
        // the db connections here

        $dbname = $this->container->getParameter('database_name');
        $dbname2 = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');


        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ToDo: optimize this Subquery/Join later by adding a bundleID field in the maindb.application table. You need to change the parser script to support reading
        // the BundleID(s) from the meta data file for each application

        $stmt = $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt->execute([$BundleID]);
        $bundle1Applications = $stmt->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt4= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt4->execute([$BundleID4]);
        $bundle1Applications4 = $stmt4->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt2= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt2->execute([$BundleID2]);
        $bundle1Applications2 = $stmt2->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt3= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt3->execute([$BundleID3]);
        $bundle1Applications3 = $stmt3->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt5= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt5->execute([$BundleID5]);
        $bundle1Applications5 = $stmt5->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt6= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt6->execute([$BundleID6]);
        $bundle1Applications6 = $stmt6->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        //var_dump($applications);



        $conn = null;

        $conn = new PDO("mysql:host=$servername;dbname=$dbname2", $username, $password);
        $stmt = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
        $stmt->execute([$BundleID]);
        $stmt2->execute([$BundleID2]);
        $stmt3->execute([$BundleID3]);
        $stmt4->execute([$BundleID4]);
        $stmt5->execute([$BundleID5]);
        $stmt6->execute([$BundleID6]);
        if ($stmt->rowCount() < 1) {
            throw $this->createNotFoundException('The bundle does not exist!');
        }

        $bundleinfo = $stmt->fetch();
        $bundleinfo2 = $stmt2->fetch();
        $bundleinfo3 = $stmt3->fetch();
        $bundleinfo5 = $stmt5->fetch();
        $bundleinfo6 = $stmt6->fetch();
        $bundleinfo4 = $stmt4->fetch();
//            return $this->render('MelodycodeFossdroidBundle:Bundle:index.html.twig', array(
//                'bundle' => $bundleinfo,
//                'bundle1Applications' => $bundle1Applications
//            ));


        return $this->render('MelodycodeFossdroidBundle:Homepage:index.html.twig', array(

                'bundle' => $bundleinfo,
                'bundle2' => $bundleinfo2,
                'bundle3' => $bundleinfo3,
                'bundle6' => $bundleinfo6,
                'bundle5' => $bundleinfo5,
                'bundle4' => $bundleinfo4,
                'bundle1Applications' => $bundle1Applications,
                'bundle1Applications2' => $bundle1Applications2,
                'bundle1Applications4' => $bundle1Applications4,
                'bundle1Applications3' => $bundle1Applications3,
                'bundle1Applications5' => $bundle1Applications5,
                'bundle1Applications6' => $bundle1Applications6,

            )
        );

    	//$user = $this->getUser();
    	//var_dump($user);

    }

}

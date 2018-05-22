<?php
namespace Melodycode\FossdroidBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PDO;



class HomepageController extends Controller {

    public function indexAction() 
    {   $BundleID1 = "1";
        $BundleID2 = "2";
        $BundleID3 = "3";
        $BundleID4 = "4";
        $BundleID5 = "5";
        $BundleID6 = "6";
        $BundleID7 = "7";
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

        $stmt1 = $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt1->execute([$BundleID1]);
        $bundle1Applications = $stmt1->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt2= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt2->execute([$BundleID2]);
        $bundle1Applications2 = $stmt2->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt3= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt3->execute([$BundleID3]);
        $bundle1Applications3 = $stmt3->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt4= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt4->execute([$BundleID4]);
        $bundle1Applications4 = $stmt4->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt5= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt5->execute([$BundleID5]);
        $bundle1Applications5 = $stmt5->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        $stmt6= $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt6->execute([$BundleID6]);
        $bundle1Applications6 = $stmt6->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");
        //var_dump($applications);
        $stmt7 = $conn->prepare('SELECT * FROM maindb.application WHERE id IN (SELECT ApplicationID from storedb.BundleApplication WHERE BundleID= ?)');
        $stmt7->execute([$BundleID7]);
        $bundle1Applications7 = $stmt7->fetchAll(PDO::FETCH_CLASS, "Melodycode\FossdroidBundle\Entity\Application");

        $conn = null;
        $conn = new PDO("mysql:host=$servername;dbname=$dbname2", $username, $password);
        $stmt1 = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
        $stmt2 = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
        $stmt3 = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
        $stmt4 = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
        $stmt5 = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
        $stmt6 = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
        $stmt7 = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
        $stmt1->execute([$BundleID1]);
        $stmt2->execute([$BundleID2]);
        $stmt3->execute([$BundleID3]);
        $stmt4->execute([$BundleID4]);
        $stmt5->execute([$BundleID5]);
        $stmt6->execute([$BundleID6]);
        $stmt7->execute([$BundleID7]);
        if ($stmt1->rowCount() < 1) {
            throw $this->createNotFoundException('The bundle does not exist!');
        }
        if ($stmt2->rowCount() < 1) {
            throw $this->createNotFoundException('The bundle does not exist!');
        }
        if ($stmt3->rowCount() < 1) {
            throw $this->createNotFoundException('The bundle does not exist!');
        }
        if ($stmt4->rowCount() < 1) {
            throw $this->createNotFoundException('The bundle does not exist!');
        }
        if ($stmt5->rowCount() < 1) {
            throw $this->createNotFoundException('The bundle does not exist!');
        }
        if ($stmt6->rowCount() < 1) {
            throw $this->createNotFoundException('The bundle does not exist!');
        }
        if ($stmt7->rowCount() < 1) {
            throw $this->createNotFoundException('The bundle does not exist!');
        }
        $bundleinfo1 = $stmt1->fetch();
        $bundleinfo2 = $stmt2->fetch();
        $bundleinfo3 = $stmt3->fetch();
        $bundleinfo4 = $stmt4->fetch();
        $bundleinfo5 = $stmt5->fetch();
        $bundleinfo6 = $stmt6->fetch();
        $bundleinfo7 = $stmt7->fetch();
//            return $this->render('MelodycodeFossdroidBundle:Bundle:index.html.twig', array(
//                'bundle' => $bundleinfo,
//                'bundle1Applications' => $bundle1Applications
//            ));
         $slug1="games";
        $repository_category = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Category');
        $category1 = $repository_category->findOneBySlug($slug1);

        if (!$category1) {
            throw $this->createNotFoundException('The category does not exist');
        }

        $repository_application1 = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Application');
        $applications3 = $repository_application1->findByPublished(0, 'created_at', $slug1);
        $slug2="system";
        $repository_category1 = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Category');
        $category2 = $repository_category1->findOneBySlug($slug1);

        if (!$category2) {
            throw $this->createNotFoundException('The category does not exist');
        }

        $repository_application2 = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Application');
        $applications2 = $repository_application2->findByPublished(0, 'created_at', $slug2);



        return $this->render('MelodycodeFossdroidBundle:Homepage:index.html.twig', array(

                'bundle1' => $bundleinfo1,
                'bundle2' => $bundleinfo2,
                'bundle3' => $bundleinfo3,
                'bundle6' => $bundleinfo6,
                'bundle5' => $bundleinfo5,
                'bundle4' => $bundleinfo4,
                'bundle7' => $bundleinfo7,
                'bundle1Applications7' => $bundle1Applications7,
                'bundle1Applications' => $bundle1Applications,
                'bundle1Applications2' => $bundle1Applications2,
                'bundle1Applications4' => $bundle1Applications4,
                'bundle1Applications3' => $bundle1Applications3,
                'bundle1Applications5' => $bundle1Applications5,
                'bundle1Applications6' => $bundle1Applications6,
                'applications3' => $applications3,
                'applications2' => $applications2,
                'category1' => $category1,
                'category2' => $category2,
'category1Name' =>$slug1,
                'category2Name' =>$slug2,
            )
        );

    	//$user = $this->getUser();
    	//var_dump($user);

    }

}

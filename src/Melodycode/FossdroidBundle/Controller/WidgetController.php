<?php

namespace Melodycode\FossdroidBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PDO;

class WidgetController extends Controller
{

    public function whatsnewAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Application');
        $applications = $repository->findByPublished($this->container->getParameter('melodycode_fossdroid.widget_limit'), 'created_at', $request->get('slug_selected'));

        $BundleID = "1";

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

        //var_dump($applications);
        $conn = null;

        $conn = new PDO("mysql:host=$servername;dbname=$dbname2", $username, $password);
        $stmt = $conn->prepare('SELECT * FROM Bundle WHERE ID = ?');
        $stmt->execute([$BundleID]);

        if ($stmt->rowCount() < 1) {
            throw $this->createNotFoundException('The bundle does not exist!');
        }

        $bundleinfo = $stmt->fetch();

//            return $this->render('MelodycodeFossdroidBundle:Bundle:index.html.twig', array(
//                'bundle' => $bundleinfo,
//                'bundle1Applications' => $bundle1Applications
//            ));


        return $this->render('MelodycodeFossdroidBundle:Widget:whatsnew.html.twig', array(
                'applications' => $applications,
                'bundle' => $bundleinfo,
                'bundle1Applications' => $bundle1Applications,
                'category_slug' => $request->get('slug_selected'),
            )
        );
    }


    public function categoriesAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('MelodycodeFossdroidBundle:Category');
        $categories = $repository->findBy(array('is_published' => 1), array('count' => 'DESC'));


        $dbname = $this->container->getParameter('store_database_name');
        $username = $this->container->getParameter('store_database_user');
        $password = $this->container->getParameter('store_database_password');
        $servername = $this->container->getParameter('store_database_host');
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare('SELECT * FROM Bundle ORDER BY Name');
            $stmt->execute();

            $bundles = $stmt->fetchAll();


            return $this->render('MelodycodeFossdroidBundle:Widget:categories.html.twig', array(
                    'categories' => $categories,
                    'slug_selected' => $request->get('slug_selected'),
                    'bundles' => $bundles
                )
            );
        } catch (PDOException $e) {
            return new Response(
                '<html><body>Your Name is: hi</body></html>');
        }
        return new Response(
            '<html><body>Your Name is: hi</body></html>');
    }

    public function searchAction(Request $request)
    {
        return $this->render('MelodycodeFossdroidBundle:Widget:search.html.twig', array(
                'q' => $request->get('q')
            )
        );
    }

}

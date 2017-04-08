<?php

namespace Iris\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PDO;
//include 'ChromePhp.php';
//use ChromePhp;
class DeveloperController extends Controller 
{

public function indexAction() 
{
        return new Response('<html><body>Developer Homepage goes here</body></html>');
         //return $this->render('DashboardBundle:Developer:new-app.html.twig');
}

public function newAppAction() 
{
        //return new Response('<html><body>Developer Page goes here</body></html>');
         return $this->render('DashboardBundle:Developer:new-app.html.twig');
         
}

public function menuAction() 
{

        //return new Response('<html><body>The Menu goes here</body></html>');
         $commands = array(
            array('path'=>'','icon'=>'','name'=>'Applications'),
         	array('path'=>'dashboard_dev_new_app','icon'=>'add_circle','name'=>'New Application'),
            array('path'=>'dashboard_dev_new_app','icon'=>'loop','name'=>'Update Application'),
            array('path'=>'','icon'=>'','name'=>'Bundles'),
            array('path'=>'dashboard_dev_new_app','icon'=>'add_circle','name'=>'New Bundle'),
            array('path'=>'dashboard_dev_new_app','icon'=>'loop','name'=>'Update Bundle')
         );
         
         return $this->render('DashboardBundle:Developer:menu.html.twig',array(
                    'commands' => $commands ));
}

}

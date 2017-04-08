<?php

namespace Iris\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PDO;
//include 'ChromePhp.php';
//use ChromePhp;
class MenuController extends Controller 
{

public function devAction() 
{
        //return new Response('<html><body>The Menu goes here</body></html>');
         return $this->render('DashboardBundle:Menu:index.html.twig');
}

}

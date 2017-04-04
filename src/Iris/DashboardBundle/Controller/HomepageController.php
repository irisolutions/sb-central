<?php

namespace Iris\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PDO;
//include 'ChromePhp.php';
//use ChromePhp;
class HomepageController extends Controller 
{

public function indexAction() 
{
        return new Response('<html><body>Dashboard Homepage goes here</body></html>');
}

}

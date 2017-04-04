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
        return new Response('<html><body>Developer Page goes here</body></html>');
}

}

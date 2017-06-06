<?php
// Melodycode\FossdroidBundle\Security\WebserviceUserProvider.php

namespace Iris\DashboardBundle\Security\User;

use Iris\DashboardBundle\Security\User\DBServiceUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use PDO;

class DBServiceUserProvider implements UserProviderInterface
{

 	private $container;
 	
 	public function __construct(ContainerInterface $service_container) 
 	{
        $this->container = $service_container;
    }
 	
    public function loadUserByUsername($username)
    {
    	
        $dbname    = $this->container->getParameter('store_database_name');
    	$servername = $this->container->getParameter('store_database_host');
		$user = $this->container->getParameter('store_database_user');
		$pass = $this->container->getParameter('store_database_password');
		
    	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $user, $pass);
    	// set the PDO error mode to exception
    	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    	
    	
    	$stmt = $conn->prepare('SELECT * FROM Administrator WHERE ID = ?');
		$stmt->execute([$username]);
		
		if ( $stmt->rowCount() < 1 )
    	{
    		throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)); 
    	}
    	
		
		$result = $stmt->fetchAll();
    
    	
  
    	//$username = "spring";
    	$password = $result[0]['Password'];
    	$salt = "";
    	// ToDo: fix the Roles for the user
    	//$roles = array('ROLE_USER'=>'ROLE');
    	$roles = array($result[0]['Role']);
    	
    	$name = $result[0]['Name'];

    	return new DBServiceUser($username, $password, $salt, $roles,$name);
    	 
        
    }

    public function refreshUser(UserInterface $user)
    {
    
        if (!$user instanceof DBServiceUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return DBServiceUser::class === $class;
    }
}
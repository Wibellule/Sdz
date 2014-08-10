<?php

# src/Sdz/UserBundle/Controller/SecurityController.php

namespace Sdz\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller
{
    public function UserListAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        return $this->render(
            'SdzUserBundle:User:list.html.twig',
            array('users' => $users )
        );
    }
}
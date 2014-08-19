<?php

// src/Sdz/BlogBundle/Controller/BlogController.php

namespace Sdz\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


class UserController extends Controller
{
    public function indexAction()
    {
        // Pour récupérer la liste de tous les utilisateurs
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        // L'appel de la vue ne change pas
        return $this->render('SdzBlogBundle:User:index.html.twig', array(
            'users' => $users
        ));
    }

    public function voirAction($id)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(
            array('id' => $id)
        );

        return $this->render('SdzBlogBundle:User:voir.html.twig', array(
            'user' => $user
        ));

    }

    public function modifierAction(User $user)
    {
        
    }

    public function supprimerAction(User $user)
    {
        
    }

    public function menuAction($nombre)
    {
        $liste = $this->getDoctrine()
                      ->getManager()
                      ->getRepository('SdzBlogBundle:Article')
                      ->findBy(
                            array(),          // Pas de critère
                            array('date' => 'desc'), // On trie par date décroissante
                            $nombre,         // On sélectionne $nombre articles
                            0                // À partir du premier
                      );

        return $this->render('SdzBlogBundle:Blog:menu.html.twig', array(
            'liste_articles' => $liste // C'est ici tout l'intérêt : le contrôleur passe les variables nécessaires au template !
        ));
    }
}

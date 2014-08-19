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

    public function modifierAction(Article $article)
    {
        // On construit le form avec cette instance d'article
        $form = $this->createForm(new ArticleEditType(), $article);

        // On récupère la requête
        $request = $this->get('Request');

        // On vérifie qu'elle est de type POST
        if($request->getMethod() == 'POST')
        {
            // On fait le lien requête <-> formulaire
            // A partir de maintenant, la variable $article contient
            // Les valeurs entrées dans le formulaire par le visiteur
            $form->submit($request);

            if($form->isValid())
            {
                // On enregistre notre objet $article dans la base de données
                $em = $this->getDoctrine()->getManager();
                $em->persist($article);
                $em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('info','Article bien modifié');

                // On redirige vers la page de visualisation de l'article nouvellement crée
                return $this->redirect(
                    $this->generateUrl(
                        'sdzblog_voir', array('slug' => $article->getSlug())
                    )
                );
            }
        }

        return $this->render('SdzBlogBundle:Blog:modifier.html.twig', array(
            'article' => $article,
            'form'    => $form->createView()
        ));
    }

    public function supprimerAction(Article $article)
    {
        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'article contre cette faille
        $form = $this->createFormBuilder()->getForm();

        $request = $this->get('Request');

        if ($request->getMethod() == 'POST') {

            $form->submit($request);
            if($form->isValid())
            {
                // On supprime l'article
                $em = $this->getDoctrine()->getManager();
                $em->remove($article);
                $em->flush();

                // Si la requête est en POST, on supprimera l'article
                $this->get('session')->getFlashBag()->add('info', 'Article bien supprimé');

                // Puis on redirige vers l'accueil
                return $this->redirect( $this->generateUrl('sdzblog_accueil') );

            }
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('SdzBlogBundle:Blog:supprimer.html.twig', array(
            'article' => $article,
            'form'    => $form->createView()
        ));
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

<?php

// src/Sdz/BlogBundle/Controller/BlogController.php

namespace Sdz\BlogBundle\Controller;

use Sdz\BlogBundle\Form\ArticleEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Sdz\BlogBundle\Entity\Article;
use Sdz\BlogBundle\Form\ArticleType;
use Symfony\Component\HttpFoundation\Response;


class BlogController extends Controller
{
    public function indexAction($page)
    {
        // Pour récupérer la liste de tous les articles : on utilise findAll()
        $articles = $this->getDoctrine()
                         ->getManager()
                         ->getRepository('SdzBlogBundle:Article')
                         ->getArticles(3, $page);

        // L'appel de la vue ne change pas
        return $this->render('SdzBlogBundle:Blog:index.html.twig', array(
            'articles'      => $articles,
            'page'          => $page,
            'nombrePage'    => ceil(count($articles) / 3)
        ));
    }

    public function voirAction(Article $article)
    {
        // A ce stade, la variable $article contient une instance de la classe Article
        // Avec l'id correspondant à l'id contenu dans la route

        // On récupère les articleCompetence pour l'article $article
        $liste_articleCompetence = $this->getDoctrine()
                                        ->getManager()
                                        ->getRepository('SdzBlogBundle:ArticleCompetence')
                                        ->findByArticle($article->getId());

        // Puis modifiez la ligne du render comme ceci, pour prendre en compte les variables :
        return $this->render('SdzBlogBundle:Blog:voir.html.twig', array(
            'article'                 => $article,
            'liste_articleCompetence' => $liste_articleCompetence
            // Pas besoin de passer les commentaires à la vue, on pourra y accéder via {{ article.commentaires }}
            // 'liste_commentaires'   => $article->getCommentaires()
        ));
    }

    public function ajouterAction()
    {
        // On teste que l'utilisateur dispose bien du rôle ROLE_AUTEUR
        if(!$this->get('security.context')->isGranted('ROLE_AUTEUR'))
        {
            // Sinon on déclence une exception " Accès interdit "
            throw new AccessDeniedHttpException('Accès limité aux auteurs');
        }

        // On crée un nouvel objet Article
        $article = new Article();
        $article->setDateEdition(new \DateTime());
        $article->setNbCommentaires(0);

        // On crée le Form grâce à la méthode du controleur
        $form = $this->createForm(new ArticleType, $article);

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

                // On redirige vers la page de visualisation de l'article nouvellement crée
                return $this->redirect(
                    $this->generateUrl(
                        'sdzblog_voir', array('slug' => $article->getSlug())
                    )
                );
            }
        }

        // A ce stade :
        // - Soit la requête est de type GET, donc le visiteur
        //   vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire n'est
        //   pas valide, donc on l'affiche de nouveau

        // On passe la méthode createView() du formulaire à la vue
        // Afin qu'ell puisse afficher le formulaire toute seule
        return $this->render(
                        'SdzBlogBundle:Blog:ajouter.html.twig',
                        array('form' => $form->createView())
                      );
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

    public function testAction()
    {
        $article = new Article;

        $article->setDate(new \DateTime()); // Champ date OK
        $article->setTitre('abc');
            // incorrect : moins de 10 caractères
        //$article->setContenu('blabla');
            // incorrect : on ne le définit pas
        $article->setAuteur('A');
            // incorrect : moins de 2 caractères

        // On récupère le service Validator
        $validator = $this->get('validator');

        // On déclenche la validation
        $liste_erreurs = $validator->validate($article);

        // Si le tableau n'est pas vide, on affiche les erreurs
        if(count($liste_erreurs) > 0)
        {
            return new Response(print_r($liste_erreurs, true));
        }else{
            return new Response("L'article est valide !");
        }

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

<?php

// src/Sdz/BlogBundle/Controller/BlogController.php

namespace Sdz\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sdz\BlogBundle\Entity\Article;
use Sdz\BlogBundle\Entity\Image;

class BlogController extends Controller
{
	/**
	 * public function indexAction
	 * @param int $page numéro de la page
	 * @return void appel le template
	 */
    public function indexAction()
    {
    	

        //Récupération du service Doctrine $doctrine = $this->getDoctrine();
        //Récupération de l'entityManager $em = $this->getDoctrine()->getManager(); ou $this->get('doctrine.orm.entity_manager');
        //Accès au répository_article $repository_article = $em->getRepository('SdzBlogBundle:Article');

        $text = "gyome34@hotmail.com, guillaume.cornu@orange.com";

        //On récupère le service
        $antispam = $this->container->get('sdz_blog.antispam');

        //Je pars du principe que le $text contient le texte d'un mesage quelconque
        if($antispam->isSpam($text))
        {
        	throw new \Exception("Votre message a été détecté comme spam !");
        }

        //Les articles ::

        $repository = $this->getDoctrine()
						   ->getManager()
						   ->getRepository('SdzBlogBundle:Article');

		$article = $repository->findAll();

		if($article === null)
		{
			throw $this->createNotFoundException('Article [id='.$id.'] inexistant.');
		}

        //Fin articles
		
		//Ici on récupèrera la liste des articles pour la transmettre au template
		
		//Pour le moment on appel simplement le template
		return $this->render(
			'SdzBlogBundle:Blog:index.html.twig',
			array('articles' => $article)
		);
        
    }
	
	/**
	 * Fonction qui va permettre de voir un article
	 * @param $id int id de l'article
	 * @return void affiche la page de l'article
	 */
	public function voirAction($id)
	{
		
		//on récupère le répository
		$repository = $this->getDoctrine()
						   ->getManager()
						   ->getRepository('SdzBlogBundle:Article');


	    //On récupère l'entité correspondant à l'id $id
	    $article = $repository->find($id);

	    // $article est donc une instance de Sdz\BlogBundle\Entity\Article

	    //Ou null si aucun article n'a été trouvé avec l'id $id
	    if($article === null)
	    {
	    	throw $this->createNotFoundException('Article [id='.$id.'] inexistant.');
	    	
	    }
		
		//Ici on récupèrera l'article correspondant à l'id $id
		return $this->render(
			'SdzBlogBundle:Blog:voir.html.twig',
			array('article' => $article)
		);
	}
	
	
	public function voirSlugAction($slug, $annee, $format)
	{
		return new Response("On pourrait afficher l'article correspondant au slug '".$slug."', créé en ".$annee." et au format ".$format.".");
	}
	
	/**
	 * fonction qui va permettre l'ajout en base de données un article
	 * @return void redirection
	 */
	public function ajouterAction()
	{
		// Création de l'entité
		$article1 = new Article();
		$article1->setTitre('Mon dernier dimanche');
		$article1->setAuteur('wibellule');
		$article1->setContenu("Test de l'image.");
		//On ne peut pas définir ni la date ni la publication,
		//car ces attributs sont définis automatiquement dans le constructeur.

		//var_dump($article);

		//On récupère l'entity manager
		$em = $this->getDoctrine()->getManager();

		//Etape 1 : on persiste l'entité
		$em->persist($article1);

		//Ici pas besoin de faire un persist, comme on a déjà l'article Doctrine sait comment le gérer.

		//Création de l'entité Image
		$image = new Image();
		$image->setUrl('http://symfony.com/logos/symfony_black_01.png?v=4');
		$image->setAlt('Logo Symfony 2');
		//On lie l'image à l'article
		$article1->setImage($image);


		//Etape 2 : on flush tout ce qui a été persisté avant
		$em->flush();


		//Etape 3 : le traitement du formulaire
		//La gestion d'un formulaire est particulière, mais l'idée est la suivante :
		if( $this->get('request')->getMethod() == 'POST' )
		{
			//Ici, on s'occupera de la création et de la gestion du formulaire
			
			$this->get('session')->getFlashBag()->add('info','Article bien enregistré');
			
			//Puis on redirige vers la page de visualisation de cet article
			return $this->redirect( $this->generateUrl('sdzblog_voir', array('id' => $article->getId()) ) );
		}
		
		return $this->render('SdzBlogBundle:Blog:ajouter.html.twig');
	}

	/**
	 * Fonction qui va récupérer un article correspondant à l'id envoyé
	 * @param $id int id de l'article
	 */
	public function modifierAction($id)
	{
		//Ici on s'occupera de la création et de la gestion du formulaire

		//Les articles ::

        $article = array(

        		'titre' 	=> 'Mon week-end à Paris',
        		'id'		=> '1',
        		'auteur'	=> 'gyome',
        		'contenu' 	=> 'Ce week-end était super',
        		'date'		=> new \Datetime()
        );

        //Fin articles
		
		return $this->render(
			'SdzBlogBundle:Blog:modifier.html.twig', 
			array('article' => $article)
		);
	}
	
	public function supprimerAction()
	{
		return $this->render('SdzBlogBundle:supprimer.html.twig');
	}
	
	public function menuAction()
	{
		//On fixe en dur une liste ici, bien entendu par la suite on la récupèrera depuis la BDD
		$liste = array(
			array('id' => 2, 'titre' => 'Mon dernier weekend !'),
			array('id' => 5, 'titre' => 'Sortie de Symfony 2.1'),
			array('id' => 9, 'titre' => 'Petit test')
		);
		
		return $this->render(
			'SdzBlogBundle:Blog:menu.html.twig',
			array(
				'liste_articles' => $liste //Point d'intérêt : Le controleur passe les variables nécessaires au template.
			)
		);
	}
}

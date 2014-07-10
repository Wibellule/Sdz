<?php

// src/Sdz/BlogBundle/Controller/BlogController.php

namespace Sdz\BlogBundle\Controller;

use Sdz\BlogBundle\Entity\Commentaire;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sdz\BlogBundle\Entity\Article;
use Sdz\BlogBundle\Entity\Image;
use Sdz\BlogBundle\Entity\ArticleCompetence;

class BlogController extends Controller
{
    /**
     * public function indexAction
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     * @internal param int $page numéro de la page
     * @return object $this appel le template
     */
    public function indexAction()
    {
    	//On fixera un id au hasard ici, il sera dynamique par la suite, évidemment
    	//$id = 5;
		
		//On veut avoir l'url de l'article d'id $id
		/*$url = $this->generateUrl(
			'sdzblog_voir', 
			array('id' => $id),
			true //Pour l'url absolue pour l'envoi de mail
		);
		// $url vaut " /blog/article/5"
		//On redirige vers cette url ( pour l'exemple )
        return $this->redirect($url);*/

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
        
        //à enlever après
        //if( !$page ){ $page = 1; }
		
		
        //on ne sait pas combien de page il y aura mais on sait qu'une page doit être supérieure à 1
        //if( $page < 1)
        //{
        	//On déclenche une exception NotFoundHttpException
        	//Cela va afficher la page d'erreur 404 que l'on pourra personnaliser plus tard
        	//throw $this->createNotFoundException('Page inexistante (page = '.$page.' )');
        //}
		
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
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     */
	public function voirAction($id)
	{
		//return new Response("Affichage de l'article d'id: ".$id.".");
		
		//Récupération du service
		/**$templating = $this->get('templating');
		//On récupère le contenu de notre template
		$contenu = $templating->render(
			'SdzBlogBundle:Blog:voir.html.twig',
			array('id' => $id)	
		);
		//On crée une réponse avec ce contenu et on la retourne
		return new Response($contenu);**/

        //On récupère l'entity manager
        $em = $this->getDoctrine()->getManager();
		
		//on récupère le répository
		$repository = $this->getDoctrine()
						   ->getManager()
						   ->getRepository('SdzBlogBundle:Article');

		// Ou
	    /** 
		 * $article = $this->getDoctrine()
		 *			   	   ->getManager()
		 *				   ->find('SdzBlogBundle:Article', $id);
		 */

	    //On récupère l'entité correspondant à l'id $id
	    $article = $em->getRepository('SdzBlogBundle:Article')->find($id);

	    // $article est donc une instance de Sdz\BlogBundle\Entity\Article

	    //Ou null si aucun article n'a été trouvé avec l'id $id
	    if($article === null)
	    {
	    	throw $this->createNotFoundException('Article [id='.$id.'] inexistant.');
	    	
	    }

        //On récupère les articleCompetence pour l'article $article
        $liste_articleCompetence = $em->getRepository('SdzBlogBundle:ArticleCompetence')->findByArticle($article->getId());

        //On récupère la liste des commentaires
        $liste_commentaires = $em->getRepository('SdzBlogBundle:Commentaire')->findAll();
		
		//Ici on récupèrera l'article correspondant à l'id $id
		return $this->render(
			'SdzBlogBundle:Blog:voir.html.twig',
			array(
                'article' => $article,
                'liste_commentaires' => $liste_commentaires,
                'liste_articleCompetence' => $liste_articleCompetence
            )
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
        $em = $this->getDoctrine()->getManager();

		// Création de l'entité
		$article1 = new Article();
		$article1->setTitre('Mon dernier dimanche');
		$article1->setAuteur('wibellule');
		$article1->setContenu("Relation ManyToMany avec attributs.");

        //Dans ce cas on persist pour lui donner un id pour après enregistrer ArticleCompetence
        $em->persist($article1);
        $em->flush();

        //Les compétences existent déjà, on les récupèrent
        $liste_competences = $em->getRepository('SdzBlogBundle:Competence')->findAll();

        //Pour chaque compétence
        foreach($liste_competences as $i => $competence)
        {
            //On crée une nouvelle relation << entre 1 article et 1 compétence >>
            $articleCompetence[$i] = new ArticleCompetence;

            //On la lie à l'article, qui est ici toujours le même
            $articleCompetence[$i]->setArticle($article1);

            //On lie la compétence, qui change ici dans la boucle foreach
            $articleCompetence[$i]->setCompetence($competence);

            //Arbitrairement, on dit que chaque compétence est requise au niveau expert
            $articleCompetence[$i]->setNiveau('Expert');

            //Et biensur, on persiste cette entité de relation, propriétaire des deux autres relations.
            $em->persist($articleCompetence[$i]);
        }

        $em->flush();

		//On ne peut pas définir ni la date ni la publication,
		//car ces attributs sont définis automatiquement dans le constructeur.

		//var_dump($article);

        //Création d'un nouveau commentaire
        $commentaire1 = new Commentaire();
        $commentaire1->setAuteur('Wibellule');
        $commentaire1->setContenu("On veut les photos !");
        $commentaire1->setDate(new \DateTime());

        //Création d'un second commentaire
        $commentaire2 = new Commentaire();
        $commentaire2->setAuteur("Gyome");
        $commentaire2->setContenu("Les photos arrivent !");
        $commentaire2->setDate(new \DateTime());

        //On lie les commentaires à l'article
        $commentaire1->setArticle($article1);
        $commentaire2->setArticle($article1);

		//On récupère l'entity manager
		$em = $this->getDoctrine()->getManager();

		//Etape 1 : on persiste l'entité
		$em->persist($article1);
        //Pour cette relation pas de cascade, car elle est définie dans l'entité Commentaire et non Article

        //On doit donc tout persister à la main ici
        $em->persist($commentaire1);
        $em->persist($commentaire2);

		//$article2 = $em->getRepository('SdzBlogBundle:Article')->find(3);

		//On modifie cet article en changeant la date à la date d'aujourd'hui
		//$article2->setDate(new \Datetime());

		//Ici pas besoin de faire un persist, comme on a déjà l'article Doctrine sait comment le gérer.

		//Création de l'entité Image
		$image = new Image();
		$image->setUrl('http://symfony.com/logos/symfony_black_01.png?v=4');
		$image->setAlt('Logo Symfony 2');
		//On lie l'image à l'article
		$article1->setImage($image);


		//Etape 2 : on flush tout ce qui a été persisté avant
		//$em->flush();


		//Etape 3 : le traitement du formulaire
		//La gestion d'un formulaire est particulière, mais l'idée est la suivante :
		if( $this->get('request')->getMethod() == 'POST' )
		{
			//Ici, on s'occupera de la création et de la gestion du formulaire
			
			$this->get('session')->getFlashBag()->add('info','Article bien enregistré');
			
			//Puis on redirige vers la page de visualisation de cet article
			$this->redirect( $this->generateUrl('sdzblog_voir', array('id' => $article->getId()) ) );
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

        //On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        //On récupère l'entité correspondant à l'id $id
        $article = $em->getRepository('SdzBlogBundle:Article')->find($id);

        if($article === null)
        {
            throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');
        }

		//On récupère toutes les catégories :
        $liste_categories = $em->getRepository('SdzBlogBundle:Categorie')->findAll();

        foreach($liste_categories as $categorie)
        {
            $article->addCategorie($categorie);
        }

        //Inutile de persister l'article, on l'a récupéré avec Doctrine

        //On déclenche l'enregistrement
        $em->flush();

        //return new Response('OK');

		return $this->render('SdzBlogBundle:Blog:modifier.html.twig', array('article' => $article));
	}
	
	public function supprimerAction()
	{
        //On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        //On récupère l'entité correspondant à l'id $id
        $article = $this->getDoctrine()->getRepository('SdzBlogBundle:Article')->find($id);

        if($article === null)
        {
            throw $this->createNotFoundException('Article [id='.$id.'] inexistant.');
        }

        //On récupère toutes les catégories :
        $liste_categories = $em->getRepository('SdzBlogBundle:Categorie')->findAll();

        //On enlève toutes ces catégories de l'article
        foreach($liste_categories as $categorie)
        {
            //On fait appel à la méthode removeCategorie() dont on a parlé plus haut
            //Attention ici $categorie est bien une instance de Categorie, et pas seulement un id
            $article->removeCategorie($categorie);
        }

        //On a donc modifié une relation Article - Categorie
        //Il faudrait persister l'entité propriétaire pour persister la relation
        //Or l'article a été récupéré depuis Doctrine, inutile de le persister

        $em->flush();
        return new Response('OK');

		//return $this->render('SdzBlogBundle:supprimer.html.twig');
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

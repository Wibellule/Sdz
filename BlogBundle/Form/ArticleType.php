<?php

namespace Sdz\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ArticleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date',       'date')
            ->add('titre',      'text')
            ->add('contenu',    'textarea')
            ->add('auteur',     'text')
            ->add('image',      new ImageType()) // Formulaire imbriqué
            ->add('categories',  'entity',
                array(
                    'class'     => 'SdzBlogBundle:Categorie',
                    'property'  => 'nom',
                    'multiple'  => 'true'
                )
            )
        ;

        $factory = $builder->getFormFactory();

        // On ajoute une fonction qui va écouter l'évènement PRE_SET_DATA
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA, // Ici, on définit l'évènement qui nous intéresse
            function(FormEvent $event) use ($factory) // Ici on définit une fonction qui sera executé lors de l'évent
            {
                // On récupère l'objet
                $article = $event->getData();
                // Cette condition est importante
                if( null === $article )
                {
                    return; // On sort de la fonction lorsque $article vaut null
                }
                // Si l'article n'est pas encore publié, on ajoute le champ publication
                if( false === $article->getPublication() )
                {
                    // On récupère le formulaire et on ajoute le champ publication
                    $event->getForm()->add(
                        $factory->createNamed(
                            'publication',
                            'checkbox',
                            null,
                            array('required' => false)
                        )
                    );
                }else{
                    // Sinon on le supprime
                    $event->getForm()->remove('publication');
                }
            }
        );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sdz\BlogBundle\Entity\Article'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sdz_blogbundle_articletype';
    }
}

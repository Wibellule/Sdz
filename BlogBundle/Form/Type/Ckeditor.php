<?php

// src/Sdz/BlogBundle/Form/Type/Ckeditor.php

namespace Sdz\BlogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Ckeditor extends AbstractType
{
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => 'ckeditor'
        ));
    }

    public function getParent()
    {
        return 'textarea';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ckeditor';
    }
}

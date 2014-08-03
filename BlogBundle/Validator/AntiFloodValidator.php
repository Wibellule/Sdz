<?php

// src/Sdz/BlogBundle/Validator/AntiFloodValidator.php

namespace Sdz\BlogBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AntiFloodValidator extends ConstraintValidator
{
    private $request;
    private $em;

    // Les arguments déclarés dans la définition du service arrivent au constructeur
    // On doit les enregistrer dans l'objet pour pouvoir s'en resservir dans la méthode validate()
    public function __construct(Request $request, EntityManager $em)
    {
        $this->request = $request;
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        // On récupère l'IP de celui qui poste
        $ip = $this->request->server->get('REMOTE_ADDR');
        // On vérifie si cette adresse IP a déjà posté un message il y a moins de 15sec
        $isFlood = $this->em->getRepository('SdzBlogBundle:Commentaire')->isFlood($ip,15);
        // Méthode à créer
        // Pour l'instant, on considère comme flood tout message de moins de 3 caractères
        if(strlen($value) < 3 && $isFlood)
        {
            // C'est cette ligne qui déclenche l'erreur pour le formulaire
            // Avec en argument le message
            $this->context->addViolation($constraint->message, array('%string%' => $value));
        }
    }
}

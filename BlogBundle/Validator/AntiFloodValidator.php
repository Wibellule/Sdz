<?php

// src/Sdz/BlogBundle/Validator/AntiFloodValidator.php

namespace Sdz\BlogBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AntiFloodValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        // Pour l'instant, on considère comme flood tout message de moins de 3 caractères
        if(strlen($value) < 3)
        {
            // C'est cette ligne qui déclenche l'erreur pour le formulaire
            // Avec en argument le message
            $this->context->addViolation($constraint->message);
        }
    }
}

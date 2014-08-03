<?php

// src/Sdz/BlogBundle/Validator/AntiFlood.php

namespace Sdz\BlogBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AntiFlood extends Constraint
{
    public $message = "Votre message ' %string% ' est considéré comme flood.";
    
    public function validateBy()
    {
        return 'sdzblog_antiflood'; // Ici, on fait appel à l'alias du service
    }
}

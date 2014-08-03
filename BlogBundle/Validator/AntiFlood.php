<?php

// src/Sdz/BlogBundle/Validator/AntiFlood.php

namespace Sdz\BlogBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AntiFlood extends Constraint
{
    public $message = "Vous avez déjà posté un message il y a moins de 15 sec, merci de patienter";
}

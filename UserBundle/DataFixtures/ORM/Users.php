<?php
// src/Sdz/BlogBundle/DataFixtures/ORM/Users.php

namespace Sdz\BlogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sdz\UserBundle\Entity\User;

class Users implements FixtureInterface
{
    //Dans l'argument de la méthode load, l'objet $manager est l'EntityManager

    public function load(ObjectManager $manager)
    {
        
    }
}

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
        // Les noms à créer
        $noms = array('Wibellule','John','Gyome');

        foreach($noms as $i => $nom)
        {
            // On crée l'utilisateur
            $users[$i] = new User;

            // Le nom d'utilisateur et le mot de passe sont identiques
            $users[$i]->setUsername($nom);
            $users[$i]->setPassword($nom);

            // Le sel et les rôles sont vides pour l'instant
            $users[$i]->setSalt('');
            $users[$i]->setRoles(array());

            // On le persiste
            $manager->persist($users[$i]);
        }

        // On déclenche le flush
        $manager->flush();
    }
}

<?php

namespace App\Tests;

use App\Entity\Facteur;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FacteurTest extends KernelTestCase
{
    public function getEntity():Facteur
    {
        return(new Facteur())->setEmail("Tests@test.fr")
                                ->setNom("Test")
                                ->setPassword("Enutro2003!!")
                                ->setRoles(["ROLE_Facteur"]);
    }

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        
        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors);
    }

    public function testInvalidEmail()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setEmail("testo@tes.fr"); #Email à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidName()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setNom("Jen'mayr-"); #Nom à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidPassword()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setPassword("Enu2004!");#Mot de passe à faire varier

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidRole()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setRoles(['ROLE_ENutro']);#Rôle à faire varier

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }
}

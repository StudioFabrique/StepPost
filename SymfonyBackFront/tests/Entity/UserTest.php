<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function getEntity():User
    {
        return(new User())->setEmail("enutro@test.fr")
                            ->setNom("Enutro")
                            ->setPassword("Enutro2003@")
                            ->setFonction("Dev")
                            ->setRoles(['ROLE_ADMIN'])
                            ->setCreatedAt(new \DateTimeImmutable())
                            ->setUpdatedAt(new \DateTimeImmutable());
    }

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        
        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors);
    }

    public function testInvalidName()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setNom("salut"); #Nom à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidEmail()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setEmail("test@test.fr");#Email à faire varier

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

    public function testInvalidFonction()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setFonction("Développeur");#Fonction à faire varier

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }
}

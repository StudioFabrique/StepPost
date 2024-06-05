<?php

namespace App\Tests;

use App\Entity\Destinataire;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DestinataireTest extends KernelTestCase
{
    public function getEntity():Destinataire
    {
        return(new Destinataire())->setEmail("Tests@test.fr")
                                ->setCivilite("Monsieur")
                                ->setNom("Test")
                                ->setPrenom("Tests")
                                ->setAdresse("34 rue du test")
                                ->setCodePostal("64000")
                                ->setVille("Pau")
                                ->setTelephone("06 95 24 20 93");
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

    public function testInvalidCivilite()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setCivilite("test"); #Civilité à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidName()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setNom("test"); #Nom à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidFirstName()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setPrenom("test"); #Prénom à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidAdresse()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setAdresse("test"); #Adresse à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidPostalCode()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setCodePostal("64000"); #Code Postale à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidVille()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setVille("Pau"); #Ville à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidTelephone()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setTelephone("06 95 24 20 92"); #Numéro de tel à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }
}

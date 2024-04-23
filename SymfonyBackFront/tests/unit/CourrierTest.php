<?php

namespace App\Tests\unit;

use App\Entity\Courrier;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClientTest extends KernelTestCase
{  
    public function getEntity():Courrier
    {
        return(new Courrier())->setType(1)
                            ->setBordereau(10001)
                            ->setNom("Enutro")
                            ->setCivilite("Monsieur")
                            ->setPrenom("Enutro")
                            ->setAdresse("34 RUE")
                            ->setCodePostal("64000")
                            ->setVille("PAu")
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

    public function testInvalidName()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setNom("Jen'mayr-"); #Nom à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidFirstName()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setPrenom("Noa");#Prénom à faire varier

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidAdresse()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setAdresse("34rued");#Adresse à faire varier

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidPostCode()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setCodePostal("64000");#Code Postal à faire varier

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidVille()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setVille("Pau-est");#Ville à faire varier

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidPhoneNumber()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setTelephone("06 95 24 20 94");#Numéro de téléphone à faire varier de la forme +33XXXXXXXXX ou +33 X XX XX XX XX ou 0XXXXXXXXX ou 0X XX XX XX XX

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidBordereau()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setBordereau(99991); #Numéro du Bordereau à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidType()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setType(0); #Type à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidCivilite()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setCivilite("Madame"); #Civilité à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }
   
    public function testInvalidProcuration()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setProcuration("TestProcu"); #Nom à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }


}

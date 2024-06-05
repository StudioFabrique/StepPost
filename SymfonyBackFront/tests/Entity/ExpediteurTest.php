<?php

namespace App\Tests\unit;

use App\Entity\Expediteur;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExpediteurTest extends KernelTestCase
{

    public function getEntity() : Expediteur
    {
        return(new Expediteur())->setNom('Test')
                                ->setEmail("tests@test.fr")
                                ->setRoles(['ROLE_ADMIN'])
                                ->setClient(Null)
                                ->setPrenom("test")
                                ->setAdresse("34 rue")
                                ->setCodePostal("64000")
                                ->setVille("Pau")
                                ->setTelephone("0695252093")
                                ->setPassword("Enutro2003@")
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

    #Fonctions permettant de faire varier les différents composants d'expéditeur afin de tester tout les potentiels problèmes (Voir Expediteur.php pour les paramètres des composants)
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
}

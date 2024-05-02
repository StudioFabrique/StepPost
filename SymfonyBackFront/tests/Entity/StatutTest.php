<?php

namespace App\Tests;

use App\Entity\Statut;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StatutTestPhpTest extends KernelTestCase
{
    public function getEntity(): Statut
    {
        return(new Statut())->setEtat('Test')
                                ->setStatutCode("12");
    }

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        
        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors);
    }

    public function testInvalidEtat():void
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setEtat("Courrier perdu"); #Etat à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }

    public function testInvalidStatutCode():void
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur->setStatutCode("123"); #Statut du code à faire varier 

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors); #Nombre d'erreur attendu
    }
}

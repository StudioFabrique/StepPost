<?php

namespace App\Tests;

use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClientTest extends KernelTestCase
{
    public function getEntity() : Client
    {
        return(new Client())->setRaisonSociale("Raison");
    }

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        
        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors);
    }

    public function testInvalidSocialRaison()
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        $expediteur ->setRaisonSociale("TestRaison"); #Raison sociale à faire varier

        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors);
    }
}

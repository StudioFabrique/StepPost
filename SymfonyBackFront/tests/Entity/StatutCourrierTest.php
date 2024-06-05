<?php

namespace App\Tests;

use App\Entity\StatutCourrier;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StatutCourrierTest extends KernelTestCase
{
    public function getEntity(): StatutCourrier
    {
        return(new StatutCourrier())->setDate(new \DateTimeImmutable());
    }  

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $expediteur = $this->getEntity();
        
        $errors = $container->get('validator')->validate($expediteur);

        $this->assertCount(0, $errors);
    }

}

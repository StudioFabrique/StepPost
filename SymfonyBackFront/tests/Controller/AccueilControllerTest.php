<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class AccueilControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        
        $client = static::createClient();

    $crawler = $client->request('GET', '/');

    // Vérifier si la redirection est correcte
    $this->assertResponseRedirects('http://localhost/accueil/');

    // Suivre la redirection et tester la page de destination
    $crawler = $client->followRedirect();

    // Maintenant, vérifiez si la réponse est réussie sur la nouvelle page
    $this->assertResponseIsSuccessful();
        // $this->assertSelectorTextContains('h1', 'Liste des courriers');
    }
}
<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Expediteur;
use App\Entity\RaisonSociale;
use App\Entity\UserStep;
use App\Repository\ClientRepository;
use App\Repository\RaisonSocialeRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private RaisonSocialeRepository $raisonSocialeRepository;
    private ClientRepository $clientRepository;
    private UserPasswordHasherInterface $userPasswordHasherInterface;

    public function __construct(RaisonSocialeRepository $raisonSocialeRepository, ClientRepository $clientRepository, UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->raisonSocialeRepository = $raisonSocialeRepository;
        $this->clientRepository = $clientRepository;
        $this->userPasswordHasherInterface = $userPasswordHasherInterface;
    }

    public function load(ObjectManager $manager): void
    {
        // Génération des fixtures pour la classe UserStep en ROLE_ADMIN afin d'accéder à l'application
        $passwordHash = '$2y$13$hV/yxvVA.ReGMr6mUt7JQO3bTtKThUvKvs2XD3Zzh6HAJBZMI3gom'; // mot de passe = test
        $user = new UserStep();
        $user
            ->setFonction('Développeur')
            ->setEmail('test@step.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($passwordHash)
            ->setNom('toto');
        $manager->persist($user);
        $manager->flush();

        // Génération des fixtures pour la classe Raison Sociale
        for ($raisonSocialeId = 1; $raisonSocialeId < 3; $raisonSocialeId++) {
            $raisonSociale = new RaisonSociale();
            $raisonSociale->setNom('Entreprise' . $raisonSocialeId);
            $manager->persist($raisonSociale);
            $manager->flush();
        }

        /* Génération des fixtures pour la classe Client ayant comme raison sociale Entreprise.1 (à condition 
        que la base de données soit nouvelle ou ayant eu ses incréments de réinitialisés) */

        $client = new Client();
        $client->setRaisonSociale($this->raisonSocialeRepository->find(1));
        $manager->persist($client);
        $manager->flush();

        $client2 = new Client();
        $client2->setRaisonSociale($this->raisonSocialeRepository->find(2));
        $manager->persist($client2);
        $manager->flush();

        // Génération des fixtures pour la classe Expediteur en fonction des clients

        $plainPassword = 'Abcd@1234';
        $expediteur = new Expediteur();
        $hashedPassword = $this->userPasswordHasherInterface->hashPassword($expediteur, $plainPassword);
        $expediteur
            ->setAdresse('10 rue des totos')
            ->setCodePostal('64000')
            ->setClient($this->clientRepository->find(1))
            ->setEmail('toto@toto.fr')
            ->setPassword($hashedPassword)
            ->setNom('toto')
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($expediteur);
        $manager->flush();

        $expediteur2 = new Expediteur();
        $hashedPassword = $this->userPasswordHasherInterface->hashPassword($expediteur2, $plainPassword);
        $expediteur2
            ->setAdresse('20 rue des tatas')
            ->setCodePostal('64000')
            ->setClient($this->clientRepository->find(2))
            ->setEmail('tata@tata.fr')
            ->setPassword($hashedPassword)
            ->setNom('tata')
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($expediteur2);
        $manager->flush();
    }
}

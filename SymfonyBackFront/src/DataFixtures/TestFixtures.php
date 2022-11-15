<?php

namespace App\DataFixtures;

use App\Entity\Courrier;
use App\Entity\Expediteur;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Génération de l'administrateur (ROLE_SUPERADMIN) :
        $admin = (new User())
            ->setEmail('test@test.fr')
            ->setNom('test')
            ->setRoles(['ROLE_ADMIN', 'ROLE_SUPERADMIN'])
            ->setFonction('Développeur')
            ->setPassword('$2y$13$FXON7E987uD.ohgj2S4nzOBwfUK//1Jihjfm8FNx3zF7lAgtkak9a')
            ->setCreatedAt(new DateTime('now'))
            ->setUpdatedAt(new DateTime('now'));
        $manager->persist($admin);

        // Génération de l'expéditeur :

        $expediteur = (new Expediteur())
            ->setEmail('martin.dhollande@outlook.fr')
            ->setNom('Dhollande')
            ->setPrenom('Martin')
            ->setRoles(['ROLE_ADMIN', 'ROLE_SUPERADMIN'])
            ->setAdresse('1, rue du test')
            ->setCodePostal('64000')
            ->setVille('Pau')
            ->setTelephone('0768472214')
            ->setPassword('password')
            ->setCreatedAt(new DateTime('now'))
            ->setUpdatedAt(new DateTime('now'));
        $manager->persist($expediteur);

        // création des courriers :

        for ($i = 1; $i < 100; $i++) {
            $courrier = (new Courrier())
                ->setType(0)
                ->setBordereau(10 . $i)
                ->setCivilite('Monsieur')
                ->setNom('nom' . $i)
                ->setPrenom('prenom' . $i)
                ->setAdresse('adresse')
                ->setCodePostal('64000')
                ->setVille('Pau')
                ->setTelephone('0607060706')
                ->setExpediteur($expediteur);
            $manager->persist($courrier);
        }

        // création des statuts



        $manager->flush();
    }
}

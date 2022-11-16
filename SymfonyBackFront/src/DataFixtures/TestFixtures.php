<?php

namespace App\DataFixtures;

use App\Entity\Courrier;
use App\Entity\Expediteur;
use App\Entity\Facteur;
use App\Entity\Statut;
use App\Entity\StatutCourrier;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des statuts :

        $statut1 = (new Statut())
            ->setStatutCode(1)
            ->setEtat('en attente');
        $manager->persist($statut1);
        $statut2 = (new Statut())
            ->setStatutCode(2)
            ->setEtat('pris en charge');
        $manager->persist($statut2);

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

        // Création du facteur :

        $facteur = (new Facteur())
            ->setEmail('facteurtest@test.fr')
            ->setNom('facteur testeur')
            ->setPassword('1234')
            ->setCreatedAt(new DateTime('2017-01-01'))
            ->setUpdatedAt(new DateTime('now'));
        $manager->persist($facteur);

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

        // création des courriers + création des statuts :

        $date = new DateTime('2018-01-01');
        for ($courrierActuel = 1; $courrierActuel < 100; $courrierActuel++) {
            $statutCourrier1 = (new StatutCourrier)
                ->setStatut($statut1)
                ->setDate($date)
                ->setFacteur($facteur);
            $statutCourrier2 = (new StatutCourrier)
                ->setStatut($statut2)
                ->setDate(date_modify($date, '+1 day'))
                ->setFacteur($facteur);
            $courrier = (new Courrier())
                ->setType(0)
                ->setBordereau(10 . $courrierActuel)
                ->setCivilite('Monsieur')
                ->setNom('nom' . $courrierActuel)
                ->setPrenom('prenom' . $courrierActuel)
                ->setAdresse('adresse')
                ->setCodePostal('64000')
                ->setVille('Pau')
                ->setTelephone('0607060706')
                ->setExpediteur($expediteur)
                ->addStatutsCourrier($statutCourrier1)
                ->addStatutsCourrier($statutCourrier2);
            $manager->persist($statutCourrier1);
            $manager->persist($statutCourrier2);
            $manager->persist($courrier);
        }


        $manager->flush();
    }
}

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
        $statut3 = (new Statut())
            ->setStatutCode(3)
            ->setEtat('avisé');
        $manager->persist($statut3);
        $statut4 = (new Statut())
            ->setStatutCode(4)
            ->setEtat('mis en instance');
        $manager->persist($statut4);
        $statut5 = (new Statut())
            ->setStatutCode(5)
            ->setEtat('distribué');
        $manager->persist($statut5);
        $statut6 = (new Statut())
            ->setStatutCode(6)
            ->setEtat('NPAI');
        $manager->persist($statut6);
        $statut7 = (new Statut())
            ->setStatutCode(7)
            ->setEtat('non réclamé');
        $manager->persist($statut7);
        $statut8 = (new Statut())
            ->setStatutCode(8)
            ->setEtat('erreur de libellé');
        $manager->persist($statut8);

        // Génération de l'administrateur (ROLE_SUPERADMIN) :
        $admin = (new User())
            ->setEmail('test@test.fr')
            ->setNom('test')
            ->setRoles(['ROLE_ADMIN', 'ROLE_GESTION' ,'ROLE_SUPERADMIN'])
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

        for ($courrierActuel = 1; $courrierActuel < 100; $courrierActuel++) {
            $date1 = new DateTime('2018-01-01');
            $date2 = new DateTime('2018-01-02');
            $statutCourrier1 = (new StatutCourrier)
                ->setStatut($statut3)
                ->setDate($date1)
                ->setFacteur($facteur);
            $manager->persist($statutCourrier1);
            $statutCourrier2 = (new StatutCourrier)
                ->setStatut($statut2)
                ->setDate($date2)
                ->setFacteur($facteur);
            $manager->persist($statutCourrier2);
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
                ->addStatutsCourrier($statutCourrier2)
                ->setProcuration($courrierActuel < 10 || $courrierActuel > 80 ? "Antoine Dupont" : null);
            $manager->persist($courrier);
        }


        $manager->flush();
    }
}

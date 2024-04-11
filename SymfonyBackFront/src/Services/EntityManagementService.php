<?php

namespace App\Services;

use App\Entity\Client;
use App\Entity\Expediteur;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\ExpediteurRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Service pour créer, modifier et supprimer des entités.
 */
class EntityManagementService
{
    private $passwordHasher, $userRepo, $dateMaker, $clientRepo, $expediteurRepo, $formattingService, $validator;

    /**
     * Constructeur
     */
    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepo,
        DateMaker $dateMaker,
        ClientRepository $clientRepo,
        ExpediteurRepository $expediteurRepo,
        FormattingService $formattingService,
        ValidatorInterface $validator
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->userRepo = $userRepo;
        $this->dateMaker = $dateMaker;
        $this->clientRepo = $clientRepo;
        $this->expediteurRepo = $expediteurRepo;
        $this->formattingService = $formattingService;
        $this->validator = $validator;
    }

    /**
     * Créer une raison sociale avec un nom
     */
    public function MakeRaisonSociale($nom)
    {
        $raisonSocialeExist = false;
        foreach ($this->clientRepo->findAll() as $client) {
            if ($client->getRaisonSociale() == $nom) $raisonSocialeExist = true;
        }
        if (!$raisonSocialeExist) {
            $raison = (new Client())->setRaisonSociale($nom);
            try {
                $this->clientRepo->add($raison, true);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Créer un utilisateur à partir d'un formulaire
     */
    public function MakeUser(Form $formData, bool $isMairie = null): User
    {
        $admin = $formData->getData();
        $pass = $formData->get('password')->getData();
        $isPassValid = preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[-!@#$%^&*])(?=.{8,})/", $pass);
        if(!$isPassValid) {
            throw new Exception("");
        }
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            $pass
        );
        $admin->setPassword($hashedPassword);
        $admin->setCreatedAt($this->dateMaker->createFromDateTimeZone());
        $admin->setUpdatedAt($this->dateMaker->createFromDateTimeZone());
        $admin->setRoles(!$isMairie ? ['ROLE_ADMIN', 'ROLE_GESTION'] : ['ROLE_ADMIN', 'ROLE_MAIRIE']);
        
        $this->userRepo->add($admin);
        return $admin;
    }

    /**
     * Créer un expéditeur à partir d'un formulaire
     */
    public function MakeExpediteur(Form $form): array
    {
        $serializer = new Serializer([(new ObjectNormalizer())]);
        $expediteur = $form->getData();
        $expediteur->setClient(null);
        $expediteurArray = $this->formattingService
            ->stringToLowerObject(
                $expediteur,
                Expediteur::class,
                array('client', 'createdAt', 'updatedAt'),
                true
            );


        $expediteur = $serializer->denormalize($expediteurArray, Expediteur::class);
        $expediteur
            ->setCreatedAt($this->dateMaker->createFromDateTimeZone())
            ->setUpdatedAt($this->dateMaker->createFromDateTimeZone())
            ->setRoles(['ROLE_INACTIF'])->setPassword(' ');
        $this->expediteurRepo
            ->add($expediteur->setClient($form->get("addClient")->getData()), true);

        return $expediteurArray;
    }

    /**
     * Modifie les données d'un admin à partir d'un formulaire
     */
    public function EditUser(Form $formData): User
    {
        $admin = $formData->getData();
        $admin->setUpdatedAt($this->dateMaker->createFromDateTimeZone());
        $this->userRepo->add($admin);
        return $admin;
    }

    /**
     * Modifie le mot de passe d'un admin à partir d'un formulaire
     */
    public function EditPasswordUser(User $admin, string $password): ?User
    {
        $isPassValid = preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[-!@#$%^&*])(?=.{8,})/", $password);
        if(!$isPassValid) {
            throw new Exception(code:3);
        }
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            $password
        );
        $admin->setPassword($hashedPassword);
        $this->userRepo->add($admin);
        return $admin;
    }
}

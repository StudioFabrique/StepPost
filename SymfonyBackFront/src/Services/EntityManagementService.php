<?php

namespace App\Services;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EntityManagementService
{
    private $passwordHasher, $userRepo, $dateMaker, $clientRepo;
    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepo, DateMaker $dateMaker, ClientRepository $clientRepo)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepo = $userRepo;
        $this->dateMaker = $dateMaker;
        $this->clientRepo = $clientRepo;
    }

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

    public function MakeUser(Form $formData, bool $isMairie = null): User
    {
        $admin = $formData->getData();
        $pass = $formData->get('password')->getData();
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

    public function EditUser(Form $formData, bool $isSuperAdmin): User
    {
        $admin = $formData->getData();
        $admin->setRoles($isSuperAdmin ? ['ROLE_ADMIN', 'ROLE_SUPERADMIN'] : ['ROLE_ADMIN']);
        $admin->setUpdatedAt($this->dateMaker->createFromDateTimeZone());
        $this->userRepo->add($admin);
        return $admin;
    }

    public function EditPasswordUser(Form $formData): User
    {
        $admin = $formData->getData();
        $pass = $formData->get('password')->getData();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            $pass
        );
        $admin->setPassword($hashedPassword);
        $this->userRepo->add($admin);
        return $admin;
    }
}

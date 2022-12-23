<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\Form;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EntityManagementService
{
    private $passwordHasher, $userRepo, $dateMaker;
    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepo, DateMaker $dateMaker)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepo = $userRepo;
        $this->dateMaker = $dateMaker;
    }

    public function Make()
    {
    }


    public function Edit()
    {
    }

    public function Delete()
    {
    }

    public function MakeUser(Form $formData): User
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
        $admin->setRoles(['ROLE_ADMIN']);
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

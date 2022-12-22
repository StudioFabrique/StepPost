<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use DateTimeZone;
use Symfony\Component\Form\Form;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EntityManagementService
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, UserRepository $userRepo)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepo = $userRepo;
    }

    public function Make()
    {
    }

    public function MakeUser(Form $formData): User
    {
        $timezone = new DateTimeZone('UTC');

        $admin = $formData->getData();
        $pass = $formData->get('password')->getData();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            $pass
        );
        $admin->setPassword($hashedPassword);
        $admin->setCreatedAt(new DateTime('now', $timezone));
        $admin->setUpdatedAt(new DateTime('now', $timezone));
        $admin->setRoles(['ROLE_ADMIN']);
        $this->userRepo->add($admin);
        return $admin;
    }

    public function Edit()
    {
    }

    public function Delete()
    {
    }
}

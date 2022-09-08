<?php

namespace App\Entity;

use App\Repository\UserStepRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserStepRepository::class)]
class UserStep extends User
{

    #[ORM\Column(type: 'string', length: 255)]
    private $fonction;


    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }
}

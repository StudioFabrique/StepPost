<?php

namespace App\Entity;

use App\Repository\FacteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FacteurRepository::class)]
class Facteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Regex(pattern:"/^[a-zA-Z0-9._-]+@[a-zA-Z]+\.[a-zA-Z]{2,}$/")]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[Assert\Regex(pattern:"/^[a-zA-Z\s\-']+$/", message:"Le nom ne peut contenir que des lettres, des tirets (-) ou des apostrophes (').")]
    #[Assert\Length(min:2, max:255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(name: 'createdAt', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updatedAt', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'facteur', targetEntity: StatutCourrier::class)]
    private Collection $statutsCourrier;

    public function __construct()
    {
        $this->statutsCourrier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, StatutCourrier>
     */
    public function getStatutsCourrier(): Collection
    {
        return $this->statutsCourrier;
    }

    public function addStatutsCourrier(StatutCourrier $statutsCourrier): self
    {
        if (!$this->statutsCourrier->contains($statutsCourrier)) {
            $this->statutsCourrier->add($statutsCourrier);
            $statutsCourrier->setFacteur($this);
        }

        return $this;
    }

    public function removeStatutsCourrier(StatutCourrier $statutsCourrier): self
    {
        if ($this->statutsCourrier->removeElement($statutsCourrier)) {
            // set the owning side to null (unless already changed)
            if ($statutsCourrier->getFacteur() === $this) {
                $statutsCourrier->setFacteur(null);
            }
        }

        return $this;
    }
}

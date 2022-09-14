<?php

namespace App\Entity;

use App\Repository\FacteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FacteurRepository::class)]
class Facteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $CreatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $UpdatedAt = null;

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
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeInterface $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $UpdatedAt): self
    {
        $this->UpdatedAt = $UpdatedAt;

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

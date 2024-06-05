<?php

namespace App\Entity;

use App\Repository\StatutCourrierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StatutCourrierRepository::class)]
#[ORM\Table(name: 'statutcourrier')]
class StatutCourrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'statutsCourrier')]
    private ?Statut $statut = null;

    #[ORM\ManyToOne(inversedBy: 'statutsCourrier')]
    private ?Courrier $courrier = null;

    #[ORM\ManyToOne(inversedBy: 'statutsCourrier')]
    private ?Facteur $facteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->statut;
    }

    public function setStatut(?Statut $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCourrier(): ?Courrier
    {
        return $this->courrier;
    }

    public function setCourrier(?Courrier $courrier): self
    {
        $this->courrier = $courrier;

        return $this;
    }

    public function getFacteur(): ?Facteur
    {
        return $this->facteur;
    }

    public function setFacteur(?Facteur $facteur): self
    {
        $this->facteur = $facteur;

        return $this;
    }
}

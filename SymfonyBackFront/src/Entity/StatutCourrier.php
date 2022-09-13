<?php

namespace App\Entity;

use App\Repository\StatutCourrierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatutCourrierRepository::class)]
class StatutCourrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $idFacteur;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    #[ORM\ManyToOne(targetEntity: Courrier::class, inversedBy: 'statutscourrier')]
    private $courrier;

    #[ORM\ManyToOne(targetEntity: Statut::class, inversedBy: 'statutscourrier')]
    private $statut;

    public function getId(): ?int
    {
        return $this->idFacteur;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getStatut(): ?Statut
    {
        return $this->statut;
    }

    public function setStatut(?Statut $statut): self
    {
        $this->statut = $statut;

        return $this;
    }
}

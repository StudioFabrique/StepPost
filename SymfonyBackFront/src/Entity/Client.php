<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Expediteur::class, orphanRemoval: true)]
    private $expediteurs;

    #[ORM\ManyToOne(targetEntity: RaisonSociale::class, inversedBy: 'clients')]
    #[ORM\JoinColumn(nullable: true)]
    private $raisonSociale;

    public function __construct()
    {
        $this->expediteurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Expediteur>
     */
    public function getExpediteurs(): Collection
    {
        return $this->expediteurs;
    }

    public function addExpediteur(Expediteur $expediteur): self
    {
        if (!$this->expediteurs->contains($expediteur)) {
            $this->expediteurs[] = $expediteur;
            $expediteur->setClient($this);
        }

        return $this;
    }

    public function removeExpediteur(Expediteur $expediteur): self
    {
        if ($this->expediteurs->removeElement($expediteur)) {
            // set the owning side to null (unless already changed)
            if ($expediteur->getClient() === $this) {
                $expediteur->setClient(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->id;
    }

    public function getRaisonSociale(): ?RaisonSociale
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(?RaisonSociale $raisonSociale): self
    {
        $this->raisonSociale = $raisonSociale;

        return $this;
    }
}

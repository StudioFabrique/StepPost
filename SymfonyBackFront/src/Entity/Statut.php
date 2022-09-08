<?php

namespace App\Entity;

use App\Repository\StatutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatutRepository::class)]
class Statut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    private $etat;

    #[ORM\OneToMany(mappedBy: 'statut', targetEntity: Statutcourrier::class)]
    private $statutscourrier;

    public function __construct()
    {
        $this->statutscourrier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Statutcourrier>
     */
    public function getStatutscourrier(): Collection
    {
        return $this->statutscourrier;
    }

    public function addStatutscourrier(Statutcourrier $statutscourrier): self
    {
        if (!$this->statutscourrier->contains($statutscourrier)) {
            $this->statutscourrier[] = $statutscourrier;
            $statutscourrier->setStatut($this);
        }

        return $this;
    }

    public function removeStatutscourrier(Statutcourrier $statutscourrier): self
    {
        if ($this->statutscourrier->removeElement($statutscourrier)) {
            // set the owning side to null (unless already changed)
            if ($statutscourrier->getStatut() === $this) {
                $statutscourrier->setStatut(null);
            }
        }

        return $this;
    }
}

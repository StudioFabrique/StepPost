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

    #[ORM\OneToMany(mappedBy: 'statut', targetEntity: StatutCourrier::class)]
    private $statutsCourrier;

    public function __construct()
    {
        $this->statutsCourrier = new ArrayCollection();
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
     * @return Collection<int, StatutCourrier>
     */
    public function getStatutsCourrier(): Collection
    {
        return $this->statutscourrier;
    }

    public function addStatutsCourrier(StatutCourrier $statutsCourrier): self
    {
        if (!$this->statutscourrier->contains($statutsCourrier)) {
            $this->statutscourrier[] = $statutsCourrier;
            $statutsCourrier->setStatut($this);
        }

        return $this;
    }

    public function removeStatutsCourrier(StatutCourrier $statutsCourrier): self
    {
        if ($this->statutscourrier->removeElement($statutsCourrier)) {
            // set the owning side to null (unless already changed)
            if ($statutsCourrier->getStatut() === $this) {
                $statutsCourrier->setStatut(null);
            }
        }

        return $this;
    }
}

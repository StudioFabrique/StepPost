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
     * @return Collection<int, Statutscourrier>
     */
    public function getStatutsCourrier(): Collection
    {
        return $this->statutsCourrier;
    }

    public function addStatutCourrier(StatutCourrier $statutCourrier): self
    {
        if (!$this->statutscouriers->contains($statutCourrier)) {
            $this->statutscouriers[] = $statutCourrier;
            $statutCourrier->setStatut($this);
        }

        return $this;
    }

    public function removeStatutCourrier(StatutCourrier $statutCourrier): self
    {
        if ($this->statutsCourrier->removeElement($statutCourrier)) {
            // set the owning side to null (unless already changed)
            if ($statutCourrier->getStatut() === $this) {
                $statutCourrier->setStatut(null);
            }
        }

        return $this;
    }

    //to string
    public function __toString()
    {
        return $this->etat;
    }
}

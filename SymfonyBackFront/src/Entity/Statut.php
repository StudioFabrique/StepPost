<?php

namespace App\Entity;

use App\Repository\StatutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StatutRepository::class)]
class Statut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;


    #[Assert\Regex(pattern:"/^[a-zA-Z\s]*$/", message:"L'état est composé de lettres seulement")]
    #[Assert\Length(min:2, max:50)]
    #[ORM\Column(type: 'string', length: 50)]
    private $etat;

    #[ORM\OneToMany(mappedBy: 'statut', targetEntity: StatutCourrier::class)]
    private Collection $statutsCourrier;

    #[Assert\Regex(pattern:"/^[0-9]*$/", message:"Le code ne peut contenir des chiffres")]
    #[ORM\Column(name: 'statutCode', nullable: false)]
    private ?int $statutCode = null;

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

    public function __toString()
    {
        return $this->etat;
    }

    /**
     * @return Collection<int, StatutCourrier>
     */
    public function getStatutsCourrier(): Collection
    {
        return $this->statutsCourrier;
    }

    public function addStatutCourrier(StatutCourrier $statutCourrier): self
    {
        if (!$this->statutsCourrier->contains($statutCourrier)) {
            $this->statutsCourrier->add($statutCourrier);
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

    public function getStatutCode(): ?int
    {
        return $this->statutCode;
    }

    public function setStatutCode(?int $statutCode): self
    {
        $this->statutCode = $statutCode;

        return $this;
    }
}

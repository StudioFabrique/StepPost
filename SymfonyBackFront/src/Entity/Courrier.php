<?php

namespace App\Entity;

use App\Repository\CourrierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourrierRepository::class)]
class Courrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer',)]
    private $type;

    #[ORM\Column(type: 'integer', unique: true)]
    private $bordereau;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'string', length: 50)]
    private $civilite;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $prenom;

    #[ORM\Column(type: 'string', length: 255)]
    private $adresse;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $complement;

    #[ORM\Column(name: 'codePostal', type: 'string', length: 255)]
    private $codePostal;

    #[ORM\Column(type: 'string', length: 255)]
    private $ville;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $telephone;

    #[ORM\OneToMany(mappedBy: 'courrier', targetEntity: StatutCourrier::class)]
    private $statutscourrier;

    #[ORM\ManyToOne(targetEntity: Expediteur::class, inversedBy: 'courriers')]
    private $expediteur;

    public function __construct()
    {
        $this->statutscourrier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getBordereau(): ?int
    {
        return $this->bordereau;
    }

    public function setBordereau(int $borderau): self
    {
        $this->borderau = $borderau;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(string $civilite): self
    {
        $this->civiliter = $civilite;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adress = $adresse;

        return $this;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(?string $complement): self
    {
        $this->complement = $complement;

        return $this;
    }

    public function getCodePostal(): ?int
    {
        return $this->codePostal;
    }

    public function setCodePostal(int $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function __toString()
    {
        return $this->id;
    }

    /**
     * @return Collection<int, StatutCourrier>
     */
    public function getStatutscourrier(): Collection
    {
        return $this->statutscourrier;
    }

    public function addStatutscourrier(StatutCourrier $statutscourrier): self
    {
        if (!$this->statutscourrier->contains($statutscourrier)) {
            $this->statutscourrier[] = $statutscourrier;
            $statutscourrier->setCourrier($this);
        }

        return $this;
    }

    public function removeStatutscourrier(StatutCourrier $statutscourrier): self
    {
        if ($this->statutscourrier->removeElement($statutscourrier)) {
            // set the owning side to null (unless already changed)
            if ($statutscourrier->getCourrier() === $this) {
                $statutscourrier->setCourrier(null);
            }
        }

        return $this;
    }

    public function getExpediteur(): ?Expediteur
    {
        return $this->expediteur;
    }

    public function setExpediteur(?Expediteur $expediteur): self
    {
        $this->expediteur = $expediteur;

        return $this;
    }
}

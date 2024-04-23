<?php

namespace App\Entity;

use App\Repository\CourrierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CourrierRepository::class)]
class Courrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Assert\Regex(pattern:"/^(0|1)$/")]
    #[Assert\NotBlank()]
    #[ORM\Column(type: 'integer',)]
    private $type;

    #[Assert\Regex(pattern: "/^[1-9]\d{4}$/")]
    #[Assert\NotBlank()]
    #[ORM\Column(type: 'integer', unique: true)]
    private $bordereau;

    #[Assert\Regex(pattern:"/^[a-zA-Z\s\-']+$/", message:"Le nom ne peut contenir que des lettres, des tirets (-) ou des apostrophes (').")]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:255)]
    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[Assert\Regex(pattern:"/^[A-Za-z]/")]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:255)]
    #[ORM\Column(type: 'string', length: 50)]
    private $civilite;

    #[Assert\Regex(pattern:"/^[a-zA-Z\s\-']+$/", message:"Le prénom ne peut contenir que des lettres, des tirets (-) ou des apostrophes (').")]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:255)]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $prenom;

    #[Assert\Regex(pattern:"/^[a-zA-Z0-9\s\&,.#-']*$/", message:"L'adresse ne peut contenir que des lettres, certains caractères spéciaux (&,.#-') ou des chiffres.")]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:255)]
    #[ORM\Column(type: 'string', length: 255)]
    private $adresse;

    #[Assert\Regex(pattern:"/^[a-zA-Z0-9\s\&,.#-']*$/", message:"Le complément d'adresse ne peut contenir que des lettres, certains caractères spéciaux (&,.#-') ou des chiffres.")]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $complement;

    #[Assert\Regex(pattern:"/^(\d{5})?$/", message:"Le code postal doit être composé de 5 chiffres.")]
    #[Assert\NotBlank()]
    #[ORM\Column(name: 'codePostal', type: 'string', length: 255)]
    private $codePostal;

    #[Assert\Regex(pattern:"/^[A-Za-zÀ-ÿ\s\- ]+$/", message:"La ville ne contient aucun numéro ni caractère spécial sauf le tiret (-).")]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:255)]
    #[ORM\Column(type: 'string', length: 255)]
    private $ville;

    #[Assert\Regex(pattern: "/^(?:(?:\+33\s?)|0)(\d\s?){9}$/",message: "Le numéro de téléphone doit être de la forme: +33 X XX XX XX XX ou +33XXXXXXXXX ou 0X XX XX XX XX ou 0XXXXXXXXX")]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 10, max: 20)]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $telephone;

    #[ORM\ManyToOne(targetEntity: Expediteur::class, inversedBy: 'courriers')]
    private $expediteur;

    #[ORM\OneToMany(mappedBy: 'courrier', targetEntity: StatutCourrier::class)]
    private Collection $statutsCourrier;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $signature = null;

    #[Assert\Regex(pattern:"/[a-zA-Z0-9]/")]
    #[Assert\Length(min: 4, max: 20)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $procuration = null;

    public function __construct()
    {
        $this->statutsCourrier = new ArrayCollection();
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

    public function setBordereau(int $bordereau): self
    {
        $this->bordereau = $bordereau;

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
        $this->civilite = $civilite;

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
        $this->adresse = $adresse;

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

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
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

    public function getExpediteur(): ?Expediteur
    {
        return $this->expediteur;
    }

    public function setExpediteur(?Expediteur $expediteur): self
    {
        $this->expediteur = $expediteur;

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
            $statutsCourrier->setCourrier($this);
        }

        return $this;
    }

    public function removeStatutsCourrier(StatutCourrier $statutsCourrier): self
    {
        if ($this->statutsCourrier->removeElement($statutsCourrier)) {
            // set the owning side to null (unless already changed)
            if ($statutsCourrier->getCourrier() === $this) {
                $statutsCourrier->setCourrier(null);
            }
        }

        return $this;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function setSignature($signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    public function getProcuration(): ?string
    {
        return $this->procuration;
    }

    public function setProcuration(?string $procuration): self
    {
        $this->procuration = $procuration;

        return $this;
    }
}

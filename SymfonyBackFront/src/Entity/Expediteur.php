<?php

namespace App\Entity;

use App\Repository\ExpediteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExpediteurRepository::class)]
class Expediteur implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Assert\NotBlank()]
    #[Assert\Regex(pattern:"/^[a-zA-Z0-9._-]+@[a-zA-Z]+\.[a-zA-Z]{2,}$/")]
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[Assert\NotBlank()]
    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[Assert\Regex(pattern:"/^[a-zA-Z\s\-']+$/", message:"Le nom ne peut contenir que des lettres, des tirets (-) ou des apostrophes (').")]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:255)]
    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

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
    #[ORM\Column(type: 'string', length: 255)]
    private $telephone;

    #[Assert\Regex(pattern:"/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/", message:"Le mot de passe doit faire 8 caractères minimum et contenir une lettre minuscule + une majuscule, un caractère spéciale et un numéro")]
    #[Assert\NotBlank()]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $password;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'updatedAt')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'createdAt')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'expediteur', targetEntity: Destinataire::class)]
    private $destinataires;

    #[ORM\OneToMany(mappedBy: 'expediteur', targetEntity: Courrier::class)]
    private $courriers;

    #[ORM\ManyToOne(inversedBy: 'expediteurs')]
    private ?Client $client = null;

    public function __construct()
    {
        $this->destinataires = new ArrayCollection();
        $this->courriers = new ArrayCollection();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection<int, Destinataire>
     */
    public function getDestinataires(): Collection
    {
        return $this->destinataires;
    }

    public function addDestinataire(Destinataire $destinataire): self
    {
        if (!$this->destinataires->contains($destinataire)) {
            $this->destinataires[] = $destinataire;
            $destinataire->setExpediteur($this);
        }

        return $this;
    }

    public function removeDestinataire(Destinataire $destinataire): self
    {
        if ($this->destinataires->removeElement($destinataire)) {
            // set the owning side to null (unless already changed)
            if ($destinataire->getExpediteur() === $this) {
                $destinataire->setExpediteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Courrier>
     */
    public function getCourriers(): Collection
    {
        return $this->courriers;
    }

    public function addCourrier(Courrier $courrier): self
    {
        if (!$this->courriers->contains($courrier)) {
            $this->courriers[] = $courrier;
            $courrier->setExpediteur($this);
        }

        return $this;
    }

    public function removeCourrier(Courrier $courrier): self
    {
        if ($this->courriers->removeElement($courrier)) {
            // set the owning side to null (unless already changed)
            if ($courrier->getExpediteur() === $this) {
                $courrier->setExpediteur(null);
            }
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

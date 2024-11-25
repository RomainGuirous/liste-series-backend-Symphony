<?php

namespace App\Entity;

use App\Repository\ProgramRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

//va concerner les données injectées en BDD
use Doctrine\ORM\Mapping as ORM;

//pour pour pouvoir créer #[UniqueEntity('prop')] (qui empeche les doublons) -> mettre au dessus de la création de classe
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//autoMapping
use Symfony\Component\Validator\Constraints as Assert;

//pour utiliser $posterFile
use Symfony\Component\HttpFoundation\File\File;

//Ici on importe le package Vich, que l’on utilisera sous l’alias “Vich”
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ProgramRepository::class)]
#[UniqueEntity('title')]
#[UniqueEntity('poster')]
//permet d'automatiser le composant Validator pour les attributs sans validation définie, en prenant par défaut ceux dans ORM
#[Assert\EnableAutoMapping]
#[Vich\Uploadable]
class Program
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Regex(
        pattern: '/plus belle la vie/',
        match: false,
        message: 'On parle de vraies séries ici!',
    )]
    private ?string $synopsis = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poster = null;

    #mapping correspondant à config/packages/vich_uploader.yaml et un fileNameProperty qui réfère à ta propriété $poster
    #[Vich\UploadableField(mapping: 'poster_file', fileNameProperty: 'poster')]
    #[Assert\File(
        maxSize: '1M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
    )]
    private ?File $posterFile = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'programs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @var Collection<int, Season>
     */
    #[ORM\OneToMany(targetEntity: Season::class, mappedBy: 'program')]
    private Collection $seasons;

    /**
     * @var Collection<int, Actor>
     */
    #[ORM\ManyToMany(targetEntity: Actor::class, mappedBy: 'programs')]
    private Collection $actors;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'slug vide')]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'programs')]
    #[Assert\NotNull(message: 'owner vide')]
    private ?User $owner = null;

    public function __construct()
    {
        $this->seasons = new ArrayCollection();
        $this->actors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): static
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    
    public function getCategory(): ?Category
    {
        return $this->category;
    }
    
    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        
        return $this;
    }
    
    /**
     * @return Collection<int, Season>
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }
    
    public function addSeason(Season $season): static
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons->add($season);
            $season->setProgram($this);
        }
        
        return $this;
    }
    
    public function removeSeason(Season $season): static
    {
        if ($this->seasons->removeElement($season)) {
            // set the owning side to null (unless already changed)
            if ($season->getProgram() === $this) {
                $season->setProgram(null);
            }
        }
        
        return $this;
    }
    
    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }
    
    public function addActor(Actor $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
            $actor->addProgram($this);
        }
        
        return $this;
    }
    
    public function removeActor(Actor $actor): static
    {
        if ($this->actors->removeElement($actor)) {
            $actor->removeProgram($this);
        }
        
        return $this;
    }
    
    public function getSlug(): ?string
    {
        return $this->slug;
    }
    
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        
        return $this;
    }
    
    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(?string $poster): static
    {
        $this->poster = $poster;

        return $this;
    }
    
    public function setPosterFile(File $image = null): Program
    {
        $this->posterFile = $image;
        if ($image) {
            $this->updatedAt = new DateTime('now');
        }

        return $this;
    }

    public function getPosterFile(): ?File
    {
        return $this->posterFile;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
